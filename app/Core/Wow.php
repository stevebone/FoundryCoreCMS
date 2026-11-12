<?php

namespace App\Core;

/**
 * WoW constant lookups used by the armory.
 * Enum values verified against the fork's source:
 *   Races      - src/server/game/Miscellaneous/RaceMask.h
 *   Classes    - src/server/game/Miscellaneous/SharedDefines.h
 *   Stats      - src/server/game/Entities/Item/ItemTemplate.h (ItemModType)
 *   InvTypes   - src/server/game/Entities/Item/ItemTemplate.h (InventoryType)
 * Names resolve through i18n keys (race.N / class.N / slot.N / stat.N ...)
 * with English fallbacks for anything untranslated.
 */
class Wow
{
    public const RACES = [
        1 => 'Human', 2 => 'Orc', 3 => 'Dwarf', 4 => 'Night Elf', 5 => 'Undead',
        6 => 'Tauren', 7 => 'Gnome', 8 => 'Troll', 9 => 'Goblin', 10 => 'Blood Elf',
        11 => 'Draenei', 22 => 'Worgen', 24 => 'Pandaren', 25 => 'Pandaren',
        26 => 'Pandaren', 27 => 'Nightborne', 28 => 'Highmountain Tauren',
        29 => 'Void Elf', 30 => 'Lightforged Draenei', 31 => 'Zandalari Troll',
        32 => 'Kul Tiran', 34 => 'Dark Iron Dwarf', 35 => 'Vulpera',
        36 => "Mag'har Orc", 37 => 'Mechagnome', 52 => 'Dracthyr', 70 => 'Dracthyr',
        84 => 'Earthen', 85 => 'Earthen', 86 => 'Haranir', 91 => 'Haranir',
    ];

    public const CLASSES = [
        1 => 'Warrior', 2 => 'Paladin', 3 => 'Hunter', 4 => 'Rogue', 5 => 'Priest',
        6 => 'Death Knight', 7 => 'Shaman', 8 => 'Mage', 9 => 'Warlock',
        10 => 'Monk', 11 => 'Druid', 12 => 'Demon Hunter', 13 => 'Evoker',
        14 => 'Adventurer', 15 => 'Traveler',
    ];

    /** Equipped-item slots in character_inventory order (bag 0, slot 0-18). */
    public const SLOTS = [
        0 => 'Head', 1 => 'Neck', 2 => 'Shoulder', 3 => 'Shirt', 4 => 'Chest',
        5 => 'Waist', 6 => 'Legs', 7 => 'Feet', 8 => 'Wrist', 9 => 'Hands',
        10 => 'Ring 1', 11 => 'Ring 2', 12 => 'Trinket 1', 13 => 'Trinket 2',
        14 => 'Back', 15 => 'Main Hand', 16 => 'Off Hand', 17 => 'Ranged',
        18 => 'Tabard',
    ];

    public const QUALITY = ['poor', 'common', 'uncommon', 'rare', 'epic', 'legendary', 'artifact', 'heirloom'];

    /** ItemModType ids -> stat labels (ItemTemplate.h). */
    public const STATS = [
        0 => 'Mana', 1 => 'Health', 3 => 'Agility', 4 => 'Strength', 5 => 'Intellect',
        6 => 'Spirit', 7 => 'Stamina', 12 => 'Defense Skill Rating', 13 => 'Dodge Rating',
        14 => 'Parry Rating', 15 => 'Block Rating', 16 => 'Hit Melee Rating',
        17 => 'Hit Ranged Rating', 18 => 'Hit Spell Rating', 19 => 'Crit Melee Rating',
        20 => 'Crit Ranged Rating', 21 => 'Crit Spell Rating', 22 => 'Corruption',
        23 => 'Corruption Resistance', 26 => 'Crit Taken Ranged Rating',
        27 => 'Crit Taken Spell Rating', 28 => 'Haste Melee Rating',
        29 => 'Haste Ranged Rating', 30 => 'Haste Spell Rating', 31 => 'Hit Rating',
        32 => 'Critical Strike', 33 => 'Hit Taken Rating', 34 => 'Crit Taken Rating',
        35 => 'PvP Resilience', 36 => 'Haste', 37 => 'Expertise Rating',
        38 => 'Attack Power', 39 => 'Ranged Attack Power', 40 => 'Versatility',
        41 => 'Healing Done', 42 => 'Damage Done', 43 => 'Mana Regeneration',
        44 => 'Armor Penetration', 45 => 'Spell Power', 46 => 'Health Regeneration',
        47 => 'Spell Penetration', 48 => 'Block Value', 49 => 'Mastery',
        50 => 'Bonus Armor', 51 => 'Fire Resistance', 52 => 'Frost Resistance',
        53 => 'Holy Resistance', 54 => 'Shadow Resistance', 55 => 'Nature Resistance',
        56 => 'Arcane Resistance', 57 => 'PvP Power', 61 => 'Speed', 62 => 'Leech',
        63 => 'Avoidance', 64 => 'Sturdiness', 71 => 'Agility, Strength or Intellect',
        72 => 'Agility or Strength', 73 => 'Agility or Intellect',
        74 => 'Strength or Intellect',
    ];

