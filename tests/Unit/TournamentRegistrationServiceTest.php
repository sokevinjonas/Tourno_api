<?php

namespace Tests\Unit;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\User;
use App\Services\TournamentRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TournamentRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TournamentRegistrationService $service;
    protected User $player;
    protected User $organizer;
    protected Tournament $tournament;
    protected GameAccount $gameAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TournamentRegistrationService::class);

        $this->organizer = User::factory()->create(['role' => 'organizer']);
        $this->organizer->wallet->update(['balance' => 100.00]);

        $this->player = User::factory()->create(['role' => 'player']);
        $this->player->profile->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);
        $this->player->wallet->update(['balance' => 100.00]);

        $this->gameAccount = GameAccount::create([
            'user_id' => $this->player->id,
            'game' => 'efootball',
            'game_username' => 'test_player',
            'team_screenshot_path' => 'screenshots/test.png',
        ]);

        $this->tournament = Tournament::create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Test Tournament',
            'description' => 'Test',
            'game' => 'efootball',
            'format' => 'swiss',
            'max_participants' => 8,
            'entry_fee' => 10.00,
            'prize_distribution' => json_encode(['1st' => 50]),
            'start_date' => now()->addDays(2),
            'tournament_duration_days' => 7,
            'time_slot' => 'evening',
            'match_deadline_minutes' => 90,
            'status' => 'open',
        ]);
    }

    public function test_register_to_tournament_creates_registration(): void
    {
        Mail::fake();

        $registration = $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );

        $this->assertNotNull($registration);
        $this->assertEquals($this->player->id, $registration->user_id);
        $this->assertEquals($this->tournament->id, $registration->tournament_id);
        $this->assertEquals('registered', $registration->status);
    }

    public function test_register_to_tournament_debits_player_wallet(): void
    {
        Mail::fake();

        $initialBalance = $this->player->wallet->balance;

        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );

        $this->player->wallet->refresh();
        $this->assertEquals($initialBalance - 10.00, $this->player->wallet->balance);
    }

    public function test_register_to_tournament_credits_organizer_wallet(): void
    {
        Mail::fake();

        $initialBalance = $this->organizer->wallet->balance;

        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );

        $this->organizer->wallet->refresh();
        $this->assertEquals($initialBalance + 10.00, $this->organizer->wallet->balance);
    }

    public function test_register_to_tournament_creates_transactions(): void
    {
        Mail::fake();

        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );

        // Check player debit transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->player->id,
            'type' => 'debit',
            'amount' => 10.00,
            'reason' => 'tournament_registration',
        ]);

        // Check organizer credit transaction
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->organizer->id,
            'type' => 'credit',
            'amount' => 10.00,
            'reason' => 'tournament_entry_received',
        ]);
    }

    public function test_cannot_register_with_unvalidated_profile(): void
    {
        Mail::fake();

        $this->player->profile->update(['status' => 'pending']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Your profile must be validated before registering to tournaments');

        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );
    }

    public function test_cannot_register_with_insufficient_balance(): void
    {
        Mail::fake();

        $this->player->wallet->update(['balance' => 5.00]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );
    }

    public function test_cannot_register_twice(): void
    {
        Mail::fake();

        // First registration
        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );

        // Second registration
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Already registered to this tournament');

        $this->service->registerToTournament(
            $this->player,
            $this->tournament,
            $this->gameAccount->id
        );
    }

    public function test_cannot_register_to_full_tournament(): void
    {
        Mail::fake();

        $fullTournament = Tournament::create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Full Tournament',
            'description' => 'Test',
            'game' => 'efootball',
            'format' => 'swiss',
            'max_participants' => 1,
            'entry_fee' => 10.00,
            'prize_distribution' => json_encode(['1st' => 10]),
            'start_date' => now()->addDays(2),
            'tournament_duration_days' => 7,
            'time_slot' => 'evening',
            'match_deadline_minutes' => 90,
            'status' => 'open',
        ]);

        // Register first player
        $this->service->registerToTournament(
            $this->player,
            $fullTournament,
            $this->gameAccount->id
        );

        // Try to register second player
        $player2 = User::factory()->create(['role' => 'player']);
        $player2->profile->update(['status' => 'validated', 'validated_at' => now()]);
        $player2->wallet->update(['balance' => 100.00]);

        $gameAccount2 = GameAccount::create([
            'user_id' => $player2->id,
            'game' => 'efootball',
            'game_username' => 'player2',
            'team_screenshot_path' => 'screenshots/test.png',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tournament is full');

        $this->service->registerToTournament(
            $player2,
            $fullTournament,
            $gameAccount2->id
        );
    }
}
