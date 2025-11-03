# 📱 People Of Data - Complete API Documentation

Complete REST API documentation for mobile app development.

**Base URL:** `https://lightgrey-echidna-227060.hostingersite.com/api/v1`  
**Version:** 1.0  
**Authentication:** Bearer Token (Laravel Sanctum)

---

## 🔐 Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your_token_here}
```

---

## 📋 Table of Contents

1. [Authentication](#authentication)
2. [Users](#users)
3. [Profile Management](#profile-management)
4. [Posts](#posts)
5. [Comments](#comments)
6. [Events](#events)
7. [Job Listings](#job-listings)
8. [Hackathons](#hackathons)
9. [Internships](#internships)
10. [Notifications](#notifications)
11. [Search](#search)
12. [Chat/Messaging](#chatmessaging)
13. [Real-time Broadcasting](#real-time-broadcasting)

---

## 🔐 Authentication

### Register User
**POST** `/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|xxxxxxxxxxxx"
  }
}
```

---

### Login
**POST** `/auth/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "secret123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxx"
  }
}
```

---

### Logout
**POST** `/auth/logout`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Get Current User
**GET** `/auth/me`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "profile": { ... }
  }
}
```

---

### Forgot Password
**POST** `/auth/forgot-password`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

---

### Reset Password
**POST** `/auth/reset-password`

**Request Body:**
```json
{
  "email": "john@example.com",
  "token": "reset_token",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

---

### Verify Email
**GET** `/auth/verify-email/{id}/{hash}`

---

### Resend Verification
**POST** `/auth/resend-verification`  
**Auth Required:** Yes

---

## 👥 Users

### List Users
**GET** `/users`  
**Auth Required:** Yes

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `search` - Search query

---

### Get User Profile
**GET** `/users/{user}`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "bio": "Data scientist",
    "avatar": "/storage/avatars/avatar.jpg",
    "profile_completed": true
  }
}
```

---

### Search Users
**GET** `/users/search`  
**Auth Required:** Yes

**Query Parameters:**
- `q` - Search query (required)
- `page` - Page number
- `per_page` - Items per page

---

## 👤 Profile Management

### Get Profile
**GET** `/profile`  
**Auth Required:** Yes

---

### Update Profile
**PUT** `/profile`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "name": "John Doe",
  "bio": "Updated bio",
  "location": "Cairo, Egypt",
  "skills": ["Python", "Machine Learning"]
}
```

---

### Get Profile Progress
**GET** `/profile/progress`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "data": {
    "completion_percentage": 75,
    "completed_fields": ["name", "email", "bio"],
    "missing_fields": ["location", "skills"]
  }
}
```

---

### Complete Profile
**POST** `/profile/complete`  
**Auth Required:** Yes

---

### Upload Avatar
**POST** `/profile/avatar`  
**Auth Required:** Yes  
**Content-Type:** `multipart/form-data`

**Request:**
- `avatar` - Image file (max 2MB)

---

### Profile Experiences

**GET** `/profile/experiences/{experience}`  
**POST** `/profile/experiences`  
**PUT** `/profile/experiences/{experience}`  
**DELETE** `/profile/experiences/{experience}`  
**Auth Required:** Yes

---

### Profile Portfolios

**GET** `/profile/portfolios/{portfolio}`  
**POST** `/profile/portfolios`  
**PUT** `/profile/portfolios/{portfolio}`  
**DELETE** `/profile/portfolios/{portfolio}`  
**Auth Required:** Yes

---

## 📝 Posts

### List Posts
**GET** `/posts`  
**Public:** Yes (no auth required)

**Query Parameters:**
- `page` - Page number
- `per_page` - Items per page
- `search` - Search query

---

### Get Post
**GET** `/posts/{post}`  
**Public:** Yes

---

### Create Post
**POST** `/posts`  
**Auth Required:** Yes  
**Content-Type:** `multipart/form-data`

**Request:**
```json
{
  "content": "Post content",
  "type": "text|image|poll",
  "images": [File, File],
  "poll_question": "Question?",
  "poll_options": ["Option 1", "Option 2"]
}
```

---

### Update Post
**PUT** `/posts/{post}`  
**Auth Required:** Yes (owner only)

---

