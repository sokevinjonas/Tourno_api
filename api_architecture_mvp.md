# 🏗️ ARCHITECTURE API LARAVEL - MVP

## 📋 Vue d'ensemble

Ce document décrit l'architecture complète de l'API REST Laravel pour le MVP de Mobile League Manager (MLM).

**Principes architecturaux** :
- **Clean Architecture** : Séparation des responsabilités
- **Repository Pattern** : Abstraction de l'accès aux données
- **Service Layer** : Logique métier centralisée
- **API Resources** : Transformation cohérente des données
- **Form Requests** : Validation centralisée
- **Policies** : Gestion des autorisations

---

## 📂 Structure des Dossiers

```
app/
├── Console/
│   └── Commands/
│       └── CleanExpiredTokensCommand.php
├── Events/
│   ├── ProfileValidated.php
│   └── TournamentStarted.php
├── Exceptions/
│   ├── InsufficientBalanceException.php
│   └── TournamentFullException.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   │   ├── OAuthController.php
│   │   │   │   ├── MagicLinkController.php
│   │   │   │   └── AuthController.php
│   │   │   ├── Profile/
│   │   │   │   ├── ProfileController.php
│   │   │   │   └── GameAccountController.php
│   │   │   ├── Tournament/
│   │   │   │   ├── TournamentController.php
│   │   │   │   ├── TournamentRegistrationController.php
│   │   │   │   └── RoundController.php
│   │   │   ├── Match/
│   │   │   │   ├── MatchController.php
│   │   │   │   └── MatchResultController.php
│   │   │   ├── Wallet/
│   │   │   │   ├── WalletController.php
│   │   │   │   └── TransactionController.php
│   │   │   ├── Moderator/
│   │   │   │   └── ProfileValidationController.php
│   │   │   └── Admin/
│   │   │       ├── UserController.php
│   │   │       ├── TournamentController.php
│   │   │       └── FinanceController.php
│   ├── Middleware/
│   │   ├── EnsureProfileCompleted.php
│   │   ├── EnsureProfileValidated.php
│   │   └── CheckRole.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── SendMagicLinkRequest.php
│   │   │   └── VerifyTokenRequest.php
│   │   ├── Profile/
│   │   │   ├── CompleteProfileRequest.php
│   │   │   └── UpdateGameAccountRequest.php
│   │   ├── Tournament/
│   │   │   ├── CreateTournamentRequest.php
│   │   │   ├── UpdateTournamentRequest.php
│   │   │   └── RegisterTournamentRequest.php
│   │   └── Match/
│   │       └── SubmitMatchResultRequest.php
│   ├── Resources/
│   │   ├── UserResource.php
│   │   ├── ProfileResource.php
│   │   ├── GameAccountResource.php
│   │   ├── TournamentResource.php
│   │   ├── TournamentDetailResource.php
│   │   ├── MatchResource.php
│   │   ├── WalletResource.php
│   │   └── TransactionResource.php
├── Listeners/
│   ├── CreateWalletForValidatedProfile.php
│   └── GenerateTournamentPairings.php
├── Models/
│   ├── User.php
│   ├── OAuthProvider.php
│   ├── LoginToken.php
│   ├── Profile.php
│   ├── GameAccount.php
│   ├── Wallet.php
│   ├── Transaction.php
│   ├── Tournament.php
│   ├── TournamentRegistration.php
│   ├── Round.php
│   ├── Match.php
│   └── MatchResult.php
├── Policies/
│   ├── TournamentPolicy.php
│   ├── MatchPolicy.php
│   └── ProfilePolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php
│   └── EventServiceProvider.php
├── Repositories/
│   ├── Contracts/
│   │   ├── UserRepositoryInterface.php
│   │   ├── TournamentRepositoryInterface.php
│   │   ├── MatchRepositoryInterface.php
│   │   └── WalletRepositoryInterface.php
│   └── Eloquent/
│       ├── UserRepository.php
│       ├── TournamentRepository.php
│       ├── MatchRepository.php
│       └── WalletRepository.php
├── Services/
│   ├── Auth/
│   │   ├── OAuthService.php
│   │   └── MagicLinkService.php
│   ├── Profile/
│   │   └── ProfileService.php
│   ├── Tournament/
│   │   ├── TournamentService.php
│   │   ├── SwissSystemService.php
│   │   └── PrizeDistributionService.php
│   ├── Match/
│   │   └── MatchService.php
│   └── Wallet/
│       └── WalletService.php
└── Traits/
    ├── HasRoles.php
    └── Uploadable.php

config/
├── sanctum.php
├── services.php (OAuth config)
└── filesystems.php (Storage config)

database/
├── factories/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    ├── RoleSeeder.php
    └── GameSeeder.php

routes/
├── api.php
└── channels.php (future: broadcasting)

tests/
├── Feature/
│   ├── Auth/
│   ├── Tournament/
│   ├── Match/
│   └── Wallet/
└── Unit/
    ├── Services/
    └── Models/
```

