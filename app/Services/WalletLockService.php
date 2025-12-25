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

        // Check if funds already locked
        $existingLock = TournamentWalletLock::where('tournament_id', $tournament->id)
            ->where('organizer_id', $organizer->id)
            ->exists();

        if ($existingLock) {
            throw new \Exception('Funds already locked for this tournament');
        }

        DB::transaction(function () use ($organizer, $tournament, $totalEntryFees) {
            // Créer le lock
            TournamentWalletLock::create([
                'tournament_id' => $tournament->id,
                'organizer_id' => $organizer->id,
                'wallet_id' => $organizer->wallet->id,
                'locked_amount' => $totalEntryFees,
                'status' => 'locked',
            ]);

            // Transférer les fonds de balance vers blocked_balance
            $organizer->wallet->balance -= $totalEntryFees;
            $organizer->wallet->blocked_balance += $totalEntryFees;
            $organizer->wallet->save();
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

                // Marquer le tournoi comme en attente de fonds
                $tournament->update(['status' => 'awaiting_organizer_funds']);

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

        // Mettre à jour le statut du tournoi
        $tournament->update(['status' => 'payout_pending']);
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
            // Calculer le reste
            $remainder = $lock->locked_amount - $lock->paid_out;

            // Libérer vers le solde normal
            $organizer->wallet->blocked_balance -= $lock->locked_amount;
            $organizer->wallet->balance += $remainder;
            $organizer->wallet->save();

            // Marquer comme libéré
            $lock->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            // Mettre à jour le statut du tournoi
            $tournament->update(['status' => 'payouts_completed']);

            // Transaction record pour la plateforme
            if ($remainder > 0) {
                DB::table('transactions')->insert([
                    'wallet_id' => $organizer->wallet->id,
                    'user_id' => $organizer->id,
                    'type' => 'credit',
                    'amount' => $remainder,
                    'balance_before' => $organizer->wallet->balance - $remainder,
                    'balance_after' => $organizer->wallet->balance,
                    'reason' => 'admin_adjustment',
                    'description' => "Tournament funds released: {$tournament->name}",
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
