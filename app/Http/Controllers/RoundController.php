<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Tournament;
use App\Services\KnockoutFormatService;
use App\Services\SwissFormatService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    protected SwissFormatService $swissService;
    protected KnockoutFormatService $knockoutService;
    protected WalletService $walletService;

    public function __construct(
        SwissFormatService $swissService,
        KnockoutFormatService $knockoutService,
        WalletService $walletService
    ) {
        $this->swissService = $swissService;
        $this->knockoutService = $knockoutService;
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
            // Route to appropriate service based on tournament format
            $round = match($tournament->format) {
                'swiss' => $this->swissService->startTournament($tournament),
                'single_elimination' => $this->knockoutService->startTournament($tournament),
                default => throw new \Exception('Unsupported tournament format: ' . $tournament->format),
            };

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
            // Only Swiss format supports manual next round generation
            // Knockout generates all rounds at start
            if ($tournament->format !== 'swiss') {
                throw new \Exception('Manual round generation is only available for Swiss format tournaments');
            }

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
            // Route to appropriate service based on tournament format
            $completedTournament = match($tournament->format) {
                'swiss' => $this->swissService->completeTournament($tournament, $this->walletService),
                'single_elimination' => $this->knockoutService->completeTournament($tournament, $this->walletService),
                default => throw new \Exception('Unsupported tournament format: ' . $tournament->format),
            };

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