---

## 🛣️ Routes & Endpoints

### **routes/api.php**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\{OAuthController, MagicLinkController, AuthController};
use App\Http\Controllers\Api\Profile\{ProfileController, GameAccountController};
use App\Http\Controllers\Api\Tournament\{TournamentController, TournamentRegistrationController, RoundController};
use App\Http\Controllers\Api\Match\{MatchController, MatchResultController};
use App\Http\Controllers\Api\Wallet\{WalletController, TransactionController};
use App\Http\Controllers\Api\Moderator\ProfileValidationController;
use App\Http\Controllers\Api\Admin\{UserController, TournamentController as AdminTournamentController, FinanceController};

/*
|--------------------------------------------------------------------------
| API Routes - MVP
|--------------------------------------------------------------------------
*/

// ========================================
// 🔐 AUTHENTICATION (Public)
// ========================================
Route::prefix('auth')->group(function () {
    // OAuth Social Login
    Route::get('/oauth/{provider}/redirect', [OAuthController::class, 'redirect']); // Google, Apple, Facebook
    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'callback']);

    // Magic Link Email Login
    Route::post('/magic-link/send', [MagicLinkController::class, 'send']);
    Route::post('/magic-link/verify', [MagicLinkController::class, 'verify']);

    // Current User
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ========================================
// 📋 PROFILE (Authenticated)
// ========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::post('/complete', [ProfileController::class, 'complete']); // Compléter le profil
        Route::put('/', [ProfileController::class, 'update']);

        // Game Accounts
        Route::prefix('game-accounts')->group(function () {
            Route::get('/', [GameAccountController::class, 'index']);
            Route::post('/', [GameAccountController::class, 'store']);
            Route::put('/{gameAccount}', [GameAccountController::class, 'update']);
            Route::delete('/{gameAccount}', [GameAccountController::class, 'destroy']);
        });
    });
});

// ========================================
// 🏆 TOURNAMENTS (Public listing + Authenticated actions)
// ========================================
// Public: Liste des tournois
Route::get('/tournaments', [TournamentController::class, 'index']);
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show']);

// Authenticated: Actions sur les tournois
Route::middleware(['auth:sanctum', 'profile.validated'])->group(function () {
    // Inscription aux tournois
    Route::post('/tournaments/{tournament}/register', [TournamentRegistrationController::class, 'register']);
    Route::delete('/tournaments/{tournament}/unregister', [TournamentRegistrationController::class, 'unregister']);

    // Organisateur: Créer et gérer ses tournois
    Route::middleware('role:organizer,admin')->group(function () {
        Route::post('/tournaments', [TournamentController::class, 'store']);
        Route::put('/tournaments/{tournament}', [TournamentController::class, 'update']);
        Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy']);
        Route::post('/tournaments/{tournament}/start', [TournamentController::class, 'start']);
        Route::post('/tournaments/{tournament}/cancel', [TournamentController::class, 'cancel']);

        // Gestion des rondes
        Route::post('/tournaments/{tournament}/rounds/{round}/generate-pairings', [RoundController::class, 'generatePairings']);
        Route::post('/tournaments/{tournament}/rounds/{round}/complete', [RoundController::class, 'complete']);
    });
});

// ========================================
// ⚽ MATCHES (Authenticated)
// ========================================
Route::middleware(['auth:sanctum', 'profile.validated'])->group(function () {
    Route::prefix('matches')->group(function () {
        Route::get('/my-matches', [MatchController::class, 'myMatches']);
        Route::get('/{match}', [MatchController::class, 'show']);

        // Soumettre un résultat
        Route::post('/{match}/submit-result', [MatchResultController::class, 'submit']);

        // Organisateur: Valider un résultat contesté
        Route::middleware('role:organizer,admin')->group(function () {
            Route::post('/{match}/validate-result', [MatchResultController::class, 'validate']);
        });
    });
});

// ========================================
// 💰 WALLET (Authenticated)
// ========================================
Route::middleware(['auth:sanctum', 'profile.validated'])->group(function () {
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'show']);
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    });
});

