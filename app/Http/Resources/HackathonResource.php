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
        $userTeam = null;
        
        if ($user) {
            $userTeam = $this->teams()
                ->where(function ($q) use ($user) {
                    $q->where('leader_id', $user->id)
                      ->orWhereHas('members', function ($query) use ($user) {
                          $query->where('user_id', $user->id);
                      });
                })
                ->with(['leader', 'members.user'])
                ->first();
        }

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
            'prizes' => $this->prizes,
            'rules' => $this->rules,
            'resources' => $this->resources,
            'cover_image' => $this->cover_image,
            'banner_image' => $this->cover_image,
            'image' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'image_url' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'is_active' => $this->is_active,
            'is_registered' => $user ? ($userTeam !== null) : false,
            'user_team' => $userTeam ? new HackathonTeamResource($userTeam) : null,
            'teams_count' => $this->teams()->count(),
            'status' => $this->getStatus(),
            'has_started' => $this->hasStarted(),
            'has_ended' => $this->hasEnded(),
            'is_registration_open' => $this->isRegistrationOpen(),
            'can_edit' => $user ? \Illuminate\Support\Facades\Gate::allows('update', $this->resource) : false,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
