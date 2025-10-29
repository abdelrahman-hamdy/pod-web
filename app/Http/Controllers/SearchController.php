<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Hackathon;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across all platform content.
     */
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|string|in:all,posts,events,jobs,hackathons,users',
            'filters' => 'nullable|array',
        ]);

        $query = $request->q;
        $type = $request->type ?? 'all';
        $filters = $request->filters ?? [];

        $results = [];

        if ($type === 'all' || $type === 'posts') {
            $results['posts'] = $this->searchPosts($query, $filters);
        }

        if ($type === 'all' || $type === 'events') {
            $results['events'] = $this->searchEvents($query, $filters);
        }

        if ($type === 'all' || $type === 'jobs') {
            $results['jobs'] = $this->searchJobs($query, $filters);
        }

        if ($type === 'all' || $type === 'hackathons') {
            $results['hackathons'] = $this->searchHackathons($query, $filters);
        }

        if ($type === 'all' || $type === 'users') {
            $results['users'] = $this->searchUsers($query, $filters);
        }

        // Calculate total results count
        $totalResults = collect($results)->sum(function ($items) {
            return is_countable($items) ? count($items) : $items->count();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => $totalResults,
                'query' => $query,
                'type' => $type,
            ]);
        }

        return view('search.results', compact('results', 'query', 'type', 'totalResults'));
    }

    /**
     * Search posts.
     */
    private function searchPosts($query, $filters = [])
    {
        $search = Post::with(['user'])
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%")
                    ->orWhereJsonContains('hashtags', $query);
            });

        // Apply filters
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
     * Search events.
     */
    private function searchEvents($query, $filters = [])
    {
        $search = Event::with(['creator'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%");
            });

        // Apply filters
        if (isset($filters['event_type'])) {
            $search->where('type', $filters['event_type']);
        }

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
     * Search job listings.
     */
    private function searchJobs($query, $filters = [])
    {
        $search = JobListing::with(['category', 'poster'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('company_name', 'like', "%{$query}%")
                    ->orWhereJsonContains('required_skills', $query);
            });

        // Apply filters
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
     * Search hackathons.
     */
    private function searchHackathons($query, $filters = [])
    {
        $search = Hackathon::with(['creator'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('theme', 'like', "%{$query}%")
                    ->orWhereJsonContains('technologies', $query);
            });

        // Apply filters
        if (isset($filters['format'])) {
            $search->where('format', $filters['format']);
        }

        if (isset($filters['entry_fee'])) {
            if ($filters['entry_fee'] === 'free') {
                $search->where('entry_fee', 0);
            } else {
                $search->where('entry_fee', '>', 0);
            }
        }

        if (isset($filters['date_from'])) {
            $search->where('start_date', '>=', $filters['date_from']);
        }

        return $search->orderBy('start_date')->limit(20)->get();
    }

    /**
     * Search users.
     */
    private function searchUsers($query, $filters = [])
    {
        $search = User::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('bio', 'like', "%{$query}%")
                    ->orWhereJsonContains('skills', $query);
            });

        // Apply filters
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

    /**
     * Live search for posts and users only (main search feature).
     */
    public function liveSearch(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 3) {
            return response()->json([
                'success' => true,
                'posts' => [],
                'users' => [],
                'total' => 0,
            ]);
        }

        $posts = Post::with(['user'])
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%")
                    ->orWhereJsonContains('hashtags', $query);
            })
            ->latest()
            ->limit(5)
            ->get();

        $users = User::where('is_active', true)
            ->where('id', '!=', auth()->id()) // Exclude current user
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->makeVisible(['avatar', 'avatar_color']);

        return response()->json([
            'success' => true,
            'posts' => $posts,
            'users' => $users,
            'total' => $posts->count() + $users->count(),
        ]);
    }

    /**
     * Search results page for posts and users.
     */
    public function searchResults(Request $request)
    {
        $query = $request->get('q', '');

        $posts = collect();
        $users = collect();

        if (strlen($query) >= 3) {
            $posts = Post::with(['user', 'likes' => function ($q) {
                $q->where('user_id', auth()->id());
            }])
                ->published()
                ->where(function ($q) use ($query) {
                    $q->where('content', 'like', "%{$query}%")
                        ->orWhereJsonContains('hashtags', $query);
                })
                ->latest()
                ->get()
                ->map(function ($post) {
                    $post->is_liked = $post->likes->isNotEmpty();

                    return $post;
                });

            $users = User::where('is_active', true)
                ->where('id', '!=', auth()->id())
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                })
                ->get();
        }

        return view('search.results', compact('posts', 'users', 'query'));
    }

    /**
     * Get search suggestions as user types.
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:50',
            'type' => 'nullable|string|in:all,posts,events,jobs,hackathons,users',
        ]);

        $query = $request->q;
        $type = $request->type ?? 'all';
        $suggestions = [];

        try {
            if ($type === 'all' || $type === 'posts') {
                $postSuggestions = Post::published()
                    ->where('content', 'like', "%{$query}%")
                    ->selectRaw('SUBSTRING(content, 1, 100) as suggestion')
                    ->limit(3)
                    ->pluck('suggestion')
                    ->map(function ($item) {
                        return ['type' => 'post', 'text' => $item];
                    });
                $suggestions = array_merge($suggestions, $postSuggestions->toArray());
            }

            if ($type === 'all' || $type === 'events') {
                $eventSuggestions = Event::active()
                    ->where('title', 'like', "%{$query}%")
                    ->limit(3)
                    ->pluck('title')
                    ->map(function ($item) {
                        return ['type' => 'event', 'text' => $item];
                    });
                $suggestions = array_merge($suggestions, $eventSuggestions->toArray());
            }

            if ($type === 'all' || $type === 'jobs') {
                $jobSuggestions = JobListing::active()
                    ->where('title', 'like', "%{$query}%")
                    ->limit(3)
                    ->pluck('title')
                    ->map(function ($item) {
                        return ['type' => 'job', 'text' => $item];
                    });
                $suggestions = array_merge($suggestions, $jobSuggestions->toArray());
            }

            if ($type === 'all' || $type === 'users') {
                $userSuggestions = User::where('is_active', true)
                    ->where('name', 'like', "%{$query}%")
                    ->limit(3)
                    ->pluck('name')
                    ->map(function ($item) {
                        return ['type' => 'user', 'text' => $item];
                    });
                $suggestions = array_merge($suggestions, $userSuggestions->toArray());
            }

            // Limit total suggestions
            $suggestions = array_slice($suggestions, 0, 10);

        } catch (\Exception $e) {
            // Return empty suggestions on error
            $suggestions = [];
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Advanced search with more complex filters.
     */
    public function advanced(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'type' => 'required|string|in:posts,events,jobs,hackathons,users',
            'filters' => 'required|array',
        ]);

        $query = $request->q;
        $type = $request->type;
        $filters = $request->filters;

        $results = [];

        switch ($type) {
            case 'posts':
                $results = $this->advancedSearchPosts($query, $filters);
                break;
            case 'events':
                $results = $this->advancedSearchEvents($query, $filters);
                break;
            case 'jobs':
                $results = $this->advancedSearchJobs($query, $filters);
                break;
            case 'hackathons':
                $results = $this->advancedSearchHackathons($query, $filters);
                break;
            case 'users':
                $results = $this->advancedSearchUsers($query, $filters);
                break;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => $results->count(),
                'query' => $query,
                'type' => $type,
            ]);
        }

        return view('search.advanced', compact('results', 'query', 'type'));
    }

    /**
     * Advanced post search with complex filters.
     */
    private function advancedSearchPosts($query, $filters)
    {
        $search = Post::with(['user', 'comments'])
            ->published();

        if ($query) {
            $search->where('content', 'like', "%{$query}%");
        }

        // Advanced filters
        if (isset($filters['author'])) {
            $search->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['author']}%");
            });
        }

        if (isset($filters['has_images'])) {
            $search->whereNotNull('images');
        }

        if (isset($filters['min_likes'])) {
            $search->where('likes_count', '>=', $filters['min_likes']);
        }

        if (isset($filters['hashtags'])) {
            foreach ($filters['hashtags'] as $hashtag) {
                $search->whereJsonContains('hashtags', $hashtag);
            }
        }

        return $search->latest()->paginate(20);
    }

    /**
     * Advanced events search.
     */
    private function advancedSearchEvents($query, $filters)
    {
        $search = Event::with(['creator', 'registrations'])
            ->active();

        if ($query) {
            $search->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Advanced filters
        if (isset($filters['has_availability'])) {
            $search->whereRaw('max_attendees IS NULL OR max_attendees > (SELECT COUNT(*) FROM event_registrations WHERE event_id = events.id AND status = "confirmed")');
        }

        if (isset($filters['price_range'])) {
            switch ($filters['price_range']) {
                case 'free':
                    $search->where('entry_fee', 0);
                    break;
                case 'paid':
                    $search->where('entry_fee', '>', 0);
                    break;
            }
        }

        return $search->orderBy('start_date')->paginate(20);
    }

    /**
     * Advanced jobs search.
     */
    private function advancedSearchJobs($query, $filters)
    {
        $search = JobListing::with(['category', 'poster', 'applications'])
            ->active();

        if ($query) {
            $search->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('company_name', 'like', "%{$query}%");
            });
        }

        // Advanced filters
        if (isset($filters['skills'])) {
            foreach ($filters['skills'] as $skill) {
                $search->whereJsonContains('required_skills', $skill);
            }
        }

        if (isset($filters['application_count'])) {
            switch ($filters['application_count']) {
                case 'low':
                    $search->has('applications', '<', 10);
                    break;
                case 'medium':
                    $search->has('applications', '>=', 10)->has('applications', '<', 50);
                    break;
                case 'high':
                    $search->has('applications', '>=', 50);
                    break;
            }
        }

        return $search->latest()->paginate(20);
    }

    /**
     * Advanced hackathons search.
     */
    private function advancedSearchHackathons($query, $filters)
    {
        $search = Hackathon::with(['creator', 'teams'])
            ->active();

        if ($query) {
            $search->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('theme', 'like', "%{$query}%");
            });
        }

        // Advanced filters
        if (isset($filters['technologies'])) {
            foreach ($filters['technologies'] as $tech) {
                $search->whereJsonContains('technologies', $tech);
            }
        }

        if (isset($filters['team_size'])) {
            $search->where('max_team_size', '>=', $filters['team_size']);
        }

        return $search->orderBy('start_date')->paginate(20);
    }

    /**
     * Advanced users search.
     */
    private function advancedSearchUsers($query, $filters)
    {
        $search = User::where('is_active', true);

        if ($query) {
            $search->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('bio', 'like', "%{$query}%");
            });
        }

        // Advanced filters
        if (isset($filters['skills'])) {
            foreach ($filters['skills'] as $skill) {
                $search->whereJsonContains('skills', $skill);
            }
        }

        if (isset($filters['completed_profile'])) {
            $search->where('profile_completed', true);
        }

        return $search->latest()->paginate(20);
    }
}
