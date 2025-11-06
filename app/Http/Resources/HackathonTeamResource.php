<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HackathonTeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hackathon_id' => $this->hackathon_id,
            'name' => $this->name,
            'description' => $this->description,
            'team_leader_id' => $this->leader_id,
            'project_title' => $this->project_title,
            'project_description' => $this->project_description,
            'project_url' => $this->project_url,
            'github_url' => $this->github_url,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'members' => $this->members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'user' => [
                        'id' => $member->user->id,
                        'name' => $member->user->name,
                        'email' => $member->user->email,
                        'avatar' => $member->user->avatar ? asset('storage/'.$member->user->avatar) : null,
                    ],
                    'role' => $member->role,
                    'joined_at' => $member->created_at?->toISOString(),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
