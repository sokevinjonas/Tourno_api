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
}
