<?php

namespace App\Core;

class Theme
{
    public static function name(): string
    {
        $u = \current_user();
        if ($u) {
            $saved = Settings::value((int) $u['id'], 'theme');
            if ($saved && in_array($saved, self::available(), true)) {
                return $saved;
            }
        }
        return config('app.theme', 'blizzard');
    }

    /** Current theme's theme.json metadata (cached). */
    public static function meta(): array
    {
        static $meta = null;
        return $meta ??= (array) (json_decode(
            @file_get_contents(self::dir() . '/theme.json'), true
        ) ?: []);
    }

    /** Declared page-background asset path (e.g. img/mainBG.avif), or null. */
    public static function background(): ?string
    {
        $bgs = self::backgrounds();
        if ($bgs) {
            $u = \current_user();
            if ($u) {
                $pick = Settings::value((int) $u['id'], 'bg');
                if ($pick && isset($bgs[$pick])) {
                    return 'img/bg/' . $pick;
                }
            }
            $site = SiteSettings::get('theme.bg');
            if ($site && isset($bgs[$site])) {
                return 'img/bg/' . $site;
            }
        }
        $bg = self::meta()['background'] ?? null;
        return is_string($bg) && $bg !== '' ? $bg : null;
    }

    /** Site favicon asset path, picked from img/favicon/ or the default. */
    public static function favicon(): string
    {
        $icons = self::favicons();
        $pick = SiteSettings::get('theme.favicon');
        if ($pick && isset($icons[$pick])) {
            return 'img/favicon/' . $pick;
        }
        return 'img/favicon.svg';
    }

    /** Mime type for a favicon asset path (for <link rel="icon" type=...>). */
    public static function faviconMime(string $path): string
    {
        return [
            'svg' => 'image/svg+xml', 'png' => 'image/png', 'ico' => 'image/x-icon',
            'webp' => 'image/webp', 'avif' => 'image/avif', 'gif' => 'image/gif',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        ][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'image/png';
    }

    /** Hero logo asset path picked from img/logo/, or the site icon fallback. */
    public static function logo(): string
    {
        $logos = self::logos();
        $pick = SiteSettings::get('theme.logo');
        return $pick && isset($logos[$pick]) ? 'img/logo/' . $pick : 'img/favicon.svg';
    }

    /**
     * Selectable hero logos in the theme's public img/logo/ folder:
     * [filename => pretty label] ("logo_legion.png" => "Legion").
     */
    public static function logos(): array
    {
        return self::scanIcons('/themes/' . self::name() . '/img/logo', 'logo');
    }

    /** Home hero art asset path picked from img/hero/, or null (gradient hero). */
    public static function hero(): ?string
    {
        $heroes = self::heroes();
        $pick = SiteSettings::get('theme.hero');
        return $pick && isset($heroes[$pick]) ? 'img/hero/' . $pick : null;
    }

    /**
     * Selectable hero emblems in the theme's public img/hero/ folder:
     * [filename => pretty label] ("hero_dragon.png" => "Dragon").
     */
    public static function heroes(): array
    {
        return self::scanIcons('/themes/' . self::name() . '/img/hero', 'hero');
    }

    /**
     * Selectable favicons in the theme's public img/favicon/ folder:
     * [filename => pretty label] ("favicon_lion.png" => "Lion").
     */
    public static function favicons(): array
    {
        return self::scanIcons('/themes/' . self::name() . '/img/favicon', '(fav)?icon');
    }

    /** Shared image-folder scanner: [filename => pretty label]. */
    private static function scanIcons(string $relDir, string $prefix): array
    {
        static $cache = [];
        if (isset($cache[$relDir])) {
            return $cache[$relDir];
        }
        $icons = [];
        foreach (glob(PUBLIC_DIR . $relDir . '/*.{svg,png,ico,webp,avif,gif,jpg,jpeg}', GLOB_BRACE) ?: [] as $file) {
            $base = basename($file);
            $label = preg_replace('/\.[^.]+$/', '', $base);
            $label = preg_replace('/\.[0-9a-f]{16,}$/', '', $label);
            $label = ucwords(str_replace('_', ' ', preg_replace('/^' . $prefix . '[_-]?/', '', $label)));
            $icons[$base] = $label !== '' ? $label : $base;
        }
        ksort($icons);
        return $cache[$relDir] = $icons;
    }

    /**
     * Selectable backgrounds in the theme's public img/bg/ folder:
     * [filename => pretty label] ("bg_midnight.webp" => "Midnight").
     */
    public static function backgrounds(): array
    {
        static $bgs = null;
        if ($bgs !== null) {
            return $bgs;
        }
        $bgs = [];
        $dir = PUBLIC_DIR . '/themes/' . self::name() . '/img/bg';
        foreach (glob($dir . '/*.{webp,avif,jpg,jpeg,png,gif}', GLOB_BRACE) ?: [] as $file) {
            $base = basename($file);
            $label = preg_replace('/\.[^.]+$/', '', $base);
            $label = ucwords(str_replace('_', ' ', preg_replace('/^bg[_-]?/', '', $label)));
            $bgs[$base] = $label !== '' ? $label : $base;
        }
        ksort($bgs);
        return $bgs;
    }

    /** Themes = directories under THEMES_DIR containing a theme.json. */
    public static function available(): array
    {
        static $themes = null;
        return $themes ??= array_values(array_filter(
            scandir(THEMES_DIR) ?: [],
            fn($d) => $d !== '.' && $d !== '..'
                && is_file(THEMES_DIR . '/' . $d . '/theme.json')
        ));
    }

    public static function dir(): string
    {
        return THEMES_DIR . '/' . self::name();
    }

    public static function asset(string $path): string
    {
        $rel = '/themes/' . self::name() . '/' . ltrim($path, '/');
        $file = PUBLIC_DIR . $rel;
        return $rel . (is_file($file) ? '?v=' . filemtime($file) : '');
    }

    public static function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        $file = self::dir() . "/templates/{$template}.php";
        if (!is_file($file)) {
            http_response_code(500);
            echo "Missing template: {$template}";
            return;
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();
        require self::dir() . '/templates/layout.php';
    }

    public static function partial(string $name, array $data = []): void
    {
        $file = self::dir() . "/templates/partials/{$name}.php";
        if (is_file($file)) {
            extract($data, EXTR_SKIP);
            require $file;
        }
    }
}
