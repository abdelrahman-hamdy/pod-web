<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        
        // Debug logging
        if ($user) {
            \Log::info("EventResource for event {$this->id}: user_id={$user->id}, registrations_loaded=" . ($this->relationLoaded('registrations') ? 'YES' : 'NO'));
            if ($this->relationLoaded('registrations')) {
                \Log::info("EventResource for event {$this->id}: registrations_count=" . $this->registrations->count());
                \Log::info("EventResource for event {$this->id}: registrations=" . $this->registrations->pluck('user_id')->toJson());
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
            'is_registered' => $user && $this->relationLoaded('registrations') ? $this->registrations->contains('user_id', $user->id) : false,
            'registration_status' => $user && $this->relationLoaded('registrations') ? (function () use ($user) {
                $registration = $this->registrations->firstWhere('user_id', $user->id);
                return $registration ? $registration->status->value : null;
            })() : null,
            'can_edit' => $user ? \Illuminate\Support\Facades\Gate::allows('update', $this->resource) : false,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
