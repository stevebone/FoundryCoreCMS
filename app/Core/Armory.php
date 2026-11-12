<?php

namespace App\Core;

use PDO;

/**
 * Armory data: realms, character lists, equipped items, item tooltips.
 * Reads TrinityCore + cms (db2 CSV imports) tables only — no writes.
 *
 * Item data layering: cms.item_* tables hold the full db2 export;
 * hotfixes.item_* is the repack's override layer for custom items
 * and is only consulted for entries missing from cms.
 */
class Armory
{
    public static function realms(): array
    {
        return Db::auth()
            ->query('SELECT id, name, address, port, gamebuild FROM realmlist ORDER BY id')
            ->fetchAll();
    }

    /** All characters (single characters DB serves every realm in a repack). */
    public static function characters(): array
    {
        return Db::characters()
            ->query('SELECT guid, name, race, class, gender, level, online
                     FROM characters ORDER BY name')
            ->fetchAll();
    }

    public static function character(int $guid): ?array
    {
        $stmt = Db::characters()->prepare(
            'SELECT guid, name, race, class, gender, level, online, zone, money, chosenTitle
             FROM characters WHERE guid = ?'
        );
        $stmt->execute([$guid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Guild name and chosen title text for the sheet's doll column.
     * chosenTitle is a CharTitles ID; Name_lang carries a "%s" placeholder
     * for the character name (e.g. "Champion %s" or "%s the Patient").
     */
    public static function characterExtras(int $guid, string $charName): array
    {
        $char = Db::characters();
        $stmt = $char->prepare(
            'SELECT g.name FROM guild_member gm
             JOIN guild g ON g.guildid = gm.guildid WHERE gm.guid = ? LIMIT 1'
        );
        $stmt->execute([$guid]);
        $guild = $stmt->fetchColumn() ?: null;

        $title = null;
        $t = $char->query(
            'SELECT chosenTitle FROM characters WHERE guid = ' . (int) $guid
        )->fetchColumn();
        if ($t) {
            $name = Db::cms()->prepare('SELECT Name_lang FROM char_titles WHERE ID = ?');
            $name->execute([(int) $t]);
            $fmt = $name->fetchColumn();
            $title = $fmt ? str_replace('%s', $charName, (string) $fmt) : null;
        }
        return ['guild' => $guild, 'title' => $title];
    }

    /**
     * Equipped items for a character, keyed by equipment slot (0-18).
     */
    public static function equipment(int $guid): array
    {
        $stmt = Db::characters()->prepare(
            'SELECT ci.slot, ii.itemEntry
             FROM character_inventory ci
             JOIN item_instance ii ON ii.guid = ci.item
             WHERE ci.guid = ? AND ci.bag = 0 AND ci.slot < 19'
        );
        $stmt->execute([$guid]);
        $rows = $stmt->fetchAll();
        if (!$rows) {
            return [];
        }

        $entries = array_map('intval', array_column($rows, 'itemEntry'));
        $names = self::itemNames($entries);

        $out = [];
        foreach ($rows as $r) {
            $slot = (int) $r['slot'];
            $info = $names[(int) $r['itemEntry']] ?? null;
            $out[$slot] = [
                'entry' => (int) $r['itemEntry'],
                'name' => $info['Display'] ?? 'Item #' . (int) $r['itemEntry'],
                'quality' => isset($info['OverallQualityID']) ? (int) $info['OverallQualityID'] : 1,
                'ilvl' => isset($info['ItemLevel']) ? (int) $info['ItemLevel'] : null,
                'icon' => $info['icon'] ?? null,
            ];
        }
        return $out;
    }

    /**
     * entry -> ['Display','OverallQualityID','ItemLevel'] for a set of item
     * entries. cms.item_search_name is primary; hotfixes fills custom items.
     */
    public static function itemNames(array $entries): array
    {
        $entries = array_values(array_unique($entries));
        if (!$entries) {
            return [];
        }
        $in = implode(',', array_fill(0, count($entries), '?'));
        $out = [];

        // cms db2 tables: search_name first (indexed), then sparse fills gaps
        // (search_name is a search index and doesn't cover every item).
        foreach ([
            "SELECT ID, Display_lang AS Display, OverallQualityID, ItemLevel
             FROM item_search_name WHERE ID IN ($in)",
            "SELECT ID, Display_lang AS Display, OverallQualityID, ItemLevel
             FROM item_sparse WHERE ID IN ($in)",
        ] as $sql) {
            $stmt = Db::cms()->prepare($sql);
            $stmt->execute($entries);
            foreach ($stmt->fetchAll() as $row) {
                $out[(int) $row['ID']] ??= $row;
            }
        }

        // Icon FileDataIDs come from the `item` table regardless of which
        // name source resolved the entry. Items with fdid 0 keep their icon
        // on the base ItemAppearance via ItemModifiedAppearance instead.
        $stmt = Db::cms()->prepare(
            "SELECT ID, IconFileDataID FROM item WHERE ID IN ($in)"
        );
        $stmt->execute($entries);
        foreach ($stmt->fetchAll() as $row) {
            if (isset($out[(int) $row['ID']])) {
                $out[(int) $row['ID']]['icon'] = (int) $row['IconFileDataID'] ?: null;
            }
        }

        $noIcon = array_keys(array_filter($out, fn ($r) => empty($r['icon'])));
        if ($noIcon) {
            $in3 = implode(',', array_fill(0, count($noIcon), '?'));
            $stmt = Db::cms()->prepare(
                "SELECT x.ItemID, MAX(a.DefaultIconFileDataID) AS fdid
                 FROM item_modified_appearance x
                 JOIN item_appearance a ON a.ID = x.ItemAppearanceID
                 WHERE x.ItemID IN ($in3) AND x.ItemAppearanceModifierID = 0
                 GROUP BY x.ItemID"
            );
            $stmt->execute($noIcon);
            foreach ($stmt->fetchAll() as $row) {
                $out[(int) $row['ItemID']]['icon'] = (int) $row['fdid'] ?: null;
            }
        }

        $missing = array_diff($entries, array_keys($out));
        if ($missing) {
            $in2 = implode(',', array_fill(0, count($missing), '?'));
            foreach (['item_search_name', 'item_sparse'] as $t) {
                $stmt = Db::db('hotfixes')->prepare(
                    "SELECT ID, Display, OverallQualityID, ItemLevel FROM {$t} WHERE ID IN ($in2)"
                );
                $stmt->execute(array_values($missing));
                foreach ($stmt->fetchAll() as $row) {
                    $out[(int) $row['ID']] ??= $row;
                }
            }
        }
        return $out;
    }

    /**
     * Full tooltip payload for one item entry, assembled from the db2
     * tables. Returns null when the entry is unknown to cms and hotfixes.
     */
    public static function itemTooltip(int $entry): ?array
    {
        $cms = Db::cms();
        $stmt = $cms->prepare('SELECT * FROM item_sparse WHERE ID = ?');
        $stmt->execute([$entry]);
        $s = $stmt->fetch();
        $custom = false;

        if (!$s) {
            // Repack custom item: hotfixes override layer.
            $stmt = Db::db('hotfixes')->prepare('SELECT * FROM item_sparse WHERE ID = ?');
            $stmt->execute([$entry]);
            $s = $stmt->fetch();
            if (!$s) {
                return null;
            }
            $custom = true;
        }

        // db2 CSV columns are Name_i (0-indexed); hotfixes uses NameN (1-indexed
        // and different casing for the stat arrays).
        $gi = static function (string $c, int $i) use ($s, $custom): int {
            $col = $custom ? $c . ($i + 1) : $c . '_' . $i;
            return (int) ($s[$col] ?? 0);
        };
        $statCol = $custom ? 'StatModifierBonusStat' : 'StatModifier_bonusStat';
        $valCol = $custom ? 'StatPercentEditor' : 'StatPercentEditor';
        $sockCol = $custom ? 'SocketType' : 'SocketType';

        $quality = (int) ($s['OverallQualityID'] ?? 1);
        $ilvl = (int) ($s['ItemLevel'] ?? 0);
        $invType = (int) ($s['InventoryType'] ?? 0);

        // Item row: class/subclass + display names.
        $stmt = $cms->prepare('SELECT ClassID, SubclassID, IconFileDataID FROM item WHERE ID = ?');
        $stmt->execute([$entry]);
        $item = $stmt->fetch() ?: [];

        $subName = null;
        if ($item) {
            $stmt = $cms->prepare(
                'SELECT DisplayName_lang FROM item_sub_class WHERE ClassID = ? AND SubClassID = ?'
            );
            $stmt->execute([(int) $item['ClassID'], (int) $item['SubclassID']]);
            $subName = $stmt->fetchColumn() ?: null;
        }

        // Weapon damage: damage tables are ilvl scaling rows; the value for
        // this item's quality is a base damage figure used with variance.
        $damage = null;
        if (!$custom && $ilvl > 0) {
            $dmgTable = match ($invType) {
                13, 21, 22 => 'item_damage_one_hand',
                17 => 'item_damage_two_hand',
                15, 26 => 'item_damage_ranged',
                25 => 'item_damage_thrown',
                default => null,
            };
            if ($dmgTable) {
                $stmt = $cms->prepare(
                    "SELECT `Quality_{$quality}` FROM {$dmgTable} WHERE ItemLevel = ? LIMIT 1"
                );
                $stmt->execute([$ilvl]);
                $base = (float) $stmt->fetchColumn();
                $delay = (int) ($s['ItemDelay'] ?? 0);
                $var = (float) ($s['DmgVariance'] ?? 0);
                if ($base > 0 && $delay > 0) {
                    $avg = $base * $delay / 1000.0;
                    $damage = [
                        'min' => (int) round($avg * (1 - $var / 2)),
                        'max' => (int) round($avg * (1 + $var / 2)),
                        'speed' => round($delay / 1000.0, 2),
                        'dps' => round($base, 1),
                    ];
                }
            }

            // Armor: armor_total[ilvl][armorclass] * armor_quality[ilvl][q],
            // shields read item_armor_shield[ilvl][q] directly.
            $armor = null;
            if ($invType === 14) {
                $stmt = $cms->prepare(
                    "SELECT `Quality_{$quality}` FROM item_armor_shield WHERE ItemLevel = ? LIMIT 1"
                );
                $stmt->execute([$ilvl]);
                $armor = (int) round((float) $stmt->fetchColumn()) ?: null;
            } else {
                $mat = [1 => 'Cloth', 2 => 'Leather', 3 => 'Mail', 4 => 'Plate'][(int) ($item['SubclassID'] ?? 0)] ?? null;
                if ($mat) {
                    $stmt = $cms->prepare(
                        "SELECT `{$mat}` FROM item_armor_total WHERE ItemLevel = ? LIMIT 1"
                    );
                    $stmt->execute([$ilvl]);
                    $total = (float) $stmt->fetchColumn();
                    $stmt = $cms->prepare(
                        "SELECT `Qualitymod_{$quality}` FROM item_armor_quality WHERE ID = ? LIMIT 1"
                    );
                    $stmt->execute([$ilvl]);
                    $mod = (float) $stmt->fetchColumn();
                    if ($total > 0) {
                        $armor = (int) round($total * ($mod ?: 1));
                    }
                }
            }
        } else {
            $armor = null;
        }

        // Stats.
        $stats = [];
        for ($i = 0; $i < 10; $i++) {
            $type = $gi($statCol, $i);
            $val = $gi($valCol, $i);
            if ($type > 0 && $val != 0) {
                $stats[] = ['name' => Wow::statName($type), 'value' => $val];
            }
        }

        // Sockets.
        $sockets = [];
        for ($i = 0; $i < 3; $i++) {
            $t = $gi($sockCol, $i);
            if ($t > 0) {
                $sockets[] = $t;
            }
        }

        // Equip / Use effects -> spell names (+ description when present).
        $effects = [];
        if (!$custom) {
            $stmt = $cms->prepare(
                'SELECT ie.TriggerType, ie.SpellID, sn.Name_lang AS spellName, sp.Description_lang AS spellDesc
                 FROM item_x_item_effect x
                 JOIN item_effect ie ON ie.ID = x.ItemEffectID
                 LEFT JOIN spell_name sn ON sn.ID = ie.SpellID
                 LEFT JOIN spell sp ON sp.ID = ie.SpellID
                 WHERE x.ItemID = ?'
            );
            $stmt->execute([$entry]);
            foreach ($stmt->fetchAll() as $fx) {
                $effects[] = [
                    'trigger' => (int) $fx['TriggerType'], // 0=use 1=equip 6=learn...
                    'spell' => (int) $fx['SpellID'],
                    'text' => $fx['spellDesc'] ?: ($fx['spellName'] ?? ''),
                ];
            }
        }

        $fdid = self::iconForItem($cms, $entry, $item);

        return [
            'entry' => $entry,
            'name' => $s['Display_lang'] ?? $s['Display'] ?? ('Item #' . $entry),
            'icon' => $fdid,
            'iconUrl' => Wow::iconUrl($fdid),
            'quality' => $quality,
            'ilvl' => $ilvl,
            'invType' => Wow::invTypeName($invType),
            'subclass' => $subName,
            'bonding' => Wow::bondingName((int) ($s['Bonding'] ?? 0)),
            'requiredLevel' => (int) ($s['RequiredLevel'] ?? 0),
            'allowableClass' => self::classMask((int) ($s['AllowableClass'] ?? 0)),
            'stats' => $stats,
            'sockets' => $sockets,
            'effects' => $effects,
            'damage' => $damage,
            'armor' => $armor ?? null,
            'description' => $s['Description_lang'] ?? $s['Description'] ?? '',
            'sellPrice' => (int) ($s['SellPrice'] ?? 0),
            'itemSet' => (int) ($s['ItemSet'] ?? 0) ?: null,
            'custom' => $custom,
        ];
    }

    /**
     * Icon FileDataID for an item: item.IconFileDataID, else the base
     * ItemAppearance's DefaultIconFileDataID (ItemAppearanceModifierID=0).
     */
    private static function iconForItem(PDO $cms, int $entry, array $itemRow): ?int
    {
        $fdid = (int) ($itemRow['IconFileDataID'] ?? 0);
        if ($fdid) {
            return $fdid;
        }
        $stmt = $cms->prepare(
            'SELECT a.DefaultIconFileDataID FROM item_modified_appearance x
             JOIN item_appearance a ON a.ID = x.ItemAppearanceID
             WHERE x.ItemID = ? AND x.ItemAppearanceModifierID = 0 LIMIT 1'
        );
        $stmt->execute([$entry]);
        $fdid = (int) $stmt->fetchColumn();
        return $fdid ?: null;
    }

    /** AllowableClass bitmask -> list of class names (empty = all). */
    private static function classMask(int $mask): array
    {
        if ($mask <= 0) {
            return [];
        }
        $out = [];
        foreach (Wow::CLASSES as $id => $name) {
            if ($id <= 16 && ($mask & (1 << ($id - 1)))) {
                $out[] = Wow::className($id);
            }
        }
        return $out;
    }
}
