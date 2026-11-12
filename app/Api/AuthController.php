<?php

namespace App\Api;

use App\Auth\AuthService;
use App\Core\Router;

class AuthController
{
    public static function register(): void
    {
        $in = self::input();
        $r = AuthService::register((string) ($in['email'] ?? ''), (string) ($in['password'] ?? ''));
        if (!$r['ok']) {
            $status = $r['error'] === 'err_exists' ? 409 : ($r['error'] === 'err_server' ? 500 : 400);
            Router::json(['error' => ['code' => $r['error'], 'message' => self::message($r['error'])]], $status);
            return;
        }
        Router::json(['data' => ['account' => $r['account']]], 201);
    }

    public static function login(): void
    {
        $in = self::input();
        $r = AuthService::login((string) ($in['email'] ?? ''), (string) ($in['password'] ?? ''));
        if (!$r['ok']) {
            $status = $r['error'] === 'err_server' ? 500 : 401;
            Router::json(['error' => ['code' => $r['error'], 'message' => self::message($r['error'])]], $status);
            return;
        }
        Router::json(['data' => ['account' => $r['account']]]);
    }

    public static function logout(): void
    {
        AuthService::logout();
        Router::json(['data' => ['ok' => true]]);
    }

    public static function me(): void
    {
        Router::json(['data' => ['account' => AuthService::user()]]);
    }

    private static function input(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($ct, 'application/json')) {
            return json_decode(file_get_contents('php://input'), true) ?: [];
        }
        return $_POST;
    }

    private static function message(string $code): string
    {
        return match ($code) {
            'err_email' => 'Invalid email address',
            'err_password_short' => 'Password must be 8-128 characters',
            'err_exists' => 'An account with this email already exists',
            'err_credentials' => 'Invalid email or password',
            default => 'Server error',
        };
    }
}