### Delete Post
**DELETE** `/posts/{post}`  
**Auth Required:** Yes (owner only)

---

### Like Post
**POST** `/posts/{post}/like`  
**Auth Required:** Yes

---

### Share Post
**POST** `/posts/{post}/share`  
**Auth Required:** Yes

---

### Vote on Poll
**POST** `/posts/{post}/vote`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "option_id": 1
}
```

---

## 💬 Comments

### Get Comments
**GET** `/posts/{post}/comments`  
**Auth Required:** Yes

---

### Add Comment
**POST** `/posts/{post}/comments`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "body": "Comment text"
}
```

---

### Update Comment
**PUT** `/comments/{comment}`  
**Auth Required:** Yes (owner only)

---

### Delete Comment
**DELETE** `/comments/{comment}`  
**Auth Required:** Yes (owner only)

---

## 🎉 Events

### List Events
**GET** `/events`  
**Public:** Yes

---

### Get Event
**GET** `/events/{event}`  
**Public:** Yes

---

### Create Event
**POST** `/events`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "title": "Event Title",
  "description": "Event description",
  "date": "2025-12-01",
  "time": "18:00",
  "location": "Cairo, Egypt",
  "max_attendees": 100
}
```

---

### Update Event
**PUT** `/events/{event}`  
**Auth Required:** Yes (owner/admin only)

---

### Delete Event
**DELETE** `/events/{event}`  
**Auth Required:** Yes (owner/admin only)

---

### Register for Event
**POST** `/events/{event}/register`  
**Auth Required:** Yes

---

### Cancel Registration
**DELETE** `/events/{event}/register`  
**Auth Required:** Yes

---

### Check In
**POST** `/events/{event}/check-in`  
**Auth Required:** Yes

---

### Get Registrations
**GET** `/events/{event}/registrations`  
**Auth Required:** Yes (admin/client only)

---

## 💼 Job Listings

### List Jobs
**GET** `/jobs`  
**Public:** Yes

---

### Get Job
**GET** `/jobs/{job}`  
**Public:** Yes

---

### Create Job
**POST** `/jobs`  
**Auth Required:** Yes (admin/client only)

---

### Update Job
**PUT** `/jobs/{job}`  
**Auth Required:** Yes (admin/client only)

---

### Delete Job
**DELETE** `/jobs/{job}`  
**Auth Required:** Yes (admin/client only)

---

### Apply for Job
**POST** `/jobs/{job}/apply`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "cover_letter": "Cover letter text",
  "resume": File (optional)
}
```

---

### Get My Applications
**GET** `/jobs/my-applications`  
**Auth Required:** Yes

---

### Close Job
**PATCH** `/jobs/{job}/close`  
**Auth Required:** Yes (admin/client only)

---

### Reopen Job
**PATCH** `/jobs/{job}/reopen`  
**Auth Required:** Yes (admin/client only)

---

### Archive Job
**PATCH** `/jobs/{job}/archive`  
**Auth Required:** Yes (admin/client only)

---

### Get Applications
**GET** `/jobs/{job}/applications`  
**Auth Required:** Yes (admin/client only)

---

### Review Application
**PATCH** `/jobs/applications/{application}/review`  
**Auth Required:** Yes (admin/client only)

---

### Accept Application
**PATCH** `/jobs/applications/{application}/accept`  
**Auth Required:** Yes (admin/client only)

---

### Reject Application
**PATCH** `/jobs/applications/{application}/reject`  
**Auth Required:** Yes (admin/client only)

---

### Update Application Notes
**PATCH** `/jobs/applications/{application}/notes`  
**Auth Required:** Yes (admin/client only)

---

## 🏆 Hackathons

### List Hackathons
**GET** `/hackathons`  
**Public:** Yes

---

### Get Hackathon
**GET** `/hackathons/{hackathon}`  
**Public:** Yes

---

### Create Hackathon
**POST** `/hackathons`  
**Auth Required:** Yes (admin/client only)

---

### Update Hackathon
**PUT** `/hackathons/{hackathon}`  
**Auth Required:** Yes (admin/client only)

---

### Delete Hackathon
**DELETE** `/hackathons/{hackathon}`  
**Auth Required:** Yes (admin/client only)

---

