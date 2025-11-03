# Asset Management Guide

**Last Updated:** November 3, 2025

This guide explains the **centralized asset management system** for handling all files and images in the People Of Data application.

---

## 🎯 The Problem This Solves

Previously, image paths were hardcoded throughout the application using inconsistent methods:
- `asset('storage/assets/logo.png')` ❌
- `Storage::url($path)` ❌
- `{{ $user->avatar }}` ❌
- Manual URL construction ❌

This caused:
- ✗ Broken images after refactoring
- ✗ Inconsistent path handling
- ✗ Difficult maintenance
- ✗ No single source of truth

---

## ✅ The Solution: Centralized Helpers

We now have **ONE standard way** to handle all files:

### 1. Static Assets (Logos, Icons, UI Images)
```blade
{{-- Old (Don't use) --}}
<img src="{{ asset('storage/assets/pod-logo.png') }}">

{{-- New (Use this) --}}
<img src="{{ static_asset('pod-logo.png') }}">
```

**Location:** `public/assets/`  
**Use for:** Logos, icons, UI images, background patterns  
**Function:** `static_asset($path)`

### 2. User Uploads (Avatars, Posts, Files)
```blade
{{-- Old (Don't use) --}}
<img src="{{ asset('storage/' . $post->image) }}">
<img src="{{ Storage::url($event->banner) }}">

{{-- New (Use this) --}}
<img src="{{ uploaded_file($post->image) }}">
<img src="{{ uploaded_file($event->banner) }}">
```

**Location:** `storage/app/public/`  
**Use for:** User avatars, post images, event banners, hackathon covers, chat attachments  
**Function:** `uploaded_file($path)`

### 3. User Avatars (With Fallback)
```blade
{{-- Old (Don't use) --}}
<img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/...' }}">

{{-- New (Use this) --}}
<img src="{{ user_avatar($user) }}">
{{-- Or use the avatar component which handles this automatically --}}
<x-avatar :src="$user->avatar" :name="$user->name" />
```

**Handles:** Automatic fallback to initials if avatar is missing  
**Function:** `user_avatar($user)`

---

## 📚 Complete Function Reference

### `static_asset($path)`
```php
// Returns URL to static assets in public/assets/
static_asset('pod-logo.png')          // → /assets/pod-logo.png
static_asset('icons/home.svg')        // → /assets/icons/home.svg
static_asset('chat-bg-pattern.png')   // → /assets/chat-bg-pattern.png
```

### `uploaded_file($path)`
```php
// Returns URL to uploaded files in storage/
uploaded_file('avatars/avatar_1_123.jpg')  // → /storage/avatars/avatar_1_123.jpg
uploaded_file('posts/abc123.png')          // → /storage/posts/abc123.png
uploaded_file(null)                        // → null (safe)

// Handles backward compatibility
uploaded_file('/storage/avatars/old.jpg')  // → /storage/avatars/old.jpg
uploaded_file('http://cdn.com/file.jpg')   // → http://cdn.com/file.jpg (unchanged)
```

### `user_avatar($user)`
```php
// Returns user avatar URL with automatic fallback
user_avatar($user)              // → /storage/avatars/avatar_1_123.jpg
user_avatar($userWithoutAvatar) // → https://ui-avatars.com/api/?name=...
user_avatar(null)               // → https://ui-avatars.com/api/?name=User
```

---

## 🔧 Usage Examples

### Landing Page Hero
```blade
{{-- Background image --}}
<div style="background-image: url('{{ static_asset('images/hero-background.png') }}')"></div>

{{-- Section images --}}
<img src="{{ static_asset('network-growth.png') }}" alt="Networking">
<img src="{{ static_asset('knowledge-exchange.png') }}" alt="Knowledge">
<img src="{{ static_asset('career-growth.png') }}" alt="Career">
```

### Post Cards
```blade
@if($post->images)
    @foreach($post->images as $image)
        <img src="{{ uploaded_file($image) }}" alt="Post image">
    @endforeach
@endif
```

### Event Banners
```blade
@if($event->banner_image)
    <img src="{{ uploaded_file($event->banner_image) }}" alt="{{ $event->title }}">
@else
    {{-- Fallback UI --}}
    <div class="placeholder-icon">
        <i class="ri-calendar-event-line"></i>
    </div>
@endif
```

### Hackathon Covers
```blade
@if($hackathon->cover_image)
    <img src="{{ uploaded_file($hackathon->cover_image) }}" alt="{{ $hackathon->title }}">
@else
    {{-- Fallback UI --}}
@endif
```

