<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserGameStat;
use App\Models\UserGlobalStat;
use App\Models\TournamentRegistration;
use Illuminate\Console\Command;

class RecalculateAllUserStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:recalculate-all {--user_id= : Recalculer uniquement pour un utilisateur spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all user stats from scratch based on tournament registrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user_id');

        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("Utilisateur #{$userId} introuvable.");
                return 1;
            }
            $this->info("Recalcul des stats pour l'utilisateur #{$userId}...");
        } else {
            $users = User::all();
            $this->info("Recalcul des stats pour TOUS les utilisateurs...");

            if (!$this->confirm('Êtes-vous sûr de vouloir recalculer toutes les stats? Cela va RÉINITIALISER toutes les données.')) {
                $this->info('Opération annulée.');
                return 0;
            }
        }

        $this->info('');
        $this->warn('⚠️  ATTENTION: Cette commande va SUPPRIMER et RECRÉER toutes les stats globales');
        $this->warn('   à partir des données de tournament_registrations.');
        $this->info('');

        $progressBar = $this->output->createProgressBar(count($users));
        $progressBar->start();

        foreach ($users as $user) {
            $this->recalculateUserStats($user);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('');
        $this->info('');
        $this->info('✅ RECALCUL TERMINÉ');

        return 0;
    }

    protected function recalculateUserStats(User $user)
    {
        // Supprimer les anciennes stats
        UserGameStat::where('user_id', $user->id)->delete();
        UserGlobalStat::where('user_id', $user->id)->delete();

        // Récupérer toutes les inscriptions de tournois complétés
        $registrations = TournamentRegistration::where('user_id', $user->id)
            ->whereHas('tournament', function($q) {
                $q->where('status', 'completed');
            })
            ->with('tournament')
            ->get();

        if ($registrations->isEmpty()) {
            return;
        }

        // Initialiser les stats globales
        $globalStats = [
            'global_rating' => 1,
            'total_tournaments_played' => 0,
            'total_tournaments_won' => 0,
            'total_matches_played' => 0,
            'total_matches_won' => 0,
            'total_matches_lost' => 0,
            'total_matches_draw' => 0,
            'total_prize_money' => 0,
            'last_tournament_at' => null,
        ];

        // Stats par jeu
        $gameStats = [];

        foreach ($registrations as $registration) {
            $tournament = $registration->tournament;
            $game = $tournament->game;

            // Calculer les points de rating
            $points = $this->calculateRatingPoints(
                $registration->final_rank,
                $tournament->registrations()->where('status', 'registered')->count(),
                $tournament->entry_fee
            );

            // Initialiser les stats du jeu si nécessaire
            if (!isset($gameStats[$game])) {
                $gameStats[$game] = [
                    'rating_points' => 1,
                    'tournaments_played' => 0,
                    'tournaments_won' => 0,
                    'total_matches_played' => 0,
                    'total_matches_won' => 0,
                    'total_matches_lost' => 0,
                    'total_matches_draw' => 0,
                    'total_prize_money' => 0,
                    'last_tournament_at' => null,
                ];
            }

            // Mettre à jour les stats du jeu
            $gameStats[$game]['rating_points'] += $points;
            $gameStats[$game]['tournaments_played'] += 1;
            $gameStats[$game]['tournaments_won'] += ($registration->final_rank === 1 ? 1 : 0);
            $gameStats[$game]['total_matches_played'] += $registration->wins + $registration->losses + $registration->draws;
            $gameStats[$game]['total_matches_won'] += $registration->wins;
            $gameStats[$game]['total_matches_lost'] += $registration->losses;
            $gameStats[$game]['total_matches_draw'] += $registration->draws;
            $gameStats[$game]['total_prize_money'] += $registration->prize_won;
            $gameStats[$game]['last_tournament_at'] = $tournament->updated_at;

            // Mettre à jour les stats globales
            $globalStats['global_rating'] += $points;
            $globalStats['total_tournaments_played'] += 1;
            $globalStats['total_tournaments_won'] += ($registration->final_rank === 1 ? 1 : 0);
            $globalStats['total_matches_played'] += $registration->wins + $registration->losses + $registration->draws;
            $globalStats['total_matches_won'] += $registration->wins;
            $globalStats['total_matches_lost'] += $registration->losses;
            $globalStats['total_matches_draw'] += $registration->draws;
            $globalStats['total_prize_money'] += $registration->prize_won;

            if (!$globalStats['last_tournament_at'] || $tournament->updated_at > $globalStats['last_tournament_at']) {
                $globalStats['last_tournament_at'] = $tournament->updated_at;
            }
        }

        // Créer les stats par jeu
        foreach ($gameStats as $game => $stats) {
            UserGameStat::create([
                'user_id' => $user->id,
                'game' => $game,
                'rating_points' => $stats['rating_points'],
                'tournaments_played' => $stats['tournaments_played'],
                'tournaments_won' => $stats['tournaments_won'],
                'total_matches_played' => $stats['total_matches_played'],
                'total_matches_won' => $stats['total_matches_won'],
                'total_matches_lost' => $stats['total_matches_lost'],
                'total_matches_draw' => $stats['total_matches_draw'],
                'total_prize_money' => $stats['total_prize_money'],
                'last_tournament_at' => $stats['last_tournament_at'],
            ]);
        }

        // Créer les stats globales
        UserGlobalStat::create([
            'user_id' => $user->id,
            'global_rating' => $globalStats['global_rating'],
            'total_tournaments_played' => $globalStats['total_tournaments_played'],
            'total_tournaments_won' => $globalStats['total_tournaments_won'],
            'total_matches_played' => $globalStats['total_matches_played'],
            'total_matches_won' => $globalStats['total_matches_won'],
            'total_matches_lost' => $globalStats['total_matches_lost'],
            'total_matches_draw' => $globalStats['total_matches_draw'],
            'total_prize_money' => $globalStats['total_prize_money'],
            'last_tournament_at' => $globalStats['last_tournament_at'],
        ]);
    }

    /**
     * Calculate rating points based on rank, participants, and entry fee
     */
    protected function calculateRatingPoints(?int $rank, int $participants, float $entryFee): int
    {
        if ($rank === null) {
            return 1; // Participation point for cancelled tournaments
        }

        // Base points according to rank
        $rankPoints = match ($rank) {
            1 => 5,  // Champion
            2 => 3,  // 2nd place
            3 => 2,  // 3rd place
            4 => 1,  // 4th place
            default => 1  // Participation
        };

        // Bonus according to tournament size
        $participantBonus = match (true) {
            $participants >= 64 => 2.0,
            $participants >= 32 => 1.75,
            $participants >= 16 => 1.5,
            $participants >= 8 => 1.25,
            default => 1.0
        };

        // Bonus according to entry fee
        $feeBonus = match (true) {
            $entryFee >= 50 => 1.5,
            $entryFee >= 20 => 1.3,
            $entryFee >= 10 => 1.2,
            $entryFee > 0 => 1.1,
            default => 1.0
        };

        return (int) round($rankPoints * $participantBonus * $feeBonus);
    }
}
