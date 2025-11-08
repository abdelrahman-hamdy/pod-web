# Mobile App Notification Integration Guide

## Quick Start for Mobile Developers

This guide provides the essential information needed to integrate push notifications in your React Native mobile app with the People of Data backend.

## 1. Required API Keys & Configuration

### Firebase Setup
1. **Get Firebase Admin Credentials** (for backend):
   - Download from Firebase Console → Project Settings → Service Accounts
   - Save as `storage/app/firebase-credentials.json` in Laravel project
   - Add to `.env`: `FIREBASE_CREDENTIALS_PATH="${PWD}/storage/app/firebase-credentials.json"`

2. **Mobile App Configuration Files**:
   - **Android**: Place `google-services.json` in `android/app/`
   - **iOS**: Add `GoogleService-Info.plist` via Xcode to iOS project

## 2. API Endpoints

Base URL: `https://your-domain.com/api/v1`

### Authentication Required Headers
```
Authorization: Bearer YOUR_AUTH_TOKEN
Content-Type: application/json
```

### Notification Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/notifications/fcm-token` | Register device FCM token |
| DELETE | `/notifications/fcm-token` | Remove FCM token |
| GET | `/notifications` | List notifications |
| GET | `/notifications/unread-count` | Get unread count |
| GET | `/notifications/{id}/details` | Get notification with navigation |
| PATCH | `/notifications/{id}/read` | Mark as read |
| PATCH | `/notifications/read-all` | Mark all as read |
| POST | `/notifications/preferences` | Update preferences |
| POST | `/notifications/test` | Send test notification |

## 3. FCM Token Registration

Register device token immediately after user login:

```javascript
// Request body for FCM token registration
POST /api/v1/notifications/fcm-token
{
  "fcm_token": "your-fcm-token-here",
  "device_type": "ios", // or "android"
  "device_info": {
    "os_version": "14.5",
    "app_version": "1.0.0",
    "device_model": "iPhone 12"
  }
}
```

## 4. Notification Payload Structure

When a push notification is received, it contains:

```json
{
  "notification": {
    "title": "New Comment",
    "body": "John Doe commented on your post"
  },
  "data": {
    "notification_type": "comment_added",
    "category": "social",
    "navigation": "{\"screen\":\"PostDetail\",\"params\":{\"post_id\":123,\"comment_id\":456},\"tab\":\"Feed\"}",
    "actor": "{\"id\":789,\"name\":\"John Doe\",\"avatar\":\"url\"}",
    "badge_count": "5",
    "post_id": "123",
    "timestamp": "2024-11-08T10:30:00Z"
  }
}
```

## 5. Navigation Structure

The `navigation` field in data contains JSON with:

```javascript
{
  "screen": "ScreenName",     // Screen to navigate to
  "params": {                 // Parameters for the screen
    "id": 123,
    // other params...
  },
  "tab": "TabName",           // Main tab (if using tab navigation)
  "sub_tab": "SubTabName"     // Sub tab (optional)
}
```

### Screen Names by Notification Type

| Notification Type | Screen | Params |
|-------------------|--------|--------|
| post_liked | PostDetail | post_id |
| comment_added | PostDetail | post_id, comment_id |
| comment_reply | PostDetail | post_id, comment_id |
| event_* | EventDetail | event_id |
| job_posted | JobDetail | job_id |
| job_application_* | JobApplication | job_id, application_id |
| hackathon_* | HackathonDetail | hackathon_id |
| hackathon_team_* | HackathonTeam | hackathon_id, team_id |
| message_received | ChatConversation | sender_id, conversation_id |

## 6. Notification Categories

Notifications are grouped into categories for channel management:

- `social` - Likes, comments, mentions
- `events` - Event reminders, registrations
- `jobs` - Job applications, updates
- `hackathons` - Hackathon teams, invitations
- `internships` - Internship applications
- `messages` - Direct messages
- `account` - Profile views, account updates
- `admin` - Admin notifications

