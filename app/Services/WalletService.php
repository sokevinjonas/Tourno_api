<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Get user wallet
     */
    public function getUserWallet(User $user): ?Wallet
    {
        return $user->wallet;
    }

    /**
     * Get wallet balance
     */
    public function getBalance(User $user): float
    {
        $wallet = $this->getUserWallet($user);
        return $wallet ? (float) $wallet->balance : 0.00;
    }

    /**
     * Credit wallet
     */
    public function credit(
        User $user,
        float $amount,
        string $reason,
        string $description = null,
        int $tournamentId = null
    ): Transaction {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $description, $tournamentId) {
            $wallet = $this->getUserWallet($user);

            if (!$wallet) {
                throw new \Exception('Wallet not found for user');
            }

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update wallet balance
            $wallet->update(['balance' => $balanceAfter]);

            // Create transaction record
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'description' => $description,
                'tournament_id' => $tournamentId,
            ]);

            return $transaction;
        });
    }

    /**
     * Debit wallet
     */
    public function debit(
        User $user,
        float $amount,
        string $reason,
        string $description = null,
        int $tournamentId = null
    ): Transaction {
        if ($amount <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $description, $tournamentId) {
            $wallet = $this->getUserWallet($user);

            if (!$wallet) {
                throw new \Exception('Wallet not found for user');
            }

            $balanceBefore = $wallet->balance;

            if ($balanceBefore < $amount) {
                throw new \Exception('Insufficient balance');
            }

            $balanceAfter = $balanceBefore - $amount;

            // Update wallet balance
            $wallet->update(['balance' => $balanceAfter]);

            // Create transaction record
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'description' => $description,
                'tournament_id' => $tournamentId,
            ]);

            return $transaction;
        });
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(User $user, float $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }

    /**
     * Get transaction history for user
     */
    public function getTransactionHistory(User $user, int $limit = 50, int $offset = 0)
    {
        return Transaction::where('user_id', $user->id)
            ->with('tournament:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get transaction statistics for user
     */
    public function getTransactionStatistics(User $user): array
    {
        $transactions = Transaction::where('user_id', $user->id)->get();

        return [
            'total_credited' => (float) $transactions->where('type', 'credit')->sum('amount'),
            'total_debited' => (float) $transactions->where('type', 'debit')->sum('amount'),
            'total_transactions' => $transactions->count(),
            'current_balance' => $this->getBalance($user),
        ];
    }

    /**
     * Admin: Add funds to user wallet
     */
    public function adminAddFunds(User $targetUser, float $amount, User $admin, string $description = null): Transaction
    {
        if ($admin->role !== 'admin') {
            throw new \Exception('Unauthorized: Only admins can add funds');
        }

        return $this->credit(
            $targetUser,
            $amount,
            'admin_adjustment',
            $description ?? "Ajout de fonds par l'administrateur"
        );
    }

    /**
     * Process tournament registration payment
     */
    public function processTournamentRegistration(User $user, float $entryFee, int $tournamentId): Transaction
    {
        return DB::transaction(function () use ($user, $entryFee, $tournamentId) {
            // Débiter le joueur
            $debitTransaction = $this->debit(
                $user,
                $entryFee,
                'tournament_registration',
                "Inscription au tournoi #$tournamentId",
                $tournamentId
            );

            // Créditer l'organisateur du tournoi
            $tournament = \App\Models\Tournament::findOrFail($tournamentId);
            $organizer = $tournament->organizer;

            $this->credit(
                $organizer,
                $entryFee,
                'tournament_entry_received',
                "Frais d'inscription reçu pour le tournoi #$tournamentId",
                $tournamentId
            );

            return $debitTransaction;
        });
    }

    /**
     * Process tournament prize payout
     */
    public function processTournamentPrize(User $user, float $prizeAmount, int $tournamentId, int $rank): Transaction
    {
        return $this->credit(
            $user,
            $prizeAmount,
            'tournament_prize',
            "Prix du tournoi #$tournamentId - Place #{$rank}",
            $tournamentId
        );
    }

    /**
     * Process tournament refund
     */
    public function processTournamentRefund(User $user, float $amount, int $tournamentId): Transaction
    {
        return $this->credit(
            $user,
            $amount,
            'refund',
            "Remboursement du tournoi #$tournamentId",
            $tournamentId
        );
    }
}
