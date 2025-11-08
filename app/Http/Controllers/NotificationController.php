<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display user's notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->notifications();

        // Filter by read status
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'unread':
                    $query->whereNull('read_at');
                    break;
                case 'read':
                    $query->whereNotNull('read_at');
                    break;
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', 'like', "%{$request->type}%");
        }

        $perPage = (int) $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'from' => $notifications->firstItem(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'to' => $notifications->lastItem(),
                    'total' => $notifications->total(),
                ],
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications count (based on viewed_at, not read_at).
     * Badge shows count of notifications that haven't been viewed yet.
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->notifications()->whereNull('viewed_at')->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    /**
     * Mark all notifications as viewed (when panel is opened).
     * This sets viewed_at which removes the badge count, but keeps notifications highlighted until clicked.
     */
    public function markAllAsViewed()
    {
        $user = Auth::user();

        // Set viewed_at for all unviewed notifications
        // This will reset the badge count but keep them highlighted
        $user->notifications()
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);

        // Return the new unread count (should be 0 since viewed_at is set)
        $unreadCount = $user->notifications()->whereNull('viewed_at')->count();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as viewed',
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            // Ensure both read_at and viewed_at are set when a notification is clicked
            if (method_exists($notification, 'forceFill')) {
                $notification->forceFill([
                    'read_at' => now(),
                    'viewed_at' => $notification->getAttribute('viewed_at') ?: now(),
                ])->save();
            } else {
                $notification->markAsRead();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read');
    }

    /**
     * Delete a notification.
     */
    public function destroy($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }

    /**
     * Clear all notifications.
     */
    public function clear()
    {
        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared',
        ]);
    }

    /**
     * Get notifications for AJAX requests (real-time updates).
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();

        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        $notifications = $user->notifications()
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get();

        // Badge count is based on unviewed notifications
        $unreadCount = $user->notifications()->whereNull('viewed_at')->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'viewed_at' => method_exists($notification, 'getAttribute') ? $notification->getAttribute('viewed_at') : null,
                    'created_at' => $notification->created_at,
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            }),
            'unread_count' => $unreadCount,
            'has_more' => $notifications->count() === $limit,
        ]);
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'notification_types' => 'array',
            'notification_types.*' => 'string|in:events,jobs,hackathons,posts,messages,team_invites',
        ]);

        $user = Auth::user();

        // This would typically be stored in a user preferences table
        // For now, we'll just return success
        // You could add a user_notification_preferences table/model

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
        ]);
    }

    /**
     * Test notification (for development/admin).
     */
    public function test(Request $request)
    {
        if (! Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:event_reminder,job_application,post_liked,team_invite',
            'message' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Create a test notification
        $user->notify(new \App\Notifications\TestNotification($validated['message']));

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
        ]);
    }
}
