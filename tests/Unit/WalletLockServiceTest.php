<?php

namespace Tests\Unit;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\TournamentWalletLock;
use App\Models\User;
use App\Services\TournamentRegistrationService;
use App\Services\WalletLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WalletLockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WalletLockService $service;
    protected User $organizer;
    protected Tournament $tournament;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WalletLockService::class);

        $this->organizer = User::factory()->create(['role' => 'organizer']);
        $this->organizer->wallet->update(['balance' => 100.00, 'blocked_balance' => 0.00]);

        $this->tournament = Tournament::create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Test Tournament',
            'description' => 'Test',
            'game' => 'efootball',
            'format' => 'swiss',
            'max_participants' => 8,
            'entry_fee' => 10.00,
            'prize_distribution' => json_encode(['1st' => 50, '2nd' => 30]),
            'start_date' => now()->addDays(2),
            'tournament_duration_days' => 7,
            'time_slot' => 'evening',
            'match_deadline_minutes' => 90,
            'status' => 'open',
        ]);
    }

    public function test_lock_funds_for_tournament_creates_lock_record(): void
    {
        // Register players
        $this->registerPlayers(2);

        $this->service->lockFundsForTournament($this->tournament);

        $this->assertDatabaseHas('tournament_wallet_locks', [
            'tournament_id' => $this->tournament->id,
            'organizer_id' => $this->organizer->id,
        ]);
    }

    public function test_lock_funds_transfers_from_balance_to_blocked(): void
    {
        Mail::fake();

        // Register 2 players (20 MLM total)
        $this->registerPlayers(2);

        $initialBalance = $this->organizer->wallet->balance;
        $initialBlocked = $this->organizer->wallet->blocked_balance;

        $this->service->lockFundsForTournament($this->tournament);

        $this->organizer->wallet->refresh();

        // Balance should decrease by entry fees
        $this->assertEquals($initialBalance - 20.00, $this->organizer->wallet->balance);

        // Blocked balance should increase by entry fees
        $this->assertEquals($initialBlocked + 20.00, $this->organizer->wallet->blocked_balance);
    }

    public function test_lock_funds_calculates_correct_total(): void
    {
        Mail::fake();

        // Register 3 players (30 MLM total)
        $this->registerPlayers(3);

        $this->service->lockFundsForTournament($this->tournament);

        $lock = TournamentWalletLock::where('tournament_id', $this->tournament->id)->first();

        $this->assertEquals(30.00, $lock->locked_amount);
    }

    public function test_unlock_funds_for_tournament_releases_funds(): void
    {
        Mail::fake();

        // Register players and lock funds
        $this->registerPlayers(2);
        $this->service->lockFundsForTournament($this->tournament);

        $this->organizer->wallet->refresh();
        $balanceBeforeUnlock = $this->organizer->wallet->balance;
        $blockedBeforeUnlock = $this->organizer->wallet->blocked_balance;

        // Unlock funds
        $this->service->unlockFundsForTournament($this->tournament);

        $this->organizer->wallet->refresh();

        // Balance should increase
        $this->assertEquals($balanceBeforeUnlock + 20.00, $this->organizer->wallet->balance);

        // Blocked balance should decrease
        $this->assertEquals($blockedBeforeUnlock - 20.00, $this->organizer->wallet->blocked_balance);
    }

    public function test_unlock_funds_marks_lock_as_released(): void
    {
        Mail::fake();

        $this->registerPlayers(2);
        $this->service->lockFundsForTournament($this->tournament);
        $this->service->unlockFundsForTournament($this->tournament);

        $lock = TournamentWalletLock::where('tournament_id', $this->tournament->id)->first();

        $this->assertEquals('released', $lock->status);
        $this->assertNotNull($lock->released_at);
    }

    public function test_cannot_lock_funds_twice_for_same_tournament(): void
    {
        Mail::fake();

        $this->registerPlayers(2);

        $this->service->lockFundsForTournament($this->tournament);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Funds already locked for this tournament');

        $this->service->lockFundsForTournament($this->tournament);
    }

    protected function registerPlayers(int $count): void
    {
        $registrationService = app(TournamentRegistrationService::class);

        for ($i = 0; $i < $count; $i++) {
            $player = User::factory()->create(['role' => 'player']);
            $player->profile->update(['status' => 'validated', 'validated_at' => now()]);
            $player->wallet->update(['balance' => 100.00]);

            $gameAccount = GameAccount::create([
                'user_id' => $player->id,
                'game' => 'efootball',
                'game_username' => "player_{$i}",
                'team_screenshot_path' => 'screenshots/test.png',
            ]);

            $registrationService->registerToTournament($player, $this->tournament, $gameAccount->id);
        }
    }
}
