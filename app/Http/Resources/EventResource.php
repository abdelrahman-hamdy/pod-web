<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\PersonalAccessToken;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolve user either from request->user() or Sanctum token (for public routes)
        $user = $request->user();
        if (! $user) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                if (! empty($token)) {
                    $accessToken = PersonalAccessToken::findToken($token);
                    if ($accessToken) {
                        $user = $accessToken->tokenable;
                    }
                }
            }
        }
        
        // Debug logging
        if ($user) {
            \Log::info("EventResource for event {$this->id}: user_id={$user->id}, registrations_loaded=" . ($this->relationLoaded('registrations') ? 'YES' : 'NO'));
            if ($this->relationLoaded('registrations')) {
                \Log::info("EventResource for event {$this->id}: registrations_count=" . $this->registrations->count());
                \Log::info("EventResource for event {$this->id}: registrations=" . $this->registrations->pluck('user_id')->toJson());
            }
        }

        // Compute registration flags robustly
        $isRegistered = false;
        $registrationStatus = null;
        if ($this->relationLoaded('registrations')) {
            // If relation is already filtered by controller, we can trust its content
            $isRegistered = $this->registrations->isNotEmpty();
            if ($isRegistered) {
                $reg = $this->registrations->first();
                if ($reg) {
                    $registrationStatus = is_object($reg->status) && method_exists($reg->status, 'value')
                        ? $reg->status->value
                        : $reg->status;
                }
            }
        } elseif ($user) {
            // Relation not loaded: fallback to direct query for safety
            // Fallback to direct query (avoids wrong UI state if relation wasn't eager loaded)
            $reg = $this->registrations()
                ->where('user_id', $user->id)
                ->select('user_id', 'status')
                ->first();
            if ($reg) {
                $isRegistered = true;
                $registrationStatus = is_object($reg->status) && method_exists($reg->status, 'value')
                    ? $reg->status->value
                    : $reg->status;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'location' => $this->location,
            'format' => is_object($this->format) && method_exists($this->format, 'value') ? $this->format->value : $this->format,
            'max_attendees' => $this->max_attendees,
            'current_attendees' => $this->when(isset($this->registrations_count), $this->registrations_count ?? 0),
            'agenda' => $this->agenda,
            'banner_image' => $this->banner_image ? url('storage/'.$this->banner_image) : null,
            'registration_deadline' => $this->registration_deadline?->toISOString(),
            'chat_opens_at' => $this->chat_opens_at?->toISOString(),
            'waitlist_enabled' => $this->waitlist_enabled,
            'is_active' => $this->is_active,
            'is_registered' => $isRegistered,
            'registration_status' => $registrationStatus,
            'can_edit' => $user ? \Illuminate\Support\Facades\Gate::allows('update', $this->resource) : false,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