### Register for Hackathon
**POST** `/hackathons/{hackathon}/register`  
**Auth Required:** Yes

---

### Get Teams
**GET** `/hackathons/{hackathon}/teams`  
**Auth Required:** Yes

---

### Join Team
**POST** `/hackathons/{hackathon}/join-team`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "team_id": 1
}
```

---

### Leave Team
**DELETE** `/hackathons/{hackathon}/leave-team`  
**Auth Required:** Yes

---

### Team Management

**GET** `/hackathons/teams` - Get my teams  
**POST** `/hackathons/teams` - Create team  
**PUT** `/hackathons/teams/{team}` - Update team  
**DELETE** `/hackathons/teams/{team}` - Delete team  
**POST** `/hackathons/teams/{team}/invite` - Invite member  
**POST** `/hackathons/teams/{team}/join-request` - Request to join

**Auth Required:** Yes

---

### Invitations & Requests

**POST** `/hackathons/invitations/{invitation}/accept`  
**POST** `/hackathons/invitations/{invitation}/reject`  
**POST** `/hackathons/join-requests/{request}/accept`  
**POST** `/hackathons/join-requests/{request}/reject`  
**Auth Required:** Yes

---

## 📚 Internships

### List Internships
**GET** `/internships`  
**Public:** Yes

---

### Get Internship
**GET** `/internships/{internship}`  
**Public:** Yes

---

### Apply for Internship
**POST** `/internships/apply`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "internship_id": 1,
  "cover_letter": "Cover letter text"
}
```

---

### Get My Applications
**GET** `/internships/my-applications`  
**Auth Required:** Yes

---

## 🔔 Notifications

### List Notifications
**GET** `/notifications`  
**Auth Required:** Yes

**Query Parameters:**
- `page` - Page number
- `per_page` - Items per page (default: 10)

---

### Get Unread Count
**GET** `/notifications/unread-count`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

### Mark as Read
**PATCH** `/notifications/{notificationId}/read`  
**Auth Required:** Yes

---

### Mark All as Read
**PATCH** `/notifications/read-all`  
**Auth Required:** Yes

---

### Delete Notification
**DELETE** `/notifications/{notificationId}`  
**Auth Required:** Yes

---

### Clear All Notifications
**DELETE** `/notifications`  
**Auth Required:** Yes

---

### Update Preferences
**POST** `/notifications/preferences`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "email_notifications": true,
  "push_notifications": true,
  "notification_types": {
    "messages": true,
    "posts": false,
    "events": true
  }
}
```

---

### Register FCM Token
**POST** `/notifications/fcm-token`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "token": "fcm_device_token"
}
```

---

### Remove FCM Token
**DELETE** `/notifications/fcm-token`  
**Auth Required:** Yes

---

## 🔍 Search

### Global Search
**GET** `/search`  
**Auth Required:** Yes

**Query Parameters:**
- `q` - Search query (required)
- `type` - Filter by type (posts, events, jobs, hackathons, users)
- `page` - Page number

---

### Search Posts
**GET** `/search/posts`  
**Auth Required:** Yes

---

### Search Events
**GET** `/search/events`  
**Auth Required:** Yes

---

### Search Jobs
**GET** `/search/jobs`  
**Auth Required:** Yes

---

### Search Hackathons
**GET** `/search/hackathons`  
**Auth Required:** Yes

---

### Search Users
**GET** `/search/users`  
**Auth Required:** Yes

---

## 💬 Chat/Messaging

All chat endpoints are prefixed with `/chat/api/` (not `/api/v1/`)

**Base URL:** `https://lightgrey-echidna-227060.hostingersite.com/chat/api`

### Authentication
**POST** `/chat/auth`  
**Auth Required:** Yes (Sanctum token)

**Request Body:**
```json
{
  "socket_id": "123.456",
  "channel_name": "private-chatify.123"
}
```

---

### Get Contacts
**GET** `/chat/api/getContacts`  
**Auth Required:** Yes

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 30)

**Response:**
```json
{
  "contacts": [
    {
      "id": 1,
      "name": "John Doe",
      "avatar": "/storage/avatars/avatar.jpg",
      "last_message": "Hello!",
      "unread_count": 2
    }
  ],
  "total": 10,
  "last_page": 1
}
```

