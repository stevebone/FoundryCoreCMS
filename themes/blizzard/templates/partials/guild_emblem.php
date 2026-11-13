<?php
// Guild tabard tile: real DB2 colors + the official emblem texture.
// The baked texture (img/guild/e{EmblemID}.webp) is a white sigil w/ alpha;
// it's used as a CSS mask over the guild's EmblemColor so the shape and
// color are the real in-game ones.
use App\Core\Guild;

$ec = Guild::emblemColors($g ?? []);
$sigil = null;
if ($ec['style'] !== null) {
    $rel = 'img/guild/e' . $ec['style'] . '.webp';
    if (is_file(PUBLIC_DIR . '/themes/' . App\Core\Theme::name() . '/' . $rel)) {
        $sigil = $rel;
    }
}
?>
<span class="guild-emblem<?= !empty($large) ? ' guild-emblem-lg' : '' ?>"
      style="background:<?= e($ec['bg']) ?>; border-color:<?= e($ec['border']) ?>">
    <?php if ($sigil): ?>
        <span class="ge-sigil" style="background:<?= e($ec['emblem']) ?>;
              -webkit-mask-image:url('<?= e(asset($sigil)) ?>'); mask-image:url('<?= e(asset($sigil)) ?>')"></span>
    <?php endif; ?>
</span>
