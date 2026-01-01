<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use App\Models\UserGameStat;
use App\Models\UserGlobalStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Get global leaderboard (all games)
     */
    public function globalLeaderboard(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 25), 100);

        $leaderboard = UserGlobalStat::with('user:id,name,avatar_url')
            ->orderByRating()
            ->paginate($perPage);

        $data = $leaderboard->map(function ($stat, $index) use ($leaderboard) {
            $rank = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;

            $winRate = $stat->total_matches_played > 0
                ? round(($stat->total_matches_won / $stat->total_matches_played) * 100, 1)
                : 0;

            return [
                'rank' => $rank,
                'user' => [
                    'id' => $stat->user->id,
                    'name' => $stat->user->name,
                    'avatar_url' => $stat->user->avatar_url,
                ],
                'stats' => [
                    'global_rating' => $stat->global_rating,
                    'tournaments_played' => $stat->total_tournaments_played,
                    'tournaments_won' => $stat->total_tournaments_won,
                    'win_rate' => $winRate,
                    'total_matches_played' => $stat->total_matches_played,
                    'total_matches_won' => $stat->total_matches_won,
                    'total_prize_money' => (float) $stat->total_prize_money,
                ],
            ];
        });

        return response()->json([
            'leaderboard' => $data,
            'pagination' => [
                'current_page' => $leaderboard->currentPage(),
                'last_page' => $leaderboard->lastPage(),
                'per_page' => $leaderboard->perPage(),
                'total' => $leaderboard->total(),
            ],
        ]);
    }

    /**
     * Get leaderboard by game
     */
    public function byGameLeaderboard(Request $request, string $game): JsonResponse
    {
        // Validate game
        $validGames = ['efootball', 'fc_mobile', 'dream_league_soccer'];
        if (!in_array($game, $validGames)) {
            return response()->json([
                'message' => 'Invalid game',
                'valid_games' => $validGames,
            ], 400);
        }

        $perPage = min((int) $request->get('per_page', 25), 100);

        $leaderboard = UserGameStat::with('user:id,name,avatar_url')
            ->byGame($game)
            ->orderByRating()
            ->paginate($perPage);

        $data = $leaderboard->map(function ($stat, $index) use ($leaderboard) {
            $rank = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $index + 1;

            $winRate = $stat->total_matches_played > 0
                ? round(($stat->total_matches_won / $stat->total_matches_played) * 100, 1)
                : 0;

            return [
                'rank' => $rank,
                'user' => [
                    'id' => $stat->user->id,
                    'name' => $stat->user->name,
                    'avatar_url' => $stat->user->avatar_url,
                ],
                'stats' => [
                    'rating_points' => $stat->rating_points,
                    'tournaments_played' => $stat->tournaments_played,
                    'tournaments_won' => $stat->tournaments_won,
                    'win_rate' => $winRate,
                    'total_matches_played' => $stat->total_matches_played,
                    'total_matches_won' => $stat->total_matches_won,
                    'total_prize_money' => (float) $stat->total_prize_money,
                ],
            ];
        });

        return response()->json([
            'game' => $game,
            'leaderboard' => $data,
            'pagination' => [
                'current_page' => $leaderboard->currentPage(),
                'last_page' => $leaderboard->lastPage(),
                'per_page' => $leaderboard->perPage(),
                'total' => $leaderboard->total(),
            ],
        ]);
    }

    /**
     * Get user stats (profile)
     */
    public function userStats(int $userId): JsonResponse
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // Global stats
        $globalStat = UserGlobalStat::where('user_id', $userId)->first();

        $globalStats = null;
        $globalRank = null;

        if ($globalStat) {
            // Calculate global rank
            $globalRank = UserGlobalStat::where('global_rating', '>', $globalStat->global_rating)->count() + 1;

            $globalWinRate = $globalStat->total_matches_played > 0
                ? round(($globalStat->total_matches_won / $globalStat->total_matches_played) * 100, 1)
                : 0;

            $globalStats = [
                'global_rating' => $globalStat->global_rating,
                'global_rank' => $globalRank,
                'tournaments_played' => $globalStat->total_tournaments_played,
                'tournaments_won' => $globalStat->total_tournaments_won,
                'total_matches_played' => $globalStat->total_matches_played,
                'total_matches_won' => $globalStat->total_matches_won,
                'total_matches_lost' => $globalStat->total_matches_lost,
                'total_matches_draw' => $globalStat->total_matches_draw,
                'win_rate' => $globalWinRate,
                'total_prize_money' => (float) $globalStat->total_prize_money,
            ];
        }

        // Stats by game
        $gameStats = UserGameStat::where('user_id', $userId)->get();

        $statsByGame = [];
        foreach ($gameStats as $gameStat) {
            // Calculate rank for this game
            $gameRank = UserGameStat::byGame($gameStat->game)
                ->where('rating_points', '>', $gameStat->rating_points)
                ->count() + 1;

            $gameWinRate = $gameStat->total_matches_played > 0
                ? round(($gameStat->total_matches_won / $gameStat->total_matches_played) * 100, 1)
                : 0;

            $statsByGame[$gameStat->game] = [
                'rating_points' => $gameStat->rating_points,
                'rank' => $gameRank,
                'tournaments_played' => $gameStat->tournaments_played,
                'tournaments_won' => $gameStat->tournaments_won,
                'total_matches_played' => $gameStat->total_matches_played,
                'total_matches_won' => $gameStat->total_matches_won,
                'win_rate' => $gameWinRate,
                'total_prize_money' => (float) $gameStat->total_prize_money,
            ];
        }

        // Recent tournaments
        $recentTournaments = TournamentRegistration::where('user_id', $userId)
            ->whereHas('tournament', function ($query) {
                $query->where('status', 'completed');
            })
            ->with('tournament:id,name,game,status')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->tournament->id,
                    'name' => $reg->tournament->name,
                    'game' => $reg->tournament->game,
                    'final_rank' => $reg->final_rank,
                    'prize_won' => (float) $reg->prize_won,
                    'completed_at' => $reg->updated_at,
                ];
            });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
            ],
            'global_stats' => $globalStats,
            'stats_by_game' => $statsByGame,
            'recent_tournaments' => $recentTournaments,
        ]);
    }

    /**
     * Get tournament rankings
     */
    public function tournamentRankings(string $tournamentId): JsonResponse
    {
        $tournament = Tournament::where('uuid', $tournamentId)->first();

        if (!$tournament) {
            return response()->json([
                'message' => 'Tournament not found',
            ], 404);
        }

        $rankings = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('status', 'registered')
            ->with('user:id,name,avatar_url')
            ->orderBy('final_rank', 'asc')
            ->get()
            ->map(function ($reg) {
                return [
                    'rank' => $reg->final_rank,
                    'user' => [
                        'id' => $reg->user->id,
                        'name' => $reg->user->name,
                        'avatar_url' => $reg->user->avatar_url,
                    ],
                    'stats' => [
                        'tournament_points' => $reg->tournament_points,
                        'wins' => $reg->wins,
                        'losses' => $reg->losses,
                        'draws' => $reg->draws,
                        'eliminated' => $reg->eliminated,
                        'eliminated_round' => $reg->eliminated_round,
                        'prize_won' => (float) $reg->prize_won,
                    ],
                ];
            });

        return response()->json([
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'game' => $tournament->game,
                'format' => $tournament->format,
                'status' => $tournament->status,
                'participants_count' => $tournament->registrations()->where('status', 'registered')->count(),
            ],
            'rankings' => $rankings,
        ]);
    }
}
