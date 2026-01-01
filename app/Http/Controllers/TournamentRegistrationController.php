<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Services\TournamentRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TournamentRegistrationController extends Controller
{
    protected TournamentRegistrationService $registrationService;

    public function __construct(TournamentRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * Register to a tournament
     */
    public function register(Request $request, string $tournamentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game_account_id' => 'required|integer|exists:game_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tournament = Tournament::where('uuid', $tournamentId)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        try {
            $registration = $this->registrationService->registerToTournament(
                $request->user(),
                $tournament,
                $request->game_account_id
            );

            return response()->json([
                'message' => 'Successfully registered to tournament',
                'registration' => $registration->load(['tournament', 'gameAccount']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Withdraw from a tournament
     */
    public function withdraw(Request $request, string $tournamentId): JsonResponse
    {
        // Find tournament by UUID first
        $tournament = Tournament::where('uuid', $tournamentId)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $registration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'Registration not found',
            ], 404);
        }

        try {
            $updatedRegistration = $this->registrationService->withdrawFromTournament(
                $registration,
                $request->user()
            );

            return response()->json([
                'message' => 'Successfully withdrawn from tournament. Entry fee has been refunded.',
                'registration' => $updatedRegistration,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Withdrawal failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's tournament registrations
     */
    public function myRegistrations(Request $request): JsonResponse
    {
        $registrations = $this->registrationService->getUserRegistrations($request->user());

        return response()->json([
            'registrations' => $registrations,
        ], 200);
    }

    /**
     * Get tournament participants
     */
    public function participants(string $tournamentId): JsonResponse
    {
        $tournament = Tournament::where('uuid', $tournamentId)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $participants = $this->registrationService->getTournamentParticipants($tournament);

        return response()->json([
            'participants' => $participants,
            'total' => $participants->count(),
        ], 200);
    }

    /**
     * Get tournament leaderboard
     */
    public function leaderboard(string $tournamentId): JsonResponse
    {
        $tournament = Tournament::where('uuid', $tournamentId)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $leaderboard = $this->registrationService->getTournamentLeaderboard($tournament);

        return response()->json([
            'leaderboard' => $leaderboard,
        ], 200);
    }

    /**
     * Check if user is registered for a tournament
     */
    public function checkRegistration(Request $request, string $tournamentId): JsonResponse
    {
        $tournament = Tournament::where('uuid', $tournamentId)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $isRegistered = $this->registrationService->isUserRegistered($request->user(), $tournament);
        $registration = $this->registrationService->getUserRegistration($request->user(), $tournament);

        return response()->json([
            'is_registered' => $isRegistered,
            'registration' => $registration,
        ], 200);
    }
}
