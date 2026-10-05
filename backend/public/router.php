<?php
/**
 * Router for PHP built-in server on Render:
 * - /api/* and existing PHP → index.php
 * - /storage/* → storage
 * - SPA assets + history fallback → public/spa
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$root = __DIR__;
$spa = $root . '/spa';

$file = $root . $uri;
if ($uri !== '/' && is_file($file)) {
    return false;
}

if (str_starts_with($uri, '/storage/')) {
    $path = $root . $uri;
    if (is_file($path)) {
        return false;
    }
}

if (
    str_starts_with($uri, '/api') ||
    str_starts_with($uri, '/sanctum') ||
    str_starts_with($uri, '/up') ||
    str_ends_with($uri, '.php')
) {
    require_once $root . '/index.php';
    return true;
}

$spaFile = $spa . $uri;
if ($uri !== '/' && is_file($spaFile)) {
    $ext = pathinfo($spaFile, PATHINFO_EXTENSION);
    $types = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ico' => 'image/x-icon',
        'json' => 'application/json',
    ];
    if (isset($types[$ext])) {
        header('Content-Type: ' . $types[$ext]);
    }
    readfile($spaFile);
    return true;
}

$index = $spa . '/index.html';
if (is_file($index)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($index);
    return true;
}

require_once $root . '/index.php';
return true;
