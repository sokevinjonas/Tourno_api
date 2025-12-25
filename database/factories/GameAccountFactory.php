<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameAccount>
 */
class GameAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game' => fake()->randomElement(['efootball', 'fc_mobile', 'dream_league_soccer']),
            'game_username' => fake()->userName(),
            'team_screenshot_path' => 'screenshots/' . fake()->uuid() . '.jpg',
        ];
    }
}
