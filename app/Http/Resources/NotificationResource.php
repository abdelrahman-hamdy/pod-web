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
        $data = $this->data ?? [];
        
        return [
            'id' => $this->id,
            'user_id' => $this->notifiable_id,
            // Prefer the explicit type stored in the notification data (from NotificationService)
            'type' => $data['type'] ?? $this->mapNotificationType($this->type ?? ''),
            'title' => $this->extractTitle($data),
            'body' => $this->extractBody($data),
            'image' => $data['image'] ?? null,
            'action_url' => $data['click_action'] ?? null,
            'action_data' => $data,
            
            // Two-level read system
            'is_read' => !is_null($this->read_at),
            'is_viewed' => !is_null($this->viewed_at ?? null),
            'read_at' => $this->formatDateTime($this->read_at),
            'viewed_at' => $this->formatDateTime($this->viewed_at ?? null),
            
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
        // Try to extract the class name from the full namespaced class
        $class = basename(str_replace('\\', '/', $className));
        
        return match ($class) {
            'PostLiked' => 'post_like',
            'CommentAdded' => 'post_comment',
            'CommentReply' => 'comment_reply',
            'CommentLiked' => 'comment_like',
            'UserFollowed' => 'follow',
            'MessageReceived' => 'message',
            'EventReminder' => 'event_reminder',
            'HackathonUpdate' => 'hackathon_update',
            'JobApplicationUpdate' => 'job_application_update',
            'InternshipApplicationUpdate' => 'internship_application_update',
            'SystemNotification' => 'system',
            default => 'system',
        };
    }
    
    /**
     * Extract title from notification data
     */
    private function extractTitle(array $data): string
    {
        return $data['title'] ?? $data['subject'] ?? 'Notification';
    }
    
    /**
     * Extract body/message from notification data
     */
    private function extractBody(array $data): string
    {
        return $data['body'] ?? $data['message'] ?? '';
    }

    /**
     * Format datetime to ISO string, handling both Carbon instances and string dates
     */
    private function formatDateTime($datetime): ?string
    {
        if (is_null($datetime)) {
            return null;
        }

        // If it's already a string, try to convert to Carbon first
        if (is_string($datetime)) {
            try {
                $datetime = \Carbon\Carbon::parse($datetime);
            } catch (\Exception $e) {
                return $datetime; // Return as-is if parsing fails
            }
        }

        // If it's a Carbon instance, convert to ISO string
        if ($datetime instanceof \Carbon\Carbon || $datetime instanceof \DateTimeInterface) {
            return $datetime->toISOString();
        }

        return null;
    }
}
