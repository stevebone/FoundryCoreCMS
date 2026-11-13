<?php

namespace App\Core;

/**
 * News articles (cms_news): homepage feed, /news list, /news/{id} articles.
 */
class News
{
    /** Most recent articles for the homepage feed. */
    public static function latest(int $n = 4): array
    {
        $stmt = Db::cms()->prepare(
            'SELECT id, title, tag, short_desc, image, created_at
             FROM cms_news ORDER BY created_at DESC, id DESC LIMIT ?'
        );
        $stmt->bindValue(1, $n, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Paginated list for /news: [rows, total, pages]. */
    public static function page(int $page = 1, int $per = 12): array
    {
        $page = max(1, $page);
        $per = max(1, min(50, $per));
        $total = (int) Db::cms()->query('SELECT COUNT(*) FROM cms_news')->fetchColumn();
        $pages = max(1, (int) ceil($total / $per));
        $page = min($page, $pages);
        $stmt = Db::cms()->prepare(
            'SELECT id, title, tag, short_desc, image, created_at
             FROM cms_news ORDER BY created_at DESC, id DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->bindValue(1, $per, \PDO::PARAM_INT);
        $stmt->bindValue(2, ($page - 1) * $per, \PDO::PARAM_INT);
        $stmt->execute();
        return ['rows' => $stmt->fetchAll(), 'total' => $total,
                'page' => $page, 'pages' => $pages];
    }

    /** Single article, or null. */
    public static function get(int $id): ?array
    {
        $stmt = Db::cms()->prepare('SELECT * FROM cms_news WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Distinct tags already used (datalist suggestions for the editor). */
    public static function tags(): array
    {
        return Db::cms()
            ->query('SELECT DISTINCT tag FROM cms_news WHERE tag != \'\' ORDER BY tag')
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Create an article. Returns the new id.
     * $in: title (required), tag, short_desc, long_desc (required), image.
     */
    public static function create(array $in, ?int $authorId): int
    {
        $title = trim((string) ($in['title'] ?? ''));
        $body = trim((string) ($in['long_desc'] ?? ''));
        if ($title === '' || $body === '') {
            return 0;
        }
        $short = trim((string) ($in['short_desc'] ?? ''));
        if ($short === '') {
            $short = mb_substr(preg_replace('/\s+/', ' ', strip_tags($body)), 0, 200);
        }
        $image = trim((string) ($in['image'] ?? ''));
        if ($image !== '' && !preg_match('#^(/|https?://)#i', $image)) {
            $image = '';
        }
        $stmt = Db::cms()->prepare(
            'INSERT INTO cms_news (title, tag, short_desc, long_desc, image, author_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            mb_substr($title, 0, 255),
            mb_substr(trim((string) ($in['tag'] ?? '')) ?: 'Update', 0, 64),
            mb_substr($short, 0, 512),
            $body,
            $image !== '' ? mb_substr($image, 0, 255) : null,
            $authorId,
        ]);
        return (int) Db::cms()->lastInsertId();
    }
}
