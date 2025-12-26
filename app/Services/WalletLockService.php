<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use App\Models\TournamentWalletLock;
use Illuminate\Support\Facades\DB;

class WalletLockService
{
    /**
     * Bloquer les fonds de l'organisateur quand le tournoi démarre
     */
    public function lockFundsForTournament(Tournament $tournament): void
    {
        $organizer = $tournament->organizer;
        $organizer->load('wallet');

        // Calculer le montant total des inscriptions
        $totalEntryFees = $tournament->entry_fee * $tournament->registrations()->count();

        DB::transaction(function () use ($organizer, $tournament, $totalEntryFees) {
            // Le lock existe déjà (créé par le TournamentObserver)
            $lock = TournamentWalletLock::where('tournament_id', $tournament->id)
                ->where('organizer_id', $organizer->id)
                ->firstOrFail();

            // Vérifier que le locked_amount correspond au total attendu
            if ($lock->locked_amount != $totalEntryFees) {
                throw new \Exception("Lock amount mismatch. Expected: {$totalEntryFees} MLM, Found: {$lock->locked_amount} MLM");
            }

            // Vérifier que l'organisateur a bien les fonds bloqués
            if ($organizer->wallet->blocked_balance < $totalEntryFees) {
                throw new \Exception("Insufficient blocked funds. Expected: {$totalEntryFees} MLM, Available: {$organizer->wallet->blocked_balance} MLM");
            }

            // NOTE: Le lock est déjà créé et le locked_amount est déjà incrémenté
            // à chaque inscription via processTournamentRegistration()
            // Ici on vérifie juste que tout est cohérent avant de démarrer le tournoi
        });
    }

    /**
     * Débloquer les fonds pour payer les récompenses
     */
    public function processPayouts(Tournament $tournament, array $winners): void
    {
        $lock = TournamentWalletLock::where('tournament_id', $tournament->id)
            ->where('status', 'locked')
            ->firstOrFail();

        $organizer = $tournament->organizer;
        $organizer->load('wallet');

        // Calculer le total des prix à distribuer
        $totalPrizesToPay = array_sum(array_column($winners, 'prize_amount'));

        // Vérifier si les fonds bloqués suffisent
        $shortage = $totalPrizesToPay - $lock->locked_amount;

        if ($shortage > 0) {
            // Les fonds bloqués ne suffisent pas, essayer de prendre depuis balance
            if ($organizer->wallet->balance < $shortage) {
                // Même avec la balance principale, c'est insuffisant
                $totalShortage = $shortage - $organizer->wallet->balance;

                // Envoyer email à l'organisateur
                \Mail::to($organizer)->send(
                    new \App\Mail\InsufficientFundsWarningMail(
                        $tournament,
                        $organizer,
                        $totalShortage,
                        $totalPrizesToPay,
                        $lock->locked_amount
                    )
                );

                // Logger l'événement
                \Log::warning("Insufficient funds for tournament {$tournament->id}. Shortage: {$totalShortage} MLM");

                throw new \Exception("Fonds insuffisants. Un email a été envoyé à l'organisateur. Manque: {$totalShortage} MLM");
            }

            // On a assez avec balance + blocked_balance, débiter le manque depuis balance
            $organizer->wallet->balance -= $shortage;
            $organizer->wallet->save();

            \Log::info("Deducted {$shortage} MLM from organizer's main balance for tournament {$tournament->id}");
        }

        DB::transaction(function () use ($lock, $tournament, $winners) {
            $lock->update(['status' => 'processing_payouts']);

            $totalPaidOut = 0;

            // Distribuer les récompenses
            foreach ($winners as $winner) {
                $prize = $winner['prize_amount'];
                $user = User::find($winner['user_id']);

                // Créditer le gagnant
                $balanceBefore = $user->wallet->balance;
                $user->wallet->balance += $prize;
                $user->wallet->save();

                // Transaction record
                DB::table('transactions')->insert([
                    'wallet_id' => $user->wallet->id,
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $prize,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $user->wallet->balance,
                    'reason' => 'tournament_prize',
                    'description' => "Prize for tournament: {$tournament->name}",
                    'tournament_id' => $tournament->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalPaidOut += $prize;
            }

            // Mettre à jour le montant payé
            $lock->update(['paid_out' => $totalPaidOut]);
        });
    }

    /**
     * Libérer le reste du solde bloqué
     */
    public function releaseFunds(Tournament $tournament): void
    {
        $lock = TournamentWalletLock::where('tournament_id', $tournament->id)
            ->where('status', 'processing_payouts')
            ->firstOrFail();

        $organizer = $tournament->organizer;
        $organizer->load('wallet');

        DB::transaction(function () use ($lock, $organizer, $tournament) {
            // Calculer le reste (profit de l'organisateur)
            $remainder = $lock->locked_amount - $lock->paid_out;

            // Ajouter le profit à la balance de l'organisateur
            $balanceBefore = $organizer->wallet->balance;
            $organizer->wallet->balance += $remainder;
            $organizer->wallet->save();

            // Marquer le lock comme libéré
            $lock->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            // Synchroniser le blocked_balance (va recalculer en excluant ce lock 'released')
            app(WalletService::class)->syncBlockedBalance($organizer);

            // Transaction record pour le profit
            if ($remainder > 0) {
                DB::table('transactions')->insert([
                    'wallet_id' => $organizer->wallet->id,
                    'user_id' => $organizer->id,
                    'type' => 'credit',
                    'amount' => $remainder,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $organizer->wallet->balance,
                    'reason' => 'tournament_profit',
                    'description' => "Profit du tournoi: {$tournament->name}",
                    'tournament_id' => $tournament->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Unlock funds for a tournament (simple unlock without payouts)
     */
    public function unlockFundsForTournament(Tournament $tournament): void
    {
        $lock = TournamentWalletLock::where('tournament_id', $tournament->id)
            ->where('status', 'locked')
            ->firstOrFail();

        $organizer = $tournament->organizer;
        $organizer->load('wallet');

        DB::transaction(function () use ($lock, $organizer) {
            // Libérer tous les fonds vers le solde normal
            $organizer->wallet->blocked_balance -= $lock->locked_amount;
            $organizer->wallet->balance += $lock->locked_amount;
            $organizer->wallet->save();

            // Marquer comme libéré
            $lock->update([
                'status' => 'released',
                'released_at' => now(),
            ]);
        });
    }

    /**
     * Obtenir le solde disponible (non bloqué)
     */
    public function getAvailableBalance(User $user): float
    {
        $user->load('wallet');
        return $user->wallet->balance - $user->wallet->blocked_balance;
    }
}