---

### Send Message
**POST** `/chat/api/sendMessage`  
**Auth Required:** Yes  
**Content-Type:** `multipart/form-data`

**Request:**
- `id` - Recipient user ID (required)
- `message` - Message text
- `file` - Optional attachment

**Response:**
```json
{
  "status": "200",
  "message": {
    "id": 123,
    "from_id": 1,
    "to_id": 2,
    "message": "Hello!",
    "created_at": "2025-01-01T12:00:00Z"
  }
}
```

---

### Fetch Messages
**POST** `/chat/api/fetchMessages`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "id": 2,
  "page": 1,
  "per_page": 30
}
```

**Response:**
```json
{
  "total": 50,
  "last_page": 2,
  "last_message_id": 123,
  "messages": [
    {
      "id": 123,
      "from_id": 1,
      "to_id": 2,
      "message": "Hello!",
      "created_at": "2025-01-01T12:00:00Z",
      "seen": false
    }
  ]
}
```

---

### Mark Messages as Seen
**POST** `/chat/api/makeSeen`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "id": 2
}
```

---

### Get User Info
**POST** `/chat/api/idInfo`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "id": 2,
  "type": "user"
}
```

---

### Add to Favorites
**POST** `/chat/api/star`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "user_id": 2
}
```

---

### Get Favorites
**POST** `/chat/api/favorites`  
**Auth Required:** Yes

---

### Search in Chat
**GET** `/chat/api/search`  
**Auth Required:** Yes

**Query Parameters:**
- `input` - Search query
- `page` - Page number
- `per_page` - Items per page

---

### Get Shared Photos
**POST** `/chat/api/shared`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "user_id": 2
}
```

---

### Delete Conversation
**POST** `/chat/api/deleteConversation`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "id": 2
}
```

---

### Update Settings
**POST** `/chat/api/updateSettings`  
**Auth Required:** Yes  
**Content-Type:** `multipart/form-data`

**Request:**
- `avatar` - Optional avatar file
- `messengerColor` - Optional color
- `dark_mode` - Optional (light/dark)

---

### Set Active Status
**POST** `/chat/api/setActiveStatus`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "status": 1
}
```

---

### Get Unread Count
**GET** `/chat/api/unread-count`  
**Auth Required:** Yes

**Response:**
```json
{
  "count": 5
}
```

---

### Download Attachment
**GET** `/chat/api/download/{fileName}`  
**Auth Required:** Yes

---

## 🔌 Real-time Broadcasting

### Authenticate Broadcasting
**POST** `/broadcasting/auth`  
**Auth Required:** Yes

**Request Body:**
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

Used for real-time WebSocket connections (Reverb/Pusher). See `docs/API_MOBILE_SETUP.md` for mobile SDK integration.

---

## 📊 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error message"]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "total": 100,
    "count": 20,
    "per_page": 20,
    "current_page": 1,
    "total_pages": 5
  }
}
```

---

## 🔒 Authentication

All authenticated endpoints require a Bearer token:

```
Authorization: Bearer {token}
```

Obtain token via `/auth/login` or `/auth/register`.

---

## ⚡ Rate Limiting

- **Public routes:** 20 requests/minute
- **Auth routes:** 5 requests/minute
- **Authenticated routes:** 60 requests/minute

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## 🚨 Error Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Server Error

---

## 📱 Mobile App Integration

For complete mobile app setup guide, see:
- `docs/API_MOBILE_SETUP.md` - Mobile SDK setup and real-time integration

---

## 🧪 Testing

**Base URL:** `https://lightgrey-echidna-227060.hostingersite.com/api/v1`

**Test Token:** Get from `/auth/login` endpoint

---

## 📚 Additional Resources

- **Project Overview:** `docs/PROJECT_OVERVIEW.md`
- **Features:** `docs/FEATURES_SPECIFICATIONS.md`
- **Technical Specs:** `docs/TECHNICAL_SPECIFICATIONS.md`
- **Database Schema:** `docs/DATABASE_SCHEMA.md`
- **User Roles:** `docs/USER_ROLES_PERMISSIONS.md`

---

**Last Updated:** 2025-01-03  
**API Version:** 1.0  
**Total Endpoints:** 118+
