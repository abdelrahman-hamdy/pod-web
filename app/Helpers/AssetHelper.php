<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class AssetHelper
{
    /**
     * Get the URL for a static asset (logos, icons, images in public/assets/)
     * 
     * @param string $path Path relative to public/assets/
     * @return string Full URL to the asset
     */
    public static function staticAsset(string $path): string
    {
        return asset('assets/' . ltrim($path, '/'));
    }

    /**
     * Get the URL for an uploaded file (user avatars, posts, etc. in storage/)
     * 
     * @param string|null $path Path relative to storage/app/public/
     * @return string|null Full URL to the file or null if path is empty
     */
    public static function uploadedFile(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Remove /storage prefix if it exists (for backward compatibility)
        $path = str_replace('/storage/', '', $path);
        $path = ltrim($path, '/');

        // If it's already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . $path);
    }

    /**
     * Get the URL for a user's avatar with fallback to initials
     * 
     * @param \App\Models\User|null $user
     * @return string URL to avatar or API-generated initials
     */
    public static function userAvatar($user): string
    {
        if (!$user) {
            return 'https://ui-avatars.com/api/?name=User&color=7F9CF5&background=EBF4FF';
        }

        if ($user->avatar) {
            $avatarUrl = self::uploadedFile($user->avatar);
            if ($avatarUrl) {
                return $avatarUrl;
            }
        }

        // Fallback to initials
        $name = urlencode($user->name ?? 'User');
        $color = $user->avatar_color ? str_replace(['bg-', 'text-'], '', $user->avatar_color) : '7F9CF5';
        
        return "https://ui-avatars.com/api/?name={$name}&color={$color}&background=EBF4FF";
    }

    /**
     * Get the URL for a post image
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function postImage(?string $path): ?string
    {
        return self::uploadedFile($path);
    }

    /**
     * Get the URL for an event banner
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function eventBanner(?string $path): ?string
    {
        return self::uploadedFile($path);
    }

    /**
     * Get the URL for a hackathon cover image
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function hackathonCover(?string $path): ?string
    {
        return self::uploadedFile($path);
    }

    /**
     * Get the URL for a chat attachment
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function chatAttachment(?string $path): ?string
    {
        return self::uploadedFile($path);
    }
}

