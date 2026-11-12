<?php
// CMS bootstrap: defines paths, loads config, registers the autoloader
// and global helpers. Required once by the public front controller.

define('WEB_DIR', dirname(__DIR__));
define('APP_DIR', __DIR__);
define('PUBLIC_DIR', WEB_DIR . '/public');
define('THEMES_DIR', WEB_DIR . '/themes');
define('LANG_DIR', APP_DIR . '/Lang');

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = APP_DIR . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    $cfg ??= require WEB_DIR . '/config.php';
    $v = $cfg;
    foreach (explode('.', $key) as $seg) {
        if (!is_array($v) || !array_key_exists($seg, $v)) {
            return $default;
        }
        $v = $v[$seg];
    }
    // Admin-editable overrides from cms.site_settings win over the file.
    // Only app.* keys are overridable — consulting the table for db.* would
    // recurse (Db::cms() itself needs config('db')).
    if (str_starts_with($key, 'app.')) {
        return App\Core\SiteSettings::get($key) ?? $v;
    }
    return $v;
}

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function t(string $key, array $vars = []): string
{
    return App\Core\Lang::t($key, $vars);
}

function asset(string $path): string
{
    return App\Core\Theme::asset($path);
}

function url(string $path = '/'): string
{
    return '/' . ltrim($path, '/');
}

function current_user(): ?array
{
    return App\Auth\AuthService::user();
}

function user_level(): int
{
    $u = current_user();
    return $u ? App\Auth\AuthService::accessLevel((int) $u['id']) : 0;
}

function is_staff(): bool
{
    return user_level() >= 1;
}

function is_admin(): bool
{
    return user_level() >= 3;
}
