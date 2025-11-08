<?php

namespace App\Services;

use App\NotificationType;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $firebaseCredentialsPath = config('services.firebase.credentials_path');

            if (! $firebaseCredentialsPath || ! file_exists($firebaseCredentialsPath)) {
                throw new \Exception('Firebase credentials file not found');
            }

            $factory = (new Factory)->withServiceAccount($firebaseCredentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: '.$e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Send push notification to a user.
     */
    public function sendNotification(string $fcmToken, NotificationType $type, array $data): bool
    {
        if (! $this->messaging) {
            Log::warning('Firebase messaging not initialized, skipping push notification');

            return false;
        }

        try {
            $notification = Notification::create(
                $data['title'] ?? $this->getDefaultTitle($type),
                $data['body'] ?? ''
            );

            // Create deep link URL
            $deepLink = $this->buildDeepLink($type, $data);

            // Build message
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData([
                    'type' => $type->value,
                    'category' => $type->category(),
                    'deep_link' => $deepLink,
                    'click_action' => $deepLink,
                ] + $data);

            // Configure for Android
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'click_action' => $deepLink,
                ],
            ]);
            $message = $message->withAndroidConfig($androidConfig);

            // Configure for iOS
            $apnsConfig = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'alert' => [
                            'title' => $data['title'] ?? $this->getDefaultTitle($type),
                            'body' => $data['body'] ?? '',
                        ],
                        'category' => $type->category(),
                    ],
                ],
            ]);
            $message = $message->withApnsConfig($apnsConfig);

            // Configure for Web
            $webPushConfig = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $data['title'] ?? $this->getDefaultTitle($type),
                    'body' => $data['body'] ?? '',
                    'icon' => $data['icon'] ?? '/images/logo.png',
                    'badge' => '/images/badge.png',
                    'requireInteraction' => true,
                ],
            ]);
            $message = $message->withWebPushConfig($webPushConfig);

            $result = $this->messaging->send($message);

            Log::info('Firebase notification sent successfully', [
                'type' => $type->value,
                'message_id' => $result,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send Firebase notification', [
                'type' => $type->value,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send enhanced mobile notification with navigation payload.
     */
    public function sendEnhancedMobileNotification(string $fcmToken, NotificationType $type, array $data): bool
    {
        if (! $this->messaging) {
            Log::warning('Firebase messaging not initialized, skipping push notification');
            return false;
        }

        try {
            $notification = Notification::create(
                $data['title'] ?? $this->getDefaultTitle($type),
                $data['body'] ?? ''
            );

            // Build enhanced data payload for mobile
            $dataPayload = [
                'notification_type' => $type->value,
                'category' => $type->category(),
                'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
                'navigation' => json_encode($data['navigation'] ?? []),
                'actor' => json_encode($data['actor'] ?? null),
                'badge_count' => (string)($data['badge_count'] ?? 0),
                'sound' => $data['sound'] ?? 'default',
                'priority' => $data['priority'] ?? 'normal',
            ];

            // Add specific IDs if available
            foreach (['post_id', 'event_id', 'job_id', 'hackathon_id', 'application_id', 'team_id', 'user_id'] as $key) {
                if (isset($data[$key])) {
                    $dataPayload[$key] = (string)$data[$key];
                }
            }

            // Build message
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($dataPayload);

            // Enhanced Android configuration
            $androidConfig = AndroidConfig::fromArray([
                'priority' => $data['priority'] === 'high' ? 'high' : 'normal',
                'notification' => [
                    'sound' => $data['sound'] ?? 'default',
                    'channel_id' => $type->category(),
                    'tag' => $type->value,
                    'color' => '#4F46E5', // Primary brand color
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                    'default_light_settings' => true,
                ],
                'data' => $dataPayload,
            ]);
            $message = $message->withAndroidConfig($androidConfig);

            // Enhanced iOS configuration
            $apnsConfig = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => $data['priority'] === 'high' ? '10' : '5',
                    'apns-push-type' => 'alert',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $data['title'] ?? $this->getDefaultTitle($type),
                            'body' => $data['body'] ?? '',
                            'subtitle' => $data['subtitle'] ?? null,
                        ],
                        'badge' => $data['badge_count'] ?? 0,
                        'sound' => $data['sound'] ?? 'default',
                        'category' => $type->category(),
                        'thread-id' => $type->category(),
                        'mutable-content' => 1,
                        'content-available' => 1,
                    ],
                    'navigation' => $data['navigation'] ?? [],
                    'actor' => $data['actor'] ?? null,
                ],
            ]);
            $message = $message->withApnsConfig($apnsConfig);

            $result = $this->messaging->send($message);

            Log::info('Enhanced mobile notification sent successfully', [
                'type' => $type->value,
                'message_id' => $result,
                'user_token' => substr($fcmToken, 0, 10) . '...',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send enhanced mobile notification', [
                'type' => $type->value,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Subscribe a token to a topic.
     */
    public function subscribeToTopic(string $token, string $topic): bool
    {
        if (! $this->messaging) {
            return false;
        }

        try {
            $this->messaging->subscribeToTopic([$token], $topic);
            
            Log::info('Subscribed to Firebase topic', [
                'topic' => $topic,
                'token' => substr($token, 0, 10) . '...',
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to subscribe to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Unsubscribe a token from a topic.
     */
    public function unsubscribeFromTopic(string $token, string $topic): bool
    {
        if (! $this->messaging) {
            return false;
        }

        try {
            $this->messaging->unsubscribeFromTopic([$token], $topic);
            
            Log::info('Unsubscribed from Firebase topic', [
                'topic' => $topic,
                'token' => substr($token, 0, 10) . '...',
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to unsubscribe from topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send push notification to multiple users.
     */
    public function sendBatchNotifications(array $fcmTokens, NotificationType $type, array $data): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($fcmTokens as $token) {
            if ($this->sendNotification($token, $type, $data)) {
                $results['success'][] = $token;
            } else {
                $results['failed'][] = $token;
            }
        }

        return $results;
    }

    /**
     * Build deep link URL based on notification type.
     */
    protected function buildDeepLink(NotificationType $type, array $data): string
    {
        $baseUrl = config('app.url');

        return match ($type) {
            // Posts
            NotificationType::POST_LIKED, NotificationType::COMMENT_ADDED, NotificationType::COMMENT_REPLY, NotificationType::POST_MENTION => $baseUrl.'/posts/'.($data['post_id'] ?? ''),

            // Events
            NotificationType::EVENT_CREATED, NotificationType::EVENT_REGISTERED, NotificationType::EVENT_WAITLISTED, NotificationType::EVENT_CONFIRMED, NotificationType::EVENT_REMINDER, NotificationType::EVENT_CANCELLED => $baseUrl.'/events/'.($data['event_id'] ?? ''),

            // Jobs
            NotificationType::JOB_POSTED => $baseUrl.'/jobs/'.($data['job_id'] ?? ''),
            NotificationType::JOB_APPLICATION_RECEIVED, NotificationType::JOB_APPLICATION_ACCEPTED, NotificationType::JOB_APPLICATION_REJECTED, NotificationType::JOB_APPLICATION_INTERVIEW => $baseUrl.'/jobs/applications/'.($data['application_id'] ?? ''),

            // Hackathons
            NotificationType::HACKATHON_CREATED, NotificationType::HACKATHON_REGISTERED, NotificationType::HACKATHON_TEAM_INVITED, NotificationType::HACKATHON_TEAM_INVITATION_ACCEPTED, NotificationType::HACKATHON_TEAM_JOIN_REQUEST, NotificationType::HACKATHON_TEAM_JOIN_ACCEPTED, NotificationType::HACKATHON_WINNER => $baseUrl.'/hackathons/'.($data['hackathon_id'] ?? ''),

            // Internships
            NotificationType::INTERNSHIP_APPLICATION_RECEIVED, NotificationType::INTERNSHIP_APPLICATION_ACCEPTED, NotificationType::INTERNSHIP_APPLICATION_REJECTED => $baseUrl.'/internships/applications/'.($data['application_id'] ?? ''),

            // Profile
            NotificationType::PROFILE_VIEWED => $baseUrl.'/profile/'.($data['viewer_id'] ?? ''),

            // Messages
            NotificationType::MESSAGE_RECEIVED => $baseUrl.'/messages',

            // Admin
            NotificationType::ADMIN_APPROVED, NotificationType::ADMIN_REJECTED => $baseUrl.'/admin',

            // Default
            default => $baseUrl,
        };
    }

    /**
     * Get default title for notification type.
     */
    protected function getDefaultTitle(NotificationType $type): string
    {
        return match ($type) {
            NotificationType::POST_LIKED => 'New Like',
            NotificationType::COMMENT_ADDED => 'New Comment',
            NotificationType::COMMENT_REPLY => 'New Reply',
            NotificationType::EVENT_CREATED => 'New Event',
            NotificationType::EVENT_REGISTERED => 'Event Registration',
            NotificationType::JOB_APPLICATION_RECEIVED => 'New Job Application',
            NotificationType::MESSAGE_RECEIVED => 'New Message',
            default => 'Notification',
        };
    }
}
