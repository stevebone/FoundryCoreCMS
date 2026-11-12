<?php

namespace App\Core;

use PDO;

/**
 * Per-account CMS preferences stored in cms.account_settings.
 * NULL columns mean "use site default". Rows are cached per request so
 * Lang::resolve() and Theme::name() share one SELECT.
 */
class Settings
{
    /** Columns that may be written through save(). */
    private const KEYS = ['lang', 'theme', 'timezone'];

    /** @var array<int, ?array> */
    private static array $cache = [];

    /** All saved prefs for an account, or null if it never saved. */
    public static function for(int $bnetId): ?array
    {
        if (!array_key_exists($bnetId, self::$cache)) {
            $stmt = Db::cms()->prepare(
                'SELECT lang, theme, timezone FROM account_settings WHERE battlenet_account = ?'
            );
            $stmt->execute([$bnetId]);
            self::$cache[$bnetId] = $stmt->fetch() ?: null;
        }
        return self::$cache[$bnetId];
    }

    /** One pref for an account (or null = use default). */
    public static function value(int $bnetId, string $key): ?string
    {
        $prefs = self::for($bnetId);
        $v = $prefs[$key] ?? null;
        return $v === '' ? null : $v;
    }

    /**
     * Upsert prefs; unknown keys are ignored, null clears back to default.
     * @param array{lang?:?string,theme?:?string,timezone?:?string} $prefs
     */
    public static function save(int $bnetId, array $prefs): void
    {
        $cols = array_intersect_key($prefs, array_flip(self::KEYS));
        if (!$cols) {
            return;
        }
        $sets = implode(', ', array_map(fn($k) => "$k = VALUES($k)", array_keys($cols)));
        $sql = 'INSERT INTO account_settings (battlenet_account, '
            . implode(', ', array_keys($cols)) . ') VALUES (?, '
            . implode(', ', array_fill(0, count($cols), '?')) . ') ON DUPLICATE KEY UPDATE ' . $sets;
        Db::cms()->prepare($sql)->execute([$bnetId, ...array_values($cols)]);
        unset(self::$cache[$bnetId]);
    }
}