### User Avatars
```blade
{{-- Option 1: Use the avatar component (RECOMMENDED) --}}
<x-avatar 
    :src="$user->avatar" 
    :name="$user->name" 
    :color="$user->avatar_color" 
    size="md" />

{{-- Option 2: Direct helper (if you need just the URL) --}}
<img src="{{ user_avatar($user) }}" alt="{{ $user->name }}">

{{-- Option 3: Use the accessor (Model) --}}
<img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
```

### Chat Background
```blade
<div style="background-image: url('{{ static_asset('chat-bg-pattern.png') }}'); background-repeat: repeat;"></div>
```

---

## 🏗️ Backend Implementation

### When Saving Files

Always save **relative paths** to the database:

```php
// ✅ CORRECT: Save relative path
$user->avatar = 'avatars/avatar_1_1234567890.jpg';

// ❌ WRONG: Don't save absolute paths
$user->avatar = '/storage/avatars/avatar_1_1234567890.jpg';  // NO!
$user->avatar = asset('storage/avatars/avatar_1_1234567890.jpg');  // NO!
```

### Storage Examples

```php
// Avatars
$filename = 'avatar_' . $user->id . '_' . time() . '.jpg';
Storage::disk('public')->put('avatars/' . $filename, $imageContent);
$user->avatar = 'avatars/' . $filename;  // Save this to DB

// Post Images
$path = $request->file('image')->store('posts', 'public');
$post->images = [$path];  // e.g., ['posts/abc123.jpg']

// Event Banners
$path = $request->file('banner')->store('events/banners', 'public');
$event->banner_image = $path;  // e.g., 'events/banners/xyz789.jpg'

// Hackathon Covers
$path = $request->file('cover')->store('hackathons/covers', 'public');
$hackathon->cover_image = $path;  // e.g., 'hackathons/covers/def456.jpg'
```

---

## 🚫 DO NOT Use These Anymore

```php
// ❌ Don't use asset() for uploaded files
asset('storage/' . $path)

// ❌ Don't use Storage::url() in views
Storage::url($path)

// ❌ Don't hardcode /storage prefix
'/storage/' . $path

// ❌ Don't concatenate asset paths
asset('storage/assets/' . $filename)
```

---

## 🎨 Avatar Component

The avatar component (`<x-avatar>`) automatically handles all path logic:

```blade
<x-avatar 
    :src="$user->avatar"           {{-- Raw database value --}}
    :name="$user->name"            {{-- For initials fallback --}}
    :color="$user->avatar_color"   {{-- Custom color --}}
    size="md"                      {{-- sm|md|lg|xl --}}
    class="border-2" />            {{-- Additional classes --}}
```

The component internally uses the helper system, so you just pass the raw avatar value.

---

## 📦 Technical Details

### Helper Files
- **Class:** `app/Helpers/AssetHelper.php`
- **Functions:** `app/Helpers/helpers.php`
- **Registration:** `composer.json` (autoload → files)

### Class Methods
```php
use App\Helpers\AssetHelper;

AssetHelper::staticAsset($path);
AssetHelper::uploadedFile($path);
AssetHelper::userAvatar($user);
AssetHelper::postImage($path);
AssetHelper::eventBanner($path);
AssetHelper::hackathonCover($path);
AssetHelper::chatAttachment($path);
```

### User Model Accessor
```php
// User.php
public function getAvatarUrlAttribute(): string
{
    return user_avatar($this);
}

// Usage
$user->avatar_url  // Always returns a valid URL
```

---

## 🔄 Migration Path

If you're updating old code:

1. **Search for:** `asset('storage/`
   - **Replace with:** `uploaded_file(`

2. **Search for:** `Storage::url(`
   - **Replace with:** `uploaded_file(`

3. **Search for:** `asset('storage/assets/`
   - **Replace with:** `static_asset(`

4. **Search for:** Manual avatar handling
   - **Replace with:** `user_avatar($user)` or `<x-avatar>`

---

## ✅ Benefits

1. **Consistency** - One way to handle all files
2. **Maintainability** - Easy to change storage location or CDN
3. **Reliability** - No more broken images after refactoring
4. **Flexibility** - Handles old paths, new paths, URLs, and null values
5. **Developer Experience** - Clear, semantic function names

---

**Remember:** When in doubt, check this guide. If you're hardcoding a path, you're probably doing it wrong!


