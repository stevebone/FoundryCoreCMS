<?php

namespace App\Api;

use App\Core\Armory;
use App\Core\Database;
use App\Core\Router;

class DbController
{
    private static function guard(): bool
    {
        if (!Database::allowed()) {
            Router::json(['error' => ['code' => 'err_forbidden', 'message' => 'Database browsing is disabled']], 403);
            return false;
        }
        return true;
    }

    /** GET /api/db/items?q=&quality=&class=&subclass=&slot=&ilvl_min=&ilvl_max=&page= */
    public static function items(): void
    {
        if (!self::guard()) {
            return;
        }
        $result = Database::searchItems($_GET);
        Router::json(['data' => $result]);
    }

    /** GET /api/db/quests?q=&lvl_min=&lvl_max=&type=&zone=&sort=&dir=&page= */
    public static function quests(): void
    {
        if (!self::guard()) {
            return;
        }
        Router::json(['data' => Database::searchQuests($_GET)]);
    }

    /** GET /api/db/zones?q= — zone-name autocomplete for the quest filter. */
    public static function zones(): void
    {
        if (!self::guard()) {
            return;
        }
        Router::json(['data' => Database::questZones((string) ($_GET['q'] ?? ''))]);
    }

    /** GET /api/db/quest-names?q= — quest-name autocomplete. */
    public static function questNames(): void
    {
        if (!self::guard()) {
            return;
        }
        Router::json(['data' => Database::questSuggest((string) ($_GET['q'] ?? ''))]);
    }

    /** GET /api/db/item-names?q= — item-name autocomplete. */
    public static function itemNames(): void
    {
        if (!self::guard()) {
            return;
        }
        Router::json(['data' => Database::itemSuggest((string) ($_GET['q'] ?? ''))]);
    }

    /** GET /api/db/quest?id=123 */
    public static function quest(): void
    {
        if (!self::guard()) {
            return;
        }
        $id = (int) ($_GET['id'] ?? 0);
        $quest = $id ? Database::questDetail($id) : null;
        if (!$quest) {
            Router::json(['error' => ['code' => 'err_notfound', 'message' => 'Quest not found']], 404);
            return;
        }
        Router::json(['data' => ['quest' => $quest]]);
    }

    /** GET /api/db/item?id=123 */
    public static function item(): void
    {
        if (!self::guard()) {
            return;
        }
        $entry = (int) ($_GET['id'] ?? $_GET['entry'] ?? 0);
        $tip = $entry ? Armory::itemTooltip($entry) : null;
        if (!$tip) {
            Router::json(['error' => ['code' => 'err_notfound', 'message' => 'Item not found']], 404);
            return;
        }
        Router::json(['data' => ['item' => $tip]]);
    }
}
