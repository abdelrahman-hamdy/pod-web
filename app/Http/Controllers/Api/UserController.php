<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($users);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        if (! $user->is_active) {
            return $this->notFoundResponse();
        }

        // Load user with related data including posts, experiences, and portfolios
        $user->load([
            'posts' => function ($query) {
                $query->latest()->limit(50);
            },
            'experiences' => function ($query) {
                $query->orderBy('start_date', 'desc');
            },
            'portfolios'
        ]);

        // Increment profile views if viewing someone else's profile
        if (auth()->check() && auth()->id() !== $user->id) {
            $user->increment('profile_views');
        }

        return $this->successResponse(new UserResource($user));
    }

    /**
     * Search users.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
            'team_id' => 'nullable|integer',
        ]);

        $excludeUserIds = [auth()->id()];

        if ($validated['team_id'] ?? null) {
            $teamMemberIds = \App\Models\HackathonTeamMember::where('team_id', $validated['team_id'])
                ->pluck('user_id')
                ->toArray();
            $excludeUserIds = array_merge($excludeUserIds, $teamMemberIds);

            $pendingInvitationIds = \App\Models\HackathonTeamInvitation::where('team_id', $validated['team_id'])
                ->where('status', 'pending')
                ->pluck('invitee_id')
                ->toArray();
            $excludeUserIds = array_merge($excludeUserIds, $pendingInvitationIds);

            $teamLeaderId = \App\Models\HackathonTeam::where('id', $validated['team_id'])
                ->value('leader_id');
            if ($teamLeaderId) {
                $excludeUserIds[] = $teamLeaderId;
            }
        }

        $users = User::where('is_active', true)
            ->whereNotIn('id', array_unique($excludeUserIds))
            ->where(function ($q) use ($validated) {
                $q->where('name', 'like', "%{$validated['q']}%")
                    ->orWhere('email', 'like', "%{$validated['q']}%")
                    ->orWhere('first_name', 'like', "%{$validated['q']}%")
                    ->orWhere('last_name', 'like', "%{$validated['q']}%");
            })
            ->limit(20)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name ?: $user->email,
                'email' => $user->email,
                'role' => is_object($user->role) && method_exists($user->role, 'value') ? $user->role->value : $user->role,
                'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'avatar_color' => $user->avatar_color,
            ]);

        return $this->successResponse(['users' => $users]);
    }
    
    /**
     * Get user's posts.
     */
    public function posts(User $user, Request $request): JsonResponse
    {
        if (! $user->is_active) {
            return $this->notFoundResponse();
        }

        $posts = $user->posts()
            ->with(['user', 'comments'])
            ->latest()
            ->paginate(20);

        // Transform using PostResource to include is_liked, is_bookmarked, etc.
        $transformedData = $posts->getCollection()->map(fn ($post) => (new \App\Http\Resources\PostResource($post))->toArray($request));

        return $this->paginatedResponse($posts, null, null, null, $transformedData);
    }

    /**
     * Get user's experiences.
     */
    public function experiences(User $user): JsonResponse
    {
        if (! $user->is_active) {
            return $this->notFoundResponse();
        }

        $experiences = $user->experiences()
            ->orderBy('start_date', 'desc')
            ->get();

        return $this->successResponse(['experiences' => $experiences]);
    }

    /**
     * Get user's portfolios.
     */
    public function portfolios(User $user): JsonResponse
    {
        if (! $user->is_active) {
            return $this->notFoundResponse();
        }

        $portfolios = $user->portfolios()
            ->latest()
            ->get();

        return $this->successResponse(['portfolios' => $portfolios]);
    }
}