// ========================================
// 🛡️ MODERATOR (Role: moderator)
// ========================================
Route::middleware(['auth:sanctum', 'role:moderator,admin'])->prefix('moderator')->group(function () {
    Route::get('/profiles/pending', [ProfileValidationController::class, 'pending']);
    Route::post('/profiles/{profile}/validate', [ProfileValidationController::class, 'validate']);
    Route::post('/profiles/{profile}/reject', [ProfileValidationController::class, 'reject']);
});

// ========================================
// 👑 ADMIN (Role: admin)
// ========================================
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Users
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/ban', [UserController::class, 'ban']);
    Route::post('/users/{user}/unban', [UserController::class, 'unban']);

    // Tournaments
    Route::get('/tournaments', [AdminTournamentController::class, 'index']);
    Route::delete('/tournaments/{tournament}', [AdminTournamentController::class, 'destroy']);

    // Finances
    Route::get('/finances/overview', [FinanceController::class, 'overview']);
    Route::get('/finances/transactions', [FinanceController::class, 'transactions']);
});
```

---

## 🎯 Controllers

### 1. **AuthController**

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        return new UserResource($request->user()->load(['profile', 'wallet', 'gameAccounts']));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
```

### 2. **OAuthController**

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OAuthService;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function __construct(private OAuthService $oauthService)
    {
    }

    public function redirect(string $provider)
    {
        return $this->oauthService->getRedirectUrl($provider);
    }

    public function callback(string $provider, Request $request)
    {
        $result = $this->oauthService->handleCallback($provider, $request);

        return response()->json($result);
    }
}
```

### 3. **MagicLinkController**

```php
<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{SendMagicLinkRequest, VerifyTokenRequest};
use App\Services\Auth\MagicLinkService;

class MagicLinkController extends Controller
{
    public function __construct(private MagicLinkService $magicLinkService)
    {
    }

    public function send(SendMagicLinkRequest $request)
    {
        $this->magicLinkService->sendMagicLink($request->email);

        return response()->json(['message' => 'Magic link sent to your email']);
    }

    public function verify(VerifyTokenRequest $request)
    {
        $result = $this->magicLinkService->verifyToken($request->token);

        return response()->json($result);
    }
}
```

### 4. **ProfileController**

```php
<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\CompleteProfileRequest;
use App\Services\Profile\ProfileService;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService)
    {
    }

    public function show(Request $request)
    {
        $profile = $request->user()->profile()->with('gameAccounts')->first();

        return new ProfileResource($profile);
    }

    public function complete(CompleteProfileRequest $request)
    {
        $profile = $this->profileService->completeProfile(
            $request->user(),
            $request->validated()
        );

        return new ProfileResource($profile);
    }

    public function update(Request $request)
    {
        // Update profile logic
    }
}
```

### 5. **TournamentController**

```php
<?php

namespace App\Http\Controllers\Api\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tournament\{CreateTournamentRequest, UpdateTournamentRequest};
use App\Services\Tournament\TournamentService;
use App\Http\Resources\{TournamentResource, TournamentDetailResource};
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct(private TournamentService $tournamentService)
    {
    }

    public function index(Request $request)
    {
        $tournaments = Tournament::with(['organizer'])
            ->when($request->game, fn($q) => $q->where('game', $request->game))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return TournamentResource::collection($tournaments);
    }

    public function show(Tournament $tournament)
    {
        return new TournamentDetailResource($tournament->load([
            'organizer',
            'registrations.user',
            'rounds.matches'
        ]));
    }

    public function store(CreateTournamentRequest $request)
    {
        $tournament = $this->tournamentService->createTournament(
            $request->user(),
            $request->validated()
        );

        return new TournamentResource($tournament);
    }

    public function start(Tournament $tournament)
    {
        $this->authorize('manage', $tournament);

        $this->tournamentService->startTournament($tournament);

        return response()->json(['message' => 'Tournament started successfully']);
    }
}
```

### 6. **MatchResultController**

```php
<?php

namespace App\Http\Controllers\Api\Match;

use App\Http\Controllers\Controller;
use App\Http\Requests\Match\SubmitMatchResultRequest;
use App\Services\Match\MatchService;
use App\Models\Match;

class MatchResultController extends Controller
{
    public function __construct(private MatchService $matchService)
    {
    }

