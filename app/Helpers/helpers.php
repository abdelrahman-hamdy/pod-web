<?php

use App\Helpers\AssetHelper;

if (!function_exists('static_asset')) {
    /**
     * Get URL for static assets (logos, icons, etc.)
     * 
     * @param string $path Path relative to public/assets/
     * @return string
     */
    function static_asset(string $path): string
    {
        return AssetHelper::staticAsset($path);
    }
}

if (!function_exists('uploaded_file')) {
    /**
     * Get URL for uploaded files (avatars, posts, etc.)
     * 
     * @param string|null $path Path relative to storage/app/public/
     * @return string|null
     */
    function uploaded_file(?string $path): ?string
    {
        return AssetHelper::uploadedFile($path);
    }
}

if (!function_exists('user_avatar')) {
    /**
     * Get URL for user avatar with fallback
     * 
     * @param \App\Models\User|null $user
     * @return string
     */
    function user_avatar($user): string
    {
        return AssetHelper::userAvatar($user);
    }
}

