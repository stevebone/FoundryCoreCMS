<?php

namespace App\Core;

/**
 * Site-wide settings stored as overrides in cms.site_settings.
 * A key absent from the table falls back to config.php values.
 */
class SiteSettings
{
    /** Keys the admin panel is allowed to write (footer.* prefix is also allowed). */
    public const EDITABLE = ['app.name', 'app.theme', 'app.default_lang'];

    private static function allowed(string $key): bool
    {
        return in_array($key, self::EDITABLE, true) || str_starts_with($key, 'footer.');
    }

    private static ?array $all = null;

    /** Every stored override: ['app.name' => 'StefalWoW', ...]. */
    public static function all(): array
    {
        if (self::$all === null) {
            try {
                self::$all = Db::cms()
                    ->query('SELECT `key`, `value` FROM site_settings')
                    ->fetchAll(\PDO::FETCH_KEY_PAIR);
            } catch (\Throwable) {
                self::$all = [];
            }
        }
        return self::$all;
    }

    public static function get(string $key): ?string
    {
        $v = self::all()[$key] ?? null;
        return $v === '' ? null : $v;
    }

    /** Upsert whitelisted overrides; null clears a key back to config default. */
    public static function save(array $overrides): void
    {
        $rows = [];
        foreach ($overrides as $key => $v) {
            if (self::allowed($key)) {
                $v = $overrides[$key];
                $rows[] = $v === null || $v === ''
                    ? [$key, null]
                    : [$key, substr((string) $v, 0, 255)];
            }
        }
        if (!$rows) {
            return;
        }
        $pdo = Db::cms();
        $stmt = $pdo->prepare(
            'INSERT INTO site_settings (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        foreach ($rows as $r) {
            $stmt->execute($r);
        }
        self::$all = null;
    }
}
