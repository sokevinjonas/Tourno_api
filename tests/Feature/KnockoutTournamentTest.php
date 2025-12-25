<?php

namespace Tests\Feature;

use App\Models\GameAccount;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnockoutTournamentTest extends TestCase
{
    use RefreshDatabase;

    protected User $organizer;
    protected array $players = [];
    protected Tournament $tournament;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake mail sending to avoid file permission issues
        \Illuminate\Support\Facades\Mail::fake();

        // Create organizer (profile and wallet are auto-created by factory)
        $this->organizer = User::factory()->create([
            'role' => 'organizer',
            'email_verified_at' => now(),
        ]);

        // Update profile to validated status
        $this->organizer->profile()->update([
            'status' => 'validated',
        ]);

        // Update wallet balance
        $this->organizer->wallet()->update([
            'balance' => 1000,
        ]);

        // Create 8 players (profile and wallet are auto-created by factory)
        for ($i = 0; $i < 8; $i++) {
            $player = User::factory()->create([
                'role' => 'player',
                'email_verified_at' => now(),
            ]);

            // Update profile to validated status
            $player->profile()->update([
                'status' => 'validated',
            ]);

            // Update wallet balance
            $player->wallet()->update([
                'balance' => 100,
            ]);

            GameAccount::factory()->create([
                'user_id' => $player->id,
                'game' => 'efootball',
            ]);

            $this->players[] = $player;
        }
    }

    public function test_can_create_knockout_tournament()
    {
        $response = $this->actingAs($this->organizer)->postJson('/api/tournaments', [
            'name' => 'Knockout Tournament',
            'description' => 'Single elimination test',
            'game' => 'efootball',
            'format' => 'single_elimination',
            'max_participants' => 8,
            'entry_fee' => 0,
            'start_date' => now()->addDays(7)->toDateTimeString(),
            'prize_distribution' => json_encode(['1' => 50, '2' => 30, '3' => 20]),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'tournament' => [
                'id',
                'name',
                'format',
                'max_participants',
            ],
        ]);

        $this->assertEquals('single_elimination', $response->json('tournament.format'));
    }

    public function test_knockout_requires_power_of_two_participants()
    {
        // Create tournament
        $tournament = Tournament::factory()->create([
            'organizer_id' => $this->organizer->id,
            'format' => 'single_elimination',
            'max_participants' => 8,
            'status' => 'open',
            'entry_fee' => 0,
        ]);

        // Register only 7 players (not power of 2)
        for ($i = 0; $i < 7; $i++) {
            $gameAccount = GameAccount::where('user_id', $this->players[$i]->id)->first();

            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $this->players[$i]->id,
                'game_account_id' => $gameAccount->id,
                'status' => 'registered',
            ]);
        }

        // Try to start tournament with 7 players
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/start");

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'error' => 'Single elimination requires a power of 2 participants (8, 16, 32, 64)',
        ]);
    }

    public function test_knockout_creates_all_rounds_at_start()
    {
        // Create and register tournament
        $tournament = Tournament::factory()->create([
            'organizer_id' => $this->organizer->id,
            'format' => 'single_elimination',
            'max_participants' => 8,
            'status' => 'open',
            'entry_fee' => 0,
        ]);

        // Register 8 players
        foreach ($this->players as $player) {
            $gameAccount = GameAccount::where('user_id', $player->id)->first();

            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $player->id,
                'game_account_id' => $gameAccount->id,
                'status' => 'registered',
            ]);
        }

        // Start tournament
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/start");

        $response->assertStatus(200);

        // Check that all 3 rounds were created (8 -> 4 -> 2 -> 1)
        $rounds = $tournament->fresh()->rounds;
        $this->assertCount(3, $rounds);

        // Check round names
        $this->assertEquals('Quarter-finals', $rounds[0]->round_name);
        $this->assertEquals('Semi-finals', $rounds[1]->round_name);
        $this->assertEquals('Final', $rounds[2]->round_name);

        // Check matches per round
        $this->assertCount(4, $rounds[0]->matches); // Quarter-finals: 4 matches
        $this->assertCount(2, $rounds[1]->matches); // Semi-finals: 2 matches
        $this->assertCount(1, $rounds[2]->matches); // Final: 1 match

        // Check first round has real players
        foreach ($rounds[0]->matches as $match) {
            $this->assertNotNull($match->player1_id);
            $this->assertNotNull($match->player2_id);
        }

        // Check subsequent rounds have null players (placeholders)
        foreach ($rounds[1]->matches as $match) {
            $this->assertNull($match->player1_id);
            $this->assertNull($match->player2_id);
        }
    }

    public function test_knockout_does_not_allow_draws()
    {
        // Create and start tournament
        $tournament = Tournament::factory()->create([
            'organizer_id' => $this->organizer->id,
            'format' => 'single_elimination',
            'max_participants' => 8,
            'status' => 'open',
            'entry_fee' => 0,
        ]);

        foreach ($this->players as $player) {
            $gameAccount = GameAccount::where('user_id', $player->id)->first();

            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $player->id,
                'game_account_id' => $gameAccount->id,
                'status' => 'registered',
            ]);
        }

        $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/start");

        // Get first match
        $match = $tournament->fresh()->rounds[0]->matches[0];

        // Try to submit draw scores
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/matches/{$match->id}/enter-score", [
                'player1_score' => 2,
                'player2_score' => 2,
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'error' => 'Draws are not allowed in single elimination format. There must be a winner.',
        ]);
    }

    public function test_knockout_eliminates_loser_and_advances_winner()
    {
        // Create and start tournament
        $tournament = Tournament::factory()->create([
            'organizer_id' => $this->organizer->id,
            'format' => 'single_elimination',
            'max_participants' => 8,
            'status' => 'open',
            'entry_fee' => 0,
        ]);

        foreach ($this->players as $player) {
            $gameAccount = GameAccount::where('user_id', $player->id)->first();

            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $player->id,
                'game_account_id' => $gameAccount->id,
                'status' => 'registered',
            ]);
        }

        $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/start");

        // Get first match from quarter-finals
        $tournament = $tournament->fresh();
        $quarterFinals = $tournament->rounds[0];
        $match = $quarterFinals->matches[0];

        $player1 = $match->player1_id;
        $player2 = $match->player2_id;

        // Submit score (player 1 wins)
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/matches/{$match->id}/enter-score", [
                'player1_score' => 3,
                'player2_score' => 1,
            ]);

        $response->assertStatus(200);

        // Check match is completed
        $updatedMatch = $match->fresh();
        $this->assertEquals('completed', $updatedMatch->status);
        $this->assertEquals($player1, $updatedMatch->winner_id);

        // Check loser is eliminated
        $loserRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('user_id', $player2)
            ->first();

        $this->assertTrue($loserRegistration->eliminated);
        $this->assertEquals(1, $loserRegistration->eliminated_round);
        $this->assertNotNull($loserRegistration->eliminated_at);

        // Check winner is advanced to next match
        $semifinalMatch = $updatedMatch->fresh()->nextMatch;
        $this->assertNotNull($semifinalMatch);
        $this->assertTrue(
            $semifinalMatch->player1_id === $player1 || $semifinalMatch->player2_id === $player1
        );
    }

    public function test_complete_knockout_tournament_flow()
    {
        // Create and start tournament
        $tournament = Tournament::factory()->create([
            'organizer_id' => $this->organizer->id,
            'format' => 'single_elimination',
            'max_participants' => 8,
            'status' => 'open',
            'entry_fee' => 0,
            'prize_distribution' => json_encode(['1' => 100, '2' => 50]),
        ]);

        foreach ($this->players as $player) {
            $gameAccount = GameAccount::where('user_id', $player->id)->first();

            TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'user_id' => $player->id,
                'game_account_id' => $gameAccount->id,
                'status' => 'registered',
            ]);
        }

        $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/start");

        $tournament = $tournament->fresh();

        // Play all quarter-final matches
        $quarterFinals = $tournament->rounds[0];
        foreach ($quarterFinals->matches as $match) {
            $this->actingAs($this->organizer)
                ->postJson("/api/matches/{$match->id}/enter-score", [
                    'player1_score' => 2,
                    'player2_score' => 1,
                ]);
        }

        // Check semi-finals are ready
        $semiFinals = $tournament->fresh()->rounds[1];
        foreach ($semiFinals->matches as $match) {
            $this->assertNotNull($match->fresh()->player1_id);
            $this->assertNotNull($match->fresh()->player2_id);
        }

        // Play semi-finals
        foreach ($semiFinals->matches as $match) {
            $this->actingAs($this->organizer)
                ->postJson("/api/matches/{$match->id}/enter-score", [
                    'player1_score' => 3,
                    'player2_score' => 0,
                ]);
        }

        // Check final is ready
        $final = $tournament->fresh()->rounds[2];
        $finalMatch = $final->matches[0]->fresh();
        $this->assertNotNull($finalMatch->player1_id);
        $this->assertNotNull($finalMatch->player2_id);

        // Play final
        $this->actingAs($this->organizer)
            ->postJson("/api/matches/{$finalMatch->id}/enter-score", [
                'player1_score' => 2,
                'player2_score' => 1,
            ]);

        // Complete tournament
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/complete");

        $response->assertStatus(200);

        // Check rankings
        $tournament = $tournament->fresh();
        $this->assertEquals('completed', $tournament->status);

        $rankings = TournamentRegistration::where('tournament_id', $tournament->id)
            ->orderBy('final_rank')
            ->get();

        // Winner should be rank 1, runner-up rank 2
        $this->assertEquals(1, $rankings[0]->final_rank);
        $this->assertEquals(2, $rankings[1]->final_rank);

        // Semi-final losers should be rank 3-4
        $this->assertContains($rankings[2]->final_rank, [3, 4]);
        $this->assertContains($rankings[3]->final_rank, [3, 4]);
    }

    public function test_manual_next_round_not_allowed_for_knockout()
    {
        $tournament = Tournament::factory()->create([
            'organizer_id' => $this->organizer->id,
            'format' => 'single_elimination',
            'max_participants' => 8,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/tournaments/{$tournament->id}/next-round");

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'error' => 'Manual round generation is only available for Swiss format tournaments',
        ]);
    }
}
