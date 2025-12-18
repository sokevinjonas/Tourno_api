<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\GameAccountController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// OAuth Social Login
Route::prefix('auth/oauth')->group(function () {
    Route::get('/{provider}/redirect', [OAuthController::class, 'redirect']);
    Route::get('/{provider}/callback', [OAuthController::class, 'callback']);
});

// Magic Link Authentication
Route::prefix('auth/magic-link')->group(function () {
    Route::post('/send', [MagicLinkController::class, 'sendLink']);
    Route::post('/verify', [MagicLinkController::class, 'verifyLink']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Get current user
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['profile', 'wallet', 'gameAccounts']);
    });

    // Logout
    Route::post('/auth/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    /*
    |--------------------------------------------------------------------------
    | Profile Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::post('/', [ProfileController::class, 'store']);
        Route::get('/statistics', [ProfileController::class, 'statistics']);
    });

    // Profile validation routes (Moderators only)
    Route::prefix('profiles')->group(function () {
        Route::get('/pending', [ProfileController::class, 'pending']);
        Route::post('/{id}/validate', [ProfileController::class, 'validate']);
        Route::post('/{id}/reject', [ProfileController::class, 'reject']);
    });

    /*
    |--------------------------------------------------------------------------
    | Game Accounts Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('game-accounts', GameAccountController::class);
});
