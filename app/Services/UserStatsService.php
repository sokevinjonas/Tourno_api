<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use App\Models\UserGameStat;
use App\Models\UserGlobalStat;
use Illuminate\Support\Facades\DB;

class UserStatsService
{
    /**
     * Update user stats after tournament completion
     */
    public function updateStatsAfterTournament(
        User $user,
        Tournament $tournament,
        TournamentRegistration $registration
    ): void {
        DB::transaction(function () use ($user, $tournament, $registration) {
            // Calculate rating points earned
            $points = $this->calculateRatingPoints(
                $registration->final_rank,
                $tournament->registrations()->where('status', 'registered')->count(),
                $tournament->entry_fee
            );

            // Update game-specific stats
            $this->updateGameStats($user, $tournament, $registration, $points);

            // Update global stats
            $this->updateGlobalStats($user, $registration, $points);
        });
    }

    /**
     * Calculate rating points based on rank, participants, and entry fee
     */
    protected function calculateRatingPoints(
        int $rank,
        int $participants,
        float $entryFee
    ): int {
        // Base points according to rank
        $rankPoints = match ($rank) {
            1 => 100,  // Champion
            2 => 75,   // 2nd place
            3 => 50,   // 3rd place
            4 => 40,
            5 => 30,
            6 => 25,
            7 => 20,
            8 => 15,
            default => 10  // Participation
        };

        // Bonus according to tournament size
        $participantBonus = match (true) {
            $participants >= 64 => 2.0,   // x2
            $participants >= 32 => 1.75,  // x1.75
            $participants >= 16 => 1.5,   // x1.5
            $participants >= 8 => 1.25,   // x1.25
            default => 1.0
        };

        // Bonus according to entry fee (paid tournaments valued more)
        $feeBonus = match (true) {
            $entryFee >= 50 => 1.5,
            $entryFee >= 20 => 1.3,
            $entryFee >= 10 => 1.2,
            $entryFee > 0 => 1.1,
            default => 1.0  // Free
        };

        // Final points calculation
        return (int) round($rankPoints * $participantBonus * $feeBonus);
    }

    /**
     * Update game-specific stats
     */
    protected function updateGameStats(
        User $user,
        Tournament $tournament,
        TournamentRegistration $registration,
        int $points
    ): void {
        $gameStat = UserGameStat::firstOrCreate(
            [
                'user_id' => $user->id,
                'game' => $tournament->game,
            ],
            [
                'rating_points' => 1000,
                'tournaments_played' => 0,
                'tournaments_won' => 0,
                'total_matches_played' => 0,
                'total_matches_won' => 0,
                'total_matches_lost' => 0,
                'total_matches_draw' => 0,
                'total_prize_money' => 0,
            ]
        );

        $gameStat->update([
            'rating_points' => $gameStat->rating_points + $points,
            'tournaments_played' => $gameStat->tournaments_played + 1,
            'tournaments_won' => $gameStat->tournaments_won + ($registration->final_rank === 1 ? 1 : 0),
            'total_matches_played' => $gameStat->total_matches_played + $registration->wins + $registration->losses + $registration->draws,
            'total_matches_won' => $gameStat->total_matches_won + $registration->wins,
            'total_matches_lost' => $gameStat->total_matches_lost + $registration->losses,
            'total_matches_draw' => $gameStat->total_matches_draw + $registration->draws,
            'total_prize_money' => $gameStat->total_prize_money + $registration->prize_won,
            'last_tournament_at' => now(),
        ]);
    }

    /**
     * Update global stats (all games combined)
     */
    protected function updateGlobalStats(
        User $user,
        TournamentRegistration $registration,
        int $points
    ): void {
        $globalStat = UserGlobalStat::firstOrCreate(
            ['user_id' => $user->id],
            [
                'global_rating' => 1000,
                'total_tournaments_played' => 0,
                'total_tournaments_won' => 0,
                'total_matches_played' => 0,
                'total_matches_won' => 0,
                'total_matches_lost' => 0,
                'total_matches_draw' => 0,
                'total_prize_money' => 0,
            ]
        );

        $globalStat->update([
            'global_rating' => $globalStat->global_rating + $points,
            'total_tournaments_played' => $globalStat->total_tournaments_played + 1,
            'total_tournaments_won' => $globalStat->total_tournaments_won + ($registration->final_rank === 1 ? 1 : 0),
            'total_matches_played' => $globalStat->total_matches_played + $registration->wins + $registration->losses + $registration->draws,
            'total_matches_won' => $globalStat->total_matches_won + $registration->wins,
            'total_matches_lost' => $globalStat->total_matches_lost + $registration->losses,
            'total_matches_draw' => $globalStat->total_matches_draw + $registration->draws,
            'total_prize_money' => $globalStat->total_prize_money + $registration->prize_won,
            'last_tournament_at' => now(),
        ]);
    }
}
