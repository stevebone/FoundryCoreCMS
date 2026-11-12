<?php

namespace App\Core;

use PDO;

class Db
{
    private static array $pool = [];

    public static function auth(): PDO
    {
        return self::db('auth');
    }

    public static function characters(): PDO
    {
        return self::db('characters');
    }

    public static function world(): PDO
    {
        return self::db('world');
    }

    public static function cms(): PDO
    {
        return self::db('cms');
    }

    public static function db(string $name): PDO
    {
        if (!isset(self::$pool[$name])) {
            $c = config('db');
            self::$pool[$name] = new PDO(
                "mysql:host={$c['host']};port={$c['port']};dbname={$name};charset=utf8mb4",
                $c['user'],
                $c['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return self::$pool[$name];
    }
}
