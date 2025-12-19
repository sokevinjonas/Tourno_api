<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\GameAccount;
use App\Models\Wallet;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalTournamentRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder registers available players to tournaments with open spots
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🎯 Starting Additional Tournament Registrations...');
            $this->command->newLine();

            // Get all open tournaments
            $tournaments = Tournament::where('status', 'open')->get();

            if ($tournaments->isEmpty()) {
                $this->command->warn('⚠️  No open tournaments found!');
                return;
            }

            $totalRegistrations = 0;

            foreach ($tournaments as $tournament) {
                $currentRegistrations = $tournament->registrations()->count();
                $availableSpots = $tournament->max_participants - $currentRegistrations;

                $this->command->info("🏆 {$tournament->name}");
                $this->command->line("   Current: {$currentRegistrations}/{$tournament->max_participants}");

                if ($availableSpots <= 0) {
                    $this->command->line("   Status: FULL ✓");
                    $this->command->newLine();
                    continue;
                }

                // Get players already registered in this tournament
                $registeredPlayerIds = $tournament->registrations()
                    ->pluck('user_id')
                    ->toArray();

                // Get available players (not registered, with enough balance)
                $availablePlayers = User::where('role', 'player')
                    ->whereNotIn('id', $registeredPlayerIds)
                    ->whereHas('wallet', function ($query) use ($tournament) {
                        $query->where('balance', '>=', $tournament->entry_fee);
                    })
                    ->inRandomOrder()
                    ->take($availableSpots)
                    ->get();

                if ($availablePlayers->isEmpty()) {
                    $this->command->warn("   ⚠️  No available players with sufficient balance!");
                    $this->command->newLine();
                    continue;
                }

                $registeredCount = 0;

                foreach ($availablePlayers as $player) {
                    if ($this->registerPlayer($tournament, $player)) {
                        $registeredCount++;
                        $totalRegistrations++;
                    }
                }

                $newTotal = $currentRegistrations + $registeredCount;
                $newAvailableSpots = $tournament->max_participants - $newTotal;

                $this->command->line("   Registered: {$registeredCount} new player(s)");
                $this->command->line("   New Total: {$newTotal}/{$tournament->max_participants}");

                if ($newAvailableSpots > 0) {
                    $this->command->line("   Remaining spots: {$newAvailableSpots}");
                } else {
                    $this->command->info("   Status: NOW FULL ✓");
                }

                $this->command->newLine();
            }

            $this->command->info('✨ Additional Registrations Completed!');
            $this->command->info("📊 Total new registrations: {$totalRegistrations}");
        });
    }

    /**
     * Register a player to a tournament
     */
    private function registerPlayer(Tournament $tournament, User $player): bool
    {
        try {
            // Get or create game account for this game
            $gameAccount = GameAccount::firstOrCreate(
                [
                    'user_id' => $player->id,
                    'game' => $tournament->game,
                ],
                [
                    'game_username' => strtolower(str_replace(' ', '_', $player->name)),
                    'team_screenshot_path' => 'screenshots/team_' . $player->id . '_' . $tournament->game . '.jpg',
                ]
            );

            // Create registration
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

            return true;
        } catch (\Exception $e) {
            $this->command->error("   ✗ Failed to register player {$player->name}: " . $e->getMessage());
            return false;
        }
    }
}
