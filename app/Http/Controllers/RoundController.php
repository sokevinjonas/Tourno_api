<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Tournament;
use App\Services\SwissFormatService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    protected SwissFormatService $swissService;
    protected WalletService $walletService;

    public function __construct(SwissFormatService $swissService, WalletService $walletService)
    {
        $this->swissService = $swissService;
        $this->walletService = $walletService;
    }

    /**
     * Start tournament (generate first round)
     */
    public function startTournament(Request $request, int $tournamentId): JsonResponse
    {
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        // Check authorization
        if ($tournament->organizer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $round = $this->swissService->startTournament($tournament);

            return response()->json([
                'message' => 'Tournament started successfully',
                'round' => $round->load('matches'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start tournament',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate next round
     */
    public function generateNextRound(Request $request, int $tournamentId): JsonResponse
    {
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        // Check authorization
        if ($tournament->organizer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $round = $this->swissService->generateNextRound($tournament);

            return response()->json([
                'message' => 'Next round generated successfully',
                'round' => $round->load('matches'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate next round',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete a round
     */
    public function completeRound(Request $request, int $tournamentId, int $roundId): JsonResponse
    {
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        // Check authorization
        if ($tournament->organizer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $round = Round::find($roundId);

        if (!$round || $round->tournament_id !== $tournament->id) {
            return response()->json([
                'message' => 'Round not found',
            ], 404);
        }

        try {
            $completedRound = $this->swissService->completeRound($round);

            return response()->json([
                'message' => 'Round completed successfully',
                'round' => $completedRound,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete round',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete tournament
     */
    public function completeTournament(Request $request, int $tournamentId): JsonResponse
    {
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        // Check authorization
        if ($tournament->organizer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $completedTournament = $this->swissService->completeTournament($tournament, $this->walletService);

            return response()->json([
                'message' => 'Tournament completed successfully. Prizes have been distributed.',
                'tournament' => $completedTournament,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete tournament',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get tournament rounds
     */
    public function getRounds(int $tournamentId): JsonResponse
    {
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $rounds = Round::where('tournament_id', $tournamentId)
            ->with(['matches.player1:id,name', 'matches.player2:id,name'])
            ->orderBy('round_number', 'asc')
            ->get();

        return response()->json([
            'rounds' => $rounds,
        ], 200);
    }
}
