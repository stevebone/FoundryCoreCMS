<?php
/**
 * Bake guild emblem textures into theme assets.
 *
 * For every cms.guild_emblem row, downloads the Blizzard emblem texture
 * (a white sigil w/ alpha on a 128x256 tabard canvas) from the warcraftdb
 * CDN, crops it to the opaque sigil bounding box (+padding) and saves it
 * to public/themes/<theme>/img/guild/e{EmblemID}.webp.
 *
 * Templates tint the white sigil via CSS mask -> the guild's EmblemColor.
 * Re-run any time; skips files that already exist unless --force.
 *
 * Usage: php tools/fetch_guild_emblems.php [--force]
 */

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

$force = in_array('--force', $argv, true);
$base = 'https://warcraftdb.com/api/v1/casc/blp/';
$outDir = dirname(__DIR__) . '/public/themes/blizzard/img/guild';
is_dir($outDir) || mkdir($outDir, 0775, true);

$cfg = require dirname(__DIR__) . '/config.php';
$db = $cfg['db'];
$pdo = new PDO(
    "mysql:host={$db['host']};port={$db['port']};dbname=cms;charset=utf8mb4",
    $db['user'], $db['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$rows = $pdo->query('SELECT EmblemID, TextureFileDataID FROM guild_emblem ORDER BY EmblemID')->fetchAll(PDO::FETCH_ASSOC);

// Opaque-pixel bounding box of a GD image (alpha channel).
$bbox = function ($im): ?array {
    $w = imagesx($im); $h = imagesy($im);
    $minX = $w; $minY = $h; $maxX = -1; $maxY = -1;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            if (((imagecolorat($im, $x, $y) >> 24) & 0x7F) < 120) {
                if ($x < $minX) $minX = $x;
                if ($x > $maxX) $maxX = $x;
                if ($y < $minY) $minY = $y;
                if ($y > $maxY) $maxY = $y;
            }
        }
    }
    return $maxX < 0 ? null : [$minX, $minY, $maxX - $minX + 1, $maxY - $minY + 1];
};

$ctx = stream_context_create(['http' => ['timeout' => 20, 'user_agent' => 'cms-emblem-bake/1.0']]);
$done = $skipped = $failed = 0;
foreach ($rows as $r) {
    $id = (int) $r['EmblemID'];
    $out = "$outDir/e{$id}.webp";
    if (!$force && is_file($out)) {
        $skipped++;
        continue;
    }
    $bin = @file_get_contents($base . (int) $r['TextureFileDataID'] . '.webp', false, $ctx);
    $im = $bin ? @imagecreatefromstring($bin) : false;
    if (!$im) {
        fwrite(STDERR, "  !! e{$id} fdid {$r['TextureFileDataID']} fetch/decode failed\n");
        $failed++;
        continue;
    }
    $box = $bbox($im);
    if ($box) {
        [$x, $y, $w, $h] = $box;
        // Emblem textures are the RIGHT HALF of a symmetric sigil, stored
        // left-aligned: the design's centerline is its left edge. Crop the
        // half, mirror it to the left and join at that edge -> full sigil.
        $half = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h]);
        if ($half) {
            $full = imagecreatetruecolor($w * 2, $h);
            imagealphablending($full, false);
            imagesavealpha($full, true);
            imagefill($full, 0, 0, imagecolorallocatealpha($full, 0, 0, 0, 127));
            imageflip($half, IMG_FLIP_HORIZONTAL);
            imagecopy($full, $half, 0, 0, 0, 0, $w, $h);
            imageflip($half, IMG_FLIP_HORIZONTAL);
            imagecopy($full, $half, $w, 0, 0, 0, $w, $h);
            $im = $full;
        }
        // Small transparent margin so the mask doesn't touch the tile edge.
        $pad = (int) round(max(imagesx($im), imagesy($im)) * 0.05);
        $padded = imagecreatetruecolor(imagesx($im) + 2 * $pad, imagesy($im) + 2 * $pad);
        imagealphablending($padded, false);
        imagesavealpha($padded, true);
        imagefill($padded, 0, 0, imagecolorallocatealpha($padded, 0, 0, 0, 127));
        imagecopy($padded, $im, $pad, $pad, 0, 0, imagesx($im), imagesy($im));
        $im = $padded;
    }
    imagewebp($im, $out, 90);
    $done++;
}
echo "Baked $done emblem(s)" . ($skipped ? ", skipped $skipped existing" : '')
   . ($failed ? ", $failed failed" : '') . " -> $outDir\n";
