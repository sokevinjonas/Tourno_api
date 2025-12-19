<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\GameAccount;
use App\Models\Wallet;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TournamentSystemSeeder extends Seeder
{
    private array $countries = [
        'Cameroon', 'Nigeria', 'Ghana', 'Kenya', 'South Africa',
        'Egypt', 'Morocco', 'Algeria', 'Tunisia', 'Senegal',
        'Ivory Coast', 'Tanzania', 'Uganda', 'Ethiopia', 'Mali'
    ];

    private array $cities = [
        'Douala', 'Yaoundé', 'Lagos', 'Accra', 'Nairobi',
        'Cape Town', 'Cairo', 'Casablanca', 'Tunis', 'Dakar',
        'Abidjan', 'Dar es Salaam', 'Kampala', 'Addis Ababa', 'Bamako'
    ];

    private array $games = ['efootball', 'fc_mobile', 'dream_league_soccer'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Starting Tournament System Seeding...');

            // Clean existing data
            $this->command->info('🧹 Cleaning existing data...');
            TournamentRegistration::truncate();
            Tournament::truncate();
            GameAccount::truncate();
            Wallet::truncate();
            Profile::truncate();
            User::truncate();
            $this->command->info('✅ Database cleaned');
            $this->command->newLine();

            // Step 1: Create Admin
            $this->command->info('👤 Creating Admin...');
            $admin = $this->createUser('Admin Master', 'admin@mlm.com', 'admin');

            // Step 2: Create Moderators
            $this->command->info('👮 Creating 5 Moderators...');
            $moderators = [];
            for ($i = 1; $i <= 5; $i++) {
                $moderators[] = $this->createUser(
                    "Moderator {$i}",
                    "moderator{$i}@mlm.com",
                    'moderator'
                );
            }

            // Step 3: Create Organizers
            $this->command->info('📋 Creating 3 Organizers...');
            $organizers = [];
            for ($i = 1; $i <= 3; $i++) {
                $organizers[] = $this->createUser(
                    "Organizer {$i}",
                    "organizer{$i}@mlm.com",
                    'organizer'
                );
            }

            // Step 4: Create 110 Players
            $this->command->info('🎮 Creating 110 Players with validated profiles...');
            $players = [];
            for ($i = 1; $i <= 110; $i++) {
                $players[] = $this->createUser(
                    "Player {$i}",
                    "player{$i}@mlm.com",
                    'player'
                );
            }

            $this->command->info('✅ Created ' . count($players) . ' players');

            // Step 5: Create 3 Swiss Format Tournaments
            $this->command->info('🏆 Creating 3 Swiss Format Tournaments...');
            $startDate = '2025-12-25 12:00:00';

            $tournaments = [
                $this->createTournament(
                    $organizers[0],
                    'Swiss Championship - eFootball',
                    'efootball',
                    $startDate
                ),
                $this->createTournament(
                    $organizers[1],
                    'Swiss Championship - FC Mobile',
                    'fc_mobile',
                    $startDate
                ),
                $this->createTournament(
                    $organizers[2],
                    'Swiss Championship - Dream League',
                    'dream_league_soccer',
                    $startDate
                ),
            ];

            // Step 6: Register players to tournaments
            $this->command->info('📝 Registering players to tournaments...');

            // Tournament 1: 18 players (full)
            $this->registerPlayers($tournaments[0], array_slice($players, 0, 18));
            $this->command->info('   ✓ Tournament 1: 18/18 players (FULL)');

            // Tournament 2: 18 players (full)
            $this->registerPlayers($tournaments[1], array_slice($players, 18, 18));
            $this->command->info('   ✓ Tournament 2: 18/18 players (FULL)');

            // Tournament 3: 17 players (1 spot left)
            $this->registerPlayers($tournaments[2], array_slice($players, 36, 17));
            $this->command->info('   ✓ Tournament 3: 17/18 players (1 spot available)');

            $this->command->info('');
            $this->command->info('✨ Tournament System Seeding Completed!');
            $this->command->info('');
            $this->command->info('📊 Summary:');
            $this->command->info('   • 1 Admin');
            $this->command->info('   • 5 Moderators');
            $this->command->info('   • 3 Organizers');
            $this->command->info('   • 110 Players (all with validated profiles)');
            $this->command->info('   • 3 Swiss Format Tournaments');
            $this->command->info('   • 53 Tournament Registrations (18+18+17)');
            $this->command->info('   • Start Date: December 25, 2025 at 12:00 PM');
        });
    }

    /**
     * Create a user with complete profile, wallet, and game account
     */
    private function createUser(string $name, string $email, string $role): User
    {
        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'email_verified_at' => now(),
        ]);

        // Create validated profile
        $profile = Profile::create([
            'user_id' => $user->id,
            'whatsapp_number' => '+237' . rand(600000000, 699999999),
            'country' => $this->countries[array_rand($this->countries)],
            'city' => $this->cities[array_rand($this->cities)],
            'status' => 'validated',
            'validated_by' => 1, // Will be admin's ID
            'validated_at' => now(),
        ]);

        // Create wallet with sufficient balance (20 pieces for participation)
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 20.00,
        ]);

        // Create game account (only for players and organizers)
        if (in_array($role, ['player', 'organizer'])) {
            $game = $this->games[array_rand($this->games)];
            GameAccount::create([
                'user_id' => $user->id,
                'game' => $game,
                'game_username' => strtolower(str_replace(' ', '_', $name)),
                'team_screenshot_path' => 'screenshots/team_' . $user->id . '.jpg',
            ]);
        }

        return $user;
    }

    /**
     * Create a tournament
     */
    private function createTournament(User $organizer, string $name, string $game, string $startDate): Tournament
    {
        return Tournament::create([
            'name' => $name,
            'description' => "Tournoi Swiss Format - {$name}. Venez affronter les meilleurs joueurs dans un format compétitif équitable!",
            'organizer_id' => $organizer->id,
            'game' => $game,
            'max_participants' => 18,
            'entry_fee' => 4.00,
            'start_date' => $startDate,
            'status' => 'open',
            'prize_distribution' => json_encode([
                '1st' => 30.00,
                '2nd' => 20.00,
                '3rd' => 15.00,
                '4th' => 10.00,
            ]),
            'total_rounds' => 5, // Swiss format typically 5 rounds for 18 players
        ]);
    }

    /**
     * Register players to a tournament
     */
    private function registerPlayers(Tournament $tournament, array $players): void
    {
        foreach ($players as $player) {
            // Get player's game account for this tournament's game
            $gameAccount = GameAccount::where('user_id', $player->id)
                ->where('game', $tournament->game)
                ->first();

            // If player doesn't have account for this game, create one
            if (!$gameAccount) {
                $gameAccount = GameAccount::create([
                    'user_id' => $player->id,
                    'game' => $tournament->game,
                    'game_username' => strtolower(str_replace(' ', '_', $player->name)),
                    'team_screenshot_path' => 'screenshots/team_' . $player->id . '_' . $tournament->game . '.jpg',
                ]);
            }

            // Register player to tournament
            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $player->id,
                'game_account_id' => $gameAccount->id,
                'status' => 'registered',
            ]);

            // Deduct entry fee from wallet
            $wallet = Wallet::where('user_id', $player->id)->first();
            $wallet->update([
                'balance' => $wallet->balance - $tournament->entry_fee,
            ]);
        }
    }
}
