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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MatchChatController extends Controller
{
    /**
     * Send a message in match chat
     */
    public function sendMessage(Request $request, int $matchId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $match = TournamentMatch::with(['player1', 'player2', 'tournament'])->findOrFail($matchId);

        // Verify user is a participant in this match
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this match'
            ], 403);
        }

        // Create message
        $matchMessage = MatchMessage::create([
            'match_id' => $matchId,
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
            Log::info("Sent match message notification to user {$opponent->id} for match {$matchId}");
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
    public function getMessages(int $matchId)
    {
        $user = Auth::user();
        $match = TournamentMatch::findOrFail($matchId);

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
        $messages = MatchMessage::forMatch($matchId)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read for current user
        if ($isParticipant) {
            MatchMessage::forMatch($matchId)
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
    public function uploadEvidence(Request $request, int $matchId)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'type' => 'required|in:screenshot,proof,result',
            'description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $match = TournamentMatch::findOrFail($matchId);

        // Verify user is a participant in this match
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant in this match'
            ], 403);
        }

        // Store the file
        $file = $request->file('file');
        $path = $file->store('evidence/matches/' . $matchId, 'public');

        // Create evidence record
        $evidence = MatchEvidence::create([
            'match_id' => $matchId,
            'user_id' => $user->id,
            'file_path' => $path,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        Log::info("User {$user->id} uploaded evidence for match {$matchId}");

        return response()->json([
            'success' => true,
            'message' => 'Evidence uploaded successfully',
            'data' => $evidence->load('user')
        ]);
    }

    /**
     * Get all evidence for a match
     */
    public function getEvidence(int $matchId)
    {
        $user = Auth::user();
        $match = TournamentMatch::findOrFail($matchId);

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

        $evidence = MatchEvidence::forMatch($matchId)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $evidence
        ]);
    }

    /**
     * Enter match scores (organizer only)
     */
    public function enterScore(Request $request, int $matchId)
    {
        $request->validate([
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $match = TournamentMatch::with('tournament')->findOrFail($matchId);

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

        return DB::transaction(function () use ($request, $match) {
            // Update match with scores
            $player1Score = $request->player1_score;
            $player2Score = $request->player2_score;

            // Determine winner
            $winnerId = null;
            if ($player1Score > $player2Score) {
                $winnerId = $match->player1_id;
            } elseif ($player2Score > $player1Score) {
                $winnerId = $match->player2_id;
            }

            $match->update([
                'player1_score' => $player1Score,
                'player2_score' => $player2Score,
                'winner_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info("Organizer entered scores for match {$match->id}: P1={$player1Score}, P2={$player2Score}");

            // Send email notifications to both players
            $match = $match->fresh(['player1', 'player2', 'round.tournament']);

            if ($winnerId) {
                // Match with winner and loser
                $winner = $winnerId === $match->player1_id ? $match->player1 : $match->player2;
                $loser = $winnerId === $match->player1_id ? $match->player2 : $match->player1;
                $winnerScore = $winnerId === $match->player1_id ? $player1Score : $player2Score;
                $loserScore = $winnerId === $match->player1_id ? $player2Score : $player1Score;

                // Send winner email
                Mail::to($winner)->send(
                    new MatchResultWinnerMail($winner, $match, $winnerScore, $loserScore)
                );

                // Send loser email
                Mail::to($loser)->send(
                    new MatchResultLoserMail($loser, $match, $loserScore, $winnerScore)
                );

                Log::info("Sent match result emails to players for match {$match->id}");
            } else {
                // Draw match - send draw email to both players
                Mail::to($match->player1)->send(
                    new MatchResultDrawMail($match->player1, $match, $player1Score)
                );

                Mail::to($match->player2)->send(
                    new MatchResultDrawMail($match->player2, $match, $player2Score)
                );

                Log::info("Sent draw match emails to players for match {$match->id}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Scores entered successfully',
                'data' => $match
            ]);
        });
    }
}