    public function submit(Match $match, SubmitMatchResultRequest $request)
    {
        $this->authorize('submitResult', $match);

        $result = $this->matchService->submitResult(
            $match,
            $request->user(),
            $request->validated()
        );

        return response()->json($result);
    }

    public function validate(Match $match)
    {
        $this->authorize('validate', $match);

        $this->matchService->validateDisputedMatch($match);

        return response()->json(['message' => 'Match result validated']);
    }
}
```

---

## 🔧 Services

### **OAuthService**

```php
<?php

namespace App\Services\Auth;

use App\Models\{User, OAuthProvider};
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;

class OAuthService
{
    public function getRedirectUrl(string $provider): array
    {
        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return ['url' => $url];
    }

    public function handleCallback(string $provider, $request): array
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        return DB::transaction(function () use ($provider, $socialUser) {
            // Rechercher le provider existant
            $oauthProvider = OAuthProvider::where('provider', $provider)
                ->where('provider_user_id', $socialUser->getId())
                ->first();

            if ($oauthProvider) {
                // User existe déjà via ce provider
                $user = $oauthProvider->user;
            } else {
                // Vérifier si l'email existe
                $user = User::where('email', $socialUser->getEmail())->first();

                if (!$user) {
                    // Créer nouveau user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'email_verified_at' => now(),
                        'avatar_url' => $socialUser->getAvatar(),
                    ]);
                }

                // Lier le provider OAuth
                OAuthProvider::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $socialUser->getId(),
                    'provider_email' => $socialUser->getEmail(),
                    'access_token' => encrypt($socialUser->token),
                    'refresh_token' => $socialUser->refreshToken ? encrypt($socialUser->refreshToken) : null,
                    'token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
                ]);
            }

            // Générer token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load(['profile', 'wallet']),
            ];
        });
    }
}
```

### **MagicLinkService**

```php
<?php

namespace App\Services\Auth;

use App\Models\{User, LoginToken};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\MagicLinkMail;
use Carbon\Carbon;

class MagicLinkService
{
    public function sendMagicLink(string $email): void
    {
        // Créer ou récupérer l'utilisateur
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => explode('@', $email)[0]] // Nom temporaire
        );

        // Générer token unique
        $token = Str::random(64);

        // Créer le login token
        LoginToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Envoyer l'email
        Mail::to($email)->send(new MagicLinkMail($token));
    }

    public function verifyToken(string $token): array
    {
        $loginToken = LoginToken::where('token', $token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Marquer comme utilisé
        $loginToken->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        // Récupérer l'utilisateur
        $user = User::where('email', $loginToken->email)->first();

        // Marquer email comme vérifié
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        // Générer token Sanctum
        $sanctumToken = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $sanctumToken,
            'token_type' => 'Bearer',
            'user' => $user->load(['profile', 'wallet']),
        ];
    }
}
```

### **ProfileService**

```php
<?php

namespace App\Services\Profile;

use App\Models\{User, Profile, GameAccount};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function completeProfile(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data) {
            // Créer le profil
            $profile = Profile::create([
                'user_id' => $user->id,
                'whatsapp_number' => $data['whatsapp_number'],
                'country' => $data['country'],
                'city' => $data['city'],
                'status' => 'pending',
            ]);

            // Créer les game accounts
            foreach ($data['game_accounts'] as $gameData) {
                // Upload screenshot
                $screenshotPath = $this->uploadScreenshot(
                    $gameData['screenshot'],
                    $user->id,
                    $gameData['game']
                );

                GameAccount::create([
                    'user_id' => $user->id,
                    'game' => $gameData['game'],
                    'game_username' => $gameData['username'],
                    'team_screenshot_path' => $screenshotPath,
                ]);
            }

            return $profile->load('gameAccounts');
        });
    }

    private function uploadScreenshot($file, int $userId, string $game): string
    {
        $filename = "user_{$userId}_{$game}_" . time() . '.' . $file->extension();
        $path = $file->storeAs('screenshots', $filename, 'public');

        return $path;
    }
}
```

### **TournamentService**

```php
<?php

namespace App\Services\Tournament;

use App\Models\{Tournament, User};
use Illuminate\Support\Facades\DB;

