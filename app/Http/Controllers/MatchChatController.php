<?php

namespace App\Http\Controllers;

use App\Mail\MatchMessageNotification;
use App\Mail\MatchResultDrawMail;
use App\Mail\MatchResultLoserMail;
use App\Mail\MatchResultWinnerMail;
use App\Models\MatchEvidence;
use App\Models\MatchMessage;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\KnockoutFormatService;
use App\Services\SwissFormatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MatchChatController extends Controller
{
    protected SwissFormatService $swissService;
    protected KnockoutFormatService $knockoutService;

    public function __construct(
        SwissFormatService $swissService,
        KnockoutFormatService $knockoutService
    ) {
        $this->swissService = $swissService;
        $this->knockoutService = $knockoutService;
    }
    /**
     * Send a message in match chat
     */
    public function sendMessage(Request $request, TournamentMatch $match)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $match->load(['player1', 'player2', 'tournament']);

        // Verify user is a participant in this match
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this match'
            ], 403);
        }

        // Create message
        $matchMessage = MatchMessage::create([
            'match_id' => $match->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        // Determine opponent
        $opponent = $match->player1_id === $user->id ? $match->player2 : $match->player1;

        // Send email notification to opponent
        if ($opponent) {
            Mail::to($opponent)->send(
                new MatchMessageNotification($matchMessage, $match, $user, $opponent)
            );
            Log::info("Sent match message notification to user {$opponent->id} for match {$match->id}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $matchMessage->load('user')
        ]);
    }

    /**
     * Get all messages for a match
     */
    public function getMessages(TournamentMatch $match)
    {
        $user = Auth::user();
        $match->load('tournament');

        // Verify user is a participant in this match or is the organizer
        $isParticipant = $match->player1_id === $user->id || $match->player2_id === $user->id;
        $isOrganizer = $match->tournament->organizer_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isParticipant && !$isOrganizer && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to match messages'
            ], 403);
        }

        // Get all messages
        $messages = MatchMessage::forMatch($match->id)
            ->with('user:id,uuid,name,email')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read for current user
        if ($isParticipant) {
            MatchMessage::forMatch($match->id)
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Upload evidence for a match
     */
    public function uploadEvidence(Request $request, TournamentMatch $match)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'type' => 'required|in:screenshot,proof,result',
            'description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // Verify user is a participant in this match
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this match'
            ], 403);
        }

        // Store the file
        $file = $request->file('file');
        $path = $file->store('evidence/matches/' . $match->id, 'public');

        // Create evidence record
        $evidence = MatchEvidence::create([
            'match_id' => $match->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        Log::info("User {$user->id} uploaded evidence for match {$match->id}");

        return response()->json([
            'success' => true,
            'message' => 'Evidence uploaded successfully',
            'data' => $evidence->load('user')
        ]);
    }

    /**
     * Get all evidence for a match
     */
    public function getEvidence(TournamentMatch $match)
    {
        $user = Auth::user();
        $match->load('tournament');

        // Verify user is a participant, organizer, or admin
        $isParticipant = $match->player1_id === $user->id || $match->player2_id === $user->id;
        $isOrganizer = $match->tournament->organizer_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isParticipant && !$isOrganizer && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to match evidence'
            ], 403);
        }

        $evidence = MatchEvidence::forMatch($match->id)
            ->with('user:id,uuid,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $evidence
        ]);
    }

    /**
     * Update match scores (organizer only) - For completed matches
     */
    public function updateScore(Request $request, TournamentMatch $match)
    {
        $request->validate([
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $match->load('tournament');

        // Verify user is the organizer or admin
        $isOrganizer = $match->tournament->organizer_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isOrganizer && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only the tournament organizer or admin can update scores'
            ], 403);
        }

        // Verify match is completed (can only update completed matches)
        if ($match->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update scores for completed matches'
            ], 400);
        }

        try {
            $player1Score = $request->player1_score;
            $player2Score = $request->player2_score;

            // Use appropriate service based on tournament format
            $updatedMatch = match($match->tournament->format) {
                'swiss' => $this->swissService->updateMatchScore($match, $player1Score, $player2Score),
                'single_elimination' => $this->knockoutService->updateMatchScore($match, $player1Score, $player2Score),
                default => throw new \Exception('Unsupported tournament format: ' . $match->tournament->format),
            };

            Log::info("Organizer updated scores for match {$match->id}: P1={$player1Score}, P2={$player2Score}");

            return response()->json([
                'success' => true,
                'message' => 'Scores updated successfully',
                'data' => $updatedMatch->fresh(['player1', 'player2', 'winner', 'round.tournament'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update scores',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Enter match scores (organizer only)
     */
    public function enterScore(Request $request, TournamentMatch $match)
    {
        $request->validate([
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $match->load('tournament');

        // Verify user is the organizer or admin
        $isOrganizer = $match->tournament->organizer_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isOrganizer && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only the tournament organizer or admin can enter scores'
            ], 403);
        }

        // Verify match is not already completed
        if ($match->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Match is already completed'
            ], 400);
        }

        try {
            $player1Score = $request->player1_score;
            $player2Score = $request->player2_score;

            // Use appropriate service based on tournament format
            $updatedMatch = match($match->tournament->format) {
                'swiss' => $this->swissService->updateMatchResult($match, $player1Score, $player2Score),
                'single_elimination' => $this->knockoutService->updateMatchResult($match, $player1Score, $player2Score),
                default => throw new \Exception('Unsupported tournament format: ' . $match->tournament->format),
            };

            Log::info("Organizer entered scores for match {$match->id}: P1={$player1Score}, P2={$player2Score}");

            return response()->json([
                'success' => true,
                'message' => 'Scores entered successfully',
                'data' => $updatedMatch->fresh(['player1', 'player2', 'winner', 'round.tournament'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enter scores',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
