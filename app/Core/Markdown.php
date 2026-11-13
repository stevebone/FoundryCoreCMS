<?php

namespace App\Core;

/**
 * Small, safe Markdown subset for news articles.
 * Raw HTML is escaped first — authors cannot inject markup or scripts.
 * Supports: ##/### headings, **bold**, *italic*, `code`, ![img](url),
 * [link](url), - / 1. lists, > quotes, --- rules, paragraphs.
 */
class Markdown
{
    public static function render(string $text): string
    {
        $text = str_replace("\r\n", "\n", trim($text));
        if ($text === '') {
            return '';
        }

        $out = [];
        $list = null; // 'ul' | 'ol' | null
        $quote = [];

        $flushList = function () use (&$list, &$out) {
            if ($list) {
                $out[] = "</{$list}>";
                $list = null;
            }
        };
        $flushQuote = function () use (&$quote, &$out) {
            if ($quote) {
                $out[] = '<blockquote>' . implode('<br>', $quote) . '</blockquote>';
                $quote = [];
            }
        };

        foreach (explode("\n", $text) as $line) {
            $t = trim($line);

            if ($t === '') {                       // paragraph break
                $flushList(); $flushQuote();
                continue;
            }
            if (preg_match('/^---+\s*$/', $t)) {   // horizontal rule
                $flushList(); $flushQuote();
                $out[] = '<hr>';
                continue;
            }
            if (preg_match('/^(#{2,3})\s+(.+)$/', $t, $m)) {      // headings
                $flushList(); $flushQuote();
                $lvl = strlen($m[1]);
                $out[] = "<h{$lvl}>" . self::inline($m[2]) . "</h{$lvl}>";
                continue;
            }
            if (preg_match('/^>\s?(.*)$/', $t, $m)) {             // quote line
                $flushList();
                $quote[] = self::inline($m[1]);
                continue;
            }
            if (preg_match('/^[-*]\s+(.+)$/', $t, $m)) {          // bullet list
                $flushQuote();
                if ($list !== 'ul') { $flushList(); $out[] = '<ul>'; $list = 'ul'; }
                $out[] = '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }
            if (preg_match('/^\d+[.)]\s+(.+)$/', $t, $m)) {       // numbered list
                $flushQuote();
                if ($list !== 'ol') { $flushList(); $out[] = '<ol>'; $list = 'ol'; }
                $out[] = '<li>' . self::inline($m[1]) . '</li>';
                continue;
            }
            $flushList(); $flushQuote();
            $out[] = '<p>' . self::inline($t) . '</p>';
        }
        $flushList(); $flushQuote();

        return implode("\n", $out);
    }

    /** Inline formatting on already-plain text; escapes everything first. */
    private static function inline(string $s): string
    {
        $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        // ![alt](url) images — url must be site-relative or http(s)
        $s = preg_replace_callback(
            '/!\[([^\]]*)\]\(([^)\s]+)\)/',
            fn($m) => self::safeUrl($m[2]) !== null
                ? '<img class="md-img" src="' . self::safeUrl($m[2]) . '" alt="' . $m[1] . '" loading="lazy">'
                : $m[0],
            $s
        );
        // [text](url) links
        $s = preg_replace_callback(
            '/(?<!!)\[([^\]]+)\]\(([^)\s]+)\)/',
            fn($m) => self::safeUrl($m[2]) !== null
                ? '<a href="' . self::safeUrl($m[2]) . '">' . $m[1] . '</a>'
                : $m[0],
            $s
        );
        // `code`, **bold**, *italic*
        $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $s);

        return $s;
    }

    /** Only site-relative or http(s) URLs are allowed in links/images. */
    private static function safeUrl(string $url): ?string
    {
        return preg_match('#^(/|https?://)#i', $url) ? $url : null;
    }
}
