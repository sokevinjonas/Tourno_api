<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\GameAccount;
use App\Models\Wallet;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\OrganizerProfile;
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
            DB::table('organizer_followers')->truncate();
            OrganizerProfile::truncate();
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

            // Step 3.5: Create Organizer Profiles
            $this->command->info('✨ Creating Organizer Profiles...');
            $this->createOrganizerProfiles($organizers);

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

            // Step 6: Register players to tournaments with varied availability
            $this->command->info('📝 Registering players to tournaments...');

            $playerIndex = 0;

            // Tournament 1: FULL (18/18)
            $this->registerPlayers($tournaments[0], array_slice($players, $playerIndex, 18));
            $this->command->info('   ✓ Tournament 1: 18/18 players (FULL)');
            $playerIndex += 18;

            // Tournament 2: 1 spot left (17/18)
            $this->registerPlayers($tournaments[1], array_slice($players, $playerIndex, 17));
            $this->command->info('   ✓ Tournament 2: 17/18 players (1 spot available)');
            $playerIndex += 17;

            // Tournament 3: 2 spots left (16/18)
            $this->registerPlayers($tournaments[2], array_slice($players, $playerIndex, 16));
            $this->command->info('   ✓ Tournament 3: 16/18 players (2 spots available)');
            $playerIndex += 16;

            $totalRegistrations = 18 + 17 + 16;

            $this->command->info('');
            $this->command->info('✨ Tournament System Seeding Completed!');
            $this->command->info('');
            $this->command->info('📊 Summary:');
            $this->command->info('   • 1 Admin');
            $this->command->info('   • 5 Moderators');
            $this->command->info('   • 3 Organizers');
            $this->command->info('   • 110 Players (all with validated profiles)');
            $this->command->info('   • 3 Swiss Format Tournaments');
            $this->command->info('   • ' . $totalRegistrations . ' Tournament Registrations (18 FULL + 17 + 16)');
            $this->command->info('   • Available spots: 3 (1 in Tournament 2, 2 in Tournament 3)');
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

    /**
     * Create organizer profiles with realistic data
     */
    private function createOrganizerProfiles(array $organizers): void
    {
        $organizerData = [
            [
                'display_name' => 'Tourno Official',
                'badge' => 'certified',
                'avatar_initial' => 'T',
                'bio' => 'Organisation officielle de tournois MLM. Nous organisons des compétitions équitables et professionnelles pour tous les joueurs.',
                'social_links' => [
                    'twitter' => 'https://twitter.com/tourno_mlm',
                    'discord' => 'https://discord.gg/tourno',
                ],
                'is_featured' => true,
            ],
            [
                'display_name' => 'Elite Gaming',
                'badge' => 'certified',
                'avatar_initial' => 'E',
                'bio' => 'Communauté de gamers passionnés. Rejoignez-nous pour des tournois compétitifs et des événements exclusifs.',
                'social_links' => [
                    'twitter' => 'https://twitter.com/elite_gaming',
                ],
                'is_featured' => false,
            ],
            [
                'display_name' => 'Pro Esports',
                'badge' => 'verified',
                'avatar_initial' => 'P',
                'bio' => 'Organisation esport professionnelle avec des années d\'expérience dans la gestion de tournois.',
                'social_links' => null,
                'is_featured' => false,
            ],
        ];

        foreach ($organizers as $index => $organizer) {
            OrganizerProfile::create([
                'user_id' => $organizer->id,
                'display_name' => $organizerData[$index]['display_name'],
                'badge' => $organizerData[$index]['badge'],
                'avatar_initial' => $organizerData[$index]['avatar_initial'],
                'bio' => $organizerData[$index]['bio'],
                'social_links' => $organizerData[$index]['social_links'],
                'is_featured' => $organizerData[$index]['is_featured'],
            ]);
        }
    }
}
