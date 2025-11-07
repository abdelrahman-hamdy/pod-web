<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends BaseApiController
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['user'])
            ->published()
            ->latest();

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $posts = $query->paginate($request->get('per_page', 15));

        // Transform using PostResource to include is_liked, is_bookmarked, etc.
        $transformedData = $posts->getCollection()->map(fn ($post) => (new PostResource($post))->toArray($request));

        return $this->paginatedResponse($posts, null, null, null, $transformedData);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'nullable|in:text,image,poll',
            'content' => 'nullable|string|max:5000',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'poll_options' => 'nullable|array|min:2|max:5',
            'poll_options.*' => 'string|max:255',
            'poll_ends_at' => 'nullable|date|after:now',
            'hashtags' => 'nullable|array|max:10',
            'hashtags.*' => 'string|max:50',
            'is_published' => 'nullable|boolean',
        ]);

        if (empty($validated['content']) && ! $request->hasFile('images')) {
            return $this->validationErrorResponse([
                'content' => ['Either content or images are required.'],
            ]);
        }

        $validated['user_id'] = $request->user()->id;
        $validated['type'] = $validated['type'] ?? 'text';
        $validated['is_published'] = $validated['is_published'] ?? true;

        // Extract hashtags from content
        if (! empty($validated['content'])) {
            $hashtags = $this->extractHashtags($validated['content']);
            $validated['hashtags'] = $hashtags;
        }

        // Handle image upload
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
            $validated['type'] = 'image';
        }

        // Format content
        if (! empty($validated['content'])) {
            $validated['content'] = $this->formatContent($validated['content']);
        }

        // Initialize poll options
        if ($validated['type'] === 'poll' && isset($validated['poll_options'])) {
            $pollOptions = [];
            foreach ($validated['poll_options'] as $option) {
                $pollOptions[] = [
                    'text' => $option,
                    'votes' => 0,
                ];
            }
            $validated['poll_options'] = $pollOptions;
        }

        $post = Post::create($validated);
        $post->load('user');

        return $this->successResponse(new PostResource($post), 'Post created successfully', 201);
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): JsonResponse
    {
        if (! $post->is_published && $post->user_id !== auth()->id()) {
            return $this->notFoundResponse();
        }

        $post->load(['user']);

        return $this->successResponse(new PostResource($post));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        if (! $post->canUserEdit($request->user())) {
            return $this->forbiddenResponse('You do not have permission to edit this post');
        }

        $validated = $request->validate([
            'content' => 'nullable|string|max:5000',
            'is_published' => 'nullable|boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'string',
        ]);

        // Handle removed images
        if (! empty($validated['removed_images'])) {
            $currentImages = $post->images ?? [];
            foreach ($validated['removed_images'] as $removedImage) {
                // Remove from storage
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($removedImage)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($removedImage);
                }
                // Remove from array
                $currentImages = array_values(array_filter($currentImages, fn ($img) => $img !== $removedImage));
            }
            $validated['images'] = $currentImages;
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $currentImages = $post->images ?? [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $currentImages[] = $path;
            }
            $validated['images'] = $currentImages;
            
            // Update type to image if images exist
            if (! empty($currentImages)) {
                $validated['type'] = 'image';
            }
        }

        // Update content formatting
        if (! empty($validated['content'])) {
            $validated['content'] = $this->formatContent($validated['content']);
        }

        // Remove helper fields from update
        unset($validated['removed_images']);

        $post->update($validated);

        return $this->successResponse(new PostResource($post->fresh(['user'])), 'Post updated successfully');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        if (! $post->canUserEdit($request->user())) {
            return $this->forbiddenResponse('You do not have permission to delete this post');
        }

        $post->delete();

        return $this->successResponse(null, 'Post deleted successfully', 204);
    }

    /**
     * Toggle like on a post.
     */
    public function toggleLike(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $like = \App\Models\PostLike::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');

            return $this->successResponse([
                'liked' => false,
                'likes_count' => $post->fresh()->likes_count,
            ], 'Post unliked');
        }

        \App\Models\PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
        $post->increment('likes_count');

        return $this->successResponse([
            'liked' => true,
            'likes_count' => $post->fresh()->likes_count,
        ], 'Post liked');
    }

    /**
     * Share a post.
     */
    public function share(Request $request, Post $post): JsonResponse
    {
        \App\Models\PostShare::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'platform' => $request->get('platform'),
            'shared_at' => now(),
        ]);

        $post->increment('shares_count');

        return $this->successResponse([
            'shares_count' => $post->fresh()->shares_count,
        ], 'Post shared successfully');
    }

    /**
     * Vote on a poll.
     */
    public function vote(Request $request, Post $post): JsonResponse
    {
        if ($post->type !== 'poll') {
            return $this->errorResponse('This post is not a poll', null, 400);
        }

        if (! $post->isPollActive()) {
            return $this->errorResponse('This poll has ended', null, 400);
        }

        $validated = $request->validate([
            'option_index' => 'required|integer|min:0',
        ]);

        if (! isset($post->poll_options[$validated['option_index']])) {
            return $this->validationErrorResponse([
                'option_index' => ['Invalid poll option'],
            ]);
        }

        // Check if user already voted
        $existingVote = \App\Models\PollVote::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingVote) {
            return $this->errorResponse('You have already voted on this poll', null, 400);
        }

        // Create vote
        \App\Models\PollVote::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'option_index' => $validated['option_index'],
            'voted_at' => now(),
        ]);

        // Update poll option votes
        $pollOptions = $post->poll_options;
        $pollOptions[$validated['option_index']]['votes']++;
        $post->update(['poll_options' => $pollOptions]);

        return $this->successResponse(null, 'Vote recorded successfully');
    }

    /**
     * Extract hashtags from content.
     */
    private function extractHashtags(string $content): array
    {
        preg_match_all('/#(\w+)/', $content, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Format content with linkified URLs.
     */
    private function formatContent(string $content): string
    {
        $pattern = '/(https?:\/\/[^\s]+)/';
        $replacement = '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>';

        return preg_replace($pattern, $replacement, $content);
    }
}
