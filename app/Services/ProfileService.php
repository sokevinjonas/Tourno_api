<?php

namespace App\Services;

use App\Mail\ProfileRejectedMail;
use App\Mail\ProfileValidatedMail;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProfileService
{
    /**
     * Create or update user profile
     */
    public function createOrUpdateProfile(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data) {
            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'whatsapp_number' => $data['whatsapp_number'],
                    'country' => $data['country'],
                    'city' => $data['city'],
                    'status' => $data['status'] ?? 'pending',
                ]
            );

            return $profile->fresh();
        });
    }

    /**
     * Get profile for a user
     */
    public function getProfile(User $user): ?Profile
    {
        return $user->profile;
    }

    /**
     * Validate a profile (moderator action)
     */
    public function validateProfile(Profile $profile, User $moderator): Profile
    {
        if (!in_array($moderator->role, ['admin', 'moderator'])) {
            throw new \Exception('Unauthorized: Only admins and moderators can validate profiles');
        }

        $profile->update([
            'status' => 'validated',
            'validated_by' => $moderator->id,
            'validated_at' => now(),
            'rejection_reason' => null,
        ]);

        // Send email notification to user
        Mail::to($profile->user)->send(
            new ProfileValidatedMail($profile->user, $profile)
        );

        return $profile->fresh();
    }

    /**
     * Reject a profile (moderator action)
     */
    public function rejectProfile(Profile $profile, User $moderator, string $reason): Profile
    {
        if (!in_array($moderator->role, ['admin', 'moderator'])) {
            throw new \Exception('Unauthorized: Only admins and moderators can reject profiles');
        }

        $profile->update([
            'status' => 'rejected',
            'validated_by' => $moderator->id,
            'validated_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Send email notification to user
        Mail::to($profile->user)->send(
            new ProfileRejectedMail($profile->user, $profile, $reason)
        );

        return $profile->fresh();
    }

    /**
     * Get all pending profiles (for moderators)
     */
    public function getPendingProfiles()
    {
        return Profile::pending()
            ->with(['user', 'user.gameAccounts'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get profile statistics
     */
    public function getProfileStatistics(): array
    {
        return [
            'total' => Profile::count(),
            'pending' => Profile::pending()->count(),
            'validated' => Profile::validated()->count(),
            'rejected' => Profile::rejected()->count(),
        ];
    }
}
