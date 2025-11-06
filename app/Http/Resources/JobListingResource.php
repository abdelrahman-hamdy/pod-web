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
        
        // Compute application flags robustly (same pattern as EventResource)
        $hasApplied = false;
        $applicationStatus = null;
        $applicationDate = null;
        
        if ($this->relationLoaded('userApplication')) {
            // If relation is already filtered by controller, we can trust its content
            $hasApplied = $this->userApplication !== null;
            if ($hasApplied) {
                $applicationStatus = is_object($this->userApplication->status) && method_exists($this->userApplication->status, 'value')
                    ? $this->userApplication->status->value
                    : $this->userApplication->status;
                $applicationDate = $this->userApplication->created_at?->toISOString();
            }
        } elseif ($user) {
            // Fallback to direct query (avoids wrong UI state if relation wasn't eager loaded)
            $application = $this->applications()
                ->where('user_id', $user->id)
                ->select('user_id', 'status', 'created_at')
                ->first();
            if ($application) {
                $hasApplied = true;
                $applicationStatus = is_object($application->status) && method_exists($application->status, 'value')
                    ? $application->status->value
                    : $application->status;
                $applicationDate = $application->created_at?->toISOString();
            }
        }

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
            'has_applied' => $hasApplied,
            'application_status' => $applicationStatus,
            'application_date' => $applicationDate,
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
