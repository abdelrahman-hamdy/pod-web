# 📱 Mobile App Real-Time Chat Setup

Guide for integrating real-time chat into your mobile app (iOS/Android) using the same Reverb WebSocket server.

## 📋 Overview

Your Laravel app exposes a broadcasting endpoint that mobile apps can connect to using Pusher-compatible SDKs. Since Reverb is Pusher-compatible, mobile apps can use Pusher SDKs.

## 🔑 API Endpoints

### Broadcasting Authentication

**Endpoint:** `POST /api/v1/broadcasting/auth`

**Headers:**
```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

**Body:**
```json
{
  "socket_id": "123.456",
  "channel_name": "private-chatify.123"
}
```

**Response:**
```json
{
  "auth": "reverb-key:signature",
  "channel_data": "..."
}
```

## 📦 Mobile SDK Setup

### iOS (Swift)

1. **Install Pusher SDK:**
```swift
// Package.swift or Podfile
dependencies: [
    .package(url: "https://github.com/pusher/pusher-websocket-swift.git", from: "10.0.0")
]
```

2. **Configure Pusher:**
```swift
import PusherSwift

class ChatManager {
    var pusher: Pusher!
    
    func setupPusher() {
        let options = PusherClientOptions(
            host: .host("your-domain.com"),
            port: 443,
            useTLS: true
        )
        
        pusher = Pusher(
            key: "YOUR_REVERB_APP_KEY",
            options: options
        )
        
        pusher.connection.delegate = self
        pusher.connect()
    }
    
    func subscribeToChat(userId: Int) {
        let channel = pusher.subscribe("private-chatify.\(userId)")
        
        channel.bind(eventName: "messaging") { (event: PusherEvent) -> Void in
            if let data = event.data {
                // Handle new message
                print("New message: \(data)")
            }
        }
    }
}
```

3. **Authentication:**
```swift
func authenticate(channelName: String, socketId: String) {
    let url = URL(string: "https://your-domain.com/api/v1/broadcasting/auth")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("Bearer \(authToken)", forHTTPHeaderField: "Authorization")
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let body = [
        "socket_id": socketId,
        "channel_name": channelName
    ]
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        // Handle auth response
    }.resume()
}
```

### Android (Kotlin/Java)

1. **Add Pusher Dependency:**
```gradle
// build.gradle
dependencies {
    implementation 'com.pusher:pusher-java-client:2.4.0'
}
```

2. **Configure Pusher:**
```kotlin
import com.pusher.client.Pusher
import com.pusher.client.PusherOptions

class ChatManager {
    private var pusher: Pusher? = null
    
    fun setupPusher() {
        val options = PusherOptions().apply {
            setHost("your-domain.com")
            setPort(443)
            isEncrypted = true
        }
        
        pusher = Pusher("YOUR_REVERB_APP_KEY", options)
        pusher?.connect()
    }
    
    fun subscribeToChat(userId: Int) {
        val channel = pusher?.subscribe("private-chatify.$userId")
        
        channel?.bind("messaging") { event ->
            // Handle new message
            val message = event.data
            println("New message: $message")
        }
    }
}
```

3. **Authentication:**
```kotlin
fun authenticate(channelName: String, socketId: String, token: String) {
    val url = "https://your-domain.com/api/v1/broadcasting/auth"
    val client = OkHttpClient()
    
    val body = JSONObject().apply {
        put("socket_id", socketId)
        put("channel_name", channelName)
    }
    
    val request = Request.Builder()
        .url(url)
        .post(body.toString().toRequestBody("application/json".toMediaType()))
        .addHeader("Authorization", "Bearer $token")
        .build()
    
    client.newCall(request).enqueue(object : Callback {
        override fun onResponse(call: Call, response: Response) {
            // Handle auth response
        }
        override fun onFailure(call: Call, e: IOException) {
            // Handle error
        }
    })
}
```

### React Native

1. **Install Pusher:**
```bash
npm install pusher-js
```

2. **Setup:**
```javascript
import Pusher from 'pusher-js';

