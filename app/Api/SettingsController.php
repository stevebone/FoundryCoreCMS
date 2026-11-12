<?php

namespace App\Api;

use App\Auth\AuthService;
use App\Core\Lang;
use App\Core\Router;
use App\Core\Settings;
use App\Core\Theme;

class SettingsController
{
    public static function get(): void
    {
        $u = AuthService::user();
        if (!$u) {
            Router::json(['error' => ['code' => 'err_auth', 'message' => 'Not logged in']], 401);
            return;
        }
        Router::json(['data' => ['settings' => Settings::for((int) $u['id']) ?? new \stdClass()]]);
    }

    public static function save(): void
    {
        $u = AuthService::user();
        if (!$u) {
            Router::json(['error' => ['code' => 'err_auth', 'message' => 'Not logged in']], 401);
            return;
        }
        $in = self::input();
        $prefs = self::validate($in);
        if (isset($prefs['err'])) {
            Router::json(['error' => ['code' => $prefs['err'], 'message' => 'Invalid setting value']], 422);
            return;
        }
        Settings::save((int) $u['id'], $prefs);
        Router::json(['data' => ['settings' => Settings::for((int) $u['id'])]]);
    }

    /** Same validation the /settings form uses. */
    public static function validate(array $in): array
    {
        $prefs = [];
        foreach (['lang' => Lang::available(), 'theme' => Theme::available()] as $key => $allowed) {
            if (!array_key_exists($key, $in)) {
                continue;
            }
            $v = trim((string) $in[$key]);
            if ($v === '') {
                $prefs[$key] = null;
            } elseif (in_array($v, $allowed, true)) {
                $prefs[$key] = $v;
            } else {
                return ['err' => 'err_invalid_' . $key];
            }
        }
        if (array_key_exists('timezone', $in)) {
            $v = trim((string) $in['timezone']);
            $prefs['timezone'] = $v === '' ? null : $v;
        }
        return $prefs;
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
