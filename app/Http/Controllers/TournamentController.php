<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\TournamentSchedulingService;
use App\Services\TournamentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    protected TournamentService $tournamentService;

    public function __construct(TournamentService $tournamentService)
    {
        $this->tournamentService = $tournamentService;
    }

    /**
     * Get all tournaments with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'game' => $request->input('game'),
            'organizer_id' => $request->input('organizer_id'),
            'sort' => $request->input('sort', 'desc'),
        ];

        // Remove null filters
        $filters = array_filter($filters, fn($value) => !is_null($value));

        $result = $this->tournamentService->getTournaments($filters);

        return response()->json($result, 200);
    }

    /**
     * Get upcoming tournaments
     */
    public function upcoming(Request $request): JsonResponse
    {
        $gameType = $request->input('game');
        $tournaments = $this->tournamentService->getUpcomingTournaments($gameType);

        return response()->json([
            'tournaments' => $tournaments,
        ], 200);
    }

    /**
     * Get tournaments currently in registration
     */
    public function registering(Request $request): JsonResponse
    {
        $gameType = $request->input('game');
        $tournaments = $this->tournamentService->getRegisteringTournaments($gameType);

        return response()->json([
            'tournaments' => $tournaments,
        ], 200);
    }

    /**
     * Get organizer's tournaments
     */
    public function myTournaments(Request $request): JsonResponse
    {
        $tournaments = $this->tournamentService->getOrganizerTournaments($request->user());

        return response()->json([
            'tournaments' => $tournaments,
        ], 200);
    }

    /**
     * Create a new tournament
     */
    public function store(Request $request): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'organizer'])) {
            return response()->json([
                'message' => 'Unauthorized: Only admins and organizers can create tournaments',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'game' => 'required|string|in:efootball,fc_mobile,dream_league_soccer',
            'format' => 'required|string|in:single_elimination,swiss,champions_league',
            'max_participants' => 'required|integer|in:8,16,32,64',
            'entry_fee' => 'required|numeric|min:0',
            'prize_distribution' => 'nullable|json',
            'rules' => 'nullable|json',
            'visibility' => 'nullable|string|in:public,private',
            'auto_managed' => 'nullable|boolean',
            'start_date' => 'required|date',
            'tournament_duration_days' => 'nullable|integer|min:1|max:30',
            'time_slot' => 'nullable|string|in:morning,afternoon,evening',
            'match_deadline_minutes' => 'nullable|integer|min:30|max:180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Validate dates logic
            $this->tournamentService->validateTournamentDates($data);

            $tournament = $this->tournamentService->createTournament($request->user(), $data);

            return response()->json([
                'message' => 'Tournament created successfully',
                'tournament' => $tournament,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create tournament',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tournament details
     */
    public function show(string $id): JsonResponse
    {
        $tournament = $this->tournamentService->getTournament($id);

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $statistics = $this->tournamentService->getTournamentStatistics($tournament);

        return response()->json([
            'tournament' => $tournament,
            'statistics' => $statistics,
        ], 200);
    }

    /**
     * Update a tournament
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tournament = Tournament::where('uuid', $id)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'max_participants' => 'sometimes|required|integer|min:2|max:128',
            'entry_fee' => 'sometimes|required|numeric|min:0',
            'prize_pool' => 'nullable|numeric|min:0',
            'prize_distribution' => 'nullable|json',
            'rules' => 'nullable|json',
            'registration_start' => 'sometimes|required|date',
            'registration_end' => 'sometimes|required|date|after:registration_start',
            'start_date' => 'sometimes|required|date|after:registration_end',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Validate dates if provided
            if (isset($data['registration_start']) || isset($data['registration_end']) || isset($data['start_date'])) {
                $dateData = [
                    'registration_start' => $data['registration_start'] ?? $tournament->registration_start,
                    'registration_end' => $data['registration_end'] ?? $tournament->registration_end,
                    'start_date' => $data['start_date'] ?? $tournament->start_date,
                ];
                $this->tournamentService->validateTournamentDates($dateData);
            }

            $updatedTournament = $this->tournamentService->updateTournament(
                $tournament,
                $request->user(),
                $data
            );

            return response()->json([
                'message' => 'Tournament updated successfully',
                'tournament' => $updatedTournament,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update tournament',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a tournament
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tournament = Tournament::where('uuid', $id)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        try {
            $this->tournamentService->deleteTournament($tournament, $request->user());

            return response()->json([
                'message' => 'Tournament deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete tournament',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change tournament status
     */
    public function changeStatus(Request $request, string $id): JsonResponse
    {
        $tournament = Tournament::where('uuid', $id)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:draft,open,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updatedTournament = $this->tournamentService->changeTournamentStatus(
                $tournament,
                $request->user(),
                $request->status
            );

            return response()->json([
                'message' => 'Tournament status updated successfully',
                'tournament' => $updatedTournament,
            ], 200);
        } catch (\Exception $e) {
            // Check if it's an authorization error
            if (str_contains($e->getMessage(), 'Unauthorized') ||
                str_contains($e->getMessage(), 'not authorized') ||
                str_contains($e->getMessage(), 'permission')) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => $e->getMessage(),
                ], 403);
            }

            return response()->json([
                'message' => 'Failed to update tournament status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview tournament schedule
     */
    public function previewSchedule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|string|in:single_elimination,swiss,champions_league',
            'max_participants' => 'required|integer|in:8,16,32,64',
            'start_date' => 'required|date',
            'tournament_duration_days' => 'nullable|integer|min:1|max:30',
            'time_slot' => 'required|string|in:morning,evening',
            'match_deadline_minutes' => 'required|integer|min:30|max:180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $schedulingService = new TournamentSchedulingService();
            $preview = $schedulingService->previewSchedule($request->all());

            return response()->json([
                'success' => true,
                'data' => $preview,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all matches for a tournament
     */
    public function getMatches(string $id): JsonResponse
    {
        $tournament = Tournament::with([
            'matches' => function ($query) {
                $query->with(['player1', 'player2', 'winner', 'round'])
                    ->orderBy('round_id', 'asc')
                    ->orderBy('created_at', 'asc');
            }
        ])->where('uuid', $id)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tournament->matches,
        ], 200);
    }

    /**
     * Get tournament rounds information
     */
    public function getRoundsInfo(string $id): JsonResponse
    {
        $tournament = Tournament::with(['rounds'])->where('uuid', $id)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        // Calculer le nombre total de rounds
        $totalRounds = ceil(log($tournament->max_participants, 2));

        // Obtenir le round actuel (le dernier round créé)
        $currentRound = $tournament->rounds()
            ->orderBy('round_number', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tournament_id' => $tournament->id,
                'tournament_name' => $tournament->name,
                'tournament_status' => $tournament->status,
                'total_rounds' => (int) $totalRounds,
                'current_round_number' => $currentRound ? $currentRound->round_number : 0,
                'current_round' => $currentRound ? [
                    'id' => $currentRound->id,
                    'round_number' => $currentRound->round_number,
                    'status' => $currentRound->status,
                    'total_matches' => $currentRound->matches()->count(),
                    'completed_matches' => $currentRound->matches()->where('status', 'completed')->count(),
                    'pending_matches' => $currentRound->matches()->whereIn('status', ['pending', 'in_progress'])->count(),
                ] : null,
                'rounds_completed' => $tournament->rounds()->where('status', 'completed')->count(),
                'rounds_remaining' => $currentRound ? ($totalRounds - $currentRound->round_number) : $totalRounds,
            ],
        ], 200);
    }
}
