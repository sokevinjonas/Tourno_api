<?php

namespace Tests\Feature;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\TournamentWalletLock;
use App\Models\User;
use App\Services\SwissFormatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected Tournament $tournament;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = User::factory()->create(['role' => 'organizer']);
        $this->organizer->wallet->update(['balance' => 100.00]);

        $this->tournament = Tournament::create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Test Tournament',
            'description' => 'Test',
            'game' => 'efootball',
            'format' => 'swiss',
            'max_participants' => 4,
            'entry_fee' => 10.00,
            'prize_distribution' => json_encode(['1st' => 50, '2nd' => 30]),
            'start_date' => now()->addDays(2),
            'tournament_duration_days' => 7,
            'time_slot' => 'evening',
            'match_deadline_minutes' => 90,
            'status' => 'open',
        ]);
    }

    public function test_organizer_funds_are_locked_when_tournament_starts(): void
    {
        // Register 2 players
        $player1 = $this->createPlayerAndRegister();
        $player2 = $this->createPlayerAndRegister();

        // Check organizer balance before start
        $this->organizer->wallet->refresh();
        $balanceBefore = $this->organizer->wallet->balance;
        $blockedBefore = $this->organizer->wallet->blocked_balance;

        // Start tournament
        $service = app(SwissFormatService::class);
        $service->startTournament($this->tournament);

        // Check organizer balance after start
        $this->organizer->wallet->refresh();
        $this->assertEquals($balanceBefore - 20.00, $this->organizer->wallet->balance);
        $this->assertEquals($blockedBefore + 20.00, $this->organizer->wallet->blocked_balance);

        // Verify wallet lock record was created
        $this->assertDatabaseHas('tournament_wallet_locks', [
            'tournament_id' => $this->tournament->id,
            'organizer_id' => $this->organizer->id,
        ]);

        $lock = TournamentWalletLock::where('tournament_id', $this->tournament->id)->first();
        $this->assertEquals(20.00, $lock->locked_amount);
    }

    public function test_funds_remain_locked_during_tournament(): void
    {
        // Register players and start tournament
        $this->createPlayerAndRegister();
        $this->createPlayerAndRegister();

        $service = app(SwissFormatService::class);
        $service->startTournament($this->tournament);

        $this->organizer->wallet->refresh();
        $blockedAmount = $this->organizer->wallet->blocked_balance;

        // Verify funds stay blocked
        $this->assertEquals(20.00, $blockedAmount);

        // Try to spend blocked balance (should fail if properly implemented)
        $this->organizer->wallet->refresh();
        $availableBalance = $this->organizer->wallet->balance;

        // Available balance should not include blocked amount
        $this->assertLessThan($blockedAmount + $availableBalance, 120.00);
    }

    protected function createPlayerAndRegister(): User
    {
        $player = User::factory()->create(['role' => 'player']);
        $player->profile->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);
        $player->wallet->update(['balance' => 100.00]);

        $gameAccount = GameAccount::create([
            'user_id' => $player->id,
            'game' => 'efootball',
            'game_username' => 'player_' . $player->id,
            'team_screenshot_path' => 'screenshots/test.png',
        ]);

        $this->actingAs($player, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/register", [
                'game_account_id' => $gameAccount->id,
            ]);

        return $player;
    }
}
