<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends BaseApiController
{
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

        return $this->paginatedResponse($comments, null, null, $transformedData);
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

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => true, // Auto-approve for now
        ]);

        $post->incrementComments();
        $comment->load('user');

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
