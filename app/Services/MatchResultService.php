<?php

namespace App\Services;

use App\Mail\MatchResultSubmittedConfirmationMail;
use App\Mail\MatchResultUpdatedConfirmationMail;
use App\Mail\OpponentSubmittedResultMail;
use App\Mail\OpponentUpdatedResultMail;
use App\Models\MatchResult;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MatchResultService
{
    protected SwissFormatService $swissService;

    public function __construct(SwissFormatService $swissService)
    {
        $this->swissService = $swissService;
    }

    /**
     * Submit match result
     */
    public function submitMatchResult(TournamentMatch $match, User $user, array $data): MatchResult
    {
        // Verify user is a participant
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            throw new \Exception('You are not a participant of this match');
        }

        // Check if match is in correct status
        if ($match->status === 'completed') {
            throw new \Exception('Match is already completed');
        }

        // Check if user has already submitted a result for this match
        $existingSubmission = MatchResult::where('match_id', $match->id)
            ->where('submitted_by', $user->id)
            ->first();

        $isUpdate = $existingSubmission !== null;

        if ($existingSubmission) {
            // Update existing submission instead of creating a new one
            $matchResult = DB::transaction(function () use ($match, $user, $data, $existingSubmission) {
                // Update scores and comment
                $existingSubmission->update([
                    'own_score' => $data['own_score'],
                    'opponent_score' => $data['opponent_score'],
                    'comment' => $data['comment'] ?? null,
                    'status' => 'pending', // Reset to pending if it was validated
                ]);

                // Handle screenshot update
                if (isset($data['screenshot'])) {
                    // Delete old screenshot if exists
                    if ($existingSubmission->screenshot_path) {
                        Storage::disk('public')->delete($existingSubmission->screenshot_path);
                    }
                    // Upload new screenshot
                    $screenshotPath = $this->uploadScreenshot($data['screenshot'], $match->id, $user->id);
                    $existingSubmission->update(['screenshot_path' => $screenshotPath]);
                }

                // Check if both players have submitted
                $this->checkAndResolveMatch($match);

                return $existingSubmission;
            });
        } else {
            // Handle screenshot upload
            $screenshotPath = null;
            if (isset($data['screenshot'])) {
                $screenshotPath = $this->uploadScreenshot($data['screenshot'], $match->id, $user->id);
            }

            $matchResult = DB::transaction(function () use ($match, $user, $data, $screenshotPath) {
                // Create match result
                $matchResult = MatchResult::create([
                    'match_id' => $match->id,
                    'submitted_by' => $user->id,
                    'own_score' => $data['own_score'],
                    'opponent_score' => $data['opponent_score'],
                    'screenshot_path' => $screenshotPath,
                    'comment' => $data['comment'] ?? null,
                    'status' => 'pending',
                ]);

                // Check if both players have submitted
                $this->checkAndResolveMatch($match);

                return $matchResult;
            });
        }

        // Send emails after transaction (different emails for new submission vs update)
        $this->sendSubmissionEmails($match, $user, $matchResult, $isUpdate);

        return $matchResult;
    }

    /**
     * Check if both players submitted and auto-resolve if scores match
     */
    protected function checkAndResolveMatch(TournamentMatch $match): void
    {
        $results = MatchResult::where('match_id', $match->id)->get();

        if ($results->count() === 2) {
            $result1 = $results->first();
            $result2 = $results->last();

            // Determine actual scores (result1 is from player perspective)
            $player1Id = $result1->submitted_by;
            $player2Id = $result2->submitted_by;

            if ($player1Id === $match->player1_id) {
                $player1Score = $result1->own_score;
                $player2ScoreFromPlayer1 = $result1->opponent_score;
                $player2Score = $result2->own_score;
                $player1ScoreFromPlayer2 = $result2->opponent_score;
            } else {
                $player1Score = $result2->own_score;
                $player1ScoreFromPlayer2 = $result2->opponent_score;
                $player2Score = $result1->own_score;
                $player2ScoreFromPlayer1 = $result1->opponent_score;
            }

            // Check if scores match
            if ($player1Score === $player1ScoreFromPlayer2 && $player2Score === $player2ScoreFromPlayer1) {
                // Scores match: auto-validate and complete match
                $result1->update(['status' => 'validated']);
                $result2->update(['status' => 'validated']);

                $this->swissService->updateMatchResult($match, $player1Score, $player2Score);
            } else {
                // Scores don't match: mark as disputed
                $match->update(['status' => 'disputed']);
                $result1->update(['status' => 'pending']);
                $result2->update(['status' => 'pending']);
            }
        }
    }

    /**
     * Moderator: Validate match result
     */
    public function validateMatchResult(TournamentMatch $match, User $moderator, int $player1Score, int $player2Score): TournamentMatch
    {
        if (!in_array($moderator->role, ['admin', 'moderator'])) {
            throw new \Exception('Unauthorized: Only admins and moderators can validate match results');
        }

        return DB::transaction(function () use ($match, $player1Score, $player2Score) {
            // Update all related match results
            MatchResult::where('match_id', $match->id)
                ->update(['status' => 'validated']);

            // Update match with final scores
            return $this->swissService->updateMatchResult($match, $player1Score, $player2Score);
        });
    }

    /**
     * Get match results for a match
     */
    public function getMatchResults(TournamentMatch $match)
    {
        return MatchResult::where('match_id', $match->id)
            ->with('submitter:id,name')
            ->get();
    }

    /**
     * Get disputed matches
     */
    public function getDisputedMatches()
    {
        return TournamentMatch::disputed()
            ->with(['tournament', 'player1', 'player2', 'matchResults'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Upload screenshot
     */
    protected function uploadScreenshot($file, int $matchId, int $userId): string
    {
        $filename = "match_{$matchId}_user_{$userId}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('match_results', $filename, 'public');

        return $path;
    }

    /**
     * Send submission emails to submitter and opponent
     */
    protected function sendSubmissionEmails(TournamentMatch $match, User $submitter, MatchResult $matchResult, bool $isUpdate = false): void
    {
        \Log::info("START sendSubmissionEmails for match {$match->id}, submitter: {$submitter->id}, isUpdate: " . ($isUpdate ? 'true' : 'false'));

        // Déterminer l'adversaire
        $opponentId = $match->player1_id === $submitter->id ? $match->player2_id : $match->player1_id;
        \Log::info("Opponent ID determined: {$opponentId}");

        $opponent = User::find($opponentId);

        if (!$opponent) {
            \Log::warning("No opponent found for match {$match->id}");
            return; // Pas d'adversaire (cas de bye)
        }

        \Log::info("Opponent found: {$opponent->name} ({$opponent->email})");

        // Charger les relations nécessaires pour les emails
        $match->load(['tournament.organizer', 'round']);

        try {
            if ($isUpdate) {
                // Emails de modification
                \Log::info("Sending UPDATE confirmation email to submitter {$submitter->email}");
                Mail::to($submitter)->send(
                    new MatchResultUpdatedConfirmationMail($match, $submitter, $opponent, $matchResult)
                );
                \Log::info("Update confirmation email sent to submitter {$submitter->id}");

                // Email de notification de modification pour l'adversaire
                \Log::info("Sending UPDATE notification email to opponent {$opponent->email}");
                Mail::to($opponent)->send(
                    new OpponentUpdatedResultMail($match, $opponent, $submitter, $matchResult)
                );
                \Log::info("Update notification email sent to opponent {$opponent->id}");
            } else {
                // Emails de première soumission
                \Log::info("Sending NEW confirmation email to submitter {$submitter->email}");
                Mail::to($submitter)->send(
                    new MatchResultSubmittedConfirmationMail($match, $submitter, $opponent, $matchResult)
                );
                \Log::info("Confirmation email sent to submitter {$submitter->id}");

                // Email de notification pour l'adversaire
                \Log::info("Sending NEW notification email to opponent {$opponent->email}");
                Mail::to($opponent)->send(
                    new OpponentSubmittedResultMail($match, $opponent, $submitter, $matchResult)
                );
                \Log::info("Notification email sent to opponent {$opponent->id}");
            }

            \Log::info("SUCCESS: Both submission emails sent for match {$match->id}");
        } catch (\Exception $e) {
            \Log::error("FAILED to send submission emails for match {$match->id}: {$e->getMessage()}");
            \Log::error("Exception trace: " . $e->getTraceAsString());
        }
    }
}
