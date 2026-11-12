<?php
use App\Core\Wow;

$leftSlots = [0, 1, 2, 14, 4, 3, 18];
$rightSlots = [8, 9, 5, 6, 7, 10, 11, 12, 13];
$weaponSlots = [15, 16, 17];

$renderSlot = function (int $slot) use ($items) {
    $item = $items[$slot] ?? null;
    $q = $item ? Wow::qualityClass($item['quality']) : '';
    echo '<div class="armory-slot">';
    echo '<span class="slot-name">' . e(Wow::slotName($slot)) . '</span>';
    if ($item) {
        $iconUrl = Wow::iconUrl($item['icon'] ?? null);
        echo '<a class="item-line item-link" href="/db/item/' . (int) $item['entry'] . '" data-item="' . (int) $item['entry'] . '">';
        if ($iconUrl) {
            echo '<img class="slot-icon ' . $q . '" src="' . e($iconUrl) . '" loading="lazy" alt="" onerror="this.style.visibility=\'hidden\'">';
        }
        echo '<span class="item-text">';
        echo '<span class="item-name ' . $q . '">' . e($item['name']) . '</span>';
        echo $item['ilvl'] ? '<span class="item-ilvl">' . e(t('armory.ilvl', ['ilvl' => $item['ilvl']])) . '</span>' : '';
        echo '</span></a>';
    } else {
        echo '<span class="item-name empty">' . t('armory.empty') . '</span>';
    }
    echo '</div>';
};

$portrait = $char ? Wow::portraitPath((int) $char['race'], (int) ($char['gender'] ?? 0)) : null;
$portraitUrl = $portrait ? asset($portrait) : null;
?>
<script>
window.AR_TT = <?= json_encode([
    'itemLevel' => t('armory.tt_ilvl'),
    'armor' => t('armory.tt_armor'),
    'damage' => t('armory.tt_damage'),
    'speed' => t('armory.tt_speed'),
    'dps' => t('armory.tt_dps'),
    'equip' => t('armory.tt_equip'),
    'use' => t('armory.tt_use'),
    'effect' => t('armory.tt_effect'),
    'reqLevel' => t('armory.tt_reqlevel'),
    'classes' => t('armory.tt_classes'),
    'sell' => t('armory.tt_sell'),
    'loading' => t('armory.tt_loading'),
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<section class="armory-page">
    <div class="container">
        <h1 class="armory-title"><?= t('armory.title') ?></h1>

        <form method="get" action="<?= url('/armory') ?>" class="armory-picker">
            <label class="auth-label" for="armoryRealm"><?= t('armory.realm') ?></label>
            <select class="auth-input" id="armoryRealm" name="realm">
                <?php foreach ($realms as $r): ?>
                    <option value="<?= (int) $r['id'] ?>" <?= $realmId === (int) $r['id'] ? 'selected' : '' ?>>
                        <?= e($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="auth-label" for="armoryChar"><?= t('armory.character') ?></label>
            <select class="auth-input" id="armoryChar" name="char" onchange="this.form.submit()">
                <option value=""><?= t('armory.select_char') ?></option>
                <?php foreach ($characters as $c): ?>
                    <option value="<?= (int) $c['guid'] ?>" <?= $char && (int) $char['guid'] === (int) $c['guid'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?> — <?= t('armory.level', ['level' => $c['level']]) ?> <?= e(Wow::className((int) $c['class'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($char): ?>
            <div class="armory-sheet">
                <div class="armory-head">
                    <?php if ($portraitUrl): ?>
                        <img class="armory-portrait" src="<?= e($portraitUrl) ?>" alt="" onerror="this.remove()">
                    <?php endif; ?>
                    <div class="armory-head-text">
                        <h2 class="armory-name"><?= e($char['name']) ?></h2>
                        <p class="armory-sub">
                            <?= t('armory.level', ['level' => $char['level']]) ?>
                            <?= e(Wow::raceName((int) $char['race'])) ?>
                            <?= e(Wow::className((int) $char['class'])) ?>
                            <?php if ($char['online']): ?><span class="armory-online">● <?= t('armory.online') ?></span><?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="paper-doll">
                    <div class="doll-col">
                        <?php foreach ($leftSlots as $s) {
                            $renderSlot($s);
                        } ?>
                    </div>
                    <div class="doll-center">
                        <?php if ($portraitUrl): ?>
                            <img class="doll-portrait" src="<?= e($portraitUrl) ?>" alt="" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'doll-silhouette'}))">
                        <?php else: ?>
                            <div class="doll-silhouette"></div>
                        <?php endif; ?>
                        <?php if (!empty($charTitle)): ?>
                            <div class="doll-title"><?= e($charTitle) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($guild)): ?>
                            <div class="doll-guild">&lt;<?= e($guild) ?>&gt;</div>
                        <?php endif; ?>
                    </div>
                    <div class="doll-col">
                        <?php foreach ($rightSlots as $s) {
                            $renderSlot($s);
                        } ?>
                    </div>
                    <div class="doll-weapons">
                        <?php foreach ($weaponSlots as $s) {
                            $renderSlot($s);
                        } ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
