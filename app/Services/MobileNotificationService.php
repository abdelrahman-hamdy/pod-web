<?php

namespace App\Services;

use App\Models\User;
use App\NotificationType;
use Illuminate\Support\Facades\Log;

/**
 * Mobile-specific notification service that handles enhanced push notifications
 * with deep linking, navigation data, and device management.
 */
class MobileNotificationService
{
    protected FirebaseNotificationService $firebaseService;
    protected NotificationService $notificationService;

    public function __construct(
        FirebaseNotificationService $firebaseService,
        NotificationService $notificationService
    ) {
        $this->firebaseService = $firebaseService;
        $this->notificationService = $notificationService;
    }

    /**
     * Send mobile push notification with enhanced navigation data.
     */
    public function sendMobileNotification(User $user, NotificationType $type, array $data): bool
    {
        if (!$user->fcm_token) {
            Log::warning('User has no FCM token', ['user_id' => $user->id]);
            return false;
        }

        // Enhance data with mobile-specific navigation payload
        $enhancedData = $this->buildMobilePayload($type, $data, $user);

        // Send via Firebase with enhanced payload
        return $this->firebaseService->sendEnhancedMobileNotification(
            $user->fcm_token,
            $type,
            $enhancedData
        );
    }

    /**
     * Build enhanced mobile notification payload with navigation data.
     */
    protected function buildMobilePayload(NotificationType $type, array $data, User $user): array
    {
        // Extract actor information
        $actorId = $data['actor_id'] ?? 
                  $data['liker_id'] ?? 
                  $data['commenter_id'] ?? 
                  $data['replier_id'] ?? 
                  $data['user_id'] ?? 
                  null;

        // Build navigation payload based on notification type
        $navigation = $this->buildNavigationPayload($type, $data);

        return array_merge($data, [
            'notification_type' => $type->value,
            'category' => $type->category(),
            'timestamp' => now()->toIso8601String(),
            'user_id' => $user->id,
            
            // Navigation data for mobile app
            'navigation' => $navigation,
            
            // Visual elements
            'icon' => $type->icon(),
            'action_icon' => $type->actionIcon(),
            'icon_color' => $type->iconColor(),
            'background_color' => $type->backgroundColor(),
            'overlay_color' => $type->overlayBackgroundColor(),
            
            // Actor information if available
            'actor' => $actorId ? $this->getActorInfo($actorId) : null,
            
            // Badge count
            'badge_count' => $user->unreadNotifications()->count() + 1,
            
            // Sound and priority
            'sound' => $this->getNotificationSound($type),
            'priority' => $this->getNotificationPriority($type),
        ]);
    }

    /**
     * Build navigation payload for mobile deep linking.
     */
    protected function buildNavigationPayload(NotificationType $type, array $data): array
    {
        return match ($type) {
            // Posts navigation
            NotificationType::POST_LIKED,
            NotificationType::COMMENT_ADDED,
            NotificationType::COMMENT_REPLY,
            NotificationType::POST_MENTION => [
                'screen' => 'PostDetail',
                'params' => [
                    'post_id' => $data['post_id'] ?? null,
                    'comment_id' => $data['comment_id'] ?? null,
                    'scroll_to_comments' => in_array($type, [
                        NotificationType::COMMENT_ADDED,
                        NotificationType::COMMENT_REPLY
                    ]),
                ],
                'tab' => 'Feed',
            ],

            // Events navigation
            NotificationType::EVENT_CREATED,
            NotificationType::EVENT_REGISTERED,
            NotificationType::EVENT_WAITLISTED,
            NotificationType::EVENT_CONFIRMED,
            NotificationType::EVENT_REMINDER,
            NotificationType::EVENT_CANCELLED => [
                'screen' => 'EventDetail',
                'params' => [
                    'event_id' => $data['event_id'] ?? null,
                    'show_registration' => in_array($type, [
                        NotificationType::EVENT_REGISTERED,
                        NotificationType::EVENT_WAITLISTED
                    ]),
                ],
                'tab' => 'Events',
            ],

            // Jobs navigation
            NotificationType::JOB_POSTED => [
                'screen' => 'JobDetail',
                'params' => [
                    'job_id' => $data['job_id'] ?? null,
                ],
                'tab' => 'Jobs',
            ],

            NotificationType::JOB_APPLICATION_RECEIVED,
            NotificationType::JOB_APPLICATION_ACCEPTED,
            NotificationType::JOB_APPLICATION_REJECTED,
            NotificationType::JOB_APPLICATION_INTERVIEW => [
                'screen' => 'JobApplication',
                'params' => [
                    'application_id' => $data['application_id'] ?? null,
                    'job_id' => $data['job_id'] ?? null,
                ],
                'tab' => 'Jobs',
                'sub_tab' => 'MyApplications',
            ],

            // Hackathons navigation
            NotificationType::HACKATHON_CREATED,
            NotificationType::HACKATHON_REGISTERED,
            NotificationType::HACKATHON_WINNER => [
                'screen' => 'HackathonDetail',
                'params' => [
                    'hackathon_id' => $data['hackathon_id'] ?? null,
                ],
                'tab' => 'Hackathons',
            ],

            NotificationType::HACKATHON_TEAM_INVITED,
            NotificationType::HACKATHON_TEAM_INVITATION_ACCEPTED,
            NotificationType::HACKATHON_TEAM_JOIN_REQUEST,
            NotificationType::HACKATHON_TEAM_JOIN_ACCEPTED => [
                'screen' => 'HackathonTeam',
                'params' => [
                    'hackathon_id' => $data['hackathon_id'] ?? null,
                    'team_id' => $data['team_id'] ?? null,
                    'show_invitations' => in_array($type, [
                        NotificationType::HACKATHON_TEAM_INVITED,
                        NotificationType::HACKATHON_TEAM_JOIN_REQUEST
                    ]),
                ],
                'tab' => 'Hackathons',
            ],

            // Internships navigation
            NotificationType::INTERNSHIP_APPLICATION_RECEIVED,
            NotificationType::INTERNSHIP_APPLICATION_ACCEPTED,
            NotificationType::INTERNSHIP_APPLICATION_REJECTED => [
                'screen' => 'InternshipApplication',
                'params' => [
                    'application_id' => $data['application_id'] ?? null,
                    'internship_id' => $data['internship_id'] ?? null,
                ],
                'tab' => 'Internships',
                'sub_tab' => 'MyApplications',
            ],

            // Profile navigation
            NotificationType::PROFILE_VIEWED => [
                'screen' => 'UserProfile',
                'params' => [
                    'user_id' => $data['viewer_id'] ?? null,
                ],
                'tab' => 'Profile',
            ],

            // Messages navigation
            NotificationType::MESSAGE_RECEIVED => [
                'screen' => 'ChatConversation',
                'params' => [
                    'conversation_id' => $data['conversation_id'] ?? null,
                    'sender_id' => $data['sender_id'] ?? null,
                ],
                'tab' => 'Messages',
            ],

            // Admin navigation
            NotificationType::ADMIN_APPROVED,
            NotificationType::ADMIN_REJECTED => [
                'screen' => 'AdminNotification',
                'params' => [
                    'notification_id' => $data['notification_id'] ?? null,
                ],
                'tab' => 'Home',
            ],

            // Default navigation
            default => [
                'screen' => 'Notifications',
                'params' => [],
                'tab' => 'Notifications',
            ],
        };
    }

