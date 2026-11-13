<?php

namespace App\Core;

use PDO;

/**
 * Guild data: listing + roster detail.
 * Reads TrinityCore characters-DB guild tables only — no writes.
 *
 * Schema (TC): guild(guildid,name,leaderguid,Emblem*,createdate,BankMoney,motd,info),
 * guild_member(guildid,guid,rank), guild_rank(guildid,rid,RankOrder,rname).
 */
class Guild
{
    private const EMBLEM_COLS = 'g.EmblemStyle, g.EmblemColor, g.BorderStyle, g.BorderColor, g.BackgroundColor';

    /** All guilds with member count, leader name, and majority faction. */
    public static function guilds(): array
    {
        $rows = Db::characters()->query(
            'SELECT g.guildid, g.name, g.leaderguid, g.createdate, g.BankMoney, ' . self::EMBLEM_COLS . ',
                    COUNT(gm.guid) AS members
             FROM guild g
             LEFT JOIN guild_member gm ON gm.guildid = g.guildid
             GROUP BY g.guildid
             ORDER BY g.name'
        )->fetchAll();

        foreach ($rows as &$g) {
            $g['leader'] = self::charName((int) $g['leaderguid']);
            $g['faction'] = self::guildFaction((int) $g['guildid'], (int) $g['leaderguid']);
        }
        return $rows;
    }

    /**
     * One guild with ranks + full roster for the detail page.
     * members: guid,name,race,class,gender,level,online,rid,rankName.
     */
    public static function detail(int $id): ?array
    {
        $c = Db::characters();
        $stmt = $c->prepare(
            'SELECT g.guildid, g.name, g.leaderguid, g.createdate, g.BankMoney, g.motd, g.info, '
            . self::EMBLEM_COLS . '
             FROM guild g WHERE g.guildid = ?'
        );
        $stmt->execute([$id]);
        $guild = $stmt->fetch();
        if (!$guild) {
            return null;
        }

        $ranks = [];
        $stmt = $c->prepare('SELECT rid, rname FROM guild_rank WHERE guildid = ? ORDER BY RankOrder, rid');
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll() as $r) {
            $ranks[(int) $r['rid']] = $r['rname'];
        }

        $stmt = $c->prepare(
            'SELECT gm.guid, gm.rank AS rid, c.name, c.race, c.class, c.gender, c.level, c.online
             FROM guild_member gm
             JOIN characters c ON c.guid = gm.guid
             WHERE gm.guildid = ?
             ORDER BY gm.rank, c.level DESC, c.name'
        );
        $stmt->execute([$id]);
        $members = $stmt->fetchAll();
        foreach ($members as &$m) {
            $m['rankName'] = $ranks[(int) $m['rid']] ?? 'Rank ' . (int) $m['rid'];
        }

