<?php

namespace App\Http\Controllers\Api;

use App\Services\MobileNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    protected MobileNotificationService $mobileNotificationService;

    public function __construct(MobileNotificationService $mobileNotificationService)
    {
        $this->mobileNotificationService = $mobileNotificationService;
    }
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->notifications();

        // Filter by read status
        if ($request->filled('filter')) {
            match ($request->filter) {
                'unread' => $query->whereNull('read_at'),
                'read' => $query->whereNotNull('read_at'),
                default => null,
            };
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', 'like', "%{$request->type}%");
        }

        $notifications = $query->latest()->paginate($request->get('per_page', 20));

        return $this->paginatedResponse($notifications);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return $this->successResponse(['count' => $count]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (! $notification) {
            return $this->notFoundResponse('Notification not found');
        }

        $notification->markAsRead();

        return $this->successResponse(null, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->successResponse(null, 'All notifications marked as read');
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (! $notification) {
            return $this->notFoundResponse('Notification not found');
        }

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted', 204);
    }

    /**
     * Clear all notifications.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return $this->successResponse(null, 'All notifications cleared', 204);
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'in_app_notifications' => 'boolean',
            'notification_types' => 'array',
        ]);

        $user = $request->user();

        $preferences = $user->notificationPreferences()->firstOrNew([]);
        $preferences->fill($validated);
        $preferences->save();

        return $this->successResponse($preferences, 'Notification preferences updated');
    }

    /**
     * Register FCM token for push notifications.
     */
    public function registerFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'nullable|string|in:ios,android',
            'device_info' => 'nullable|array',
        ]);

        $user = $request->user();
        $result = $this->mobileNotificationService->registerDevice($user, $validated);

        return $this->successResponse($result, 'FCM token registered successfully');
    }

    /**
     * Remove FCM token.
     */
    public function removeFcmToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'fcm_token' => null,
            'device_type' => null,
            'device_info' => null,
        ]);

        return $this->successResponse(null, 'FCM token removed', 204);
    }

    /**
     * Send test notification (for development).
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->fcm_token) {
            return $this->errorResponse('No FCM token registered', 400);
        }

        $success = $this->mobileNotificationService->sendTestNotification($user);

        if ($success) {
            return $this->successResponse(null, 'Test notification sent successfully');
        } else {
            return $this->errorResponse('Failed to send test notification', 500);
        }
    }

    /**
     * Get notification details for mobile deep linking.
     */
    public function getNotificationDetails(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return $this->notFoundResponse('Notification not found');
        }

        // Build navigation data from notification
        $notificationData = $notification->data;
        $notificationType = \App\NotificationType::tryFrom($notificationData['type'] ?? '');
        
        $response = [
            'id' => $notification->id,
            'type' => $notification->type,
            'data' => $notificationData,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
            'navigation' => $notificationType ? $this->buildNavigationFromNotification($notificationType, $notificationData) : null,
        ];

        // Mark as read
        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return $this->successResponse($response);
    }

    /**
     * Build navigation data from notification.
     */
    protected function buildNavigationFromNotification(\App\NotificationType $type, array $data): array
    {
        $mobileService = new \App\Services\MobileNotificationService(
            app(\App\Services\FirebaseNotificationService::class),
            app(\App\Services\NotificationService::class)
        );
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($mobileService);
        $method = $reflection->getMethod('buildNavigationPayload');
        $method->setAccessible(true);
        
        return $method->invoke($mobileService, $type, $data);
    }
}
