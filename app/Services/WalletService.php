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
        $user->load('wallet');

        $balance = (float) $user->wallet->balance;

        // Calculer le vrai blocked_balance en additionnant les locks actifs
        $blockedBalance = $this->getActualBlockedBalance($user);
        $availableBalance = $balance - $blockedBalance;

        $stats = [
            // Soldes du wallet
            'balance' => $balance,
            'blocked_balance' => $blockedBalance,
            'available_balance' => $availableBalance,

            // Statistiques des transactions
            'total_credited' => (float) $transactions->where('type', 'credit')->sum('amount'),
            'total_debited' => (float) $transactions->where('type', 'debit')->sum('amount'),
            'total_transactions' => $transactions->count(),

            // Compatibilité
            'current_balance' => $balance,
        ];

        // Si c'est un organisateur, ajouter les statistiques de tournois
        if ($user->role === 'organizer') {
            $tournamentStats = $this->getOrganizerTournamentStats($user);
            $stats['tournament_stats'] = $tournamentStats;
        }

        // Si c'est un joueur, ajouter les statistiques de participation
        if ($user->role === 'player') {
            $playerStats = $this->getPlayerTournamentStats($user);
            $stats['tournament_stats'] = $playerStats;
        }

        return $stats;
    }

    /**
     * Get tournament statistics for organizer
     */
    protected function getOrganizerTournamentStats(User $organizer): array
    {
        $locks = \App\Models\TournamentWalletLock::where('organizer_id', $organizer->id)
            ->with('tournament')
            ->get();

        // Stats globales
        $totalCollected = (float) $locks->sum('locked_amount');
        $totalPaidOut = (float) $locks->sum('paid_out');
        $totalProfit = $totalCollected - $totalPaidOut;

        // Montant actuellement bloqué (locks actifs)
        $currentlyBlocked = (float) $locks
            ->whereIn('status', ['locked', 'processing_payouts'])
            ->sum('locked_amount');

        // Montant disponible pour retrait (locks released)
        $fundsAvailableForWithdrawal = 0.0;
        $releasedLocks = [];

        foreach ($locks->where('status', 'released') as $lock) {
            $remainder = $lock->locked_amount - $lock->paid_out;
            if ($remainder > 0) {
                $fundsAvailableForWithdrawal += $remainder;
                $releasedLocks[] = [
                    'tournament_id' => $lock->tournament_id,
                    'tournament_name' => $lock->tournament->name ?? 'Unknown',
                    'locked_amount' => (float) $lock->locked_amount,
                    'paid_out' => (float) $lock->paid_out,
                    'available_for_withdrawal' => (float) $remainder,
                    'released_at' => $lock->released_at,
                ];
            }
        }

        $activeTournaments = \App\Models\Tournament::where('organizer_id', $organizer->id)
            ->where('status', 'in_progress')
            ->count();

        $completedTournaments = \App\Models\Tournament::where('organizer_id', $organizer->id)
            ->where('status', 'completed')
            ->count();

        return [
            'total_collected' => $totalCollected,        // Total frais d'inscription collectés (tous les temps)
            'total_paid_out' => $totalPaidOut,          // Total payé en prix (tous les temps)
            'total_profit' => $totalProfit,             // Profit net (tous les temps)
            'currently_blocked' => $currentlyBlocked,   // Fonds actuellement bloqués pour tournois en cours
            'available_for_withdrawal' => $fundsAvailableForWithdrawal, // Fonds de tournois terminés disponibles pour retrait
            'active_tournaments' => $activeTournaments, // Tournois en cours
            'completed_tournaments' => $completedTournaments, // Tournois terminés
            'released_funds_by_tournament' => $releasedLocks, // Détails des fonds disponibles par tournoi
        ];
    }

    /**
     * Get tournament statistics for player
     */
    protected function getPlayerTournamentStats(User $player): array
    {
        $registrations = \App\Models\TournamentRegistration::where('user_id', $player->id)
            ->where('status', 'registered')
            ->get();

        $totalPrizesWon = (float) $registrations->sum('prize_won');
        $tournamentsWon = $registrations->where('final_rank', 1)->count();
        $podiumFinishes = $registrations->whereIn('final_rank', [1, 2, 3])->count();

        // Compter les tournois actifs et complétés via des requêtes séparées
        $activeTournaments = \App\Models\TournamentRegistration::where('user_id', $player->id)
            ->where('status', 'registered')
            ->whereHas('tournament', function ($query) {
                $query->whereIn('status', ['open', 'in_progress']);
            })
            ->count();

        $completedTournaments = \App\Models\TournamentRegistration::where('user_id', $player->id)
            ->where('status', 'registered')
            ->whereHas('tournament', function ($query) {
                $query->where('status', 'completed');
            })
            ->count();

        return [
            'total_prizes_won' => $totalPrizesWon,      // Total prix gagnés
            'tournaments_won' => $tournamentsWon,       // Nombre de victoires
            'podium_finishes' => $podiumFinishes,       // Top 3 finishes
            'active_tournaments' => $activeTournaments, // Tournois en cours
            'completed_tournaments' => $completedTournaments, // Tournois terminés
        ];
    }

    /**
     * Calculer le vrai blocked_balance en additionnant les locks actifs
     */
    protected function getActualBlockedBalance(User $user): float
    {
        // Pour les organisateurs, additionner tous les tournament_wallet_locks actifs
        if ($user->role === 'organizer') {
            return (float) \App\Models\TournamentWalletLock::where('organizer_id', $user->id)
                ->whereIn('status', ['locked', 'processing_payouts'])
                ->sum('locked_amount');
        }

        // Pour les joueurs, le blocked_balance est 0 (ils ne bloquent pas de fonds)
        return 0.0;
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

            // Ajouter les frais au tournament_wallet_lock
            $tournament = \App\Models\Tournament::findOrFail($tournamentId);
            $organizer = $tournament->organizer;
            $organizer->load('wallet');

            // Trouver ou créer le wallet lock pour ce tournoi
            $lock = \App\Models\TournamentWalletLock::firstOrCreate(
                [
                    'tournament_id' => $tournamentId,
                    'organizer_id' => $organizer->id,
                ],
                [
                    'wallet_id' => $organizer->wallet->id,
                    'locked_amount' => 0,
                    'status' => 'locked',
                ]
            );

            // Incrémenter le locked_amount dans tournament_wallet_locks
            $lock->increment('locked_amount', $entryFee);

            // Synchroniser le blocked_balance du wallet avec la somme des locks actifs
            $this->syncBlockedBalance($organizer);

            // Créer la transaction pour tracer ce mouvement
            Transaction::create([
                'wallet_id' => $organizer->wallet->id,
                'user_id' => $organizer->id,
                'type' => 'credit',
                'amount' => $entryFee,
                'balance_before' => $organizer->wallet->balance,
                'balance_after' => $organizer->wallet->balance, // balance ne change pas
                'reason' => 'tournament_entry_received',
                'description' => "Frais d'inscription reçu pour le tournoi #$tournamentId (bloqué)",
                'tournament_id' => $tournamentId,
            ]);

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

    /**
     * Synchroniser le blocked_balance du wallet avec la somme des tournament_wallet_locks actifs
     */
    public function syncBlockedBalance(User $user): void
    {
        $user->load('wallet');

        // Calculer la somme de tous les locks actifs pour cet utilisateur
        $totalBlocked = \App\Models\TournamentWalletLock::where('organizer_id', $user->id)
            ->whereIn('status', ['locked', 'processing_payouts'])
            ->sum('locked_amount');

        // Mettre à jour le blocked_balance du wallet
        $user->wallet->update(['blocked_balance' => $totalBlocked]);
    }
}
