<?php

namespace App\Services;

use App\Models\User;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Mail\MatchResultDrawMail;
use App\Mail\MatchResultLoserMail;
use Illuminate\Support\Facades\DB;
use App\Mail\MatchResultWinnerMail;
use App\Mail\TournamentStartedMail;
use App\Mail\NextRoundGeneratedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\TournamentRegistration;

class SwissFormatService
{
    protected WalletLockService $walletLockService;
    protected UserStatsService $userStatsService;

    public function __construct(
        WalletLockService $walletLockService,
        UserStatsService $userStatsService
    ) {
        $this->walletLockService = $walletLockService;
        $this->userStatsService = $userStatsService;
    }

    /**
     * Start tournament and generate first round
     */
    public function startTournament(Tournament $tournament): Round
    {
        if ($tournament->status !== 'open') {
            throw new \Exception('Tournament must be in open status to start');
        }

        $participants = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('status', 'registered')
            ->count();

        if ($participants < 2) {
            throw new \Exception('Tournament must have at least 2 participants to start');
        }

        return DB::transaction(function () use ($tournament) {
            // Update tournament status
            $tournament->update(['status' => 'in_progress']);

            // Lock organizer's funds for this tournament
            $this->walletLockService->lockFundsForTournament($tournament);

            // Generate first round
            $round = $this->generateNextRound($tournament);

            // Notify all participants that tournament has started
            $this->notifyParticipants($tournament);

            return $round;
        });
    }

    /**
     * Generate next round for tournament
     */
    public function generateNextRound(Tournament $tournament): Round
    {
        // Get current round number
        $lastRound = Round::where('tournament_id', $tournament->id)
            ->orderBy('round_number', 'desc')
            ->first();

        // Si ce n'est pas le premier round, vérifier que tous les matchs du round précédent sont terminés
        if ($lastRound) {
            $pendingMatches = $lastRound->matches()
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            if ($pendingMatches > 0) {
                throw new \Exception("Cannot generate next round: {$pendingMatches} match(es) from Round {$lastRound->round_number} are not completed yet");
            }
        }

        $nextRoundNumber = $lastRound ? $lastRound->round_number + 1 : 1;

        // Calculate total rounds needed
        $participants = $tournament->registrations()->where('status', 'registered')->count();
        $totalRounds = (int) ceil(log($participants, 2));

        if ($nextRoundNumber > $totalRounds) {
            throw new \Exception('All rounds have been completed');
        }

        return DB::transaction(function () use ($tournament, $nextRoundNumber) {
            // Create round
            $round = Round::create([
                'tournament_id' => $tournament->id,
                'round_number' => $nextRoundNumber,
                'status' => 'in_progress',
                'start_date' => now(),
            ]);

            // Generate pairings
            $this->generatePairings($tournament, $round);

            // Send email notifications to all players in this round
            $matches = $round->matches()->with(['player1', 'player2'])->get();
            foreach ($matches as $match) {
                // Send email to player 1
                if ($match->player1) {
                    Mail::to($match->player1)->send(
                        new NextRoundGeneratedMail($match->player1, $tournament, $round, $match)
                    );
                }

                // Send email to player 2
                if ($match->player2) {
                    Mail::to($match->player2)->send(
                        new NextRoundGeneratedMail($match->player2, $tournament, $round, $match)
                    );
                }
            }

            return $round;
        });
    }

    /**
     * Generate pairings for a round using Swiss system
     */
    protected function generatePairings(Tournament $tournament, Round $round): void
    {
        // Get all registered participants ordered by points, then wins
        $participants = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('status', 'registered')
            ->orderBy('tournament_points', 'desc')
            ->orderBy('wins', 'desc')
            ->get();

        if ($round->round_number === 1) {
            // First round: random pairing
            $this->randomPairing($tournament, $round, $participants);
        } else {
            // Subsequent rounds: Swiss pairing (same score players)
            $this->swissPairing($tournament, $round, $participants);
        }
    }