    /** InventoryType ids -> equip-slot labels (ItemTemplate.h, 0-34). */
    public const INV_TYPES = [
        0 => 'Non-equippable', 1 => 'Head', 2 => 'Neck', 3 => 'Shoulder', 4 => 'Shirt',
        5 => 'Chest', 6 => 'Waist', 7 => 'Legs', 8 => 'Feet', 9 => 'Wrist',
        10 => 'Hands', 11 => 'Finger', 12 => 'Trinket', 13 => 'One-Hand',
        14 => 'Shield', 15 => 'Ranged', 16 => 'Back', 17 => 'Two-Hand', 18 => 'Bag',
        19 => 'Tabard', 20 => 'Robe', 21 => 'Main Hand', 22 => 'Off Hand',
        23 => 'Held In Off-hand', 24 => 'Ammo', 25 => 'Thrown', 26 => 'Ranged',
        27 => 'Quiver', 28 => 'Relic', 29 => 'Profession Tool',
        30 => 'Profession Gear',
    ];

    public const BONDING = [
        0 => '', 1 => 'Binds when picked up', 2 => 'Binds when equipped',
        3 => 'Binds when used', 4 => 'Quest Item', 5 => 'Quest Item',
    ];

    /**
     * BLP->webp icon CDN keyed by IconFileDataID (retail shard of warcraftdb;
     * the mop. subdomain is their classic database).
     */
    public const ICON_BASE = 'https://warcraftdb.com/api/v1/casc/blp/';

    /** race id -> portrait filename stem (charactercreate-races-{key}-{gender}). */
    public const PORTRAIT_KEYS = [
        1 => 'human', 2 => 'orc', 3 => 'dwarf', 4 => 'nightelf', 5 => 'undead',
        6 => 'tauren', 7 => 'gnome', 8 => 'troll', 9 => 'goblin', 10 => 'bloodelf',
        11 => 'draenei', 22 => 'worgen', 24 => 'panda', 25 => 'panda', 26 => 'panda',
        27 => 'nightborne', 28 => 'highmountain', 29 => 'voidelf',
        30 => 'lightforged', 31 => 'zandalari', 32 => 'kultiranhuman',
        34 => 'darkirondwarf', 35 => 'vulpera', 36 => 'maghar', 37 => 'mechagnome',
        52 => 'dracthyr', 70 => 'dracthyr', 84 => 'earthen', 85 => 'earthen',
        86 => 'haranir', 91 => 'haranir',
    ];

    private static function lookup(string $prefix, array $map, int $id, string $fallback): string
    {
        $key = $prefix . '.' . $id;
        $t = \t($key);
        return $t !== $key ? $t : ($map[$id] ?? $fallback);
    }

    public static function raceName(int $id): string
    {
        return self::lookup('race', self::RACES, $id, 'Race ' . $id);
    }

    public static function className(int $id): string
    {
        return self::lookup('class', self::CLASSES, $id, 'Class ' . $id);
    }

    public static function slotName(int $id): string
    {
        return self::lookup('slot', self::SLOTS, $id, 'Slot ' . $id);
    }

    public static function statName(int $id): string
    {
        return self::lookup('stat', self::STATS, $id, 'Stat ' . $id);
    }

    public static function invTypeName(int $id): string
    {
        return self::lookup('invtype', self::INV_TYPES, $id, '');
    }

    public static function bondingName(int $id): string
    {
        return self::lookup('bonding', self::BONDING, $id, '');
    }

    /** Expansion display names keyed by quest_template.Expansion. */
    public const EXPANSIONS = [
        0 => 'Classic', 1 => 'The Burning Crusade', 2 => 'Wrath of the Lich King',
        3 => 'Cataclysm', 4 => 'Mists of Pandaria', 5 => 'Warlords of Draenor',
        6 => 'Legion', 7 => 'Battle for Azeroth', 8 => 'Shadowlands',
        9 => 'Dragonflight', 10 => 'The War Within', 11 => 'Midnight',
    ];

    public static function expansionName(int $id): ?string
    {
        return self::EXPANSIONS[$id] ?? null; // -1/-2 = legacy/current → null
    }

    public static function qualityClass(?int $id): string
    {
        return 'q-' . (self::QUALITY[$id ?? 1] ?? 'common');
    }

    public static function qualityName(?int $id): string
    {
        return self::lookup('quality', self::QUALITY, $id ?? 1, '');
    }

    /**
     * Theme-relative portrait path for a race+gender, or null when the
     * race has no portrait (custom race etc.). Gender: 0=male, 1=female.
     */
    public static function portraitPath(int $race, int $gender): ?string
    {
        $key = self::PORTRAIT_KEYS[$race] ?? null;
        if ($key === null) {
            return null;
        }
        $path = 'img/races/' . $key . '-' . ($gender === 1 ? 'female' : 'male') . '.png';
        $file = PUBLIC_DIR . '/themes/' . Theme::name() . '/' . $path;
        return is_file($file) ? $path : null;
    }

    /** Icon CDN URL for an IconFileDataID, or null when unavailable. */
    public static function iconUrl(?int $fdid): ?string
    {
        return $fdid ? self::ICON_BASE . $fdid . '.webp' : null;
    }
}
