<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HackathonResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'registration_deadline' => $this->registration_deadline?->toISOString(),
            'location' => $this->location,
            'format' => $this->format?->value,
            'max_participants' => $this->max_participants,
            'max_team_size' => $this->max_team_size,
            'min_team_size' => $this->min_team_size,
            'skill_level' => $this->skill_requirements?->value,
            'technologies' => $this->technologies,
            'rules' => $this->rules,
            'image' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'is_active' => $this->is_active,
            'is_registered' => $user ? $this->teams()->where('leader_id', $user->id)->orWhereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->exists() : false,
            'can_edit' => $user ? \Illuminate\Support\Facades\Gate::allows('update', $this->resource) : false,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
