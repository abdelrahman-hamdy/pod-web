# Project File Structure

**Last Updated:** November 3, 2025

This document defines the official file organization structure for the People Of Data web application.

---

## 📁 Static Assets (Public Directory)

**Location:** `public/assets/`  
**Purpose:** Static files that don't change (logos, icons, UI images)  
**Access Method:** `asset('assets/filename.ext')`  
**URL Pattern:** `https://your-domain.com/assets/filename.ext`

### Current Static Assets:
```
public/assets/
├── pod-logo.png              # Main application logo
├── career-growth.png         # Landing page icon
├── knowledge-exchange.png    # Landing page icon
├── network-growth.png        # Landing page icon
├── chat-bg-pattern.png       # Chat background pattern
├── chat-bg-pattern2.png      # Chat background pattern (variant)
└── chat-bg-pattern3.png      # Chat background pattern (variant)
```

---

## 📁 User-Generated Content (Storage Directory)

**Location:** `storage/app/public/`  
**Purpose:** Files uploaded by users (avatars, post images, documents)  
**Access Method:** Via `/storage` symlink (created by `php artisan storage:link`)  
**URL Pattern:** `https://your-domain.com/storage/path/to/file.ext`

### User Upload Categories:

#### 1. User Avatars
```
storage/app/public/avatars/
└── avatar_{user_id}_{timestamp}.jpg
```
- **Format:** Always converted to JPEG (400x400px)
- **Naming:** `avatar_{user_id}_{timestamp}.jpg`
- **Database Storage:** Just the filename, e.g., `avatars/avatar_1_1761690049.jpg`
- **URL Generation:** Handled by `User->avatar_url` accessor

#### 2. Post Images
```
storage/app/public/posts/
└── {random_hash}.{ext}
```
- **Formats:** jpg, png, gif, webp
- **Naming:** Laravel's random hash naming
- **Max Size:** 2MB per image

#### 3. Chat Attachments
```
storage/app/public/attachments/
└── {uuid}.{ext}
```
- **Formats:** Images and documents
- **Naming:** UUID format

#### 4. Event Banners
```
storage/app/public/events/banners/
└── {random_hash}.{ext}
```

#### 5. Hackathon Content
```
storage/app/public/hackathons/
└── covers/
    └── {random_hash}.{ext}

storage/app/public/hackathon_projects/{team_id}/
└── {unique_id}_{timestamp}.{ext}
```

---

## 🚫 Deprecated/Removed Directories

The following directories have been removed from the codebase:

- ❌ `storage/app/public/assets/` - Moved to `public/assets/`
- ❌ `storage/app/public/users-avatar/` - Consolidated into `avatars/`
- ❌ `resources/views/temp/` - Old HTML templates removed
- ❌ `jules-scratch/` - Temporary verification scripts removed
- ❌ Chatify backup files (`*-original-backup.blade.php`)

---

## 📝 Important Rules

### For Developers:

1. **Static Assets**
   - Always place in `public/assets/`
   - Reference with `asset('assets/filename.ext')`
   - These should be committed to Git

2. **User Uploads**
   - Always use `Storage::disk('public')->put()`
   - Store in appropriate subdirectory (`avatars/`, `posts/`, etc.)
   - **Never commit these to Git** (already in `.gitignore`)

3. **Avatar Handling**
   - Use `User->avatar_url` accessor for displaying
   - Store relative path in database (e.g., `avatars/filename.jpg`)
   - Avatar component handles URL generation automatically

### For Deployment:

1. **On Server:**
   ```bash
   # Create the storage symlink
   php artisan storage:link
   
   # If exec() is disabled, create manually:
   ln -sfn ../storage/app/public storage
   ```

2. **Pull Updates:**
   ```bash
   git pull origin main
   php artisan config:clear
   php artisan view:clear
   ```

3. **File Permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

---

## 🔗 Related Documentation

- Laravel Storage: https://laravel.com/docs/11.x/filesystem
- Asset Bundling: https://laravel.com/docs/11.x/vite
- File Uploads: https://laravel.com/docs/11.x/requests#files

---

**Last Verified:** November 3, 2025  
**Verified By:** System Audit & Cleanup

