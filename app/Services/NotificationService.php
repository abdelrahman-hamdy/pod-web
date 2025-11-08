<?php

namespace App\Services;

use App\Models\User;
use App\NotificationType;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Centralized notification service that handles all notifications.
 */
class NotificationService
{
    protected $firebaseService;
    protected $mobileService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
        // Mobile service will be injected when needed
        $this->mobileService = null;
    }

    /**
     * Set mobile notification service.
     */
    public function setMobileService($mobileService): void
    {
        $this->mobileService = $mobileService;
    }

    /**
     * Send notification to a user.
     */
    public function send(User $user, NotificationType $type, array $data, array $channels = ['database']): void
    {
        $preferences = $user->notificationPreferences;

        // Check if user has in-app notifications enabled
        if (in_array('database', $channels) && $preferences->in_app_notifications) {
            $this->sendDatabaseNotification($user, $type, $data);
        }

        // Check if user has push notifications enabled
        if (in_array('push', $channels) && $preferences->push_notifications && $user->fcm_token) {
            $this->sendPushNotification($user, $type, $data);
        }

        // Check if user has email notifications enabled and type requires it
        if (in_array('mail', $channels) && $preferences->email_notifications && $type->requiresEmail()) {
            $this->sendEmailNotification($user, $type, $data);
        }
    }

    /**
     * Send notification to multiple users.
     */
    public function sendBatch(array $users, NotificationType $type, array $data, array $channels = ['database']): void
    {
        $pushTokens = [];
        $emailUsers = [];

        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $preferences = $user->notificationPreferences;

            // Send database notification
            if (in_array('database', $channels) && $preferences->in_app_notifications) {
                $this->sendDatabaseNotification($user, $type, $data);
            }

            // Collect push tokens
            if (in_array('push', $channels) && $preferences->push_notifications && $user->fcm_token) {
                $pushTokens[] = $user->fcm_token;
            }

            // Collect email users
            if (in_array('mail', $channels) && $preferences->email_notifications && $type->requiresEmail()) {
                $emailUsers[] = $user;
            }
        }

        // Send batch push notifications
        if (! empty($pushTokens)) {
            $this->firebaseService->sendBatchNotifications($pushTokens, $type, $data);
        }

        // Send batch email notifications (if needed)
        // This would typically be handled by a queued job
    }

    /**
     * Send database (in-app) notification.
     */
    protected function sendDatabaseNotification(User $user, NotificationType $type, array $data): void
    {
        // Extract actor info from various possible fields
        $actorId = $data['actor_id'] ?? $data['liker_id'] ?? $data['commenter_id'] ?? $data['replier_id'] ?? $data['user_id'] ?? null;
        $actorName = $data['actor_name'] ?? $data['liker_name'] ?? $data['commenter_name'] ?? $data['replier_name'] ?? 'Someone';
        $actorAvatar = $data['actor_avatar'] ?? $data['avatar'] ?? null;
        $actorAvatarColor = $data['actor_avatar_color'] ?? $data['avatar_color'] ?? null;

        // If actor_id exists and no color is set, try to fetch the user's avatar color
        if ($actorId && ! $actorAvatarColor) {
            $actor = \App\Models\User::find($actorId);
            if ($actor) {
                $actorAvatarColor = $actor->avatar_color;
            }
        }

        $notificationData = array_merge($data, [
            'type' => $type->value,
            'actor_id' => $actorId,
            'actor_name' => $actorName,
            'actor_avatar' => $actorAvatar,
            'actor_avatar_color' => $actorAvatarColor,
            'icon' => $type->icon(),
            'action_icon' => $type->actionIcon(),
            'icon_color' => $type->iconColor(),
            'overlay_background_color' => $type->overlayBackgroundColor(),
            'background_color' => $type->backgroundColor(),
            'category' => $type->category(),
            'click_action' => $type->buildUrl($data),
        ]);

        $user->notify(new \App\Notifications\CustomNotification($type, $notificationData));
    }

    /**
     * Send push notification.
     */
    protected function sendPushNotification(User $user, NotificationType $type, array $data): void
    {
        if (! $user->fcm_token) {
            return;
        }

        // Use mobile service if available for enhanced mobile notifications
        if ($this->mobileService) {
            $this->mobileService->sendMobileNotification($user, $type, $data);
        } else {
            // Fallback to basic Firebase notification
            $this->firebaseService->sendEnhancedMobileNotification($user->fcm_token, $type, $data);
        }
    }

    /**
     * Send email notification.
     */
    protected function sendEmailNotification(User $user, NotificationType $type, array $data): void
    {
        // Email notifications would be sent via Laravel's Mail system
        // This is a placeholder for future implementation
        \Log::info('Email notification would be sent', [
            'user' => $user->id,
            'type' => $type->value,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(DatabaseNotification $notification): void
    {
        if (! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all user notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Delete notification.
     */
    public function delete(DatabaseNotification $notification): void
    {
        $notification->delete();
    }

    /**
     * Clear all user notifications.
     */
    public function clear(User $user): void
    {
        $user->notifications()->delete();
    }
}
