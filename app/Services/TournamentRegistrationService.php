<?php

namespace App\Services;

use App\Mail\TournamentNewRegistrationMail;
use App\Mail\TournamentRegistrationConfirmationMail;
use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TournamentRegistrationService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Register user to a tournament
     */
    public function registerToTournament(User $user, Tournament $tournament, int $gameAccountId): TournamentRegistration
    {
        return DB::transaction(function () use ($user, $tournament, $gameAccountId) {
            // Validate registration
            $this->validateRegistration($user, $tournament, $gameAccountId);

            // Check if user has sufficient balance
            if (!$this->walletService->hasSufficientBalance($user, $tournament->entry_fee)) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Process payment: Debit participant and credit organizer's blocked_balance
            // Les entry fees vont directement dans le blocked_balance de l'organisateur
            $this->walletService->processTournamentRegistration(
                $user,
                $tournament->entry_fee,
                $tournament->id
            );

            // NOTE: Flow des fonds:
            // 1. Inscription: Joueur → Organisateur (blocked_balance directement)
            // 2. Démarrage: Créer le lock record (les fonds sont déjà bloqués)
            // 3. Fin: blocked_balance → Gagnants + Reste à l'organisateur (balance)

            // Create registration
            $registration = TournamentRegistration::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'tournament_id' => $tournament->id,
                'user_id' => $user->id,
                'game_account_id' => $gameAccountId,
                'status' => 'registered',
                'tournament_points' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ]);

            // Get total registrations after this one
            $totalRegistrations = $tournament->registrations()->where('status', 'registered')->count();

            // Send confirmation email to participant
            Mail::to($user)->send(
                new TournamentRegistrationConfirmationMail($tournament, $user)
            );

            // Send notification email to organizer
            Mail::to($tournament->organizer)->send(
                new TournamentNewRegistrationMail(
                    $tournament,
                    $user,
                    $totalRegistrations,
                    $tournament->max_participants
                )
            );

            return $registration->fresh();
        });
    }

    /**
     * Validate registration requirements
     */
    protected function validateRegistration(User $user, Tournament $tournament, int $gameAccountId): void
    {
        // Check if user has a validated profile
        if (!$user->profile || $user->profile->status !== 'validated') {
            throw new \Exception('Your profile must be validated before registering to tournaments');
        }

        // Check if tournament is accepting registrations
        if ($tournament->status !== 'open') {
            throw new \Exception('Tournament is not accepting registrations');
        }

        // Check if tournament is full
        $currentRegistrations = $tournament->registrations()->where('status', 'registered')->count();
        if ($currentRegistrations >= $tournament->max_participants) {
            throw new \Exception('Tournament is full');
        }

        // Check if user is already registered
        $existingRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->where('status', 'registered')
            ->first();

        if ($existingRegistration) {
            throw new \Exception('Already registered to this tournament');
        }

        // Validate game account
        $gameAccount = GameAccount::where('id', $gameAccountId)
            ->where('user_id', $user->id)
            ->first();

        if (!$gameAccount) {
            throw new \Exception('Game account not found');
        }

        // Check if game account matches tournament game
        if ($gameAccount->game !== $tournament->game) {
            throw new \Exception("Game account type must match tournament game ({$tournament->game})");
        }
    }

    /**
     * Withdraw from tournament (before start)
     */
    public function withdrawFromTournament(TournamentRegistration $registration, User $user): TournamentRegistration
    {
        return DB::transaction(function () use ($registration, $user) {
            // Verify ownership
            if ($registration->user_id !== $user->id) {
                throw new \Exception('Unauthorized');
            }

            // Check if tournament has started
            if ($registration->tournament->status === 'in_progress' || $registration->tournament->status === 'completed') {
                throw new \Exception('Cannot withdraw from a tournament that has started');
            }

            // Update registration status
            $registration->update(['status' => 'withdrawn']);

            // Décrémenter le locked_amount du tournament_wallet_lock
            $organizer = $registration->tournament->organizer;
            $organizer->load('wallet');
            $entryFee = $registration->tournament->entry_fee;

            $lock = \App\Models\TournamentWalletLock::where('tournament_id', $registration->tournament_id)
                ->where('organizer_id', $organizer->id)
                ->firstOrFail();

            // Décrémenter le locked_amount
            $lock->decrement('locked_amount', $entryFee);

            // Synchroniser le blocked_balance du wallet
            $this->walletService->syncBlockedBalance($organizer);

            // Créer transaction pour tracer le remboursement
            \App\Models\Transaction::create([
                'wallet_id' => $organizer->wallet->id,
                'user_id' => $organizer->id,
                'type' => 'debit',
                'amount' => $entryFee,
                'balance_before' => $organizer->wallet->balance,
                'balance_after' => $organizer->wallet->balance, // balance ne change pas
                'reason' => 'tournament_entry_refunded',
                'description' => "Remboursement frais d'inscription pour tournoi #{$registration->tournament_id}",
                'tournament_id' => $registration->tournament_id,
            ]);

            // Refund entry fee to participant
            $this->walletService->processTournamentRefund(
                $user,
                $entryFee,
                $registration->tournament_id
            );

            return $registration->fresh();
        });
    }

    /**
     * Get user's tournament registrations
     */
    public function getUserRegistrations(User $user)
    {
        return TournamentRegistration::where('user_id', $user->id)
            ->with(['tournament', 'gameAccount'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get tournament participants
     */
    public function getTournamentParticipants(Tournament $tournament)
    {
        return TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('status', 'registered')
            ->with(['user', 'gameAccount'])
            ->orderBy('tournament_points', 'desc')
            ->orderBy('wins', 'desc')
            ->get();
    }

    /**
     * Get tournament leaderboard
     */
    public function getTournamentLeaderboard(Tournament $tournament)
    {
        return TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('status', 'registered')
            ->with(['user:id,name,avatar_url', 'gameAccount:id,in_game_name'])
            ->orderBy('tournament_points', 'desc')
            ->orderBy('wins', 'desc')
            ->orderBy('draws', 'desc')
            ->get()
            ->map(function ($registration, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $registration->user,
                    'game_account' => $registration->gameAccount,
                    'points' => $registration->tournament_points,
                    'wins' => $registration->wins,
                    'draws' => $registration->draws,
                    'losses' => $registration->losses,
                    'played' => $registration->wins + $registration->draws + $registration->losses,
                ];
            });
    }

    /**
     * Check if user is registered for tournament
     */
    public function isUserRegistered(User $user, Tournament $tournament): bool
    {
        return TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->where('status', 'registered')
            ->exists();
    }

    /**
     * Get user's registration for a tournament
     */
    public function getUserRegistration(User $user, Tournament $tournament): ?TournamentRegistration
    {
        return TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->first();
    }
}
