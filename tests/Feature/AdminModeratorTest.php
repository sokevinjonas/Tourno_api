<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModeratorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->moderator = User::factory()->create(['role' => 'moderator']);
        $this->player = User::factory()->create(['role' => 'player']);
    }

    // Profile Validation Tests

    public function test_moderator_can_view_pending_profiles(): void
    {
        // Create pending profile
        $pendingUser = User::factory()->create(['role' => 'player']);
        $pendingUser->profile->update(['status' => 'pending']);

        $response = $this->actingAs($this->moderator, 'sanctum')
            ->getJson('/api/profiles/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'profiles' => [
                    '*' => [
                        'id',
                        'user_id',
                        'status',
                        'whatsapp_number',
                        'country',
                        'city',
                    ],
                ],
            ]);
    }

    public function test_moderator_can_validate_profile(): void
    {
        $pendingUser = User::factory()->create(['role' => 'player']);
        $pendingUser->profile->update(['status' => 'pending']);

        $response = $this->actingAs($this->moderator, 'sanctum')
            ->postJson("/api/profiles/{$pendingUser->profile->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile validated successfully',
            ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $pendingUser->profile->id,
            'status' => 'validated',
            'validated_by' => $this->moderator->id,
        ]);
    }

    public function test_moderator_can_reject_profile(): void
    {
        $pendingUser = User::factory()->create(['role' => 'player']);
        $pendingUser->profile->update(['status' => 'pending']);

        $response = $this->actingAs($this->moderator, 'sanctum')
            ->postJson("/api/profiles/{$pendingUser->profile->id}/reject", [
                'rejection_reason' => 'Invalid information',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile rejected successfully',
            ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $pendingUser->profile->id,
            'status' => 'rejected',
            'rejection_reason' => 'Invalid information',
            'validated_by' => $this->moderator->id,
        ]);
    }

    public function test_player_cannot_access_moderator_endpoints(): void
    {
        $response = $this->actingAs($this->player, 'sanctum')
            ->getJson('/api/profiles/pending');

        $response->assertStatus(403);
    }

    // Admin Wallet Management Tests

    public function test_admin_can_add_funds_to_user_wallet(): void
    {
        $targetUser = User::factory()->create(['role' => 'player']);
        $initialBalance = $targetUser->wallet->balance;

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/wallet/add-funds', [
                'user_id' => $targetUser->id,
                'amount' => 50.00,
                'description' => 'Compensation for bug',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'transaction',
                'new_balance',
            ]);

        $targetUser->wallet->refresh();
        $this->assertEquals($initialBalance + 50.00, $targetUser->wallet->balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $targetUser->id,
            'type' => 'credit',
            'amount' => 50.00,
            'reason' => 'admin_adjustment',
            'description' => 'Compensation for bug',
        ]);
    }

    public function test_moderator_cannot_add_funds(): void
    {
        $targetUser = User::factory()->create(['role' => 'player']);

        $response = $this->actingAs($this->moderator, 'sanctum')
            ->postJson('/api/wallet/add-funds', [
                'user_id' => $targetUser->id,
                'amount' => 50.00,
            ]);

        $response->assertStatus(403);
    }

    public function test_player_cannot_add_funds(): void
    {
        $targetUser = User::factory()->create(['role' => 'player']);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson('/api/wallet/add-funds', [
                'user_id' => $targetUser->id,
                'amount' => 50.00,
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_add_negative_funds(): void
    {
        $targetUser = User::factory()->create(['role' => 'player']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/wallet/add-funds', [
                'user_id' => $targetUser->id,
                'amount' => -50.00,
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_has_access_to_all_moderator_endpoints(): void
    {
        // Create pending profile
        $pendingUser = User::factory()->create(['role' => 'player']);
        $pendingUser->profile->update(['status' => 'pending']);

        // Test admin can access moderator endpoint
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/profiles/pending');

        $response->assertStatus(200);

        // Test admin can validate profile
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/profiles/{$pendingUser->profile->id}/validate");

        $response->assertStatus(200);
    }
}
