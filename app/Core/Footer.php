<?php

namespace App\Core;

/**
 * Footer structure + admin-controlled visibility.
 * The columns/links are defined once here and shared by the footer
 * partial and the Site Settings admin form. Visibility/title overrides
 * live in cms.site_settings as footer.* keys (absent = shown/default).
 */
class Footer
{
    public static function columns(): array
    {
        return [
            1 => [
                'title_key' => 'footer.col_game',
                'links' => [
                    ['t' => 'nav.home', 'url' => '/'],
                    ['t' => 'nav.game_info', 'url' => '/#editions'],
                    ['t' => 'nav.news', 'url' => '/#news'],
                    ['t' => 'footer.link_status', 'url' => '#status'],
                ],
            ],
            2 => [
                'title_key' => 'footer.col_database',
                'db_only'   => true,
                'links' => [
                    ['t' => 'db.items', 'url' => '/db/items'],
                    ['t' => 'db.quests', 'url' => '/db/quests'],
                    ['t' => 'db.creatures', 'url' => '/db/creatures'],
                    ['t' => 'db.objects', 'url' => '/db/objects'],
                ],
            ],
            3 => [
                'title_key' => 'footer.col_support',
                'links' => [
                    ['t' => 'footer.link_howto', 'url' => '#'],
                    ['t' => 'footer.link_downloads', 'url' => '#'],
                    ['t' => 'footer.link_contact', 'url' => '#'],
                ],
            ],
            4 => [
                'title_key' => 'footer.col_account',
                'links' => [
                    ['t' => 'footer.link_register', 'url' => '/register'],
                    ['t' => 'footer.link_myaccount', 'url' => '/settings'],
                    ['t' => 'footer.link_characters', 'url' => '/armory'],
                ],
            ],
        ];
    }

    public static function socialOn(): bool
    {
        return self::on('footer.social');
    }

    /**
     * Supported social platforms: key => label + inline SVG icon markup
     * (viewBox 0 0 24 24, fill/stroke = currentColor).
     */
    public static function socials(): array
    {
        return [
            'discord' => [
                'label' => 'Discord',
                'svg' => '<path d="M19.6 5.3A16 16 0 0 0 15.7 4l-.2.4a13 13 0 0 1 3.2 1.6 13.4 13.4 0 0 0-11.4 0A13 13 0 0 1 10.5 4.4L10.3 4a16 16 0 0 0-3.9 1.3A18.3 18.3 0 0 0 4 18.1a16.2 16.2 0 0 0 5 2.5l.4-.7a11.6 11.6 0 0 1-2-1l.5-.4a12.8 12.8 0 0 0 8.2 0l.5.4a11.6 11.6 0 0 1-2 1l.4.7a16.2 16.2 0 0 0 5-2.5 18.3 18.3 0 0 0-2.4-12.8zM9.7 15.4c-.9 0-1.6-.8-1.6-1.8s.7-1.8 1.6-1.8 1.6.8 1.6 1.8-.7 1.8-1.6 1.8zm4.6 0c-.9 0-1.6-.8-1.6-1.8s.7-1.8 1.6-1.8 1.6.8 1.6 1.8-.7 1.8-1.6 1.8z"/>',
            ],
            'youtube' => [
                'label' => 'YouTube',
                'svg' => '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.3 5 12 5 12 5s-6.3 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8c1.5.4 7.8.4 7.8.4s6.3 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15.2V8.8l5.2 3.2z"/>',
            ],
            'x' => [
                'label' => 'X',
                'svg' => '<path d="M17.5 3h3.1l-6.8 7.8L21.8 21h-6.3l-4.9-6.4L5 21H1.9l7.3-8.3L2.2 3h6.4l4.4 5.9zm-1.1 16h1.7L7.1 4.7H5.3z"/>',
            ],
            'reddit' => [
                'label' => 'Reddit',
                'svg' => '<path fill-rule="evenodd" d="M12 2.6c.4 0 .7.3.7.7l.4 3.6c.3 0 .6 0 .9.1l2.2-1.4c.3-.2.7-.3 1-.3 1 0 1.8.8 1.8 1.8 0 .8-.5 1.4-1.2 1.7.5.9.8 2 .8 3.1 0 3.5-3 6.7-6.6 6.7S5.4 15.3 5.4 11.8c0-1.1.3-2.2.8-3.1-.7-.3-1.2-1-1.2-1.7 0-1 .8-1.8 1.8-1.8.4 0 .7.1 1 .3l2.2 1.4c.3-.1.6-.1.9-.1l.4-3.6c0-.4.3-.6.7-.6zM9.3 10.6a1.3 1.3 0 1 0 .001 2.6A1.3 1.3 0 0 0 9.3 10.6zm5.4 0a1.3 1.3 0 1 0 .001 2.6 1.3 1.3 0 0 0-.001-2.6zM8.6 14.6a.6.6 0 0 1 .9-.8c.8.6 1.6.9 2.5.9s1.7-.3 2.5-.9a.6.6 0 0 1 .9.8c-1 .8-2.1 1.2-3.4 1.2s-2.4-.4-3.4-1.2z"/>',
            ],
            'bluesky' => [
                'label' => 'Bluesky',
                'svg' => '<path d="M12 3c-2 3.6-5 5.4-8.7 4.7 1.2 3 4 4.4 7.4 3.7-3.3.9-5.2 3.5-4 7.1 3.4-1.2 5-3.6 5-7 0 3.4 1.6 5.8 5 7 1.2-3.6-.7-6.2-4-7.1 3.4.7 6.2-.7 7.4-3.7C16.9 8.4 14 6.6 12 3z"/>',
            ],
            'instagram' => [
                'label' => 'Instagram',
                'svg' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.5" ry="4.5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17" cy="7" r="1.3"/>',
            ],
            'facebook' => [
                'label' => 'Facebook',
                'svg' => '<path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.5 1.5-1.5h1.5V5H14c-2.5 0-4 1.7-4 4.2V11H7.5v3H10v7h3.5z"/>',
            ],
        ];
    }

    /** Configured URL for a social platform, or null when unset. */
    public static function socialUrl(string $key): ?string
    {
        return SiteSettings::get("footer.social.{$key}");
    }

    /** Platforms that have a URL configured (what the footer renders). */
    public static function socialLinks(): array
    {
        $out = [];
        foreach (self::socials() as $key => $s) {
            if ($url = self::socialUrl($key)) {
                $out[] = $s + ['url' => $url];
            }
        }
        return $out;
    }

    public static function colOn(int $n): bool
    {
        return self::on("footer.col{$n}.show");
    }

    public static function linkOn(int $col, int $link): bool
    {
        return self::on("footer.col{$col}.link{$link}.show");
    }

    /** Custom admin-set column title, or the translated default. */
    public static function colTitle(int $n, string $fallbackKey): string
    {
        return SiteSettings::get("footer.col{$n}.title") ?: t($fallbackKey);
    }

    private static function on(string $key): bool
    {
        return (SiteSettings::get($key) ?? '1') === '1';
    }
}
