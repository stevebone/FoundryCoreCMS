<?php

namespace App\Api;

use App\Core\Markdown;
use App\Core\Router;

class NewsController
{
    /** GM level 2+ / admin may write news. */
    private static function guard(): bool
    {
        if (user_level() < 2) {
            Router::json(['error' => ['code' => 'err_forbidden', 'message' => 'Insufficient access']], 403);
            return false;
        }
        return true;
    }

    /** POST /api/news/preview — render markdown body to safe HTML. */
    public static function preview(): void
    {
        if (!self::guard()) {
            return;
        }
        $body = (string) ($_POST['body'] ?? '');
        Router::json(['data' => ['html' => Markdown::render($body)]]);
    }

    /** POST /api/news/image — upload an article image to /uploads/news/. */
    public static function image(): void
    {
        if (!self::guard()) {
            return;
        }
        $f = $_FILES['image'] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Router::json(['error' => ['code' => 'err_upload', 'message' => 'No file uploaded']], 422);
            return;
        }
        if ($f['size'] > 4 * 1024 * 1024) {
            Router::json(['error' => ['code' => 'err_upload', 'message' => 'File too large (max 4MB)']], 422);
            return;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $ext = [
            'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp',
            'image/gif' => 'gif', 'image/avif' => 'avif',
        ][$mime] ?? null;
        if ($ext === null) {
            Router::json(['error' => ['code' => 'err_upload', 'message' => 'Only png/jpg/webp/gif/avif allowed']], 422);
            return;
        }
        $dir = PUBLIC_DIR . '/uploads/news';
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            Router::json(['error' => ['code' => 'err_server', 'message' => 'Upload dir not writable']], 500);
            return;
        }
        $name = date('Ym') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
            Router::json(['error' => ['code' => 'err_server', 'message' => 'Upload failed']], 500);
            return;
        }
        Router::json(['data' => ['url' => '/uploads/news/' . $name]]);
    }
}
