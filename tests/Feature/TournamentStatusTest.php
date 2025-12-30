<?php

namespace Tests\Feature;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $organizer;
    protected Tournament $tournament;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->organizer = User::factory()->create(['role' => 'organizer']);

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
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_change_tournament_status(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", [
                'status' => 'open',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tournament status updated successfully',
            ]);

        $this->assertDatabaseHas('tournaments', [
            'id' => $this->tournament->id,
            'status' => 'open',
        ]);
    }

    public function test_organizer_can_change_own_tournament_status(): void
    {
        $response = $this->actingAs($this->organizer, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", [
                'status' => 'open',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tournaments', [
            'id' => $this->tournament->id,
            'status' => 'open',
        ]);
    }

    public function test_organizer_cannot_change_other_tournament_status(): void
    {
        $otherOrganizer = User::factory()->create(['role' => 'organizer']);

        $response = $this->actingAs($otherOrganizer, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", [
                'status' => 'open',
            ]);

        $response->assertStatus(403);
    }

    public function test_player_cannot_change_tournament_status(): void
    {
        $player = User::factory()->create(['role' => 'player']);

        $response = $this->actingAs($player, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", [
                'status' => 'open',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_set_invalid_status(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    public function test_valid_status_transitions(): void
    {
        // Draft -> Open
        $this->tournament->update(['status' => 'draft']);
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", ['status' => 'open']);
        $response->assertStatus(200);

        // Open -> In Progress
        $this->tournament->update(['status' => 'open']);
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", ['status' => 'in_progress']);
        $response->assertStatus(200);

        // In Progress -> Completed
        $this->tournament->update(['status' => 'in_progress']);
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", ['status' => 'completed']);
        $response->assertStatus(200);

        // Any -> Cancelled
        $this->tournament->update(['status' => 'open']);
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tournaments/{$this->tournament->id}/status", ['status' => 'cancelled']);
        $response->assertStatus(200);
    }
}
