<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'avatar_color' => $this->avatar_color,
            'bio' => $this->bio,
            'title' => $this->title,
            'company' => $this->company,
            'city' => $this->city,
            'country' => $this->country,
            'gender' => $this->gender?->value,
            'skills' => $this->skills,
            'experience_level' => $this->experience_level?->value,
            'education' => $this->education,
            'portfolio_links' => $this->portfolio_links,
            'linkedin_url' => $this->linkedin_url,
            'github_url' => $this->github_url,
            'twitter_url' => $this->twitter_url,
            'website_url' => $this->website_url,
            'role' => is_object($this->role) && method_exists($this->role, 'value') ? $this->role->value : $this->role,
            'profile_completed' => $this->profile_completed,
            'profile_onboarding_seen' => $this->profile_onboarding_seen,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
