<?php

namespace App\Services;

use App\Mail\MatchResultDrawMail;
use App\Mail\MatchResultLoserMail;
use App\Mail\MatchResultWinnerMail;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class KnockoutFormatService
{
    protected WalletLockService $walletLockService;

    public function __construct(WalletLockService $walletLockService)
    {
        $this->walletLockService = $walletLockService;
    }

    /**
     * Start tournament and generate all rounds
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

        // Verify participant count is power of 2
        if (!$this->isPowerOfTwo($participants)) {
            throw new \Exception('Single elimination requires a power of 2 participants (8, 16, 32, 64)');
        }

        return DB::transaction(function () use ($tournament) {
            // Update tournament status
            $tournament->update(['status' => 'in_progress']);

            // Lock organizer's funds for this tournament
            $this->walletLockService->lockFundsForTournament($tournament);

            // Generate all rounds structure
            $round = $this->generateAllRounds($tournament);

            // Notify all participants that tournament has started
            $this->notifyParticipants($tournament);

            return $round;
        });
    }

    /**
     * Generate all rounds for knockout tournament
     */
    protected function generateAllRounds(Tournament $tournament): Round
    {
        $participants = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('status', 'registered')
            ->get();

        $participantCount = $participants->count();
        $totalRounds = (int) log($participantCount, 2);

        $tournament->update(['total_rounds' => $totalRounds]);

        $rounds = [];
        $previousRoundMatches = [];

        // Create all rounds
        for ($i = 1; $i <= $totalRounds; $i++) {
            $roundName = $this->getRoundName($i, $totalRounds);
            $matchesInRound = (int) ($participantCount / pow(2, $i));

            $round = Round::create([
                'tournament_id' => $tournament->id,
                'round_number' => $i,
                'round_name' => $roundName,
                'status' => $i === 1 ? 'in_progress' : 'pending',
                'start_date' => $i === 1 ? now() : null,
            ]);

            $rounds[$i] = $round;

            // Create matches for this round
            if ($i === 1) {
                // First round: pair up participants
                $previousRoundMatches = $this->createFirstRoundMatches($tournament, $round, $participants);
            } else {
                // Subsequent rounds: create placeholder matches
                $previousRoundMatches = $this->createPlaceholderMatches($tournament, $round, $previousRoundMatches, $matchesInRound);
            }
        }

        return $rounds[1]; // Return first round
    }

    /**
     * Create first round matches with actual participants
     */
    protected function createFirstRoundMatches(Tournament $tournament, Round $round, $participants): array
    {
        $shuffled = $participants->shuffle();
        $matches = [];

        for ($i = 0; $i < count($shuffled); $i += 2) {
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round_id' => $round->id,
                'player1_id' => $shuffled[$i]->user_id,
                'player2_id' => $shuffled[$i + 1]->user_id,
                'status' => 'scheduled',
                'scheduled_at' => now(),
                'bracket_position' => (int) ($i / 2) + 1,
            ]);

            $matches[] = $match;
        }

        return $matches;
    }

    /**
     * Create placeholder matches for future rounds
     */
    protected function createPlaceholderMatches(Tournament $tournament, Round $round, array $previousMatches, int $matchCount): array
    {
        $matches = [];

        for ($i = 0; $i < $matchCount; $i++) {
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round_id' => $round->id,
                'player1_id' => null, // Will be filled by winner of previous match
                'player2_id' => null,
                'status' => 'scheduled',
                'bracket_position' => $i + 1,
            ]);

            // Link previous round matches to this match
            if (isset($previousMatches[$i * 2])) {
                $previousMatches[$i * 2]->update(['next_match_id' => $match->id]);
            }
            if (isset($previousMatches[$i * 2 + 1])) {
                $previousMatches[$i * 2 + 1]->update(['next_match_id' => $match->id]);
            }

            $matches[] = $match;
        }

        return $matches;
    }

    /**
     * Update match result and advance winner
     */
    public function updateMatchResult(TournamentMatch $match, int $player1Score, int $player2Score): TournamentMatch
    {
        return DB::transaction(function () use ($match, $player1Score, $player2Score) {
            // In knockout, draws are not allowed
            if ($player1Score === $player2Score) {
                throw new \Exception('Draws are not allowed in single elimination format. There must be a winner.');
            }

            // Determine winner
            $winnerId = $player1Score > $player2Score ? $match->player1_id : $match->player2_id;
            $loserId = $player1Score > $player2Score ? $match->player2_id : $match->player1_id;

            // Update match
            $match->update([
                'player1_score' => $player1Score,
                'player2_score' => $player2Score,
                'winner_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Mark loser as eliminated
            $this->eliminatePlayer($loserId, $match->tournament_id, $match->round->round_number);

            // Advance winner to next match
            if ($match->next_match_id) {
                $this->advanceWinner($winnerId, $match->next_match_id);
            }

            // Send email notifications
            $match = $match->fresh(['player1', 'player2', 'round.tournament']);

            $winner = $winnerId === $match->player1_id ? $match->player1 : $match->player2;
            $loser = $winnerId === $match->player1_id ? $match->player2 : $match->player1;
            $winnerScore = $winnerId === $match->player1_id ? $player1Score : $player2Score;
            $loserScore = $winnerId === $match->player1_id ? $player2Score : $player1Score;

            // Send winner email
            Mail::to($winner)->send(
                new MatchResultWinnerMail($winner, $match, $winnerScore, $loserScore)
            );

            // Send loser email (with elimination notice if final round)
            Mail::to($loser)->send(
                new MatchResultLoserMail($loser, $match, $loserScore, $winnerScore)
            );

            return $match;
        });
    }

    /**
     * Eliminate a player from the tournament
     */
    protected function eliminatePlayer(int $userId, int $tournamentId, int $roundNumber): void
    {
        TournamentRegistration::where('user_id', $userId)
            ->where('tournament_id', $tournamentId)
            ->update([
                'eliminated' => true,
                'eliminated_round' => $roundNumber,
                'eliminated_at' => now(),
            ]);
    }

    /**
     * Advance winner to next match
     */
    protected function advanceWinner(int $winnerId, int $nextMatchId): void
    {
        $nextMatch = TournamentMatch::find($nextMatchId);

        if ($nextMatch->player1_id === null) {
            $nextMatch->update(['player1_id' => $winnerId]);
        } elseif ($nextMatch->player2_id === null) {
            $nextMatch->update(['player2_id' => $winnerId]);
        }

        // Check if both players are now assigned
        if ($nextMatch->fresh()->player1_id && $nextMatch->fresh()->player2_id) {
            $nextMatch->update([
                'scheduled_at' => now(),
            ]);

            // Check if this is the first match with both players in this round
            $round = $nextMatch->round;
            if ($round->status === 'pending') {
                $round->update([
                    'status' => 'in_progress',
                    'start_date' => now(),
                ]);
            }
        }
    }

    /**
     * Complete tournament and distribute prizes
     */
    public function completeTournament(Tournament $tournament, WalletService $walletService): Tournament
    {
        return DB::transaction(function () use ($tournament, $walletService) {
            // Get final rankings based on elimination round (later elimination = better rank)
            $rankings = TournamentRegistration::where('tournament_id', $tournament->id)
                ->where('status', 'registered')
                ->orderByRaw('CASE WHEN eliminated = false THEN 1 ELSE 0 END DESC')
                ->orderBy('eliminated_round', 'desc')
                ->get();

            // Assign final ranks
            foreach ($rankings as $index => $registration) {
                $rank = $index + 1;
                $registration->update(['final_rank' => $rank]);

                // Distribute prizes if defined
                if ($tournament->prize_distribution) {
                    $prizeDistribution = json_decode($tournament->prize_distribution, true);
                    if (isset($prizeDistribution[(string)$rank])) {
                        $prizeAmount = $prizeDistribution[(string)$rank];

                        $walletService->processTournamentPrize(
                            $registration->user,
                            $prizeAmount,
                            $tournament->id,
                            $rank
                        );

                        $registration->update(['prize_won' => $prizeAmount]);
                    }
                }
            }

            // Update tournament status
            $tournament->update(['status' => 'completed']);

            return $tournament->fresh();
        });
    }

    /**
     * Get round name based on round number and total rounds
     */
    protected function getRoundName(int $roundNumber, int $totalRounds): string
    {
        $roundsFromEnd = $totalRounds - $roundNumber + 1;

        return match($roundsFromEnd) {
            1 => 'Final',
            2 => 'Semi-finals',
            3 => 'Quarter-finals',
            4 => 'Round of 16',
            5 => 'Round of 32',
            6 => 'Round of 64',
            default => "Round $roundNumber",
        };
    }

    /**
     * Check if a number is power of 2
     */
    protected function isPowerOfTwo(int $number): bool
    {
        return ($number & ($number - 1)) === 0 && $number > 0;
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
                new \App\Mail\TournamentStartedMail($tournament, $user, $firstMatch)
            );

            \Log::info("Sent tournament started email to user {$user->id} for tournament {$tournament->id}");
        }
    }
}
