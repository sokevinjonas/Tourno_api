<?php

namespace App\Services;

use App\Models\User;
use App\Models\OrganizerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizerService
{
    /**
     * Cost constants
     */
    const CERTIFIED_BADGE_COST = 50.00;
    const VERIFICATION_COST = 200.00;

    /**
     * Upgrade user to organizer
     */
    public function upgradeToOrganizer(User $user): array
    {
        // Check if user is already an organizer
        if ($user->role === 'organizer') {
            throw new \Exception('User is already an organizer');
        }

        // Load user's wallet
        $user->load('wallet');

        // Check if user has sufficient balance
        if (!$user->wallet || $user->wallet->balance < self::CERTIFIED_BADGE_COST) {
            throw new \Exception('Insufficient balance. You need 50 MLM pieces to become an organizer.');
        }

        DB::beginTransaction();
        try {
            // Deduct the cost from wallet
            $balanceBefore = $user->wallet->balance;
            $user->wallet->balance -= self::CERTIFIED_BADGE_COST;
            $user->wallet->save();

            // Create transaction record
            DB::table('transactions')->insert([
                'wallet_id' => $user->wallet->id,
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => self::CERTIFIED_BADGE_COST,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->wallet->balance,
                'reason' => 'admin_adjustment',
                'description' => 'Upgrade to organizer - Certified badge',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update user role
            $user->role = 'organizer';
            $user->save();

            // Create organizer profile with certified badge
            $organizerProfile = OrganizerProfile::create([
                'user_id' => $user->id,
                'display_name' => $user->name,
                'avatar_initial' => strtoupper(substr($user->name, 0, 1)),
                'badge' => 'certified',
                'is_featured' => false,
            ]);

            DB::commit();

            return [
                'user' => $user,
                'organizer_profile' => $organizerProfile,
                'transaction_amount' => self::CERTIFIED_BADGE_COST,
                'new_balance' => $user->wallet->balance,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Submit verification request
     */
    public function submitVerificationRequest(
        User $user,
        string $badgeType,
        string $natureDocument,
        UploadedFile $docRecto,
        UploadedFile $docVerso,
        UploadedFile $contratSigner
    ): array {
        // Check if user is an organizer
        if ($user->role !== 'organizer') {
            throw new \Exception('Only organizers can submit verification requests');
        }

        $organizerProfile = $user->organizerProfile;

        if (!$organizerProfile) {
            throw new \Exception('Organizer profile not found');
        }

        // Check if already has a pending request
        if ($organizerProfile->status === 'attente') {
            throw new \Exception('You already have a pending verification request');
        }

        // Load user's wallet
        $user->load('wallet');

        // Check if user has sufficient balance
        if (!$user->wallet || $user->wallet->balance < self::VERIFICATION_COST) {
            throw new \Exception('Insufficient balance. You need 200 MLM pieces to submit a verification request.');
        }

        DB::beginTransaction();
        try {
            // Store uploaded files
            $docRectoPath = $docRecto->store('organizers/documents', 'public');
            $docVersoPath = $docVerso->store('organizers/documents', 'public');
            $contratSignerPath = $contratSigner->store('organizers/contracts', 'public');

            // Deduct the cost from wallet
            $balanceBefore = $user->wallet->balance;
            $user->wallet->balance -= self::VERIFICATION_COST;
            $user->wallet->save();

            // Create transaction record
            DB::table('transactions')->insert([
                'wallet_id' => $user->wallet->id,
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => self::VERIFICATION_COST,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->wallet->balance,
                'reason' => 'admin_adjustment',
                'description' => 'Verification request for ' . $badgeType . ' badge',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update organizer profile with verification data
            $organizerProfile->update([
                'nature_document' => $natureDocument,
                'doc_recto' => $docRectoPath,
                'doc_verso' => $docVersoPath,
                'contrat_signer' => $contratSignerPath,
                'status' => 'attente',
            ]);

            DB::commit();

            return [
                'organizer_profile' => $organizerProfile,
                'requested_badge' => $badgeType,
                'transaction_amount' => self::VERIFICATION_COST,
                'new_balance' => $user->wallet->balance,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded files if transaction failed
            if (isset($docRectoPath)) Storage::disk('public')->delete($docRectoPath);
            if (isset($docVersoPath)) Storage::disk('public')->delete($docVersoPath);
            if (isset($contratSignerPath)) Storage::disk('public')->delete($contratSignerPath);

            throw $e;
        }
    }

    /**
     * Validate verification request
     */
    public function validateVerificationRequest(int $profileId, string $badge, User $moderator): array
    {
        $organizerProfile = OrganizerProfile::find($profileId);

        if (!$organizerProfile) {
            throw new \Exception('Organizer profile not found');
        }

        // Update profile with badge and status
        $organizerProfile->update([
            'badge' => $badge,
            'status' => 'valider',
            'processed_by_user_id' => $moderator->id,
            'rejection_reason' => null,
        ]);

        return [
            'organizer_profile' => $organizerProfile,
            'processed_by' => $moderator,
        ];
    }

    /**
     * Reject verification request with refund
     */
    public function rejectVerificationRequest(int $profileId, ?string $rejectionReason, User $moderator): array
    {
        $organizerProfile = OrganizerProfile::with('user.wallet')->find($profileId);

        if (!$organizerProfile) {
            throw new \Exception('Organizer profile not found');
        }

        DB::beginTransaction();
        try {
            // Refund the verification cost
            if ($organizerProfile->user && $organizerProfile->user->wallet) {
                $balanceBefore = $organizerProfile->user->wallet->balance;
                $organizerProfile->user->wallet->balance += self::VERIFICATION_COST;
                $organizerProfile->user->wallet->save();

                // Create refund transaction record
                DB::table('transactions')->insert([
                    'wallet_id' => $organizerProfile->user->wallet->id,
                    'user_id' => $organizerProfile->user->id,
                    'type' => 'credit',
                    'amount' => self::VERIFICATION_COST,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $organizerProfile->user->wallet->balance,
                    'reason' => 'refund',
                    'description' => 'Refund for rejected verification request',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Delete uploaded documents
            if ($organizerProfile->doc_recto) {
                Storage::disk('public')->delete($organizerProfile->doc_recto);
            }
            if ($organizerProfile->doc_verso) {
                Storage::disk('public')->delete($organizerProfile->doc_verso);
            }
            if ($organizerProfile->contrat_signer) {
                Storage::disk('public')->delete($organizerProfile->contrat_signer);
            }

            // Update status to rejected and clear documents
            $organizerProfile->update([
                'status' => 'rejeter',
                'rejection_reason' => $rejectionReason,
                'processed_by_user_id' => $moderator->id,
                'nature_document' => null,
                'doc_recto' => null,
                'doc_verso' => null,
                'contrat_signer' => null,
            ]);

            DB::commit();

            return [
                'organizer_profile' => $organizerProfile,
                'refund_amount' => self::VERIFICATION_COST,
                'new_balance' => $organizerProfile->user->wallet?->balance,
                'processed_by' => $moderator,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user's wallet info
     */
    public function getWalletInfo(User $user): array
    {
        $user->load('wallet');

        return [
            'balance' => $user->wallet?->balance ?? 0,
            'has_sufficient_for_upgrade' => ($user->wallet?->balance ?? 0) >= self::CERTIFIED_BADGE_COST,
            'has_sufficient_for_verification' => ($user->wallet?->balance ?? 0) >= self::VERIFICATION_COST,
        ];
    }
}