    /**
     * Random pairing for first round
     */
    protected function randomPairing(Tournament $tournament, Round $round, $participants): void
    {
        $shuffled = $participants->shuffle();
        $paired = [];

        for ($i = 0; $i < count($shuffled); $i += 2) {
            if (isset($shuffled[$i + 1])) {
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round_id' => $round->id,
                    'player1_id' => $shuffled[$i]->user_id,
                    'player2_id' => $shuffled[$i + 1]->user_id,
                    'status' => 'scheduled',
                    'scheduled_at' => now(),
                    'deadline_at' => now()->addMinutes($tournament->match_deadline_minutes),
                ]);
            } else {
                // Odd number of players: bye (automatic win)
                $this->assignBye($tournament, $round, $shuffled[$i]->user_id);
            }
        }
    }

    /**
     * Swiss pairing: pair players with similar scores
     */
    protected function swissPairing(Tournament $tournament, Round $round, $participants): void
    {
        $paired = [];
        $toBePaired = $participants->makeVisible(['user_id'])->toArray();

        while (count($toBePaired) > 0) {
            $player1 = array_shift($toBePaired);

            // Find opponent with same or close score who hasn't played against player1
            $opponent = null;
            foreach ($toBePaired as $key => $candidate) {
                if (!$this->havePlayed($tournament, $player1['user_id'], $candidate['user_id'])) {
                    $opponent = $candidate;
                    unset($toBePaired[$key]);
                    $toBePaired = array_values($toBePaired);
                    break;
                }
            }

            if ($opponent) {
                // Create match
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round_id' => $round->id,
                    'player1_id' => $player1['user_id'],
                    'player2_id' => $opponent['user_id'],
                    'status' => 'scheduled',
                    'scheduled_at' => now(),
                    'deadline_at' => now()->addMinutes($tournament->match_deadline_minutes),
                ]);
            } else {
                // No valid opponent: assign bye
                $this->assignBye($tournament, $round, $player1['user_id']);
            }
        }
    }

    /**
     * Check if two players have already played against each other
     */
    protected function havePlayed(Tournament $tournament, int $player1Id, int $player2Id): bool
    {
        return TournamentMatch::where('tournament_id', $tournament->id)
            ->where(function ($query) use ($player1Id, $player2Id) {
                $query->where(function ($q) use ($player1Id, $player2Id) {
                    $q->where('player1_id', $player1Id)
                      ->where('player2_id', $player2Id);
                })->orWhere(function ($q) use ($player1Id, $player2Id) {
                    $q->where('player1_id', $player2Id)
                      ->where('player2_id', $player1Id);
                });
            })
            ->exists();
    }

    /**
     * Assign bye (automatic win) to a player
     */
    protected function assignBye(Tournament $tournament, Round $round, int $playerId): void
    {
        $match = TournamentMatch::create([
            'tournament_id' => $tournament->id,
            'round_id' => $round->id,
            'player1_id' => $playerId,
            'player2_id' => null, // Bye
            'player1_score' => 1,
            'player2_score' => 0,
            'winner_id' => $playerId,
            'status' => 'completed',
            'scheduled_at' => now(),
            'completed_at' => now(),
        ]);

        // Update player stats for bye
        $this->updatePlayerStats($playerId, $tournament->id, 'win');
    }

    /**
     * Update match result and player stats
     */
    public function updateMatchResult(TournamentMatch $match, int $player1Score, int $player2Score): TournamentMatch
    {
        return DB::transaction(function () use ($match, $player1Score, $player2Score) {
            // Determine winner
            $winnerId = null;
            if ($player1Score > $player2Score) {
                $winnerId = $match->player1_id;
                $result = 'win';
            } elseif ($player2Score > $player1Score) {
                $winnerId = $match->player2_id;
                $result = 'loss';
            } else {
                $result = 'draw';
            }

            // Update match
            $match->update([
                'player1_score' => $player1Score,
                'player2_score' => $player2Score,
                'winner_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Update player1 stats
            $this->updatePlayerStats($match->player1_id, $match->tournament_id, $result);

            // Update player2 stats
            $player2Result = $result === 'win' ? 'loss' : ($result === 'loss' ? 'win' : 'draw');
            $this->updatePlayerStats($match->player2_id, $match->tournament_id, $player2Result);

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
            } else {
                // Draw match - send draw email to both players
                Mail::to($match->player1)->send(
                    new MatchResultDrawMail($match->player1, $match, $player1Score)
                );

                Mail::to($match->player2)->send(
                    new MatchResultDrawMail($match->player2, $match, $player2Score)
                );
            }

            return $match;
        });
    }

    /**
     * Update match scores (for already completed matches)
     * This will revert old stats and apply new ones
     */
    public function updateMatchScore(TournamentMatch $match, int $player1Score, int $player2Score): TournamentMatch
    {
        return DB::transaction(function () use ($match, $player1Score, $player2Score) {
            // Save old scores to revert stats
            $oldPlayer1Score = $match->player1_score;
            $oldPlayer2Score = $match->player2_score;
            $oldWinnerId = $match->winner_id;

            // Determine old result for player1
            $oldResult = $this->determineResult($oldPlayer1Score, $oldPlayer2Score);

            // Revert old stats for both players
            $this->revertPlayerStats($match->player1_id, $match->tournament_id, $oldResult);
            $oldPlayer2Result = $oldResult === 'win' ? 'loss' : ($oldResult === 'loss' ? 'win' : 'draw');
            $this->revertPlayerStats($match->player2_id, $match->tournament_id, $oldPlayer2Result);

            // Determine new winner and result
            $newWinnerId = null;
            $newResult = $this->determineResult($player1Score, $player2Score);

            if ($player1Score > $player2Score) {
                $newWinnerId = $match->player1_id;
            } elseif ($player2Score > $player1Score) {
                $newWinnerId = $match->player2_id;
            }

            // Update match with new scores
            $match->update([
                'player1_score' => $player1Score,
                'player2_score' => $player2Score,
                'winner_id' => $newWinnerId,
            ]);

            // Apply new stats for both players
            $this->updatePlayerStats($match->player1_id, $match->tournament_id, $newResult);
            $newPlayer2Result = $newResult === 'win' ? 'loss' : ($newResult === 'loss' ? 'win' : 'draw');
            $this->updatePlayerStats($match->player2_id, $match->tournament_id, $newPlayer2Result);

            // Send email notifications about score correction
            $match = $match->fresh(['player1', 'player2', 'round.tournament']);

            // Send correction email to player 1
            $player1Result = $this->determineResult($player1Score, $player2Score);
            Mail::to($match->player1)->send(
                new \App\Mail\MatchScoreCorrectedMail(
                    $match->player1,
                    $match,
                    $oldPlayer1Score,
                    $oldPlayer2Score,
                    $player1Score,
                    $player2Score,
                    $player1Result
                )
            );

            // Send correction email to player 2
            $player2Result = $player1Result === 'win' ? 'loss' : ($player1Result === 'loss' ? 'win' : 'draw');
            Mail::to($match->player2)->send(
                new \App\Mail\MatchScoreCorrectedMail(
                    $match->player2,
                    $match,
                    $oldPlayer2Score,
                    $oldPlayer1Score,
                    $player2Score,
                    $player1Score,
                    $player2Result
                )
            );

            return $match;
        });
    }

    /**
     * Determine match result (win/loss/draw) for player1
     */
    protected function determineResult(int $player1Score, int $player2Score): string
    {
        if ($player1Score > $player2Score) {
            return 'win';
        } elseif ($player2Score > $player1Score) {
            return 'loss';
        }
        return 'draw';
    }

    /**
     * Revert player statistics (subtract points when updating a match)
     */
    protected function revertPlayerStats(int $userId, int $tournamentId, string $result): void
    {
        $registration = TournamentRegistration::where('user_id', $userId)
            ->where('tournament_id', $tournamentId)
            ->first();

        if (!$registration) {
            return;
        }

        $updates = [];

        if ($result === 'win') {
            $updates['wins'] = max(0, $registration->wins - 1);
            $updates['tournament_points'] = max(0, $registration->tournament_points - 3);
        } elseif ($result === 'draw') {
            $updates['draws'] = max(0, $registration->draws - 1);
            $updates['tournament_points'] = max(0, $registration->tournament_points - 1);
        } elseif ($result === 'loss') {
            $updates['losses'] = max(0, $registration->losses - 1);
        }

        $registration->update($updates);
    }

    /**
     * Update player statistics
     */
    protected function updatePlayerStats(int $userId, int $tournamentId, string $result): void
    {
        $registration = TournamentRegistration::where('user_id', $userId)
            ->where('tournament_id', $tournamentId)
            ->first();

        if (!$registration) {
            return;
        }

        $updates = [];

        if ($result === 'win') {
            $updates['wins'] = $registration->wins + 1;
            $updates['tournament_points'] = $registration->tournament_points + 3;
        } elseif ($result === 'draw') {
            $updates['draws'] = $registration->draws + 1;
            $updates['tournament_points'] = $registration->tournament_points + 1;
        } elseif ($result === 'loss') {
            $updates['losses'] = $registration->losses + 1;
        }

        $registration->update($updates);
    }

    /**
     * Complete round
     */
    public function completeRound(Round $round): Round
    {
        // Check if all matches in round are completed
        $pendingMatches = TournamentMatch::where('round_id', $round->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'pending_validation'])
            ->count();

        if ($pendingMatches > 0) {
            throw new \Exception('Cannot complete round while matches are still pending');
        }

        $round->update([
            'status' => 'completed',
            'end_date' => now(),
        ]);

        return $round->fresh();
    }

    /**
     * Complete tournament and distribute prizes
     */
    public function completeTournament(Tournament $tournament, WalletService $walletService): Tournament
    {
        return DB::transaction(function () use ($tournament, $walletService) {
            // Verify all matches are completed
            $pendingMatches = TournamentMatch::where('tournament_id', $tournament->id)
                ->whereIn('status', ['scheduled', 'in_progress', 'pending_validation', 'disputed'])
                ->count();

            if ($pendingMatches > 0) {
                throw new \Exception("Cannot complete tournament while {$pendingMatches} match(es) are still pending");
            }

            // Get final rankings
            $rankings = TournamentRegistration::where('tournament_id', $tournament->id)
                ->where('status', 'registered')
                ->orderBy('tournament_points', 'desc')
                ->orderBy('wins', 'desc')
                ->orderBy('draws', 'desc')
                ->get();

            // Prepare winners array for payout
            $winners = [];
            $totalPrizePool = 0;

            // Update final ranks and prepare winners
            foreach ($rankings as $index => $registration) {
                $rank = $index + 1;
                $registration->update(['final_rank' => $rank]);

                // Prepare prize distribution if defined
                if ($tournament->prize_distribution) {
                    $prizeDistribution = json_decode($tournament->prize_distribution, true);

                    // Support both "1st", "2nd", "3rd" AND "1", "2", "3" formats
                    $rankKey = $this->getRankKey($rank);
                    $prizeAmount = $prizeDistribution[$rankKey] ?? $prizeDistribution[(string)$rank] ?? null;

                    if ($prizeAmount !== null && $prizeAmount > 0) {
                        $winners[] = [
                            'user_id' => $registration->user_id,
                            'prize_amount' => $prizeAmount,
                            'rank' => $rank,
                        ];

                        $totalPrizePool += $prizeAmount;
                        $registration->update(['prize_won' => $prizeAmount]);
                    }
                }
            }

            // Process payouts using WalletLockService
            if (!empty($winners)) {
                $this->walletLockService->processPayouts($tournament, $winners);
                $this->walletLockService->releaseFunds($tournament);
            }

            // Update user stats for all participants
            foreach ($rankings as $registration) {
                $this->userStatsService->updateStatsAfterTournament(
                    $registration->user,
                    $tournament,
                    $registration
                );
            }

            // Update tournament status
            $tournament->update(['status' => 'completed']);

            // Send emails to all participants via bulk job
            $this->sendCompletionEmails($tournament, $rankings, $winners);

            return $tournament->fresh();
        });
    }

    /**
     * Send completion emails to all participants
     */
    protected function sendCompletionEmails(Tournament $tournament, $rankings, array $winners): void
    {
        $emailData = [];
        $topPlayers = $rankings->take(3);

        // Prepare emails for all participants
        foreach ($rankings as $registration) {
            $user = $registration->user;

            // Check if this user won a prize
            $wonPrize = collect($winners)->firstWhere('user_id', $user->id);

            // If user won a prize, send prize notification email
            if ($wonPrize) {
                $emailData[] = [
                    'recipient' => $user,
                    'mailable' => new \App\Mail\TournamentPrizeWonMail(
                        $user,
                        $tournament,
                        $wonPrize['rank'],
                        $wonPrize['prize_amount']
                    ),
                    'context' => [
                        'user_id' => $user->id,
                        'tournament_id' => $tournament->id,
                        'type' => 'prize_won',
                        'rank' => $wonPrize['rank'],
                        'amount' => $wonPrize['prize_amount'],
                    ],
                ];
            }

            // Send tournament completion email to everyone
            $emailData[] = [
                'recipient' => $user,
                'mailable' => new \App\Mail\TournamentCompletedMail(
                    $user,
                    $tournament,
                    $registration,
                    $topPlayers
                ),
                'context' => [
                    'user_id' => $user->id,
                    'tournament_id' => $tournament->id,
                    'type' => 'tournament_completed',
                    'rank' => $registration->final_rank,
                ],
            ];
        }

        // Dispatch bulk email job with 100ms delay between emails
        \App\Jobs\SendBulkEmailsJob::dispatch($emailData, 100);

        \Log::info("Tournament completion emails queued", [
            'tournament_id' => $tournament->id,
            'total_emails' => count($emailData),
        ]);
    }

    /**
     * Get the rank key in the prize_distribution array
     * Supports both "1st", "2nd", "3rd" format AND "1", "2", "3" format
     */
    private function getRankKey(int $rank): string
    {
        return match ($rank) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => (string)$rank . 'th',
        };
    }

    /**
     * Notify all participants that tournament has started
     */
    protected function notifyParticipants(Tournament $tournament): void
    {
        $tournament->load(['registrations.user', 'rounds']);

        // Get first round
        $firstRound = $tournament->rounds()->where('round_number', 1)->first();

        if (!$firstRound) {
            \Log::warning("No first round found for tournament {$tournament->id}");
            return;
        }

        foreach ($tournament->registrations as $registration) {
            $user = $registration->user;

            // Find user's first match
            $firstMatch = $tournament->matches()
                ->where('round_id', $firstRound->id)
                ->where(function ($query) use ($user) {
                    $query->where('player1_id', $user->id)
                        ->orWhere('player2_id', $user->id);
                })
                ->with(['player1', 'player2'])
                ->first();

            // Send email notification
            \Mail::to($user)->send(
                new TournamentStartedMail($tournament, $user, $firstMatch)
            );

            \Log::info("Sent tournament started email to user {$user->id} for tournament {$tournament->id}");
        }
    }
}
