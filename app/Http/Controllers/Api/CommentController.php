<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\NotificationType;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends BaseApiController
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display comments for a post.
     */
    public function index(Request $request, Post $post): JsonResponse
    {
        $comments = Comment::where('post_id', $post->id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        // Transform using CommentResource to include can_edit field with request context
        $transformedData = $comments->getCollection()->map(fn ($comment) => (new CommentResource($comment))->toArray($request));

        return $this->paginatedResponse($comments, null, null, null, $transformedData);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validated['parent_id'] ?? null) {
            $parentComment = Comment::findOrFail($validated['parent_id']);
            if ($parentComment->post_id !== $post->id) {
                return $this->errorResponse('Invalid parent comment', null, 400);
            }
        }

        $user = $request->user();
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => true, // Auto-approve for now
        ]);

        $post->incrementComments();
        $comment->load('user');

        // Send notification to relevant users
        if ($validated['parent_id'] ?? null) {
            // Reply to comment
            $parentComment = Comment::find($validated['parent_id']);
            if ($parentComment && $parentComment->user_id !== $user->id) {
                $this->notificationService->send(
                    $parentComment->user,
                    NotificationType::COMMENT_REPLY,
                    [
                        'title' => 'New Reply',
                        'body' => $user->name.' replied to your comment',
                        'post_id' => $post->id,
                        'comment_id' => $comment->id,
                        'replier_id' => $user->id,
                        'replier_name' => $user->name,
                        'avatar' => $user->avatar,
                    ],
                    ['database', 'push']
                );
            }
        } elseif ($post->user_id !== $user->id) {
            // New comment on post
            $this->notificationService->send(
                $post->user,
                NotificationType::COMMENT_ADDED,
                [
                    'title' => 'New Comment',
                    'body' => $user->name.' commented on your post',
                    'post_id' => $post->id,
                    'comment_id' => $comment->id,
                    'commenter_id' => $user->id,
                    'commenter_name' => $user->name,
                    'avatar' => $user->avatar,
                ],
                ['database', 'push']
            );
        }

        return $this->successResponse(new CommentResource($comment), 'Comment added successfully', 201);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        if (! $comment->canUserEdit($request->user())) {
            return $this->forbiddenResponse('You do not have permission to edit this comment');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        return $this->successResponse(new CommentResource($comment->fresh('user')), 'Comment updated successfully');
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        if (! $comment->canUserEdit($request->user())) {
            return $this->forbiddenResponse('You do not have permission to delete this comment');
        }

        $post = $comment->post;
        $comment->delete();
        $post->decrement('comments_count');

        return $this->successResponse(null, 'Comment deleted successfully', 204);
    }
}
