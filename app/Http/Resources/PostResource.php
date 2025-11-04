<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'content' => $this->content,
            'images' => $this->images ? array_map(fn ($image) => asset('storage/'.$image), $this->images) : null,
            'poll_options' => $this->poll_options,
            'poll_ends_at' => $this->poll_ends_at?->toISOString(),
            'hashtags' => $this->hashtags,
            'likes_count' => $this->likes_count ?? 0,
            'comments_count' => $this->comments_count ?? 0,
            'shares_count' => $this->shares_count ?? 0,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'is_liked' => $user ? $this->isLikedBy($user) : false,
            'is_bookmarked' => false, // TODO: Implement bookmark feature
            'has_voted' => $user && $this->type === 'poll' ? $this->hasUserVoted($user) : false,
            'is_poll_active' => $this->isPollActive(),
            'can_edit' => $user ? $this->canUserEdit($user) : false,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
