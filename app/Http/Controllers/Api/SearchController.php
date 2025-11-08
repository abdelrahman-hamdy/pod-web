<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EventResource;
use App\Http\Resources\HackathonResource;
use App\Http\Resources\JobListingResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Event;
use App\Models\Hackathon;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    /**
     * Global search across all platform content.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|string|in:all,posts,events,jobs,hackathons,users',
            'filters' => 'nullable|array',
        ]);

        $query = $validated['q'];
        $type = $validated['type'] ?? 'all';
        $filters = $validated['filters'] ?? [];

        $results = [];

        if ($type === 'all' || $type === 'posts') {
            $results['posts'] = PostResource::collection($this->searchPosts($query, $filters));
        }

        if ($type === 'all' || $type === 'events') {
            $results['events'] = EventResource::collection($this->searchEvents($query, $filters));
        }

        if ($type === 'all' || $type === 'jobs') {
            $results['jobs'] = JobListingResource::collection($this->searchJobs($query, $filters));
        }

        if ($type === 'all' || $type === 'hackathons') {
            $results['hackathons'] = HackathonResource::collection($this->searchHackathons($query, $filters));
        }

        if ($type === 'all' || $type === 'users') {
            $results['users'] = UserResource::collection($this->searchUsers($query, $filters));
        }

        return $this->successResponse([
            'results' => $results,
            'query' => $query,
            'type' => $type,
        ]);
    }

    /**
     * Search posts.
     */
    public function posts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'filters' => 'nullable|array',
        ]);

        $posts = $this->searchPosts($validated['q'], $validated['filters'] ?? []);

        return $this->successResponse(PostResource::collection($posts));
    }

    /**
     * Search events.
     */
    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'filters' => 'nullable|array',
        ]);

        $events = $this->searchEvents($validated['q'], $validated['filters'] ?? []);

        return $this->successResponse(EventResource::collection($events));
    }

    /**
     * Search jobs.
     */
    public function jobs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'filters' => 'nullable|array',
        ]);

        $jobs = $this->searchJobs($validated['q'], $validated['filters'] ?? []);

        return $this->successResponse(JobListingResource::collection($jobs));
    }

    /**
     * Search hackathons.
     */
    public function hackathons(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'filters' => 'nullable|array',
        ]);

        $hackathons = $this->searchHackathons($validated['q'], $validated['filters'] ?? []);

        return $this->successResponse(HackathonResource::collection($hackathons));
    }

    /**
     * Search users.
     */
    public function users(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'filters' => 'nullable|array',
        ]);

        $users = $this->searchUsers($validated['q'], $validated['filters'] ?? []);

        return $this->successResponse(UserResource::collection($users));
    }

    /**
     * Search posts helper.
     */
    private function searchPosts(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $search = Post::with(['user'])
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%")
                    ->orWhereJsonContains('hashtags', $query);
            });

        if (isset($filters['post_type'])) {
            $search->where('type', $filters['post_type']);
        }

        if (isset($filters['date_from'])) {
            $search->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $search->where('created_at', '<=', $filters['date_to']);
        }

        return $search->latest()->limit(20)->get();
    }

    /**
     * Search events helper.
     */
    private function searchEvents(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $search = Event::with(['creator'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            });

        if (isset($filters['location'])) {
            $search->where('location', 'like', "%{$filters['location']}%");
        }

        if (isset($filters['date_from'])) {
            $search->where('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $search->where('end_date', '<=', $filters['date_to']);
        }

        return $search->orderBy('start_date')->limit(20)->get();
    }

    /**
     * Search jobs helper.
     */
    private function searchJobs(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $search = JobListing::with(['category', 'poster'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('company_name', 'like', "%{$query}%")
                    ->orWhereJsonContains('required_skills', $query);
            });

        if (isset($filters['experience_level'])) {
            $search->where('experience_level', $filters['experience_level']);
        }

        if (isset($filters['location_type'])) {
            $search->where('location_type', $filters['location_type']);
        }

        if (isset($filters['salary_min'])) {
            $search->where('salary_min', '>=', $filters['salary_min']);
        }

        if (isset($filters['category_id'])) {
            $search->where('category_id', $filters['category_id']);
        }

        return $search->latest()->limit(20)->get();
    }

    /**
     * Search hackathons helper.
     */
    private function searchHackathons(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $search = Hackathon::with(['creator'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereJsonContains('technologies', $query);
            });

        if (isset($filters['format'])) {
            $search->where('format', $filters['format']);
        }

        if (isset($filters['entry_fee']) && $filters['entry_fee'] === 'free') {
            $search->where('entry_fee', 0);
        }

        if (isset($filters['date_from'])) {
            $search->where('start_date', '>=', $filters['date_from']);
        }

        return $search->orderBy('start_date')->limit(20)->get();
    }

    /**
     * Search users helper.
     */
    private function searchUsers(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $search = User::where('is_active', true)
            ->where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('bio', 'like', "%{$query}%")
                    ->orWhereJsonContains('skills', $query);
            });

        if (isset($filters['experience_level'])) {
            $search->where('experience_level', $filters['experience_level']);
        }

        if (isset($filters['city'])) {
            $search->where('city', 'like', "%{$filters['city']}%");
        }

        if (isset($filters['country'])) {
            $search->where('country', 'like', "%{$filters['country']}%");
        }

        return $search->latest()->limit(20)->get();
    }
}
