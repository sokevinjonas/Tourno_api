<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users with optional filters (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['profile', 'wallet']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by profile status
        if ($request->has('profile_status')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('status', $request->profile_status);
            });
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by country
        if ($request->has('country')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 20);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }

    /**
     * Get user details by UUID (Admin and Moderator)
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $user->load([
            'profile',
            'wallet',
            'gameAccounts',
            'tournaments',
            'registrations.tournament',
        ]);

        return response()->json([
            'user' => $user,
        ], 200);
    }

    /**
     * Update user role (Admin only)
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized: Only admins can update user roles',
            ], 403);
        }

        $request->validate([
            'role' => 'required|string|in:admin,moderator,organizer,player',
        ]);

        // Prevent self-demotion
        if ($user->id === $request->user()->id && $request->role !== 'admin') {
            return response()->json([
                'message' => 'Cannot change your own admin role',
            ], 422);
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user->fresh(['profile', 'wallet']),
        ], 200);
    }

    /**
     * Get user statistics (Admin only)
     */
    public function statistics(Request $request): JsonResponse
    {

        $stats = [
            'total_users' => User::count(),
            'by_role' => [
                'admins' => User::where('role', 'admin')->count(),
                'moderators' => User::where('role', 'moderator')->count(),
                'organizers' => User::where('role', 'organizer')->count(),
                'players' => User::where('role', 'player')->count(),
            ],
            'profiles' => [
                'pending' => User::whereHas('profile', function ($q) {
                    $q->where('status', 'pending');
                })->count(),
                'validated' => User::whereHas('profile', function ($q) {
                    $q->where('status', 'validated');
                })->count(),
                'rejected' => User::whereHas('profile', function ($q) {
                    $q->where('status', 'rejected');
                })->count(),
            ],
            'recent_signups' => [
                'today' => User::whereDate('created_at', today())->count(),
                'this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
                'this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
        ];

        return response()->json([
            'statistics' => $stats,
        ], 200);
    }
}
