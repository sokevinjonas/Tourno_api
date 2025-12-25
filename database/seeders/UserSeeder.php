<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\GameAccount;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
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
            $this->command->info('🚀 Starting User Seeding...');
            $this->command->newLine();

            // Step 1: Create 2 Admins
            $this->command->info('👤 Creating 2 Admins...');
            $admins = [];
            for ($i = 1; $i <= 2; $i++) {
                $admins[] = $this->createUser(
                    "Admin {$i}",
                    "admin{$i}@mlm.com",
                    'admin'
                );
            }
            $this->command->info('✅ Created 2 admins');

            // Step 2: Create 5 Moderators
            $this->command->info('👮 Creating 5 Moderators...');
            $moderators = [];
            for ($i = 1; $i <= 5; $i++) {
                $moderators[] = $this->createUser(
                    "Moderator {$i}",
                    "moderator{$i}@mlm.com",
                    'moderator'
                );
            }
            $this->command->info('✅ Created 5 moderators');

            // Step 3: Create 50 Players
            $this->command->info('🎮 Creating 50 Players with validated profiles...');
            $players = [];
            for ($i = 1; $i <= 50; $i++) {
                $players[] = $this->createUser(
                    "Player {$i}",
                    "player{$i}@mlm.com",
                    'player'
                );
            }
            $this->command->info('✅ Created ' . count($players) . ' players');

            $this->command->newLine();
            $this->command->info('✨ User Seeding Completed!');
            $this->command->newLine();
            $this->command->info('📊 Summary:');
            $this->command->info('   • 2 Admins');
            $this->command->info('   • 5 Moderators');
            $this->command->info('   • 50 Players (all with validated profiles)');
            $this->command->info('   • Total Users: ' . (2 + 5 + 50));
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
        Profile::create([
            'user_id' => $user->id,
            'whatsapp_number' => '+237' . rand(600000000, 699999999),
            'country' => $this->countries[array_rand($this->countries)],
            'city' => $this->cities[array_rand($this->cities)],
            'status' => 'validated',
            'validated_by' => 1, // Admin's ID
            'validated_at' => now(),
        ]);

        // Create wallet (only players get balance)
        Wallet::create([
            'user_id' => $user->id,
            'balance' => $role === 'player' ? 4.00 : 0.00,
        ]);

        // Create game accounts (only for players)
        if ($role === 'player') {
            // Create account for each game
            foreach ($this->games as $game) {
                GameAccount::create([
                    'user_id' => $user->id,
                    'game' => $game,
                    'game_username' => strtolower(str_replace(' ', '_', $name)) . '_' . $game,
                    'team_screenshot_path' => 'screenshots/team_' . $user->id . '_' . $game . '.jpg',
                ]);
            }
        }

        return $user;
    }
}
