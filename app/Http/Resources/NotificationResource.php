<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data;
        
        return [
            'id' => $this->id,
            'user_id' => $this->notifiable_id,
            'type' => $this->mapNotificationType($this->type),
            'title' => $this->extractTitle($data),
            'body' => $this->extractBody($data),
            'image' => $data['image'] ?? null,
            'action_url' => $data['click_action'] ?? null,
            'action_data' => $data,
            
            // Two-level read system
            'is_read' => !is_null($this->read_at),
            'is_viewed' => !is_null($this->viewed_at),
            'read_at' => $this->read_at?->toISOString(),
            'viewed_at' => $this->viewed_at?->toISOString(),
            
            // Actor information for avatar and user context
            'actor' => [
                'id' => $data['actor_id'] ?? null,
                'name' => $data['actor_name'] ?? 'User',
                'avatar' => $data['actor_avatar'] ?? null,
                'avatar_color' => $data['actor_avatar_color'] ?? null,
            ],
            
            // Notification styling information
            'icon' => $data['icon'] ?? null,
            'action_icon' => $data['action_icon'] ?? null,
            'icon_color' => $data['icon_color'] ?? null,
            'overlay_background_color' => $data['overlay_background_color'] ?? null,
            'background_color' => $data['background_color'] ?? null,
            'category' => $data['category'] ?? 'general',
            
            'created_at' => $this->created_at->toISOString(),
            'time_ago' => $this->created_at->diffForHumans(),
        ];
    }
    
    /**
     * Map Laravel notification class names to Flutter enum values
     */
    private function mapNotificationType(string $className): string
    {
        $mapping = [
            'PostLiked' => 'post_like',
            'CommentAdded' => 'post_comment', 
            'CommentReply' => 'comment_reply',
            'HackathonTeamInvite' => 'follow',
            'EventReminder' => 'event_reminder',
            'JobApplicationReceived' => 'job_application_update',
        ];
        
        $simpleClassName = class_basename($className);
        return $mapping[$simpleClassName] ?? 'system';
    }
    
    /**
     * Extract title from notification data
     */
    private function extractTitle(array $data): string
    {
        return $data['title'] ?? $data['message'] ?? 'New Notification';
    }
    
    /**
     * Extract body from notification data
     */
    private function extractBody(array $data): string
    {
        return $data['body'] ?? $data['message'] ?? '';
    }
}
