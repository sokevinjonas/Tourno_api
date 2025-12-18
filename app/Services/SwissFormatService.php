<?php

namespace App\Services;

use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use Illuminate\Support\Facades\DB;

class SwissFormatService
{
    /**
     * Start tournament and generate first round
     */
    public function startTournament(Tournament $tournament): Round
    {
        if ($tournament->status !== 'registering') {
            throw new \Exception('Tournament must be in registering status to start');
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

            // Generate first round
            return $this->generateNextRound($tournament);
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
        $toBePaired = $participants->toArray();

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

            return $match->fresh();
        });
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
            // Get final rankings
            $rankings = TournamentRegistration::where('tournament_id', $tournament->id)
                ->where('status', 'registered')
                ->orderBy('tournament_points', 'desc')
                ->orderBy('wins', 'desc')
                ->orderBy('draws', 'desc')
                ->get();

            // Update final ranks
            foreach ($rankings as $index => $registration) {
                $rank = $index + 1;
                $registration->update(['final_rank' => $rank]);

                // Distribute prizes if defined
                if ($tournament->prize_distribution) {
                    $prizeDistribution = json_decode($tournament->prize_distribution, true);
                    if (isset($prizeDistribution[(string)$rank])) {
                        $prizeAmount = $prizeDistribution[(string)$rank];

                        // Credit prize to winner
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
}
