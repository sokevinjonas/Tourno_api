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
            // Créer le lock
            TournamentWalletLock::create([
                'tournament_id' => $tournament->id,
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
            $lock->update(['status' => 'released']);

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
     * Obtenir le solde disponible (non bloqué)
     */
    public function getAvailableBalance(User $user): float
    {
        $user->load('wallet');
        return $user->wallet->balance - $user->wallet->blocked_balance;
    }
}
