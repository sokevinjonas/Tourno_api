<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Services\UserStatsService;
use App\Services\WalletService;
use Illuminate\Console\Command;

class FixKnockoutTournamentStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:fix-knockout-stats {tournament_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix knockout tournament stats and redistribute prizes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tournamentId = $this->argument('tournament_id');

        // Si pas d'ID fourni, chercher le dernier tournoi eFootball complété
        if (!$tournamentId) {
            $this->info('Recherche du dernier tournoi eFootball avec 16 participants...');

            $tournament = Tournament::where('game', 'efootball')
                ->where('status', 'completed')
                ->where('format', 'single_elimination')
                ->withCount('registrations')
                ->having('registrations_count', 16)
                ->latest('updated_at')
                ->first();

            if (!$tournament) {
                $this->error('Aucun tournoi trouvé correspondant aux critères.');
                return 1;
            }

            $this->info("Tournoi trouvé: {$tournament->name} (ID: {$tournament->id})");

            if (!$this->confirm('Voulez-vous corriger ce tournoi?')) {
                $this->info('Opération annulée.');
                return 0;
            }
        } else {
            $tournament = Tournament::find($tournamentId);

            if (!$tournament) {
                $this->error("Tournoi #{$tournamentId} introuvable.");
                return 1;
            }
        }

        $this->info('');
        $this->info('=== TOURNOI ===');
        $this->info("Nom: {$tournament->name}");
        $this->info("ID: {$tournament->id}");
        $this->info("UUID: {$tournament->uuid}");
        $this->info("Format: {$tournament->format}");
        $this->info("Statut: {$tournament->status}");
        $this->info('');

        // Étape 1: Recalculer les stats
        $this->info('=== ÉTAPE 1: RECALCUL DES STATS ===');
        $this->recalculateStats($tournament);

        // Étape 2: Vérifier et distribuer les prix
        $this->info('');
        $this->info('=== ÉTAPE 2: DISTRIBUTION DES PRIX ===');
        $this->distributePrizes($tournament);

        // Étape 3: Mettre à jour les stats globales
        $this->info('');
        $this->info('=== ÉTAPE 3: STATS GLOBALES ===');
        $this->updateGlobalStats($tournament);

        $this->info('');
        $this->info('✅ CORRECTION TERMINÉE');

        return 0;
    }

    protected function recalculateStats(Tournament $tournament)
    {
        $registrations = TournamentRegistration::where('tournament_id', $tournament->id)->get();

        foreach ($registrations as $reg) {
            $wins = TournamentMatch::where('tournament_id', $tournament->id)
                ->where('winner_id', $reg->user_id)
                ->where('status', 'completed')
                ->count();

            $losses = TournamentMatch::where('tournament_id', $tournament->id)
                ->where(function($q) use ($reg) {
                    $q->where('player1_id', $reg->user_id)
                      ->orWhere('player2_id', $reg->user_id);
                })
                ->where('winner_id', '!=', $reg->user_id)
                ->where('status', 'completed')
                ->count();

            $oldWins = $reg->wins;
            $oldPoints = $reg->tournament_points;

            $reg->update([
                'wins' => $wins,
                'losses' => $losses,
                'tournament_points' => $wins * 3
            ]);

            $this->line("{$reg->user->name}: {$oldWins}→{$wins}W, {$oldPoints}→" . ($wins*3) . "pts");
        }
    }

    protected function distributePrizes(Tournament $tournament)
    {
        if (!$tournament->prize_distribution) {
            $this->warn('Pas de distribution de prix configurée pour ce tournoi.');
            return;
        }

        $prizeDistribution = json_decode($tournament->prize_distribution, true);
        $this->info("Distribution: " . json_encode($prizeDistribution));

        $winners = TournamentRegistration::where('tournament_id', $tournament->id)
            ->whereNotNull('final_rank')
            ->where('final_rank', '<=', 3)
            ->orderBy('final_rank')
            ->with('user.wallet')
            ->get();

        if ($winners->isEmpty()) {
            $this->warn('Aucun gagnant trouvé (final_rank non défini).');
            return;
        }

        $walletService = app(WalletService::class);

        foreach ($winners as $winner) {
            $rank = $winner->final_rank;

            // Essayer différents formats de clé
            $rankKey = match($rank) {
                1 => '1st',
                2 => '2nd',
                3 => '3rd',
                default => $rank . 'th'
            };

            $prizeAmount = $prizeDistribution[$rankKey] ?? $prizeDistribution[(string)$rank] ?? 0;

            if ($prizeAmount > 0) {
                $currentBalance = $winner->user->wallet->balance ?? 0;

                if ($winner->prize_won > 0) {
                    $this->warn("  {$winner->user->name} (Rang {$rank}): Déjà reçu {$winner->prize_won} pièces - IGNORÉ");
                } else {
                    $this->info("  {$winner->user->name} (Rang {$rank}):");
                    $this->line("    Solde actuel: {$currentBalance} pièces");
                    $this->line("    Prix à ajouter: {$prizeAmount} pièces");

                    try {
                        $walletService->credit(
                            $winner->user,
                            $prizeAmount,
                            'tournament_prize',
                            "Prix du tournoi {$tournament->name} - Rang {$rank} (correction manuelle)"
                        );

                        $winner->update(['prize_won' => $prizeAmount]);

                        $newBalance = $winner->user->wallet->fresh()->balance;
                        $this->info("    ✅ {$prizeAmount} pièces ajoutées (nouveau solde: {$newBalance})");
                    } catch (\Exception $e) {
                        $this->error("    ❌ Erreur: " . $e->getMessage());
                    }
                }
            }
        }
    }

    protected function updateGlobalStats(Tournament $tournament)
    {
        $registrations = TournamentRegistration::where('tournament_id', $tournament->id)->get();
        $userStatsService = app(UserStatsService::class);

        foreach ($registrations as $reg) {
            $reg->refresh();

            try {
                $userStatsService->updateStatsAfterTournament(
                    $reg->user,
                    $tournament,
                    $reg
                );
                $this->line("  ✅ {$reg->user->name}");
            } catch (\Exception $e) {
                $this->error("  ❌ {$reg->user->name}: " . $e->getMessage());
            }
        }
    }
}
