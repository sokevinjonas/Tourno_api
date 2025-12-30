<?php

namespace Tests\Feature;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TournamentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $player;
    protected User $organizer;
    protected Tournament $tournament;
    protected GameAccount $gameAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organizer
        $this->organizer = User::factory()->create([
            'role' => 'organizer',
            'email' => 'organizer@test.com',
        ]);

        // Create player with validated profile
        $this->player = User::factory()->create([
            'role' => 'player',
            'email' => 'player@test.com',
        ]);

        $this->player->profile->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        // Create game account
        $this->gameAccount = GameAccount::create([
            'user_id' => $this->player->id,
            'game' => 'efootball',
            'game_username' => 'test_player',
            'team_screenshot_path' => 'screenshots/test.png',
        ]);

        // Create tournament
        $this->tournament = Tournament::create([
            'organizer_id' => $this->organizer->id,
            'name' => 'Test Tournament',
            'description' => 'Test Description',
            'game' => 'efootball',
            'format' => 'swiss',
            'max_participants' => 8,
            'entry_fee' => 10.00,
            'prize_distribution' => json_encode(['1st' => 50, '2nd' => 30, '3rd' => 20]),
            'start_date' => now()->addDays(2),
            'tournament_duration_days' => 7,
            'time_slot' => 'evening',
            'match_deadline_minutes' => 90,
            'status' => 'open',
        ]);

        // Give player enough balance
        $this->player->wallet->update(['balance' => 100.00]);
        $this->organizer->wallet->update(['balance' => 100.00]);
    }

    public function test_player_can_register_to_tournament(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/register", [
                'game_account_id' => $this->gameAccount->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'registration' => [
                    'id',
                    'user_id',
                    'tournament_id',
                    'game_account_id',
                    'status',
                ],
            ]);

        // Verify registration was created
        $this->assertDatabaseHas('tournament_registrations', [
            'user_id' => $this->player->id,
            'tournament_id' => $this->tournament->id,
            'status' => 'registered',
        ]);

        // Verify wallet was debited
        $this->player->wallet->refresh();
        $this->assertEquals(90.00, $this->player->wallet->balance);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->player->id,
            'type' => 'debit',
            'amount' => 10.00,
            'reason' => 'tournament_registration',
        ]);

        // Verify organizer received funds
        $this->organizer->wallet->refresh();
        $this->assertEquals(110.00, $this->organizer->wallet->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->organizer->id,
            'type' => 'credit',
            'amount' => 10.00,
            'reason' => 'tournament_entry_received',
        ]);
    }

    public function test_cannot_register_without_validated_profile(): void
    {
        // Create player without validated profile
        $unvalidatedPlayer = User::factory()->create([
            'role' => 'player',
        ]);

        $gameAccount = GameAccount::create([
            'user_id' => $unvalidatedPlayer->id,
            'game' => 'efootball',
            'game_username' => 'unvalidated_player',
            'team_screenshot_path' => 'screenshots/test.png',
        ]);

        $response = $this->actingAs($unvalidatedPlayer, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/register", [
                'game_account_id' => $gameAccount->id,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Registration failed',
                'error' => 'Your profile must be validated before registering to tournaments',
            ]);
    }

    public function test_cannot_register_with_insufficient_balance(): void
    {
        // Set player balance to less than entry fee
        $this->player->wallet->update(['balance' => 5.00]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/register", [
                'game_account_id' => $this->gameAccount->id,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Registration failed',
                'error' => 'Insufficient wallet balance',
            ]);
    }

    public function test_cannot_register_twice_to_same_tournament(): void
    {
        // First registration
        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/register", [
                'game_account_id' => $this->gameAccount->id,
            ]);

        // Second registration attempt
        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/register", [
                'game_account_id' => $this->gameAccount->id,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Registration failed',
                'error' => 'Already registered to this tournament',
            ]);
    }

    public function test_cannot_register_to_full_tournament(): void
    {
        // Create tournament with 1 max participant
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
        $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/tournaments/{$fullTournament->id}/register", [
                'game_account_id' => $this->gameAccount->id,
            ]);

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

        $response = $this->actingAs($player2, 'sanctum')
            ->postJson("/api/tournaments/{$fullTournament->id}/register", [
                'game_account_id' => $gameAccount2->id,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Registration failed',
                'error' => 'Tournament is full',
            ]);
    }
}
