<?php

namespace App\Http\Controllers;

use App\Services\GameAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameAccountController extends Controller
{
    protected GameAccountService $gameAccountService;

    public function __construct(GameAccountService $gameAccountService)
    {
        $this->gameAccountService = $gameAccountService;
    }

    /**
     * Get all game accounts for current user
     */
    public function index(Request $request): JsonResponse
    {
        $gameAccounts = $this->gameAccountService->getUserGameAccounts($request->user());

        return response()->json([
            'game_accounts' => $gameAccounts,
        ], 200);
    }

    /**
     * Create a new game account
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'game' => 'required|string|in:efootball,fc_mobile,dream_league_soccer',
            'game_username' => 'required|string|max:100',
            'team_screenshot_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['screenshot'] = $request->file('screenshot');

            $gameAccount = $this->gameAccountService->createGameAccount(
                $request->user(),
                $data
            );

            return response()->json([
                'message' => 'Game account created successfully',
                'game_account' => $gameAccount,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create game account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific game account
     */
    public function show(Request $request, \App\Models\GameAccount $gameAccount): JsonResponse
    {
        // Check if game account belongs to the authenticated user
        if ($gameAccount->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'game_account' => $gameAccount,
        ], 200);
    }

    /**
     * Update a game account
     */
    public function update(Request $request, \App\Models\GameAccount $gameAccount): JsonResponse
    {
        // Check if game account belongs to the authenticated user
        if ($gameAccount->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'in_game_name' => 'sometimes|required|string|max:100',
            'screenshot' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            if ($request->hasFile('screenshot')) {
                $data['screenshot'] = $request->file('screenshot');
            }

            $updatedGameAccount = $this->gameAccountService->updateGameAccount(
                $gameAccount,
                $data
            );

            return response()->json([
                'message' => 'Game account updated successfully',
                'game_account' => $updatedGameAccount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update game account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a game account
     */
    public function destroy(Request $request, \App\Models\GameAccount $gameAccount): JsonResponse
    {
        // Check if game account belongs to the authenticated user
        if ($gameAccount->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->gameAccountService->deleteGameAccount($gameAccount);

            return response()->json([
                'message' => 'Game account deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete game account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