class TournamentService
{
    public function createTournament(User $organizer, array $data): Tournament
    {
        return Tournament::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'organizer_id' => $organizer->id,
            'game' => $data['game'],
            'max_participants' => $data['max_participants'],
            'entry_fee' => $data['entry_fee'],
            'start_date' => $data['start_date'],
            'status' => 'open',
            'prize_distribution' => $data['prize_distribution'],
        ]);
    }

    public function startTournament(Tournament $tournament): void
    {
        $participantsCount = $tournament->registrations()->count();

        // Calculer le nombre de rondes : N = ⌈log₂(P)⌉
        $totalRounds = (int) ceil(log($participantsCount, 2));

        DB::transaction(function () use ($tournament, $totalRounds) {
            $tournament->update([
                'status' => 'in_progress',
                'total_rounds' => $totalRounds,
                'current_round' => 1,
            ]);

            // Créer la première ronde
            $round = $tournament->rounds()->create([
                'round_number' => 1,
                'status' => 'pending',
            ]);

            // Générer les appariements (délégué au SwissSystemService)
            app(SwissSystemService::class)->generatePairings($tournament, $round);
        });
    }
}
```

### **SwissSystemService**

```php
<?php

namespace App\Services\Tournament;

use App\Models\{Tournament, Round};
use Illuminate\Support\Collection;

class SwissSystemService
{
    public function generatePairings(Tournament $tournament, Round $round): void
    {
        $registrations = $tournament->registrations()
            ->with('user')
            ->get();

        if ($round->round_number === 1) {
            // Première ronde : appariement aléatoire
            $this->generateRandomPairings($tournament, $round, $registrations);
        } else {
            // Rondes suivantes : apparier joueurs avec même score
            $this->generateSwissPairings($tournament, $round, $registrations);
        }
    }

    private function generateRandomPairings(Tournament $tournament, Round $round, Collection $registrations): void
    {
        $shuffled = $registrations->shuffle();

        for ($i = 0; $i < $shuffled->count(); $i += 2) {
            if (isset($shuffled[$i]) && isset($shuffled[$i + 1])) {
                $tournament->matches()->create([
                    'round_id' => $round->id,
                    'player1_id' => $shuffled[$i]->user_id,
                    'player2_id' => $shuffled[$i + 1]->user_id,
                    'status' => 'scheduled',
                    'scheduled_at' => now(),
                ]);
            }
        }
    }

    private function generateSwissPairings(Tournament $tournament, Round $round, Collection $registrations): void
    {
        // Grouper par points
        $grouped = $registrations->groupBy('tournament_points')->sortKeysDesc();

        foreach ($grouped as $points => $players) {
            $shuffled = $players->shuffle();

            for ($i = 0; $i < $shuffled->count(); $i += 2) {
                if (isset($shuffled[$i]) && isset($shuffled[$i + 1])) {
                    $tournament->matches()->create([
                        'round_id' => $round->id,
                        'player1_id' => $shuffled[$i]->user_id,
                        'player2_id' => $shuffled[$i + 1]->user_id,
                        'status' => 'scheduled',
                        'scheduled_at' => now(),
                    ]);
                }
            }
        }
    }
}
```

### **MatchService**

```php
<?php

namespace App\Services\Match;

use App\Models\{Match, User, MatchResult};
use Illuminate\Support\Facades\DB;

class MatchService
{
    public function submitResult(Match $match, User $user, array $data): array
    {
        return DB::transaction(function () use ($match, $user, $data) {
            // Upload screenshot
            $screenshotPath = $this->uploadScreenshot($data['screenshot'], $match->id, $user->id);

            // Créer le résultat
            $matchResult = MatchResult::create([
                'match_id' => $match->id,
                'submitted_by' => $user->id,
                'own_score' => $data['own_score'],
                'opponent_score' => $data['opponent_score'],
                'screenshot_path' => $screenshotPath,
                'comment' => $data['comment'] ?? null,
                'status' => 'pending',
            ]);

            // Vérifier si les 2 joueurs ont soumis
            $results = $match->results;

            if ($results->count() === 2) {
                $this->checkResults($match, $results);
            }

            return [
                'message' => 'Result submitted successfully',
                'match' => $match->fresh(['results']),
            ];
        });
    }

    private function checkResults(Match $match, $results): void
    {
        $result1 = $results->first();
        $result2 = $results->last();

        // Comparer les scores
        if ($result1->own_score === $result2->opponent_score
            && $result1->opponent_score === $result2->own_score) {
            // Scores concordent → Validation automatique
            $this->validateMatch($match, $result1);
        } else {
            // Scores différents → Litige
            $match->update(['status' => 'disputed']);
        }
    }

