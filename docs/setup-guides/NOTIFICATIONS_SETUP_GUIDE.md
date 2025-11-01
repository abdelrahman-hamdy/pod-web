# Notifications System Setup Guide

## Overview

A complete notifications system has been implemented with Firebase Cloud Messaging (FCM) for push notifications, deep linking support, and a comprehensive notification panel in the UI.

## Features

### 1. **Notification Types**
The system supports 30+ notification types across all system activities:
- **Posts**: Likes, comments, replies, mentions
- **Events**: Creation, registration, reminders, cancellations
- **Jobs**: Applications received, accepted, rejected, interviews
- **Hackathons**: Team invites, join requests, winners
- **Internships**: Application received, accepted, rejected
- **Messages**: New messages received
- **Account**: Profile views, activation, suspension
- **Admin**: Approval/rejection actions

### 2. **Notification Channels**
- **In-App**: Database-stored notifications with real-time updates
- **Push**: Firebase Cloud Messaging for web and mobile
- **Email**: Reserved for important notifications (account status, applications, etc.)

### 3. **Deep Linking**
Each notification includes a deep link that opens the related content:
- Posts → `/posts/{id}`
- Events → `/events/{id}`
- Jobs → `/jobs/{id}` or `/jobs/applications/{id}`
- Hackathons → `/hackathons/{id}`
- Messages → `/messages`
- Profile → `/profile/{id}`

### 4. **Visual Design**
Each notification type has:
- Unique icon (Heroicons)
- Custom icon color
- Background color for visual categorization
- Category grouping (social, events, jobs, etc.)

## Setup Instructions

### 1. Firebase Configuration

#### Step 1: Create Firebase Project
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select existing one
3. Enable Cloud Messaging in the project settings

#### Step 2: Generate Service Account Key
1. Go to **Project Settings** → **Service Accounts**
2. Click **Generate New Private Key**
3. Save the JSON file securely

#### Step 3: Install Credentials
1. Place the Firebase service account JSON file in `storage/app/firebase-credentials.json`
2. Or set the path in `.env`:
```env
FIREBASE_CREDENTIALS_PATH=/path/to/your/firebase-credentials.json
```

#### Step 4: Configure Firebase Web App
1. In Firebase Console, go to **Project Settings** → **General**
2. Add a web app to your project
3. Copy the Firebase configuration

#### Step 5: Frontend Firebase Setup
The frontend needs to initialize Firebase for push notifications. This will be added when you build your mobile app.

For now, notifications work in-app and will be ready for push when you configure the mobile app.

### 2. Database Setup

Migrations have already been run. The system includes:

#### `users` Table
- `fcm_token`: FCM token for push notifications (nullable)

#### `user_notification_preferences` Table
- `email_notifications`: Enable/disable email notifications
- `push_notifications`: Enable/disable push notifications  
- `in_app_notifications`: Enable/disable in-app notifications
- `notification_types`: JSON field for per-type preferences

### 3. UI Components

#### Notifications Panel
The panel is integrated into the header component and includes:
- Real-time unread count badge
- Dropdown panel with latest notifications
- Mobile-responsive full-screen panel
- Mark all as read functionality
- Deep linking to related content
- Auto-refresh every 30 seconds

## Usage

### Sending Notifications

Use the `NotificationService` to send notifications:

```php
use App\Services\NotificationService;
use App\NotificationType;

// Inject the service
public function __construct(NotificationService $notificationService)
{
    $this->notificationService = $notificationService;
}

// Send a notification
$this->notificationService->send(
    $user,                                    // Recipient
    NotificationType::POST_LIKED,              // Type
    [
        'title' => 'New Like',
        'body' => Auth::user()->name . ' liked your post',
        'post_id' => $post->id,
        'liker_id' => Auth::id(),
        'liker_name' => Auth::user()->name,
        'avatar' => Auth::user()->avatar,
    ],
    ['database', 'push']                      // Channels
);

// Send to multiple users
$this->notificationService->sendBatch(
    $users,                                   // Array of users
    NotificationType::EVENT_CREATED,
    [
        'title' => 'New Event',
        'body' => 'A new event has been created',
        'event_id' => $event->id,
    ],
    ['database', 'push']
);
```

### Notification Triggers Already Implemented

The following triggers are already integrated:

#### Posts
- **PostController**: Like notifications
- **CommentController**: Comment and reply notifications

