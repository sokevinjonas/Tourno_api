<?php

namespace App\Services;

use App\Models\LoginToken;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MagicLinkService
{
    /**
     * Send magic link to user's email
     */
    public function sendMagicLink(string $email, string $ipAddress = null, string $userAgent = null): array
    {
        // Delete old expired tokens for this email
        LoginToken::where('email', $email)
            ->expired()
            ->delete();

        // Generate unique token
        $token = Str::random(64);

        // Create login token
        $loginToken = LoginToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Generate magic link URL
        $magicLink = config('app.frontend_url') . '/auth/verify?token=' . $token;

        // Send email with magic link
        $this->sendEmail($email, $magicLink);

        return [
            'message' => 'Magic link sent to your email',
            'expires_in' => '15 minutes',
        ];
    }

    /**
     * Verify magic link token and authenticate user
     */
    public function verifyMagicLink(string $token): array
    {
        $loginToken = LoginToken::where('token', $token)
            ->valid()
            ->first();

        if (!$loginToken) {
            throw new \Exception('Invalid or expired magic link');
        }

        return DB::transaction(function () use ($loginToken) {
            // Mark token as used
            $loginToken->update([
                'is_used' => true,
                'used_at' => now(),
            ]);

            // Find or create user
            $user = User::where('email', $loginToken->email)->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => explode('@', $loginToken->email)[0],
                    'email' => $loginToken->email,
                    'email_verified_at' => now(),
                ]);

                // Create wallet with initial bonus
                $this->createWalletWithBonus($user);
            } else {
                // Mark email as verified
                if (!$user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }
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
     * Create wallet with initial bonus for new user
     */
    protected function createWalletWithBonus(User $user): void
    {
        $initialBonus = 10.00;

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
            'description' => 'Bonus de bienvenue - 10 pièces MLM offertes',
        ]);
    }

    /**
     * Send magic link email
     */
    protected function sendEmail(string $email, string $magicLink): void
    {
        // For development, just log the magic link
        // In production, replace with actual email sending
        Mail::raw(
            "Cliquez sur ce lien pour vous connecter à MLM :\n\n{$magicLink}\n\nCe lien expire dans 15 minutes.",
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Votre lien de connexion MLM');
            }
        );
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return LoginToken::expired()->delete();
    }
}