    private function validateMatch(Match $match, MatchResult $result): void
    {
        DB::transaction(function () use ($match, $result) {
            // Déterminer le gagnant
            $winnerId = null;
            if ($result->own_score > $result->opponent_score) {
                $winnerId = $result->submitted_by;
            } elseif ($result->own_score < $result->opponent_score) {
                $winnerId = $match->player1_id === $result->submitted_by
                    ? $match->player2_id
                    : $match->player1_id;
            }

            // Mettre à jour le match
            $match->update([
                'player1_score' => $match->player1_id === $result->submitted_by
                    ? $result->own_score
                    : $result->opponent_score,
                'player2_score' => $match->player2_id === $result->submitted_by
                    ? $result->own_score
                    : $result->opponent_score,
                'winner_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Mettre à jour les stats des registrations
            $this->updateRegistrationStats($match);
        });
    }

    private function updateRegistrationStats(Match $match): void
    {
        // Logique de mise à jour des points, wins, draws, losses
        // 3 points victoire, 1 point nul, 0 défaite
    }

    private function uploadScreenshot($file, int $matchId, int $userId): string
    {
        $filename = "match_{$matchId}_user_{$userId}_" . time() . '.' . $file->extension();
        return $file->storeAs('match_results', $filename, 'public');
    }
}
```

### **WalletService**

```php
<?php

namespace App\Services\Wallet;

use App\Models\{Wallet, Transaction, User};
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function createWallet(User $user, float $initialBalance = 10.00): Wallet
    {
        return DB::transaction(function () use ($user, $initialBalance) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => $initialBalance,
            ]);

            // Créer la transaction initiale
            Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $initialBalance,
                'balance_before' => 0,
                'balance_after' => $initialBalance,
                'reason' => 'initial_bonus',
                'description' => 'Welcome bonus - 10 pieces',
            ]);

            return $wallet;
        });
    }

    public function debit(Wallet $wallet, float $amount, string $reason, ?string $description = null, ?int $tournamentId = null): Transaction
    {
        if ($wallet->balance < $amount) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        return DB::transaction(function () use ($wallet, $amount, $reason, $description, $tournamentId) {
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $wallet->update(['balance' => $balanceAfter]);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'description' => $description,
                'tournament_id' => $tournamentId,
            ]);
        });
    }

    public function credit(Wallet $wallet, float $amount, string $reason, ?string $description = null, ?int $tournamentId = null): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $reason, $description, $tournamentId) {
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->update(['balance' => $balanceAfter]);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'description' => $description,
                'tournament_id' => $tournamentId,
            ]);
        });
    }
}
```

---

## 🔒 Middlewares

### **EnsureProfileValidated**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileValidated
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user->profile || $user->profile->status !== 'validated') {
            return response()->json([
                'message' => 'Your profile must be validated before accessing this resource',
                'profile_status' => $user->profile?->status ?? 'not_completed',
            ], 403);
        }

        return $next($request);
    }
}
```

### **CheckRole**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. Required roles: ' . implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
```

---

## ✅ Form Requests (Validation)

### **CompleteProfileRequest**

```php
<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'whatsapp_number' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'game_accounts' => 'required|array|min:1|max:3',
            'game_accounts.*.game' => 'required|in:efootball,fc_mobile,dream_league_soccer',
            'game_accounts.*.username' => 'required|string|max:50',
            'game_accounts.*.screenshot' => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ];
    }
}
```

### **CreateTournamentRequest**

```php
<?php

namespace App\Http\Requests\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class CreateTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'organizer' || $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'game' => 'required|in:efootball,fc_mobile,dream_league_soccer',
            'max_participants' => 'required|integer|min:2|max:128',
            'entry_fee' => 'required|numeric|min:0',
            'start_date' => 'required|date|after:now',
            'prize_distribution' => 'required|array',
            'prize_distribution.1' => 'required|numeric|min:0|max:100',
            'prize_distribution.2' => 'nullable|numeric|min:0|max:100',
            'prize_distribution.3' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
```

---

## 📦 API Resources

### **UserResource**

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
            'game_accounts' => GameAccountResource::collection($this->whenLoaded('gameAccounts')),
        ];
    }
}
```

---

## 📋 Policies

### **TournamentPolicy**

```php
<?php

namespace App\Policies;

use App\Models\{User, Tournament};

class TournamentPolicy
{
    public function manage(User $user, Tournament $tournament): bool
    {
        return $user->id === $tournament->organizer_id || $user->role === 'admin';
    }
}
```

---

**Fin du Document**

Ce document d'architecture sera mis à jour au fur et à mesure du développement.
