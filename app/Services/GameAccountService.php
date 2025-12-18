<?php

namespace App\Services;

use App\Models\GameAccount;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class GameAccountService
{
    /**
     * Create a game account for user
     */
    public function createGameAccount(User $user, array $data): GameAccount
    {
        // Check if user already has an account for this game type
        $existingAccount = GameAccount::where('user_id', $user->id)
            ->where('game_type', $data['game_type'])
            ->first();

        if ($existingAccount) {
            throw new \Exception("You already have an account for {$data['game_type']}");
        }

        // Handle screenshot upload
        $screenshotPath = null;
        if (isset($data['screenshot'])) {
            $screenshotPath = $this->uploadScreenshot($data['screenshot'], $user->id, $data['game_type']);
        }

        $gameAccount = GameAccount::create([
            'user_id' => $user->id,
            'game_type' => $data['game_type'],
            'in_game_name' => $data['in_game_name'],
            'screenshot_path' => $screenshotPath,
        ]);

        return $gameAccount;
    }

    /**
     * Update a game account
     */
    public function updateGameAccount(GameAccount $gameAccount, array $data): GameAccount
    {
        $updateData = [];

        if (isset($data['in_game_name'])) {
            $updateData['in_game_name'] = $data['in_game_name'];
        }

        // Handle screenshot upload
        if (isset($data['screenshot'])) {
            // Delete old screenshot if exists
            if ($gameAccount->screenshot_path) {
                Storage::disk('public')->delete($gameAccount->screenshot_path);
            }

            $updateData['screenshot_path'] = $this->uploadScreenshot(
                $data['screenshot'],
                $gameAccount->user_id,
                $gameAccount->game_type
            );
        }

        $gameAccount->update($updateData);

        return $gameAccount->fresh();
    }

    /**
     * Delete a game account
     */
    public function deleteGameAccount(GameAccount $gameAccount): bool
    {
        // Delete screenshot if exists
        if ($gameAccount->screenshot_path) {
            Storage::disk('public')->delete($gameAccount->screenshot_path);
        }

        return $gameAccount->delete();
    }

    /**
     * Get all game accounts for a user
     */
    public function getUserGameAccounts(User $user)
    {
        return GameAccount::where('user_id', $user->id)->get();
    }

    /**
     * Get game account by ID and verify ownership
     */
    public function getGameAccount(int $id, User $user): ?GameAccount
    {
        return GameAccount::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Upload screenshot and return path
     */
    protected function uploadScreenshot($file, int $userId, string $gameType): string
    {
        $filename = "user_{$userId}_{$gameType}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('game_accounts', $filename, 'public');

        return $path;
    }

    /**
     * Validate game type
     */
    public function isValidGameType(string $gameType): bool
    {
        $allowedGameTypes = ['efootball', 'fc_mobile', 'dream_league_soccer'];
        return in_array($gameType, $allowedGameTypes);
    }
}
