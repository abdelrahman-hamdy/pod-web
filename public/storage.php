<?php

$path = $_GET['path'] ?? null;

if (!$path) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

// Prevent directory traversal attacks
$path = str_replace(['..', '/', '\\'], '', $path);
$path = ltrim($path, '/');

$realPath = dirname(__DIR__) . '/storage/app/public/' . $path;

if (!file_exists($realPath) || is_dir($realPath)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

$mimeType = mime_content_type($realPath);

header("Content-Type: " . $mimeType);
header("Content-Length: " . filesize($realPath));

readfile($realPath);
exit;
