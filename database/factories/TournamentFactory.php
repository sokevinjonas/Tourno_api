<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_id' => User::factory(),
            'name' => fake()->words(3, true) . ' Tournament',
            'description' => fake()->sentence(),
            'game' => fake()->randomElement(['efootball', 'fc_mobile', 'dream_league_soccer']),
            'format' => fake()->randomElement(['single_elimination', 'swiss', 'champions_league']),
            'max_participants' => fake()->randomElement([8, 16, 32]),
            'entry_fee' => fake()->randomFloat(2, 0, 100),
            'start_date' => now()->addDays(7),
            'status' => 'draft',
            'visibility' => 'public',
            'unique_url' => Str::random(10),
            'creation_fee_paid' => 0,
            'auto_managed' => false,
            'tournament_duration_days' => null,
            'time_slot' => 'evening',
            'match_deadline_minutes' => 60,
            'total_rounds' => null,
            'current_round' => 0,
            'prize_distribution' => null,
        ];
    }
}