    /**
     * Get actor information for notification.
     */
    protected function getActorInfo(int $actorId): ?array
    {
        $actor = User::find($actorId);
        
        if (!$actor) {
            return null;
        }

        return [
            'id' => $actor->id,
            'name' => $actor->name,
            'avatar' => $actor->avatar,
            'avatar_color' => $actor->avatar_color,
            'role' => $actor->role,
            'is_verified' => $actor->is_verified ?? false,
        ];
    }

    /**
     * Get notification sound based on type.
     */
    protected function getNotificationSound(NotificationType $type): string
    {
        return match ($type->category()) {
            'messages' => 'message.wav',
            'social' => 'social.wav',
            'events' => 'event.wav',
            'jobs', 'internships' => 'opportunity.wav',
            'hackathons' => 'competition.wav',
            'admin' => 'admin.wav',
            default => 'default',
        };
    }

    /**
     * Get notification priority based on type.
     */
    protected function getNotificationPriority(NotificationType $type): string
    {
        return match ($type) {
            // High priority notifications
            NotificationType::MESSAGE_RECEIVED,
            NotificationType::EVENT_REMINDER,
            NotificationType::JOB_APPLICATION_ACCEPTED,
            NotificationType::JOB_APPLICATION_INTERVIEW,
            NotificationType::HACKATHON_WINNER,
            NotificationType::INTERNSHIP_APPLICATION_ACCEPTED,
            NotificationType::ADMIN_APPROVED,
            NotificationType::ADMIN_REJECTED => 'high',

            // Normal priority
            default => 'normal',
        };
    }

    /**
     * Register or update device token for push notifications.
     */
    public function registerDevice(User $user, array $deviceData): array
    {
        // Update FCM token
        $user->update([
            'fcm_token' => $deviceData['fcm_token'],
            'device_type' => $deviceData['device_type'] ?? null,
            'device_info' => $deviceData['device_info'] ?? null,
        ]);

        // Subscribe to relevant topics
        $this->subscribeToTopics($user, $deviceData['fcm_token']);

        return [
            'success' => true,
            'message' => 'Device registered successfully',
            'topics' => $this->getUserTopics($user),
        ];
    }

    /**
     * Subscribe user to relevant Firebase topics.
     */
    protected function subscribeToTopics(User $user, string $token): void
    {
        $topics = $this->getUserTopics($user);

        foreach ($topics as $topic) {
            try {
                $this->firebaseService->subscribeToTopic($token, $topic);
            } catch (\Exception $e) {
                Log::error('Failed to subscribe to topic', [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get topics user should be subscribed to.
     */
    protected function getUserTopics(User $user): array
    {
        $topics = ['all_users'];

        // Add role-based topics
        if ($user->role) {
            $topics[] = "role_{$user->role}";
        }

        // Add preference-based topics
        $preferences = $user->notificationPreferences;
        if ($preferences) {
            $types = $preferences->notification_types ?? [];
            foreach ($types as $type => $enabled) {
                if ($enabled) {
                    $topics[] = "pref_{$type}";
                }
            }
        }

        return $topics;
    }

    /**
     * Send batch mobile notifications.
     */
    public function sendBatchMobileNotifications(array $users, NotificationType $type, array $data): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'no_token' => 0,
        ];

        foreach ($users as $user) {
            if (!$user->fcm_token) {
                $results['no_token']++;
                continue;
            }

            if ($this->sendMobileNotification($user, $type, $data)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Test notification for development.
     */
    public function sendTestNotification(User $user, string $type = 'test'): bool
    {
        $testType = NotificationType::POST_LIKED; // Use an existing type for testing

        $testData = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification from People of Data',
            'post_id' => 1,
            'test_mode' => true,
            'timestamp' => now()->toIso8601String(),
        ];

        return $this->sendMobileNotification($user, $testType, $testData);
    }
}
