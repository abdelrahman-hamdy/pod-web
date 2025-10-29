<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with(['user'])
            ->published()
            ->latest();

        // Apply filters
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('featured')) {
            $query->featured();
        }

        // Filter by hashtag
        if ($request->filled('hashtag')) {
            $query->whereJsonContains('hashtags', $request->hashtag);
        }

        $posts = $query->paginate(15);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:text,image,url,poll',
            'content' => 'nullable|string|max:5000',
            'url' => 'nullable|url|max:255',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'poll_options' => 'nullable|array|min:2|max:5',
            'poll_options.*' => 'string|max:255',
            'poll_ends_at' => 'nullable|date|after:now',
            'hashtags' => 'nullable|array|max:10',
            'hashtags.*' => 'string|max:50',
            'is_published' => 'nullable|boolean',
        ]);

        // Custom validation: require either content or images
        if (empty($validated['content']) && ! $request->hasFile('images')) {
            return response()->json([
                'success' => false,
                'message' => 'Either content or images are required.',
                'errors' => ['content' => ['Either content or images are required.']],
            ], 422);
        }

        $validated['user_id'] = Auth::id();
        $validated['type'] = $validated['type'] ?? 'text';
        $validated['is_published'] = $validated['is_published'] ?? true;

        // Extract hashtags from content and remove them from content
        if (! empty($validated['content'])) {
            $hashtags = $this->extractHashtags($validated['content']);
            $validated['hashtags'] = $hashtags;

            // Remove hashtags from content to avoid duplication
            $validated['content'] = $this->removeHashtagsFromContent($validated['content']);
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

        // Handle URL metadata (you might want to implement URL preview fetching)
        if ($validated['type'] === 'url') {
            // This is a placeholder - you would implement URL metadata fetching here
            $validated['url_title'] = $validated['url'];
        }

        // Initialize poll options with vote counts
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

        try {
            $post = Post::create($validated);
            $post->load('user');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post created successfully!',
                    'post' => $post,
                ]);
            }

            return redirect()->route('posts.index')
                ->with('success', 'Post created successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating post: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error creating post: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        // Only show published posts or own posts
        if (! $post->is_published && $post->user_id !== Auth::id()) {
            abort(404);
        }

        $userId = Auth::id();

        $post->load([
            'user',
            'comments' => function ($query) {
                $query->whereNull('parent_id')->with('user')->latest();
            },
            'likes' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
        ]);

        // Calculate if the current user liked this post
        $post->is_liked = $post->likes->isNotEmpty();

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        if (! $post->canUserEdit(Auth::user())) {
            abort(403, 'Unauthorized to edit this post.');
        }

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        if (! $post->canUserEdit(Auth::user())) {
            abort(403, 'Unauthorized to edit this post.');
        }

        \Log::info('Update POST received', [
            'post_id' => $post->id,
            'content' => $request->input('content', ''),
            'has_images_field' => $request->has('images'),
            'images_count' => count($request->file('images') ?? []),
            'has_remove_images' => $request->has('remove_images'),
            'remove_images' => $request->input('remove_images', []),
        ]);

        // Validate input
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
        ]);

        // Get content
        $content = $request->input('content', '');

        // Start with existing images
        $currentImages = $post->images ? (array) $post->images : [];

        \Log::info('Post Update Started', [
            'post_id' => $post->id,
            'current_images' => $currentImages,
            'current_images_count' => count($currentImages),
            'current_images_type' => gettype($post->images),
            'new_content' => substr($content, 0, 50),
            'has_remove_images' => $request->filled('remove_images'),
            'has_new_images' => $request->hasFile('images'),
        ]);

        // Handle image removal
        if ($request->filled('remove_images')) {
            $imagesToRemove = array_filter($request->remove_images, function ($value) {
                return ! empty($value);
            });

            \Log::info('Images to remove', ['count' => count($imagesToRemove), 'images' => $imagesToRemove]);

            if (! empty($imagesToRemove)) {
                // Delete removed image files and keep the rest
                foreach ($currentImages as $key => $image) {
                    if (in_array($image, $imagesToRemove)) {
                        // Delete the image file from storage
                        Storage::disk('public')->delete($image);
                        // Remove from array
                        unset($currentImages[$key]);
                    }
                }
                // Re-index array
                $currentImages = array_values($currentImages);
            }
        }

        // Handle new image upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $currentImages[] = $path;
            }
            \Log::info('Images uploaded', ['new_count' => count($currentImages)]);
        }

        // Validation: require either content or images
        $hasContent = ! empty($content) && trim($content) !== '';
        $hasImages = ! empty($currentImages);

        \Log::info('Validation check', [
            'hasContent' => $hasContent,
            'hasImages' => $hasImages,
            'content_length' => strlen($content),
        ]);

        if (! $hasContent && ! $hasImages) {
            \Log::warning('Validation failed - no content and no images');

            return back()->withErrors(['content' => 'Post must have either content or at least one image.']);
        }

        // Extract hashtags from content and remove them from content
        $hashtags = $this->extractHashtags($content);
        $contentWithoutHashtags = $this->removeHashtagsFromContent($content);

        // Update the post
        $post->content = $contentWithoutHashtags;
        $post->images = $currentImages;
        $post->hashtags = $hashtags;

        \Log::info('Before save - post attributes', [
            'images' => $post->images,
            'images_json' => json_encode($post->images),
            'images_type' => gettype($post->images),
        ]);

        $saved = $post->save();

        \Log::info('After save - checking database', [
            'saved' => $saved,
            'post_images_attr' => $post->images,
            'post_raw_images' => $post->getRawOriginal('images') ?? 'null',
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if (! $post->canUserEdit(Auth::user())) {
            abort(403, 'Unauthorized to delete this post.');
        }

        // Delete associated images
        if ($post->images) {
            foreach ($post->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Like/unlike a post.
     */
    public function toggleLike(Post $post)
    {
        $userId = Auth::id();
        $liked = \App\Models\PostLike::toggleLike($post->id, $userId);

        // Update post likes count
        $actualCount = $post->getActualLikesCount();
        $post->update(['likes_count' => $actualCount]);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $actualCount,
        ]);
    }

    /**
     * Share a post.
     */
    public function share(Request $request, Post $post)
    {
        $validated = $request->validate([
            'platform' => 'nullable|string|in:twitter,linkedin,facebook,instagram,whatsapp',
        ]);

        \App\Models\PostShare::recordShare($post->id, Auth::id(), $validated['platform'] ?? null);

        // Update post shares count
        $actualCount = $post->getActualSharesCount();
        $post->update(['shares_count' => $actualCount]);

        return response()->json([
            'success' => true,
            'shares_count' => $actualCount,
        ]);
    }

    /**
     * Vote on a poll post.
     */
    public function vote(Request $request, Post $post)
    {
        if ($post->type !== 'poll' || ! $post->isPollActive()) {
            return response()->json(['error' => 'Poll is not active'], 400);
        }

        $validated = $request->validate([
            'option_index' => 'required|integer|min:0',
        ]);

        $pollOptions = $post->poll_options;
        if (! isset($pollOptions[$validated['option_index']])) {
            return response()->json(['error' => 'Invalid option'], 400);
        }

        // Record the vote
        $voted = \App\Models\PollVote::recordVote($post->id, Auth::id(), $validated['option_index']);

        if (! $voted) {
            return response()->json(['error' => 'You have already voted on this poll'], 400);
        }

        // Update poll options with actual vote counts
        $voteCounts = \App\Models\PollVote::getVoteCounts($post->id);
        foreach ($pollOptions as $index => &$option) {
            $option['votes'] = $voteCounts[$index] ?? 0;
        }

        $post->update(['poll_options' => $pollOptions]);

        return response()->json([
            'success' => true,
            'poll_options' => $post->fresh()->poll_options,
            'user_voted' => true,
        ]);
    }

    /**
     * Extract hashtags from content text.
     */
    private function extractHashtags(string $content): array
    {
        // Match hashtags (word characters after #)
        preg_match_all('/#(\w+)/', $content, $matches);

        // Get unique hashtags, limit to 10
        $hashtags = array_unique($matches[1] ?? []);
        $hashtags = array_slice($hashtags, 0, 10);

        return array_values($hashtags);
    }

    /**
     * Remove hashtags from content text.
     */
    private function removeHashtagsFromContent(string $content): string
    {
        // Remove hashtags from content (keep the text, remove the # and word)
        return preg_replace('/#\w+\s*/', '', $content);
    }
}
