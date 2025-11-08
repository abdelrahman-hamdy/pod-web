# Firebase Push Notifications Setup Guide

## Overview
This guide will help you set up Firebase Cloud Messaging (FCM) for push notifications in the People of Data mobile app.

## Prerequisites
- A Google/Firebase account
- Access to Firebase Console (https://console.firebase.google.com)
- The mobile app source code

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com)
2. Click "Create Project" or select an existing project
3. Enter project name: "People of Data" (or your preferred name)
4. Follow the setup wizard (you can disable Google Analytics if not needed)

## Step 2: Add Your Apps to Firebase

### For Android:
1. In Firebase Console, click "Add app" → Select Android
2. Enter your Android package name (e.g., `com.peopleofdata.app`)
3. Download the `google-services.json` file
4. Place it in your React Native app's `android/app/` directory

### For iOS:
1. In Firebase Console, click "Add app" → Select iOS
2. Enter your iOS bundle ID (e.g., `com.peopleofdata.app`)
3. Download the `GoogleService-Info.plist` file
4. Add it to your iOS project through Xcode (drag to the project navigator)

## Step 3: Get Firebase Admin SDK Credentials

1. In Firebase Console, go to Project Settings → Service Accounts
2. Click "Generate new private key"
3. Save the JSON file securely
4. Place it in your Laravel project at: `storage/app/firebase-credentials.json`
5. **IMPORTANT**: Never commit this file to version control!

## Step 4: Configure Laravel Environment

Add the following to your `.env` file:

```env
# Firebase Configuration
FIREBASE_CREDENTIALS_PATH="${PWD}/storage/app/firebase-credentials.json"

# Optional: Firebase Project Settings (from Firebase Console)
FIREBASE_PROJECT_ID="your-project-id"
FIREBASE_DATABASE_URL="https://your-project-id.firebaseio.com"
```

## Step 5: Set Up Mobile App (React Native)

### Install Required Packages:

```bash
npm install @react-native-firebase/app @react-native-firebase/messaging
# or
yarn add @react-native-firebase/app @react-native-firebase/messaging
```

### iOS Additional Setup:

1. Run `cd ios && pod install`
2. Add Push Notification capability in Xcode:
   - Select your project in Xcode
   - Go to "Signing & Capabilities"
   - Click "+ Capability" → "Push Notifications"
   - Click "+ Capability" → "Background Modes"
   - Check "Remote notifications"

3. In `AppDelegate.m` or `AppDelegate.swift`, add:

```swift
// AppDelegate.swift
import Firebase

// In didFinishLaunchingWithOptions
FirebaseApp.configure()
```

### Android Additional Setup:

Ensure your `android/build.gradle` has:

```gradle
buildscript {
    dependencies {
        classpath 'com.google.gms:google-services:4.3.15'
    }
}
```

And in `android/app/build.gradle`:

```gradle
apply plugin: 'com.google.gms.google-services'
```

## Step 6: Initialize Firebase in React Native App

Create a notification service in your React Native app:

```javascript
// services/NotificationService.js
import messaging from '@react-native-firebase/messaging';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_BASE_URL } from '../config';

class NotificationService {
  async requestPermission() {
    const authStatus = await messaging().requestPermission();
    const enabled =
      authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
      authStatus === messaging.AuthorizationStatus.PROVISIONAL;

    if (enabled) {
      console.log('Authorization status:', authStatus);
      return true;
    }
    return false;
  }

  async getToken() {
    try {
      const token = await messaging().getToken();
      if (token) {
        await this.sendTokenToServer(token);
        return token;
      }
    } catch (error) {
      console.error('Error getting FCM token:', error);
    }
  }

  async sendTokenToServer(token) {
    try {
      const authToken = await AsyncStorage.getItem('auth_token');
      const response = await fetch(`${API_BASE_URL}/api/v1/notifications/fcm-token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`,
        },
        body: JSON.stringify({
          fcm_token: token,
          device_type: Platform.OS, // 'ios' or 'android'
          device_info: {
            os_version: Platform.Version,
            app_version: DeviceInfo.getVersion(),
            device_model: DeviceInfo.getModel(),
          }
        }),
      });
      
      const data = await response.json();
      console.log('Token registered:', data);
    } catch (error) {
      console.error('Error sending token to server:', error);
    }
  }

  async handleNotification(remoteMessage) {
    // Parse the notification data
    const { data } = remoteMessage;
    const navigation = data.navigation ? JSON.parse(data.navigation) : null;
    
    if (navigation) {
      // Navigate to the appropriate screen
      this.navigateToScreen(navigation);
    }
  }

  navigateToScreen(navigation) {
    const { screen, params, tab } = navigation;
    
    // Use your navigation library (e.g., React Navigation)
    // This is a simplified example
    switch (screen) {
      case 'PostDetail':
        NavigationService.navigate('PostDetail', { postId: params.post_id });
        break;
      case 'EventDetail':
        NavigationService.navigate('EventDetail', { eventId: params.event_id });
        break;
      case 'JobDetail':
        NavigationService.navigate('JobDetail', { jobId: params.job_id });
        break;
      case 'ChatConversation':
        NavigationService.navigate('Chat', { userId: params.sender_id });
        break;
      // Add more cases as needed
      default:
        NavigationService.navigate('Notifications');
    }
  }

  setupNotificationListeners() {
    // Foreground notification handler
    messaging().onMessage(async remoteMessage => {
      console.log('Foreground notification:', remoteMessage);
      // Show local notification or update UI
      this.showLocalNotification(remoteMessage);
    });

    // Background/Quit notification handler
    messaging().setBackgroundMessageHandler(async remoteMessage => {
      console.log('Background notification:', remoteMessage);
      // Update badge count or other background tasks
    });

    // When app is opened from notification
    messaging().onNotificationOpenedApp(remoteMessage => {
      console.log('Notification opened app:', remoteMessage);
      this.handleNotification(remoteMessage);
    });

    // Check if app was opened from notification (when app was closed)
    messaging()
      .getInitialNotification()
      .then(remoteMessage => {
        if (remoteMessage) {
          console.log('Initial notification:', remoteMessage);
          this.handleNotification(remoteMessage);
        }
      });
  }

  async showLocalNotification(remoteMessage) {
    // Use a local notification library like react-native-push-notification
    // to show notification when app is in foreground
  }
}

export default new NotificationService();
```

