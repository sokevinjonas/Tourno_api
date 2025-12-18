<?php

namespace App\Services;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
                throw new \Exception('Insufficient balance. Please add funds to your wallet.');
            }

            // Debit entry fee from wallet
            $this->walletService->processTournamentRegistration(
                $user,
                $tournament->entry_fee,
                $tournament->id
            );

            // Create registration
            $registration = TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $user->id,
                'game_account_id' => $gameAccountId,
                'status' => 'registered',
                'tournament_points' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ]);

            return $registration->fresh();
        });
    }

    /**
     * Validate registration requirements
     */
    protected function validateRegistration(User $user, Tournament $tournament, int $gameAccountId): void
    {
        // Check if user has a validated profile
        if (!$user->profile || $user->profile->validation_status !== 'validated') {
            throw new \Exception('Your profile must be validated before registering to tournaments');
        }

        // Check if tournament is accepting registrations
        if ($tournament->status !== 'registering') {
            throw new \Exception('Tournament is not accepting registrations');
        }

        // Check registration period
        if (now()->lt($tournament->registration_start)) {
            throw new \Exception('Registration has not started yet');
        }

        if (now()->gt($tournament->registration_end)) {
            throw new \Exception('Registration period has ended');
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
            throw new \Exception('You are already registered for this tournament');
        }

        // Validate game account
        $gameAccount = GameAccount::where('id', $gameAccountId)
            ->where('user_id', $user->id)
            ->first();

        if (!$gameAccount) {
            throw new \Exception('Game account not found');
        }

        // Check if game account matches tournament game type
        if ($gameAccount->game_type !== $tournament->game_type) {
            throw new \Exception("Game account type must match tournament game type ({$tournament->game_type})");
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

            // Refund entry fee
            $this->walletService->processTournamentRefund(
                $user,
                $registration->tournament->entry_fee,
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
