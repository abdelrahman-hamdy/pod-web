<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with posts and events.
     */
    public function index()
    {
        $userId = auth()->id();

        // Get initial posts (first 5) with like status
        $posts = Post::with(['user', 'likes' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->published()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($post) {
                $post->is_liked = $post->likes->isNotEmpty();

                return $post;
            });

        // Get upcoming events for the events section
        $events = Event::with('creator')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(3)
            ->get();

        return view('dashboard.index', compact('posts', 'events'));
    }

    /**
     * Load more posts for infinite scroll.
     */
    public function loadMorePosts(Request $request)
    {
        $offset = $request->get('offset', 0);
        $limit = 5;
        $userId = auth()->id();

        $posts = Post::with(['user', 'likes' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->published()
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($post) {
                $post->is_liked = $post->likes->isNotEmpty();

                return $post;
            });

        // Check if there are more posts (we got $limit items, if exactly limit, there might be more)
        $hasMore = $posts->count() === $limit;

        return response()->json([
            'posts' => $posts,
            'hasMore' => $hasMore,
            'nextOffset' => $offset + $limit,
        ]);
    }
}
