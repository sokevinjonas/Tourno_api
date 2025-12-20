<?php

namespace App\Services;

use App\Models\OAuthProvider;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class OAuthService
{
    /**
     * Redirect to OAuth provider
     */
    public function redirectToProvider(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback and authenticate user
     */
    public function handleProviderCallback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            throw new \Exception('OAuth authentication failed: ' . $e->getMessage());
        }

        return DB::transaction(function () use ($provider, $socialiteUser) {
            // Check if OAuth provider already exists
            $oauthProvider = OAuthProvider::where('provider', $provider)
                ->where('provider_user_id', $socialiteUser->getId())
                ->first();

            if ($oauthProvider) {
                // Update existing OAuth provider tokens
                $oauthProvider->update([
                    'access_token' => $socialiteUser->token,
                    'refresh_token' => $socialiteUser->refreshToken,
                    'token_expires_at' => $socialiteUser->expiresIn
                        ? now()->addSeconds($socialiteUser->expiresIn)
                        : null,
                ]);

                $user = $oauthProvider->user;
            } else {
                // Check if user exists with this email
                $user = User::where('email', $socialiteUser->getEmail())->first();

                if (!$user) {
                    // Create new user
                    $user = $this->createUserFromSocialite($socialiteUser);

                    // Create wallet with initial bonus
                    $this->createWalletWithBonus($user);
                }

                // Create OAuth provider record
                OAuthProvider::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $socialiteUser->getId(),
                    'provider_email' => $socialiteUser->getEmail(),
                    'access_token' => $socialiteUser->token,
                    'refresh_token' => $socialiteUser->refreshToken,
                    'token_expires_at' => $socialiteUser->expiresIn
                        ? now()->addSeconds($socialiteUser->expiresIn)
                        : null,
                ]);
            }

            // Mark email as verified
            if (!$user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            }

            // Generate Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            return [
                'user' => $user->load(['profile', 'wallet']),
                'token' => $token,
            ];
        });
    }

    /**
     * Create user from Socialite user data
     */
    protected function createUserFromSocialite($socialiteUser): User
    {
        return User::create([
            'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'User',
            'email' => $socialiteUser->getEmail(),
            'avatar_url' => $socialiteUser->getAvatar(),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Create wallet with initial bonus for new user
     */
    protected function createWalletWithBonus(User $user): void
    {
        $initialBonus = 100.00;

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => $initialBonus,
        ]);

        Transaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $initialBonus,
            'balance_before' => 0,
            'balance_after' => $initialBonus,
            'reason' => 'initial_bonus',
            'description' => 'Bonus de bienvenue - 100 pièces MLM offertes',
        ]);
    }

    /**
     * Validate OAuth provider
     */
    protected function validateProvider(string $provider): void
    {
        $allowedProviders = ['google', 'apple', 'facebook'];

        if (!in_array($provider, $allowedProviders)) {
            throw new \InvalidArgumentException("Provider {$provider} is not supported.");
        }
    }
}