#### Events
- To be implemented based on your event workflow

#### Jobs  
- To be implemented in JobListingController

#### Hackathons
- To be implemented in HackathonController

### API Endpoints

All notification endpoints are under `/api/v1/notifications`:

```http
GET  /api/v1/notifications              # List notifications
GET  /api/v1/notifications/unread-count # Get unread count
PATCH /api/v1/notifications/{id}/read   # Mark as read
PATCH /api/v1/notifications/read-all    # Mark all as read
DELETE /api/v1/notifications/{id}       # Delete notification
DELETE /api/v1/notifications            # Clear all
POST /api/v1/notifications/preferences  # Update preferences
POST /api/v1/notifications/fcm-token    # Register FCM token
DELETE /api/v1/notifications/fcm-token  # Remove FCM token
```

### Notification Types Reference

See `app/NotificationType.php` for the complete list of types and their associated icons/colors.

## Mobile App Integration

When building your mobile app:

1. **Register FCM Token**:
   When a user logs in, send their FCM token to:
   ```http
   POST /api/v1/notifications/fcm-token
   Content-Type: application/json
   
   {
     "fcm_token": "user_fcm_token_here"
   }
   ```

2. **Handle Deep Links**:
   When a user clicks a push notification, extract the `deep_link` from the notification data and navigate to that URL.

3. **Notification Preferences**:
   Allow users to manage their preferences via:
   ```http
   POST /api/v1/notifications/preferences
   ```

## Architecture

### Key Components

1. **NotificationType Enum** (`app/NotificationType.php`)
   - Defines all notification types
   - Provides icons, colors, and categories

2. **NotificationService** (`app/Services/NotificationService.php`)
   - Central service for sending notifications
   - Handles preferences and channel selection

3. **FirebaseNotificationService** (`app/Services/FirebaseNotificationService.php`)
   - Handles Firebase Cloud Messaging
   - Builds deep links
   - Configures push notifications for web/Android/iOS

4. **CustomNotification** (`app/Notifications/CustomNotification.php`)
   - Laravel notification class for database storage

5. **UserNotificationPreference Model**
   - Stores user notification preferences
   - Auto-created with defaults when user is created

### File Structure

```
app/
├── NotificationType.php                 # Notification type enum
├── Services/
│   ├── NotificationService.php         # Main notification service
│   └── FirebaseNotificationService.php # FCM integration
├── Models/
│   ├── User.php                        # Has fcm_token and preferences
│   └── UserNotificationPreference.php # Preferences model
├── Notifications/
│   └── CustomNotification.php          # Database notification
└── Http/Controllers/
    ├── PostController.php              # Like notifications
    ├── CommentController.php           # Comment/reply notifications
    └── Api/NotificationController.php  # API endpoints

resources/views/components/
└── notifications-panel.blade.php       # UI component

database/migrations/
├── 2025_11_01_114459_add_fcm_token_to_users_table.php
└── 2025_11_01_114459_create_user_notification_preferences_table.php
```

## Testing

To test notifications:

1. **Trigger a notification**: Like a post or comment on someone's post
2. **Check the panel**: Click the bell icon in the header
3. **View notification**: Click on a notification to navigate to the related content
4. **Mark as read**: Click "Mark all read" or click individual notifications

## Future Enhancements

Consider adding:

1. **Email Notifications**: Implement Laravel Mail classes
2. **Notification Preferences UI**: Build a settings page
3. **Notification Templates**: Customize notification messages
4. **Grouping**: Group similar notifications
5. **Badges**: Display notification badges on various pages
6. **Real-time Updates**: WebSocket integration for instant updates
7. **Notification History**: Archive old notifications

## Troubleshooting

### Push Notifications Not Working
- Check `FIREBASE_CREDENTIALS_PATH` in `.env`
- Verify Firebase credentials file exists and is valid
- Check logs at `storage/logs/laravel.log`

### Notifications Not Showing in UI
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check network requests in browser dev tools

### FCM Token Issues
- Ensure proper Firebase initialization in mobile app
- Verify token registration API call succeeds
- Check token format validity

## Support

For issues or questions, check:
- Firebase Console: https://console.firebase.google.com/
- Laravel Notifications: https://laravel.com/docs/notifications
- Firebase Cloud Messaging: https://firebase.google.com/docs/cloud-messaging

