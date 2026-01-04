<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\GameAccountController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MatchChatController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentRegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

    Route::get('is-authenticated', function () {
        return response()->json(['authenticated' => true]);
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
        Route::post('/{profile}/validate', [ProfileController::class, 'validate']);
        Route::post('/{profile}/reject', [ProfileController::class, 'reject']);
    });

    /*
    |--------------------------------------------------------------------------
    | Game Accounts Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('game-accounts', GameAccountController::class);

    /*
    |--------------------------------------------------------------------------
    | Wallet & Transactions
    |--------------------------------------------------------------------------
    */
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'show']);
        Route::get('/balance', [WalletController::class, 'balance']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::get('/statistics', [WalletController::class, 'statistics']);

        // Admin only: Add funds to user wallet
        Route::post('/add-funds', [WalletController::class, 'addFunds']);
    });

    /*
    |--------------------------------------------------------------------------
    | Users Management (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Users Management routes (Moderators and Admins)
        Route::get('/statistics', [UserController::class, 'statistics']);
        Route::get('/{user}', [UserController::class, 'show']); // Users Management routes (Moderators and Admins)
        Route::patch('/{user}/role', [UserController::class, 'updateRole']);
    });

    /*
    |--------------------------------------------------------------------------
    | Tournaments Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('tournaments')->group(function () {
        // Public for authenticated users
        Route::get('/registering', [TournamentController::class, 'registering']);
        // Organizer/Admin only
        Route::post('/', [TournamentController::class, 'store']);
        Route::get('/my/tournaments', [TournamentController::class, 'myTournaments']);
        Route::put('/{id}', [TournamentController::class, 'update']);
        Route::delete('/{id}', [TournamentController::class, 'destroy']);
        Route::post('/{id}/status', [TournamentController::class, 'changeStatus']);

        // Tournament registrations
        Route::post('/{id}/register', [TournamentRegistrationController::class, 'register']);
        Route::post('/{id}/withdraw', [TournamentRegistrationController::class, 'withdraw']);
        Route::get('/{id}/participants', [TournamentRegistrationController::class, 'participants']);
        Route::get('/{id}/leaderboard', [TournamentRegistrationController::class, 'leaderboard']);
        Route::get('/{id}/check-registration', [TournamentRegistrationController::class, 'checkRegistration']);

        // Tournament rounds management (Organizer/Admin only)
        Route::post('/{id}/start', [RoundController::class, 'startTournament']);
        Route::post('/{id}/next-round', [RoundController::class, 'generateNextRound']);
        Route::get('/{id}/rounds', [RoundController::class, 'getRounds']);
        Route::post('/{tournamentId}/rounds/{roundId}/complete', [RoundController::class, 'completeRound']);
        Route::post('/{id}/complete', [RoundController::class, 'completeTournament']);
    });

    // User's tournament registrations
    Route::get('/my/registrations', [TournamentRegistrationController::class, 'myRegistrations']);

    /*
    |--------------------------------------------------------------------------
    | Matches & Results
    |--------------------------------------------------------------------------
    */
    Route::prefix('matches')->group(function () {
        Route::get('/{match}', [MatchController::class, 'show']);
        Route::post('/{match}/submit-result', [MatchController::class, 'submitResult']);
        Route::get('/my/matches', [MatchController::class, 'myMatches']);
        Route::get('/my/pending', [MatchController::class, 'myPendingMatches']);

        // Match chat
        Route::post('/{match}/messages', [MatchChatController::class, 'sendMessage']);
        Route::get('/{match}/messages', [MatchChatController::class, 'getMessages']);

        // Match evidence
        Route::post('/{match}/evidence', [MatchChatController::class, 'uploadEvidence']);
        Route::get('/{match}/evidence', [MatchChatController::class, 'getEvidence']);

        // Enter scores (Organizer only)
        Route::post('/{match}/enter-score', [MatchChatController::class, 'enterScore']);

        // Update scores (Organizer only) - For completed matches
        Route::patch('/{match}/update-score', [MatchChatController::class, 'updateScore']);

        // Moderator only: disputed matches
        Route::get('/disputed/all', [MatchController::class, 'disputed']);
        Route::post('/{match}/validate', [MatchController::class, 'validateResult']);
    });

    /*
    |--------------------------------------------------------------------------
    | Organizers Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('organizers')->group(function () {
        Route::get('/check-if-organizer', [OrganizerController::class, 'checkIfOrganizer']);
        Route::post('/upgrade', [OrganizerController::class, 'upgradeToOrganizer']);
        Route::get('/my/following', [OrganizerController::class, 'myFollowing']);
        Route::post('/{id}/follow', [OrganizerController::class, 'toggleFollow']);
        Route::get('/{id}/check-following', [OrganizerController::class, 'checkFollowing']);

        // Verification requests
        Route::post('/verification/submit', [OrganizerController::class, 'submitVerificationRequest']);
        Route::get('/verification/pending', [OrganizerController::class, 'getPendingVerifications']);
        Route::post('/verification/{profile}/validate', [OrganizerController::class, 'validateVerificationRequest']);
        Route::post('/verification/{profile}/reject', [OrganizerController::class, 'rejectVerificationRequest']);
    });
});

/*
|--------------------------------------------------------------------------
| // Public tournament queries (NO Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('tournaments')->group(function () {
        // Public tournament queries
        Route::get('/', [TournamentController::class, 'index']);
        Route::get('/upcoming', [TournamentController::class, 'upcoming']);
        Route::post('/preview-schedule', [TournamentController::class, 'previewSchedule']);
        Route::get('/{id}', [TournamentController::class, 'show']);
        Route::get('/{id}/matches', [TournamentController::class, 'getMatches']);
        Route::get('/{id}/rounds-info', [TournamentController::class, 'getRoundsInfo']);
});

/*
|--------------------------------------------------------------------------
| Organizers (Public - NO Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('organizers')->group(function () {
    Route::get('/', [OrganizerController::class, 'index']);
    Route::get('/{organizer}', [OrganizerController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Leaderboards & Stats (Public - NO Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('leaderboard')->group(function () {
    Route::get('/global', [LeaderboardController::class, 'globalLeaderboard']);
    Route::get('/by-game/{game}', [LeaderboardController::class, 'byGameLeaderboard']);
});

Route::prefix('users')->group(function () {
    Route::get('/{user}/stats', [LeaderboardController::class, 'userStats']);
});

Route::prefix('tournaments')->group(function () {
    Route::get('/{id}/rankings', [LeaderboardController::class, 'tournamentRankings']);
});