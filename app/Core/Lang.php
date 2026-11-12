<?php

namespace App\Core;

class Lang
{
    private static ?string $current = null;
    private static array $strings = [];
    private static array $fallback = [];

    public static function available(): array
    {
        return config('app.languages', ['en']);
    }

    public static function current(): string
    {
        return self::$current ??= self::resolve();
    }

    public static function t(string $key, array $vars = []): string
    {
        self::load();
        $s = self::$strings[$key] ?? self::$fallback[$key] ?? $key;
        foreach ($vars as $k => $v) {
            $s = str_replace('{' . $k . '}', (string) $v, $s);
        }
        return $s;
    }

    private static function resolve(): string
    {
        $langs = self::available();

        $u = \current_user();
        if ($u) {
            $saved = Settings::value((int) $u['id'], 'lang');
            if ($saved && in_array($saved, $langs, true)) {
                return $saved;
            }
        }

        $cookie = $_COOKIE['cms_lang'] ?? null;
        if ($cookie && in_array($cookie, $langs, true)) {
            return $cookie;
        }

        foreach (self::accepted() as $code) {
            if (in_array($code, $langs, true)) {
                return $code;
            }
        }

        return config('app.default_lang', 'en');
    }

    private static function accepted(): array
    {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        preg_match_all('/([a-zA-Z]{2})(?:-[a-zA-Z]{2})?/', $header, $m);
        return $m[1] ?? [];
    }

    private static function load(): void
    {
        if (self::$strings || self::$fallback) {
            return;
        }
        $default = config('app.default_lang', 'en');
        $current = self::current();
        self::$strings = self::loadFile($current);
        self::$fallback = $current === $default ? [] : self::loadFile($default);
    }

    private static function loadFile(string $lang): array
    {
        $file = LANG_DIR . '/' . $lang . '.json';
        if (!is_file($file)) {
            return [];
        }
        return json_decode(file_get_contents($file), true) ?: [];
    }
}
