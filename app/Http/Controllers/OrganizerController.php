<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tournament;
use App\Models\OrganizerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizerController extends Controller
{
    /**
     * Get all organizers with their profiles and statistics
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'organizer')
            ->with(['organizerProfile', 'followers'])
            ->withCount('followers');

        // Filter by featured
        if ($request->has('featured') && $request->boolean('featured')) {
            $query->whereHas('organizerProfile', function ($q) {
                $q->where('is_featured', true);
            });
        }

        // Filter by badge
        if ($request->has('badge')) {
            $query->whereHas('organizerProfile', function ($q) use ($request) {
                $q->where('badge', $request->badge);
            });
        }

        // Order by followers count
        if ($request->input('sort') === 'followers') {
            $query->orderBy('followers_count', 'desc');
        } else {
            $query->latest();
        }

        $organizers = $query->get()->map(function ($organizer) {
            $tournamentsCount = Tournament::where('organizer_id', $organizer->id)->count();

            return [
                'id' => $organizer->id,
                'name' => $organizer->organizerProfile?->display_name ?? $organizer->name,
                'badge' => $organizer->organizerProfile?->badge,
                'tournaments' => $tournamentsCount,
                'followers' => $organizer->followers_count,
                'avatar' => $organizer->organizerProfile?->avatar_url ?? $organizer->organizerProfile?->avatar_initial ?? strtoupper(substr($organizer->name, 0, 1)),
                'is_featured' => $organizer->organizerProfile?->is_featured ?? false,
                'bio' => $organizer->organizerProfile?->bio,
                'social_links' => $organizer->organizerProfile?->social_links,
            ];
        });

        return response()->json([
            'organizers' => $organizers,
            'total' => $organizers->count(),
        ], 200);
    }

    /**
     * Get a single organizer with detailed information
     */
    public function show(int $id): JsonResponse
    {
        $organizer = User::where('role', 'organizer')
            ->with(['organizerProfile', 'followers'])
            ->withCount('followers')
            ->find($id);

        if (!$organizer) {
            return response()->json([
                'message' => 'Organizer not found',
            ], 404);
        }

        $tournamentsCount = Tournament::where('organizer_id', $organizer->id)->count();
        $tournaments = Tournament::where('organizer_id', $organizer->id)
            ->withCount('registrations')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'organizer' => [
                'id' => $organizer->id,
                'name' => $organizer->organizerProfile?->display_name ?? $organizer->name,
                'email' => $organizer->email,
                'badge' => $organizer->organizerProfile?->badge,
                'tournaments' => $tournamentsCount,
                'followers' => $organizer->followers_count,
                'avatar' => $organizer->organizerProfile?->avatar_url ?? $organizer->organizerProfile?->avatar_initial ?? strtoupper(substr($organizer->name, 0, 1)),
                'is_featured' => $organizer->organizerProfile?->is_featured ?? false,
                'bio' => $organizer->organizerProfile?->bio,
                'social_links' => $organizer->organizerProfile?->social_links,
                'recent_tournaments' => $tournaments,
            ],
        ], 200);
    }

    /**
     * Follow/Unfollow an organizer
     */
    public function toggleFollow(Request $request, int $organizerId): JsonResponse
    {
        $user = $request->user();

        // Prevent user from following themselves
        if ($user->id === $organizerId) {
            return response()->json([
                'message' => 'You cannot follow yourself',
            ], 400);
        }

        $organizer = User::where('role', 'organizer')->find($organizerId);

        if (!$organizer) {
            return response()->json([
                'message' => 'Organizer not found',
            ], 404);
        }

        // Check if already following
        $isFollowing = DB::table('organizer_followers')
            ->where('user_id', $user->id)
            ->where('organizer_id', $organizerId)
            ->exists();

        if ($isFollowing) {
            // Unfollow
            DB::table('organizer_followers')
                ->where('user_id', $user->id)
                ->where('organizer_id', $organizerId)
                ->delete();

            return response()->json([
                'message' => 'Organizer unfollowed successfully',
                'is_following' => false,
                'followers_count' => $organizer->followers()->count(),
            ], 200);
        } else {
            // Follow
            DB::table('organizer_followers')->insert([
                'user_id' => $user->id,
                'organizer_id' => $organizerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Organizer followed successfully',
                'is_following' => true,
                'followers_count' => $organizer->followers()->count(),
            ], 200);
        }
    }

    /**
     * Check if current user is following an organizer
     */
    public function checkFollowing(Request $request, int $organizerId): JsonResponse
    {
        $user = $request->user();

        $isFollowing = DB::table('organizer_followers')
            ->where('user_id', $user->id)
            ->where('organizer_id', $organizerId)
            ->exists();

        return response()->json([
            'is_following' => $isFollowing,
        ], 200);
    }

    /**
     * Get user's following organizers
     */
    public function myFollowing(Request $request): JsonResponse
    {
        $user = $request->user();

        $following = $user->followingOrganizers()
            ->with(['organizerProfile'])
            ->withCount('followers')
            ->get()
            ->map(function ($organizer) {
                $tournamentsCount = Tournament::where('organizer_id', $organizer->id)->count();

                return [
                    'id' => $organizer->id,
                    'name' => $organizer->organizerProfile?->display_name ?? $organizer->name,
                    'badge' => $organizer->organizerProfile?->badge,
                    'tournaments' => $tournamentsCount,
                    'followers' => $organizer->followers_count,
                    'avatar' => $organizer->organizerProfile?->avatar_url ?? $organizer->organizerProfile?->avatar_initial ?? strtoupper(substr($organizer->name, 0, 1)),
                    'is_featured' => $organizer->organizerProfile?->is_featured ?? false,
                ];
            });

        return response()->json([
            'following' => $following,
            'total' => $following->count(),
        ], 200);
    }

    /**
     * Check if current user is an organizer
     */
    public function checkIfOrganizer(Request $request): JsonResponse
    {
        $user = $request->user();

        $response = [
            'is_organizer' => $user->role === 'organizer',
            'role' => $user->role,
            'badge' => null,
        ];

        // If user is an organizer, load their profile and get badge
        if ($user->role === 'organizer') {
            $user->load('organizerProfile');
            $response['badge'] = $user->organizerProfile?->badge;
        }

        return response()->json($response, 200);
    }

    /**
     * Upgrade current user to organizer
     */
    public function upgradeToOrganizer(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is already an organizer
        if ($user->role === 'organizer') {
            return response()->json([
                'message' => 'User is already an organizer',
            ], 400);
        }

        // Update user role
        $user->role = 'organizer';
        $user->save();

        // Create organizer profile
        $organizerProfile = OrganizerProfile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'avatar_initial' => strtoupper(substr($user->name, 0, 1)),
            'is_featured' => false,
        ]);

        return response()->json([
            'message' => 'User upgraded to organizer successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'organizer_profile' => [
                'id' => $organizerProfile->id,
                'display_name' => $organizerProfile->display_name,
                'avatar_initial' => $organizerProfile->avatar_initial,
                'is_featured' => $organizerProfile->is_featured,
            ],
        ], 200);
    }

    /**
     * Submit verification request for verified/partner badge
     */
    public function submitVerificationRequest(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is an organizer
        if ($user->role !== 'organizer') {
            return response()->json([
                'message' => 'Only organizers can submit verification requests',
            ], 403);
        }

        $validated = $request->validate([
            'badge_type' => 'required|in:verified,partner',
            'nature_document' => 'required|in:cnib,permis,passport',
            'doc_recto' => 'required|string',
            'doc_verso' => 'required|string',
            'contrat_signer' => 'required|string',
        ]);

        $organizerProfile = $user->organizerProfile;

        if (!$organizerProfile) {
            return response()->json([
                'message' => 'Organizer profile not found',
            ], 404);
        }

        // Check if already has a pending request
        if ($organizerProfile->status === 'attente') {
            return response()->json([
                'message' => 'You already have a pending verification request',
            ], 400);
        }

        // Update organizer profile with verification data
        $organizerProfile->update([
            'nature_document' => $validated['nature_document'],
            'doc_recto' => $validated['doc_recto'],
            'doc_verso' => $validated['doc_verso'],
            'contrat_signer' => $validated['contrat_signer'],
            'status' => 'attente',
        ]);

        return response()->json([
            'message' => 'Verification request submitted successfully',
            'verification' => [
                'nature_document' => $organizerProfile->nature_document,
                'status' => $organizerProfile->status,
                'requested_badge' => $validated['badge_type'],
            ],
        ], 200);
    }

    /**
     * Get all pending verification requests (Moderators only)
     */
    public function getPendingVerifications(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is moderator or admin
        if (!in_array($user->role, ['moderator', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Moderators only.',
            ], 403);
        }

        $pendingVerifications = OrganizerProfile::with(['user:id,name,email', 'processedBy:id,name,email'])
            ->where('status', 'attente')
            ->latest()
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'organizer' => [
                        'id' => $profile->user->id,
                        'name' => $profile->display_name ?? $profile->user->name,
                        'email' => $profile->user->email,
                    ],
                    'nature_document' => $profile->nature_document,
                    'doc_recto' => $profile->doc_recto,
                    'doc_verso' => $profile->doc_verso,
                    'contrat_signer' => $profile->contrat_signer,
                    'status' => $profile->status,
                    'rejection_reason' => $profile->rejection_reason,
                    'processed_by' => $profile->processedBy ? [
                        'id' => $profile->processedBy->id,
                        'name' => $profile->processedBy->name,
                    ] : null,
                    'submitted_at' => $profile->updated_at,
                ];
            });

        return response()->json([
            'verifications' => $pendingVerifications,
            'total' => $pendingVerifications->count(),
        ], 200);
    }

    /**
     * Validate verification request (Moderators only)
     */
    public function validateVerificationRequest(Request $request, int $profileId): JsonResponse
    {
        $user = $request->user();

        // Check if user is moderator or admin
        if (!in_array($user->role, ['moderator', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Moderators only.',
            ], 403);
        }

        $validated = $request->validate([
            'badge' => 'required|in:verified,partner',
        ]);

        $organizerProfile = OrganizerProfile::find($profileId);

        if (!$organizerProfile) {
            return response()->json([
                'message' => 'Organizer profile not found',
            ], 404);
        }

        // Update profile with badge and status
        $organizerProfile->update([
            'badge' => $validated['badge'],
            'status' => 'valider',
            'processed_by_user_id' => $user->id,
            'rejection_reason' => null, // Clear any previous rejection reason
        ]);

        return response()->json([
            'message' => 'Verification request validated successfully',
            'organizer_profile' => [
                'id' => $organizerProfile->id,
                'display_name' => $organizerProfile->display_name,
                'badge' => $organizerProfile->badge,
                'status' => $organizerProfile->status,
                'processed_by' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ],
        ], 200);
    }

    /**
     * Reject verification request (Moderators only)
     */
    public function rejectVerificationRequest(Request $request, int $profileId): JsonResponse
    {
        $user = $request->user();

        // Check if user is moderator or admin
        if (!in_array($user->role, ['moderator', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized. Moderators only.',
            ], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $organizerProfile = OrganizerProfile::find($profileId);

        if (!$organizerProfile) {
            return response()->json([
                'message' => 'Organizer profile not found',
            ], 404);
        }

        // Update status to rejected
        $organizerProfile->update([
            'status' => 'rejeter',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'processed_by_user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Verification request rejected',
            'rejection_reason' => $organizerProfile->rejection_reason,
            'processed_by' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ], 200);
    }
}