## 7. Mobile App Headers

Send these headers with API requests for enhanced functionality:

```javascript
headers: {
  'X-Mobile-App': 'true',           // Identify as mobile app
  'X-FCM-Token': 'current-token',   // Current FCM token
  'X-Device-Type': 'ios',           // or 'android'
  'X-App-Version': '1.0.0',
  'X-OS-Version': '14.5'
}
```

Response will include:
```
X-Notification-Count: 5  // Unread notification count
```

## 8. Handle Notification Actions

### When App is in Foreground
```javascript
messaging().onMessage(async remoteMessage => {
  const navigation = JSON.parse(remoteMessage.data.navigation);
  // Show in-app notification banner
  // Update badge count
});
```

### When App is in Background/Closed
```javascript
messaging().onNotificationOpenedApp(remoteMessage => {
  const navigation = JSON.parse(remoteMessage.data.navigation);
  navigateToScreen(navigation);
});
```

### Navigation Handler Example
```javascript
function navigateToScreen(navigation) {
  const { screen, params, tab } = navigation;
  
  // First navigate to tab if specified
  if (tab) {
    navigation.navigate(tab);
  }
  
  // Then navigate to specific screen
  navigation.navigate(screen, params);
}
```

## 9. Testing Notifications

### Test via API
```bash
curl -X POST https://your-domain.com/api/v1/notifications/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Test via Artisan Command (Backend)
```bash
php artisan notification:test user@example.com --type=post_liked
```

## 10. Notification Preferences

Allow users to customize their notification preferences:

```javascript
POST /api/v1/notifications/preferences
{
  "email_notifications": true,
  "push_notifications": true,
  "in_app_notifications": true,
  "notification_types": {
    "social": true,
    "events": true,
    "jobs": false,
    "messages": true
  }
}
```

## 11. Error Handling

Common error responses:

```json
{
  "success": false,
  "message": "No FCM token registered",
  "code": 400
}
```

Handle token expiration:
- If 401 response, refresh auth token
- Re-register FCM token after login

## 12. Badge Management

### iOS Badge Update
```javascript
import { getBadgeCount, setBadgeCount } from 'react-native-push-notification';

// Update badge from notification
const badgeCount = parseInt(remoteMessage.data.badge_count || '0');
setBadgeCount(badgeCount);
```

### Android Badge (via notification channels)
Badges are handled automatically through notification channels.

## 13. Deep Linking URLs

For universal/app links, notifications also include web URLs:

| Type | URL Pattern |
|------|-------------|
| Posts | `peopleofdata.com/posts/{id}` |
| Events | `peopleofdata.com/events/{id}` |
| Jobs | `peopleofdata.com/jobs/{id}` |
| Hackathons | `peopleofdata.com/hackathons/{id}` |
| Messages | `peopleofdata.com/chatify/{sender_id}` |

## 14. Notification Sounds

Custom sounds by category:
- `default` - Default system sound
- `message.wav` - Message notifications
- `social.wav` - Social interactions
- `event.wav` - Event notifications

Place sound files in:
- iOS: `ios/YourApp/sounds/`
- Android: `android/app/src/main/res/raw/`

## 15. Troubleshooting Checklist

- [ ] FCM token is registered in backend
- [ ] User has notification preferences enabled
- [ ] Firebase credentials are configured correctly
- [ ] App has notification permissions granted
- [ ] Correct Firebase project is being used
- [ ] Network connectivity is available
- [ ] APNs certificates configured (iOS)
- [ ] Notification channels created (Android 8+)

## Support & Testing

For testing and debugging:
1. Check Laravel logs: `/storage/logs/laravel.log`
2. Use Firebase Console test message feature
3. Monitor Firebase Cloud Messaging reports
4. Test with development/staging Firebase project first

## Security Notes

- Always use HTTPS for API calls
- Validate FCM tokens on backend
- Implement token refresh mechanism
- Don't expose sensitive data in notification body
- Use notification encryption for sensitive content