        return [
            'guild' => $guild,
            'ranks' => $ranks,
            'members' => $members,
            'leader' => self::charName((int) $guild['leaderguid']),
            'faction' => self::factionFromMembers($members, (int) $guild['leaderguid']),
        ];
    }

    /** Majority race faction over the roster; leader race breaks ties/fills empty. */
    private static function guildFaction(int $guildId, int $leaderGuid): string
    {
        $stmt = Db::characters()->prepare(
            'SELECT c.guid, c.race FROM guild_member gm
             JOIN characters c ON c.guid = gm.guid WHERE gm.guildid = ?'
        );
        $stmt->execute([$guildId]);
        return self::factionFromMembers($stmt->fetchAll(), $leaderGuid);
    }

    private static function factionFromMembers(array $members, int $leaderGuid): string
    {
        $votes = ['alliance' => 0, 'horde' => 0];
        $leaderFaction = 'neutral';
        foreach ($members as $m) {
            $f = Wow::raceFaction((int) $m['race']);
            if (isset($votes[$f])) {
                $votes[$f]++;
            }
            if ((int) $m['guid'] === $leaderGuid) {
                $leaderFaction = $f;
            }
        }
        if ($votes['alliance'] !== $votes['horde']) {
            return $votes['alliance'] > $votes['horde'] ? 'alliance' : 'horde';
        }
        return $leaderFaction !== 'neutral' ? $leaderFaction : 'neutral';
    }

    /**
     * Guild perks (cms.guild_perk_spells -> spell name/description/icon).
     * All guilds share the same perk set; returns [] when the table is empty.
     */
    public static function perks(): array
    {
        $cms = Db::cms();
        $rows = $cms->query(
            'SELECT gps.SpellID, sn.Name_lang AS name, s.Description_lang AS descr,
                    sm.SpellIconFileDataID AS iconFdid
             FROM guild_perk_spells gps
             LEFT JOIN spell_name sn ON sn.ID = gps.SpellID
             LEFT JOIN spell s ON s.ID = gps.SpellID
             LEFT JOIN spell_misc sm ON sm.SpellID = gps.SpellID
             ORDER BY gps.ID'
        )->fetchAll();
        if (!$rows) {
            return [];
        }

        // Effect magnitudes for $sN/$mN tokens, durations for $<spellId>d.
        $ids = array_map('intval', array_column($rows, 'SpellID'));
        $in = implode(',', array_fill(0, count($ids), '?'));
        $eff = [];
        $stmt = $cms->prepare(
            "SELECT SpellID, EffectIndex, EffectBasePointsF FROM spell_effect WHERE SpellID IN ($in)"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $e) {
            $eff[(int) $e['SpellID']][(int) $e['EffectIndex'] + 1] = (float) $e['EffectBasePointsF'];
        }
        preg_match_all('/\$(\d+)d/', implode(' ', array_map('strval', array_column($rows, 'descr'))), $dm);
        $durIds = array_unique(array_merge($ids, array_map('intval', $dm[1])));
        $in = implode(',', array_fill(0, count($durIds), '?'));
        $dur = [];
        $stmt = $cms->prepare(
            "SELECT sm.SpellID, sd.Duration FROM spell_misc sm
             JOIN spell_duration sd ON sd.ID = sm.DurationIndex
             WHERE sm.SpellID IN ($in)"
        );
        $stmt->execute($durIds);
        foreach ($stmt->fetchAll() as $d) {
            $dur[(int) $d['SpellID']] = (int) $d['Duration'];
        }

        foreach ($rows as &$r) {
            $r['icon'] = isset($r['iconFdid']) ? Wow::iconUrl((int) $r['iconFdid']) : null;
            $r['descr'] = self::spellText(
                (string) ($r['descr'] ?? ''),
                $eff[(int) $r['SpellID']] ?? [],
                $dur
            );
        }
        return $rows;
    }

    /**
     * Resolve WoW description tokens: ${expr} blocks, $<spellId>d durations,
     * and bare $sN/$mN effect magnitudes (1-indexed EffectIndex).
     */
    private static function spellText(string $text, array $eff, array $dur): string
    {
        $fmt = static fn ($v) => (float) $v == (int) $v
            ? (string) (int) $v
            : rtrim(rtrim(sprintf('%.2F', $v), '0'), '.');

        $text = preg_replace_callback('/\$\{([^}]*)\}/', static function ($m) use ($eff, $fmt) {
            $expr = preg_replace_callback(
                '/\$[ms](\d+)/',
                static fn ($x) => (string) ($eff[(int) $x[1]] ?? 0),
                $m[1]
            );
            if (!preg_match('/^[\d.+\-*\/() ]+$/', $expr)) {
                return $m[0];
            }
            try {
                return $fmt(eval("return $expr;"));
            } catch (\Throwable) {
                return $m[0];
            }
        }, $text);

        $text = preg_replace_callback('/\$(\d+)d/', static function ($m) use ($dur) {
            $ms = $dur[(int) $m[1]] ?? 0;
            return $ms ? self::duration($ms) : $m[0];
        }, $text);

        return preg_replace_callback(
            '/\$[ms](\d+)/',
            static fn ($x) => $fmt($eff[(int) $x[1]] ?? 0),
            $text
        );
    }

    /** Milliseconds -> "5 min" / "2 hrs" / "1 day" style duration. */
    private static function duration(int $ms): string
    {
        $s = intdiv($ms, 1000);
        foreach ([86400 => 'day', 3600 => 'hr', 60 => 'min', 1 => 'sec'] as $unit => $label) {
            if ($s >= $unit && $s % $unit === 0) {
                $n = intdiv($s, $unit);
                return $n . ' ' . $label . ($n === 1 || $label === 'min' ? '' : 's');
            }
        }
        return $s . ' sec';
    }

    /** Achievement points / earned count / in-progress count for the banner box. */
    public static function achievementStats(int $guildId): array
    {
        $c = Db::characters();
        $stmt = $c->prepare('SELECT achievement FROM guild_achievement WHERE guildId = ?');
        $stmt->execute([$guildId]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $points = 0;
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = Db::cms()->prepare("SELECT COALESCE(SUM(Points),0) FROM achievement WHERE ID IN ($in)");
            $stmt->execute($ids);
            $points = (int) $stmt->fetchColumn();
        }
        $stmt = $c->prepare('SELECT COUNT(*) FROM guild_achievement_progress WHERE guildId = ?');
        $stmt->execute([$guildId]);
        return [
            'points' => $points,
            'earned' => count($ids),
            'progress' => (int) $stmt->fetchColumn(),
        ];
    }

    /** Earned achievements for the ACHIEVEMENTS tab (icon/name/points/date desc). */
    public static function achievements(int $guildId): array
    {
        $stmt = Db::characters()->prepare(
            'SELECT achievement, `date` FROM guild_achievement WHERE guildId = ? ORDER BY `date` DESC'
        );
        $stmt->execute([$guildId]);
        $rows = $stmt->fetchAll();
        if (!$rows) {
            return [];
        }
        $ids = array_map('intval', array_column($rows, 'achievement'));
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Db::cms()->prepare(
            "SELECT ID, Title_lang, Points, IconFileID FROM achievement WHERE ID IN ($in)"
        );
        $stmt->execute($ids);
        $meta = [];
        foreach ($stmt->fetchAll() as $a) {
            $meta[(int) $a['ID']] = $a;
        }
        foreach ($rows as &$r) {
            $a = $meta[(int) $r['achievement']] ?? [];
            $r['title'] = $a['Title_lang'] ?? 'Achievement #' . (int) $r['achievement'];
            $r['points'] = (int) ($a['Points'] ?? 0);
            $r['icon'] = isset($a['IconFileID']) ? Wow::iconUrl((int) $a['IconFileID']) : null;
        }
        return $rows;
    }

    /**
     * Real emblem colors for a guild row (cms.guild_color_* tables, keyed by
     * the ids stored on the guild). DB2 column order is Red,Blue,Green —
     * hex is composed by field name, not position.
     * Returns ['style'=>EmblemID|null, 'bg'=>hex, 'border'=>hex, 'emblem'=>hex].
     */
    public static function emblemColors(array $g): array
    {
        $cms = Db::cms();
        $color = function (string $table, int $id) use ($cms): string {
            static $cache = [];
            if (!array_key_exists($table . ':' . $id, $cache)) {
                $stmt = $cms->prepare("SELECT Red, Green, `Blue` FROM {$table} WHERE ID = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                $cache[$table . ':' . $id] = $row
                    ? sprintf('#%02X%02X%02X', (int) $row['Red'], (int) $row['Green'], (int) $row['Blue'])
                    : null;
            }
            return $cache[$table . ':' . $id];
        };
        return [
            'style' => isset($g['EmblemStyle']) ? (int) $g['EmblemStyle'] : null,
            'bg' => $color('guild_color_background', (int) ($g['BackgroundColor'] ?? 0)) ?? '#1c2130',
            'border' => $color('guild_color_border', (int) ($g['BorderColor'] ?? 0)) ?? '#555c70',
            'emblem' => $color('guild_color_emblem', (int) ($g['EmblemColor'] ?? 0)) ?? '#c8ccd8',
        ];
    }

    private static function charName(int $guid): ?string
    {
        if (!$guid) {
            return null;
        }
        $stmt = Db::characters()->prepare('SELECT name FROM characters WHERE guid = ?');
        $stmt->execute([$guid]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Copper -> "1 234g" short label for the banner stat. */
    public static function moneyShort(int $copper): string
    {
        $gold = intdiv($copper, 10000);
        return $gold > 0 ? number_format($gold) . 'g' : ($copper > 0 ? intdiv($copper, 100) . 's' : '0');
    }
}