## Step 7: Initialize in App Component

In your main App component:

```javascript
// App.js
import React, { useEffect } from 'react';
import NotificationService from './services/NotificationService';

function App() {
  useEffect(() => {
    // Request permission and get token
    NotificationService.requestPermission().then(granted => {
      if (granted) {
        NotificationService.getToken();
      }
    });

    // Setup notification listeners
    NotificationService.setupNotificationListeners();
  }, []);

  // ... rest of your app
}
```

## Step 8: Testing Notifications

### Test from Laravel Backend:

```bash
# Run migrations
php artisan migrate

# Test notification sending (in tinker)
php artisan tinker

>>> $user = App\Models\User::where('fcm_token', '!=', null)->first();
>>> $service = app(App\Services\MobileNotificationService::class);
>>> $service->sendTestNotification($user);
```

### Test from API:

```bash
# Send test notification via API
curl -X POST https://your-domain.com/api/v1/notifications/test \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Content-Type: application/json"
```

## Step 9: Notification Channels Setup (Android)

For Android 8.0+ (API level 26+), create notification channels in your app:

```javascript
// In your Android-specific code
import PushNotification from 'react-native-push-notification';

PushNotification.createChannel(
  {
    channelId: "social",
    channelName: "Social Notifications",
    channelDescription: "Likes, comments, and mentions",
    soundName: "default",
    importance: 4,
    vibrate: true,
  },
  (created) => console.log(`createChannel 'social' returned '${created}'`)
);

// Create channels for each category: events, jobs, hackathons, messages, etc.
```

## Step 10: Troubleshooting

### Common Issues:

1. **No notifications received:**
   - Check if FCM token is saved in database
   - Verify Firebase credentials are correct
   - Check device has internet connection
   - Ensure app has notification permissions

2. **iOS specific issues:**
   - Ensure Push Notification entitlement is added
   - Check APN certificates are configured in Firebase Console
   - Verify Background Modes are enabled

3. **Android specific issues:**
   - Check google-services.json is in correct location
   - Verify package name matches Firebase configuration
   - Ensure notification channels are created for Android 8.0+

4. **Laravel backend issues:**
   - Check Firebase credentials file exists and is readable
   - Verify FIREBASE_CREDENTIALS_PATH in .env
   - Check Laravel logs for Firebase errors

### Debug Commands:

```bash
# Check if Firebase service is working
php artisan tinker
>>> $firebase = app(App\Services\FirebaseNotificationService::class);
>>> // Should not throw error

# Check user FCM tokens
>>> App\Models\User::whereNotNull('fcm_token')->count();

# View notification logs
tail -f storage/logs/laravel.log | grep -i notification
```

## Security Notes

1. **Never commit** `firebase-credentials.json` to version control
2. Add to `.gitignore`:
   ```
   storage/app/firebase-credentials.json
   android/app/google-services.json
   ios/GoogleService-Info.plist
   ```

3. Store credentials securely in production environment
4. Use environment-specific Firebase projects (dev, staging, production)
5. Rotate service account keys periodically
6. Monitor Firebase usage and quotas in Firebase Console

## API Endpoints for Mobile App

The following notification endpoints are available:

- `POST /api/v1/notifications/fcm-token` - Register device token
- `DELETE /api/v1/notifications/fcm-token` - Remove device token
- `GET /api/v1/notifications` - List notifications
- `GET /api/v1/notifications/unread-count` - Get unread count
- `GET /api/v1/notifications/{id}/details` - Get notification with navigation data
- `PATCH /api/v1/notifications/{id}/read` - Mark as read
- `PATCH /api/v1/notifications/read-all` - Mark all as read
- `POST /api/v1/notifications/preferences` - Update preferences
- `POST /api/v1/notifications/test` - Send test notification (for debugging)

## Support

For issues or questions:
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check Firebase Console for delivery reports
3. Use Firebase Cloud Messaging test feature in console
4. Review mobile app console logs for client-side issues
