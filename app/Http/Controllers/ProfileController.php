<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get current user's profile
     */
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileService->getProfile($request->user());

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'profile' => $profile,
        ], 200);
    }

    /**
     * Create or update user profile
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'whatsapp_number' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $profile = $this->profileService->createOrUpdateProfile(
                $request->user(),
                $validator->validated()
            );

            return response()->json([
                'message' => 'Profile saved successfully',
                'profile' => $profile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all pending profiles (Moderators only)
     */
    public function pending(Request $request): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $profiles = $this->profileService->getPendingProfiles();

        return response()->json([
            'profiles' => $profiles,
        ], 200);
    }

    /**
     * Validate a profile (Moderators only)
     */
    public function validate(Request $request, int $profileId): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $profile = \App\Models\Profile::findOrFail($profileId);
            $validatedProfile = $this->profileService->validateProfile($profile, $request->user());

            return response()->json([
                'message' => 'Profile validated successfully',
                'profile' => $validatedProfile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to validate profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a profile (Moderators only)
     */
    public function reject(Request $request, int $profileId): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $profile = \App\Models\Profile::findOrFail($profileId);
            $rejectedProfile = $this->profileService->rejectProfile(
                $profile,
                $request->user(),
                $request->reason
            );

            return response()->json([
                'message' => 'Profile rejected',
                'profile' => $rejectedProfile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get profile statistics (Admins only)
     */
    public function statistics(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $stats = $this->profileService->getProfileStatistics();

        return response()->json([
            'statistics' => $stats,
        ], 200);
    }
}
