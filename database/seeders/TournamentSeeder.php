<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tournament;
use App\Models\GameAccount;
use Illuminate\Database\Seeder;
use App\Models\OrganizerProfile;
use Illuminate\Support\Facades\DB;
use App\Services\TournamentRegistrationService;

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

            // Créer le profil organisateur avec badge certified
            OrganizerProfile::create([
                'user_id' => $organizer->id,
                'display_name' => $organizer->name,
                'badge' => 'certified',
                'bio' => 'Organisateur certifié',
                'status' => 'valider',
            ]);

            $this->command->info("✓ Organisateur créé avec badge certified");
        }

        // Define tournaments configuration
        $tournaments = [
            [
                'name' => 'Tournoi E-football Décembre 2025',
                'game' => 'efootball',
                'max_participants' => 8,
                'registered_count' => 7, // Laisser 1 place
                'prize_distribution' => [
                    '1st' => 18,  // 1er: 18 GPA
                    '2nd' => 10,  // 2ème: 10 GPA
                    '3rd' => 0,   // 3ème: 0 GPA
                ], // Total: 28 GPA (7 × 4)
            ],
            [
                'name' => 'Tournoi Dream League Soccer Décembre 2025',
                'game' => 'dream_league_soccer',
                'max_participants' => 16,
                'registered_count' => 15, // Laisser 1 place
                'prize_distribution' => [
                    '1st' => 35,  // 1er: 35 GPA
                    '2nd' => 20,  // 2ème: 20 GPA
                    '3rd' => 5,   // 3ème: 5 GPA
                ], // Total: 60 GPA (15 × 4)
            ],
            [
                'name' => 'Tournoi FC Mobile Décembre 2025',
                'game' => 'fc_mobile',
                'max_participants' => 32,
                'registered_count' => 31, // Laisser 1 place
                'prize_distribution' => [
                    '1st' => 70,  // 1er: 70 GPA
                    '2nd' => 40,  // 2ème: 40 GPA
                    '3rd' => 14,  // 3ème: 14 GPA
                ], // Total: 124 GPA (31 × 4)
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
                'prize_distribution' => json_encode($tournamentData['prize_distribution']),
                'start_date' => now()->setTime(00, 30, 0),
                'tournament_duration_days' => 1,
                'time_slot' => 'evening',
                'match_deadline_minutes' => 60,
                'status' => 'open',
                'auto_managed' => true,
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

                    // Ensure player has enough balance for entry fee
                    $requiredBalance = $tournament->entry_fee;
                    if ($player->wallet->balance < $requiredBalance) {
                        $player->wallet->update(['balance' => $requiredBalance]); // Donner le montant exact requis
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
