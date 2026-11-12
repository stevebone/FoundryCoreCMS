<?php

namespace App\Api;

use App\Core\Db;
use App\Core\Router;
use Throwable;

class RealmController
{
    public static function status(): void
    {
        try {
            $rows = Db::auth()
                ->query('SELECT id, name, address, port, gamebuild FROM realmlist ORDER BY id')
                ->fetchAll();
        } catch (Throwable) {
            Router::json(['error' => 'Auth database unavailable'], 503);
            return;
        }

        $data = array_map(static fn(array $r): array => [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'address' => $r['address'],
            'port' => (int) $r['port'],
            'gamebuild' => (int) $r['gamebuild'],
            'online' => self::probe($r['address'], (int) $r['port']),
        ], $rows);

        Router::json(['data' => $data]);
    }

    public static function online(): void
    {
        try {
            $n = (int) Db::characters()
                ->query('SELECT COUNT(*) AS c FROM characters WHERE online = 1')
                ->fetch()['c'];
            Router::json(['data' => ['online' => $n]]);
        } catch (Throwable) {
            Router::json(['error' => 'Characters database unavailable'], 503);
        }
    }

    private static function probe(string $host, int $port): bool
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 1.0);
        if ($sock) {
            fclose($sock);
            return true;
        }
        return false;
    }
}
