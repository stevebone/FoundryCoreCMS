<?php

namespace App\Api;

use App\Core\Lang;
use App\Core\Router;
use App\Core\SiteSettings;
use App\Core\Theme;

class AdminController
{
    public static function saveSettings(): void
    {
        if (!is_admin()) {
            Router::json(['error' => ['code' => 'err_forbidden', 'message' => 'Admin access required']], 403);
            return;
        }
        $overrides = self::validateForm(self::input());
        if (isset($overrides['err'])) {
            Router::json(['error' => ['code' => $overrides['err'], 'message' => 'Invalid settings']], 422);
            return;
        }
        SiteSettings::save($overrides);
        Router::json(['data' => ['settings' => SiteSettings::all()]]);
    }

    /** Shared validation for the admin form and the API. */
    public static function validateForm(array $in): array
    {
        $out = [];

        // Site basics — required when any site_* field is present.
        if (array_intersect(['site_name', 'site_theme', 'default_lang'], array_keys($in))) {
            $name = trim((string) ($in['site_name'] ?? ''));
            if ($name === '' || strlen($name) > 64) {
                return ['err' => 'err_name'];
            }
            $theme = (string) ($in['site_theme'] ?? '');
            $lang = (string) ($in['default_lang'] ?? '');
            if (!in_array($theme, Theme::available(), true) || !in_array($lang, Lang::available(), true)) {
                return ['err' => 'err_invalid'];
            }
            $out += ['app.name' => $name, 'app.theme' => $theme, 'app.default_lang' => $lang];

            // Optional site-wide page background (empty = theme.json default).
            if (array_key_exists('site_bg', $in)) {
                $bg = trim((string) $in['site_bg']);
                if ($bg !== '' && !isset(Theme::backgrounds()[$bg])) {
                    return ['err' => 'err_invalid_bg'];
                }
                $out['theme.bg'] = $bg === '' ? null : $bg;
            }

            // Optional site favicon (empty = img/favicon.svg default).
            if (array_key_exists('site_favicon', $in)) {
                $fav = trim((string) $in['site_favicon']);
                if ($fav !== '' && !isset(Theme::favicons()[$fav])) {
                    return ['err' => 'err_invalid_bg'];
                }
                $out['theme.favicon'] = $fav === '' ? null : $fav;
            }

            // Optional hero logo (empty = img/favicon.svg default).
            if (array_key_exists('site_logo', $in)) {
                $logo = trim((string) $in['site_logo']);
                if ($logo !== '' && !isset(Theme::logos()[$logo])) {
                    return ['err' => 'err_invalid_bg'];
                }
                $out['theme.logo'] = $logo === '' ? null : $logo;
            }

            // Optional home hero emblem (empty = img/favicon.svg default).
            if (array_key_exists('site_hero', $in)) {
                $hero = trim((string) $in['site_hero']);
                if ($hero !== '' && !isset(Theme::heroes()[$hero])) {
                    return ['err' => 'err_invalid_bg'];
                }
                $out['theme.hero'] = $hero === '' ? null : $hero;
            }
        }

        // Footer section posted by the admin form (f_footer sentinel marks it;
        // an unchecked checkbox is simply absent, so absence = '0').
        if (isset($in['f_footer'])) {
            $out['footer.social'] = isset($in['f_social']) ? '1' : '0';
            foreach (\App\Core\Footer::socials() as $key => $def) {
                $v = trim((string) ($in["f_social_url_{$key}"] ?? ''));
                if ($v !== '' && !preg_match('#^https?://#i', $v)) {
                    $v = 'https://' . $v;
                }
                $out["footer.social.{$key}"] = $v === '' ? null : substr($v, 0, 255);
            }
            foreach (\App\Core\Footer::columns() as $n => $col) {
                $out["footer.col{$n}.show"] = isset($in["f_col{$n}_show"]) ? '1' : '0';
                $t = trim((string) ($in["f_col{$n}_title"] ?? ''));
                $out["footer.col{$n}.title"] = $t === '' ? null : substr($t, 0, 64);
                foreach ($col['links'] as $m => $link) {
                    $k = $m + 1;
                    $out["footer.col{$n}.link{$k}.show"] = isset($in["f_col{$n}_link{$k}"]) ? '1' : '0';
                }
            }
        }

        // Database tab posted by the admin form.
        if (isset($in['f_db'])) {
            $out['db.enabled'] = isset($in['f_db_enabled']) ? '1' : '0';
        }

        // API callers may pass footer.*/db.* keys directly.
        foreach ($in as $k => $v) {
            if (!str_starts_with($k, 'footer.') && !str_starts_with($k, 'db.')) {
                continue;
            }
            if (str_ends_with($k, '.title') || preg_match('#^footer\.social\..+#', $k)) {
                $v = trim((string) $v);
                $out[$k] = $v === '' ? null : substr($v, 0, 255);
            } else {
                $out[$k] = in_array($v, ['1', 1, true, 'on'], true) ? '1' : '0';
            }
        }

        return $out;
    }

    private static function input(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($ct, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?: [];
        }
        return $_POST;
    }
}
