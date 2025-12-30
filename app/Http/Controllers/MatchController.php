<?php

namespace App\Http\Controllers;

use App\Models\TournamentMatch;
use App\Services\MatchResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchController extends Controller
{
    protected MatchResultService $matchResultService;

    public function __construct(MatchResultService $matchResultService)
    {
        $this->matchResultService = $matchResultService;
    }

    /**
     * Get match details
     */
    public function show(int $matchId): JsonResponse
    {
        $match = TournamentMatch::with([
            'tournament',
            'round',
            'player1:id,name,avatar_url',
            'player2:id,name,avatar_url',
            'winner:id,name',
            'matchResults.submitter:id,name'
        ])->find($matchId);

        if (!$match) {
            return response()->json([
                'message' => 'Match not found',
            ], 404);
        }

        return response()->json([
            'match' => $match,
        ], 200);
    }

    /**
     * Submit match result
     */
    public function submitResult(Request $request, int $matchId): JsonResponse
    {
        $match = TournamentMatch::find($matchId);

        if (!$match) {
            return response()->json([
                'message' => 'Match not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'own_score' => 'required|integer|min:0',
            'opponent_score' => 'required|integer|min:0',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['screenshot'] = $request->file('screenshot');

            $matchResult = $this->matchResultService->submitMatchResult(
                $match,
                $request->user(),
                $data
            );

            return response()->json([
                'message' => 'Match result submitted successfully',
                'match_result' => $matchResult,
                'match' => $match->fresh(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit match result',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's matches
     */
    public function myMatches(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $matches = TournamentMatch::where(function ($query) use ($userId) {
            $query->where('player1_id', $userId)
                  ->orWhere('player2_id', $userId);
        })
        ->with(['tournament', 'round', 'player1:id,name', 'player2:id,name'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'matches' => $matches,
        ], 200);
    }

    /**
     * Get pending matches for user
     */
    public function myPendingMatches(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $matches = TournamentMatch::whereIn('status', ['scheduled', 'in_progress'])
            ->where(function ($query) use ($userId) {
                $query->where('player1_id', $userId)
                      ->orWhere('player2_id', $userId);
            })
            ->with(['tournament', 'round', 'player1:id,name', 'player2:id,name', 'matchResults'])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return response()->json([
            'matches' => $matches,
        ], 200);
    }

    /**
     * Get disputed matches (Moderators only)
     */
    public function disputed(Request $request): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $matches = $this->matchResultService->getDisputedMatches();

        return response()->json([
            'matches' => $matches,
        ], 200);
    }

    /**
     * Validate disputed match (Moderators only)
     */
    public function validateResult(Request $request, int $matchId): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $match = TournamentMatch::find($matchId);

        if (!$match) {
            return response()->json([
                'message' => 'Match not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $validatedMatch = $this->matchResultService->validateMatchResult(
                $match,
                $request->user(),
                $request->player1_score,
                $request->player2_score
            );

            return response()->json([
                'message' => 'Match result validated successfully',
                'match' => $validatedMatch,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to validate match result',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
