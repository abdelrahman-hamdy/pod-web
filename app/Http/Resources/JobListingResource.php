<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        
        // Get user's application if loaded (optimized with eager loading)
        $userApplication = $user && $this->relationLoaded('userApplication') 
            ? $this->userApplication 
            : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'company_name' => $this->company_name,
            'company_description' => $this->company_description,
            'location_type' => $this->location_type?->value,
            'location' => $this->location,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'required_skills' => $this->required_skills,
            'experience_level' => is_object($this->experience_level) && method_exists($this->experience_level, 'value') ? $this->experience_level->value : $this->experience_level,
            'application_deadline' => $this->application_deadline?->toISOString(),
            'status' => $this->status,
            'has_applied' => $userApplication !== null,
            'application_status' => $userApplication?->status->value ?? null,
            'application_date' => $userApplication?->created_at?->toISOString() ?? null,
            'can_edit' => $user ? \Illuminate\Support\Facades\Gate::allows('update', $this->resource) : false,
            'applications_count' => $this->when(
                $user && ($user->hasAnyRole(['admin', 'superadmin', 'client'])),
                $this->when(isset($this->applications_count), $this->applications_count ?? 0)
            ),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'poster' => new UserResource($this->whenLoaded('poster')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
