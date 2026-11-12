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
        $bg = self::meta()['background'] ?? null;
        return is_string($bg) && $bg !== '' ? $bg : null;
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
