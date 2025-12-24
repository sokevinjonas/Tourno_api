<?php

namespace Database\Seeders;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\User;
use App\Services\TournamentRegistrationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrationService = app(TournamentRegistrationService::class);

        // Get an organizer (or create one if none exists)
        $organizer = User::where('role', 'organizer')->first();
        if (!$organizer) {
            $organizer = User::factory()->create(['role' => 'organizer']);
            $organizer->wallet->update(['balance' => 1000.00]);
        }

        // Ensure organizer has enough balance for prizes
        $organizer->wallet->update(['balance' => 1000.00]);

        // Define tournaments configuration
        $tournaments = [
            [
                'name' => 'Tournoi E-football Décembre 2025',
                'game' => 'efootball',
                'max_participants' => 8,
                'registered_count' => 7, // Laisser 1 place
            ],
            [
                'name' => 'Tournoi Dream League Soccer Décembre 2025',
                'game' => 'dream_league_soccer',
                'max_participants' => 16,
                'registered_count' => 15, // Laisser 1 place
            ],
            [
                'name' => 'Tournoi FC Mobile Décembre 2025',
                'game' => 'fc_mobile',
                'max_participants' => 32,
                'registered_count' => 31, // Laisser 1 place
            ],
        ];

        foreach ($tournaments as $tournamentData) {
            $this->command->info("Création du tournoi: {$tournamentData['name']}");

            // Create tournament
            $tournament = Tournament::create([
                'organizer_id' => $organizer->id,
                'name' => $tournamentData['name'],
                'description' => "Tournoi officiel {$tournamentData['game']} - Format Suisse",
                'game' => $tournamentData['game'],
                'format' => 'swiss',
                'max_participants' => $tournamentData['max_participants'],
                'entry_fee' => 4.00,
                'prize_distribution' => json_encode([
                    '1st' => 40,
                    '2nd' => 20,
                    '3rd' => 10,
                ]),
                'start_date' => now()->setTime(19, 30, 0),
                'tournament_duration_days' => 1,
                'time_slot' => 'evening',
                'match_deadline_minutes' => 90,
                'status' => 'open',
            ]);

            $this->command->info("✓ Tournoi créé: {$tournament->name}");

            // Get validated players
            $players = User::where('role', 'player')
                ->whereHas('profile', function ($query) {
                    $query->where('status', 'validated');
                })
                ->limit($tournamentData['registered_count'])
                ->get();

            $this->command->info("Inscription de {$players->count()} joueurs...");

            foreach ($players as $player) {
                try {
                    // Ensure player has a game account for this game
                    $gameAccount = GameAccount::where('user_id', $player->id)
                        ->where('game', $tournamentData['game'])
                        ->first();

                    if (!$gameAccount) {
                        $gameAccount = GameAccount::create([
                            'user_id' => $player->id,
                            'game' => $tournamentData['game'],
                            'game_username' => $player->name . '_' . strtoupper(substr($tournamentData['game'], 0, 3)),
                            'team_screenshot_path' => 'screenshots/default-team.png',
                        ]);
                    }

                    // Ensure player has enough balance (at least 10 pièces)
                    if ($player->wallet->balance < 10.00) {
                        $player->wallet->update(['balance' => 50.00]);
                    }

                    // Register player to tournament
                    $registrationService->registerToTournament(
                        $player,
                        $tournament,
                        $gameAccount->id
                    );

                    $this->command->info("  ✓ {$player->name} inscrit au tournoi");
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Erreur pour {$player->name}: {$e->getMessage()}");
                }
            }

            $this->command->info("Places restantes: " . ($tournament->max_participants - $players->count()) . "/" . $tournament->max_participants);
            $this->command->newLine();
        }

        $this->command->info("✓ Tous les tournois ont été créés avec succès!");
    }
}
