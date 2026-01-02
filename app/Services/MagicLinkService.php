<?php

namespace App\Services;

use App\Models\LoginToken;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Mail\MagicLinkMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MagicLinkService
{
    /**
     * Send authentication code to user's email
     */
    public function sendMagicLink(string $email, string $ipAddress = null, string $userAgent = null): array
    {
        // Delete old expired tokens for this email
        LoginToken::where('email', $email)
            ->expired()
            ->delete();

        // Generate 6-digit code
        $code = $this->generateCode();

        // Create login token
        $loginToken = LoginToken::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Send email with code
        $this->sendEmail($email, $code);

        return [
            'message' => 'Code de vérification envoyé à votre email',
            'expires_in' => '15 minutes',
        ];
    }

    /**
     * Verify authentication code and authenticate user
     */
    public function verifyMagicLink(string $code): array
    {
        $loginToken = LoginToken::where('code', $code)
            ->valid()
            ->first();

        if (!$loginToken) {
            throw new \Exception('Code invalide ou expiré');
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
        $initialBonus = 4.00;

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
            'description' => 'Bonus de bienvenue - 4 pièces GPA offertes',
        ]);
    }

    /**
     * Generate a 6-digit code
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send authentication code email
     */
    protected function sendEmail(string $email, string $code): void
    {
        Mail::to($email)->send(new MagicLinkMail($code, $email));
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return LoginToken::expired()->delete();
    }
}