class ChatManager {
  constructor(authToken) {
    this.pusher = new Pusher('YOUR_REVERB_APP_KEY', {
      wsHost: 'your-domain.com',
      wsPort: 443,
      wssPort: 443,
      forceTLS: true,
      enabledTransports: ['ws', 'wss'],
      authEndpoint: 'https://your-domain.com/api/v1/broadcasting/auth',
      auth: {
        headers: {
          'Authorization': `Bearer ${authToken}`,
        },
      },
    });
  }

  subscribeToChat(userId) {
    const channel = this.pusher.subscribe(`private-chatify.${userId}`);
    
    channel.bind('messaging', (data) => {
      console.log('New message:', data);
      // Handle new message
    });
  }
}
```

### Flutter (Dart)

1. **Add Pusher Dependency:**
```yaml
# pubspec.yaml
dependencies:
  pusher_channels_flutter: ^2.0.0
```

2. **Setup:**
```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

class ChatManager {
  late PusherChannelsFlutter pusher;
  
  Future<void> setupPusher() async {
    pusher = PusherChannelsFlutter.getInstance();
    
    await pusher.init(
      apiKey: "YOUR_REVERB_APP_KEY",
      cluster: "mt1", // Not used with Reverb, but required
      onConnectionStateChange: onConnectionStateChange,
      onError: onError,
      onSubscriptionSucceeded: onSubscriptionSucceeded,
      onEvent: onEvent,
      onSubscriptionError: onSubscriptionError,
      endpoint: "https://your-domain.com",
      authEndpoint: "https://your-domain.com/api/v1/broadcasting/auth",
    );
    
    await pusher.connect();
  }
  
  void subscribeToChat(int userId) async {
    await pusher.subscribe(
      channelName: "private-chatify.$userId",
    );
  }
  
  void onEvent(PusherEvent event) {
    if (event.eventName == "messaging") {
      print("New message: ${event.data}");
    }
  }
}
```

## 🔐 Authentication Flow

1. User logs in via API → receives Sanctum token
2. Mobile app stores token
3. When connecting to WebSocket:
   - Pusher SDK requests auth via `/api/v1/broadcasting/auth`
   - API validates Sanctum token
   - API returns signed auth response
   - Pusher SDK connects to Reverb

## 📨 Sending Messages via API

**Endpoint:** `POST /chat/api/sendMessage`

**Headers:**
```
Authorization: Bearer {sanctum_token}
Content-Type: multipart/form-data
```

**Body:**
```
id: {recipient_user_id}
message: {message_text}
file: {optional_attachment}
```

**Response:**
```json
{
  "status": "200",
  "message": "<html>message card</html>",
  "tempID": "temp-123"
}
```

## 🔔 Receiving Messages

Messages are received in real-time via WebSocket:

**Event:** `messaging`

**Channel:** `private-chatify.{userId}`

**Payload:**
```json
{
  "from_id": 1,
  "to_id": 2,
  "message": "<html>message card</html>"
}
```

## ✅ Testing Checklist

- [ ] Mobile app connects to Reverb
- [ ] Authentication endpoint works
- [ ] Can subscribe to private channels
- [ ] Can receive messages in real-time
- [ ] Can send messages via API
- [ ] Messages appear instantly on both web and mobile

## 🔍 Debugging

**Check WebSocket connection:**
- Mobile SDK should log connection status
- Check Reverb logs: `storage/logs/reverb.log`
- Check Laravel logs for auth errors

**Common Issues:**
- **Auth fails:** Check Sanctum token is valid
- **Can't connect:** Check `REVERB_HOST` matches domain
- **No messages:** Check channel name matches: `private-chatify.{userId}`

---

## 📚 Additional Resources

- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Pusher iOS SDK](https://github.com/pusher/pusher-websocket-swift)
- [Pusher Android SDK](https://github.com/pusher/pusher-java-client)
- [Pusher React Native](https://github.com/pusher/pusher-js)
- [Flutter Pusher](https://pub.dev/packages/pusher_channels_flutter)

---

**Need help?** Check that your API broadcasting auth endpoint is working by testing it with Postman/curl first.
