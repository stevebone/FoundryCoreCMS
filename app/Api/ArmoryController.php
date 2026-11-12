<?php

namespace App\Api;

use App\Core\Armory;
use App\Core\Router;
use App\Core\Wow;
use Throwable;

class ArmoryController
{
    public static function realms(): void
    {
        try {
            Router::json(['data' => ['realms' => Armory::realms()]]);
        } catch (Throwable) {
            Router::json(['error' => ['code' => 'err_server', 'message' => 'Server error']], 500);
        }
    }

    public static function characters(): void
    {
        try {
            $chars = array_map(fn($c) => [
                'guid' => (int) $c['guid'],
                'name' => $c['name'],
                'level' => (int) $c['level'],
                'race' => Wow::raceName((int) $c['race']),
                'class' => Wow::className((int) $c['class']),
                'online' => (bool) $c['online'],
            ], Armory::characters());
            Router::json(['data' => ['characters' => $chars]]);
        } catch (Throwable) {
            Router::json(['error' => ['code' => 'err_server', 'message' => 'Server error']], 500);
        }
    }

    public static function character(): void
    {
        $guid = (int) ($_GET['guid'] ?? 0);
        $char = $guid ? Armory::character($guid) : null;
        if (!$char) {
            Router::json(['error' => ['code' => 'err_notfound', 'message' => 'Character not found']], 404);
            return;
        }
        $items = Armory::equipment($guid);
        $slots = [];
        foreach (Wow::SLOTS as $slot => $label) {
            $slots[$slot] = $items[$slot] ?? null;
        }
        Router::json(['data' => [
            'character' => [
                'guid' => (int) $char['guid'],
                'name' => $char['name'],
                'level' => (int) $char['level'],
                'race' => Wow::raceName((int) $char['race']),
                'class' => Wow::className((int) $char['class']),
                'online' => (bool) $char['online'],
            ],
            'equipment' => $slots,
        ]]);
    }

    public static function item(): void
    {
        $entry = (int) ($_GET['entry'] ?? 0);
        $tip = $entry ? Armory::itemTooltip($entry) : null;
        if (!$tip) {
            Router::json(['error' => ['code' => 'err_notfound', 'message' => 'Item not found']], 404);
            return;
        }
        Router::json(['data' => ['item' => $tip]]);
    }
}
