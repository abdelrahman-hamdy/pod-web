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
     * Note: This method is not used - the route redirects to home.
     * Posts are displayed on the dashboard instead.
     */
    public function index(Request $request)
    {
        return redirect()->route('home');
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

        // Linkify URLs inside content to clickable anchors (no preview storage)
        if (! empty($validated['content'])) {
            $validated['content'] = $this->formatContent($validated['content']);
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

            // Ensure user role is visible for business badge in JavaScript
            if ($post->user) {
                $post->user->makeVisible('role');
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Post created successfully!',
                    'post' => $post,
                ]);
            }

            return redirect()->route('home')
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

        $rawContent = $this->toRawTextFromFormatted($post->content ?? '');

        return view('posts.edit', [
            'post' => $post,
            'rawContent' => $rawContent,
        ]);
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

        // Extract hashtags from content
        $hashtags = $this->extractHashtags($content);

        // Update the post (format on save)
        $post->content = $this->formatContent($content);
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

        return redirect()->route('home')
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
            'platform' => 'nullable|string|in:twitter,linkedin,facebook,instagram,whatsapp,email,telegram,copy',
        ]);

        if (Auth::check()) {
            \App\Models\PostShare::recordShare($post->id, Auth::id(), $validated['platform'] ?? null);
            $actualCount = $post->getActualSharesCount();
            $post->update(['shares_count' => $actualCount]);

            return response()->json([
                'success' => true,
                'shares_count' => $actualCount,
            ]);
        }

        // Guest: increment stored counter silently without creating a row
        $post->increment('shares_count');

        return response()->json([
            'success' => true,
            'shares_count' => $post->shares_count,
        ]);
    }

    /**
     * Increment shares and redirect to external share URL (GET, CSRF-less).
     */
    public function shareRedirect(Request $request, Post $post)
    {
        $validated = $request->validate([
            'platform' => 'nullable|string|in:twitter,linkedin,facebook,instagram,whatsapp,email,telegram',
            'url' => 'required|string',
        ]);

        if (Auth::check()) {
            \App\Models\PostShare::recordShare($post->id, Auth::id(), $validated['platform'] ?? null);
            $actualCount = $post->getActualSharesCount();
            $post->update(['shares_count' => $actualCount]);
        } else {
            $post->increment('shares_count');
        }

        return redirect()->away($validated['url']);
    }

    /**
     * Lightweight GET ping to count a share without redirecting (e.g., copy button).
     */
    public function sharePing(Request $request, Post $post)
    {
        $validated = $request->validate([
            'platform' => 'nullable|string|in:twitter,linkedin,facebook,instagram,whatsapp,email,telegram,copy',
        ]);

        if (Auth::check()) {
            \App\Models\PostShare::recordShare($post->id, Auth::id(), $validated['platform'] ?? null);
            $actualCount = $post->getActualSharesCount();
            $post->update(['shares_count' => $actualCount]);
        } else {
            $post->increment('shares_count');
        }

        return response()->noContent();
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

    private function linkifyUrls(string $text): string
    {
        $pattern = '/(?<![\"\'>])(https?:\/\/[^\s<]+)/i';
        return preg_replace_callback($pattern, function ($matches) {
            $url = $matches[1];
            $display = strlen($url) > 80 ? substr($url, 0, 77).'…' : $url;
            $safeUrl = e($url);
            $safeDisplay = e($display);
            return '<a href="'.$safeUrl.'" target="_blank" rel="nofollow noopener" class="text-indigo-600 hover:underline">'.$safeDisplay.'</a>';
        }, e($text));
    }

    private function highlightHashtags(string $html): string
    {
        // Replace hashtags only in text nodes (outside of HTML tags)
        return preg_replace_callback('/(^|>)([^<]+)(?=<|$)/', function ($m) {
            $prefix = $m[1];
            $text = $m[2];
            $replaced = preg_replace('/(^|\s)#(\w+)/', '$1<span class="text-indigo-600 font-semibold">#$2</span>', $text);
            return $prefix.$replaced;
        }, $html);
    }

    private function formatContent(string $raw): string
    {
        $withLinks = $this->linkifyUrls($raw);
        return $this->highlightHashtags($withLinks);
    }

    private function toRawTextFromFormatted(string $html): string
    {
        if ($html === '') {
            return '';
        }

        // Replace anchor tags with their href (fallback to inner text if no href)
        $intermediate = preg_replace_callback('/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is', function ($m) {
            $href = trim($m[1] ?? '');
            $text = trim(strip_tags($m[2] ?? ''));
            return $href !== '' ? $href : $text;
        }, $html);

        // Replace <br> with newlines
        $intermediate = preg_replace('/<br\s*\/?>(\r?\n)?/i', "\n", $intermediate);

        // Strip remaining tags and decode entities
        $stripped = strip_tags($intermediate);
        $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $decoded = preg_replace("/\r?\n\s*\r?\n+/", "\n\n", $decoded);

        return $decoded;
    }
}
