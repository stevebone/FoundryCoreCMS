<?php
use App\Core\Wow;

App\Core\Theme::partial('db_nav', ['section' => 'items']);

$q = Wow::qualityClass($item['quality']);
$c = (int) ($item['sellPrice'] ?? 0);
$sell = [];
if ($c) {
    if (($g = intdiv($c, 10000))) {
        $sell[] = $g . 'g';
    }
    if (($s = intdiv($c % 10000, 100))) {
        $sell[] = $s . 's';
    }
    if (($cc = $c % 100) || !$sell) {
        $sell[] = $cc . 'c';
    }
}
$typeLine = implode(', ', array_filter([$item['invType'], $item['subclass']]));

$rel = $relations ?? ['disenchants' => [], 'rewards' => [], 'droppedBy' => [], 'sameModel' => []];
$tabs = [
    'disenchants' => [t('db.tab_disenchants'), count($rel['disenchants'])],
    'rewards' => [t('db.tab_rewards'), count($rel['rewards'])],
    'droppedBy' => [t('db.tab_dropped'), count($rel['droppedBy'])],
    'sameModel' => [t('db.tab_samemodel'), count($rel['sameModel'])],
];
$active = 'disenchants';
foreach (['disenchants', 'rewards', 'droppedBy', 'sameModel'] as $t) {
    if ($rel[$t]) {
        $active = $t;
        break;
    }
}

$fmtChance = fn($v): string => rtrim(rtrim(sprintf('%.2f', (float) $v), '0'), '.') ?: '0';

$fact = function (string $label, string $value, string $cls = ''): void {
    echo '<div class="qf"><span class="qf-label">' . e($label) . '</span>'
        . '<span class="qf-val ' . $cls . '">' . $value . '</span></div>';
};
?>
<section class="db-page">
    <div class="container">
        <div class="db-item-head">
            <?php if ($item['iconUrl']): ?>
                <span class="db-icon-wrap db-item-icon <?= $q ?>">
                    <img src="<?= e($item['iconUrl']) ?>" alt="">
                </span>
            <?php endif; ?>
            <h1 class="db-item-name <?= $q ?>"><?= e($item['name']) ?></h1>
            <div class="db-item-actions">
                <?php if ($item['iconUrl']): ?>
                    <a class="db-tool-btn" href="<?= e($item['iconUrl']) ?>" target="_blank" rel="noopener">Icon</a>
                <?php endif; ?>
                <button type="button" class="db-tool-btn" data-copy
                        data-copied="<?= e(t('db.copied')) ?>"><?= t('db.copy_link') ?></button>
            </div>
        </div>

        <nav class="db-breadcrumb">
            <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <a href="<?= url('/db/items') ?>"><?= t('db.items') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <span class="db-crumb-here <?= $q ?>"><?= e($item['name']) ?></span>
        </nav>

        <div class="db-detail">
            <div class="item-tooltip">
                <div class="tt-head">
                    <?php if ($item['iconUrl']): ?>
                        <span class="db-icon-wrap tt-icon-sm <?= $q ?>">
                            <img src="<?= e($item['iconUrl']) ?>" alt="">
                        </span>
                    <?php endif; ?>
                    <div class="tt-head-text">
                        <div class="tt-name <?= $q ?>"><?= e($item['name']) ?></div>
                    </div>
                    <?php if ($item['subclass']): ?>
                        <span class="tt-head-right"><?= e($item['subclass']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($item['ilvl']): ?>
                    <div class="tt-ilvl"><?= t('armory.tt_ilvl') ?> <?= (int) $item['ilvl'] ?></div>
                <?php endif; ?>
                <?php if ($item['bonding']): ?>
                    <div><?= e($item['bonding']) ?></div>
                <?php endif; ?>
                <?php if ($typeLine): ?>
                    <div><?= e($typeLine) ?></div>
                <?php endif; ?>
                <?php if ($item['armor']): ?>
                    <div><?= (int) $item['armor'] ?> <?= t('armory.tt_armor') ?></div>
                <?php endif; ?>
                <?php if ($item['damage']): ?>
                    <div>
                        <?= $item['damage']['min'] ?> - <?= $item['damage']['max'] ?> <?= t('armory.tt_damage') ?>
                        <span class="tt-muted">(<?= t('armory.tt_speed') ?> <?= $item['damage']['speed'] ?>)</span>
                    </div>
                    <div class="tt-muted">(<?= $item['damage']['dps'] ?> <?= t('armory.tt_dps') ?>)</div>
                <?php endif; ?>
                <?php foreach ($item['stats'] as $st): ?>
                    <div class="tt-stat">+<?= (int) $st['value'] ?> <?= e($st['name']) ?></div>
                <?php endforeach; ?>
                <?php foreach ($item['effects'] as $fx): ?>
                    <?php if (!$fx['text']) {
                        continue;
                    } ?>
                    <div class="tt-equip">
                        <?= t($fx['trigger'] === 0 ? 'armory.tt_use' : ($fx['trigger'] === 1 ? 'armory.tt_equip' : 'armory.tt_effect')) ?>:
                        <?= e($fx['text']) ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($item['requiredLevel']): ?>
                    <div><?= t('armory.tt_reqlevel') ?> <?= (int) $item['requiredLevel'] ?></div>
                <?php endif; ?>
                <?php if ($item['allowableClass']): ?>
                    <div><?= t('armory.tt_classes') ?>: <?= e(implode(', ', $item['allowableClass'])) ?></div>
                <?php endif; ?>
                <?php if ($item['description']): ?>
                    <div class="tt-flavor">"<?= e($item['description']) ?>"</div>
                <?php endif; ?>
                <?php if ($sell): ?>
                    <div class="tt-sell"><?= t('armory.tt_sell') ?>: <?= e(implode(' ', $sell)) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="db-qfacts">
            <?php
            $fact(t('db.qf_name'), e($item['name']), $q);
            $fact(t('db.f_quality'), e(Wow::qualityName($item['quality'])), $q);
            $fact(t('db.type'), e($item['class'] ?? ''));
            $fact(t('db.f_subclass'), e($item['subclass'] ?? ''));
            $fact(t('db.f_ilvl'), (string) (int) $item['ilvl']);
            $fact(t('db.col_req'), (string) (int) $item['requiredLevel']);
            $fact(t('db.qf_sell'), e(implode(' ', $sell) ?: '-'));
            ?>
        </div>

        <nav class="db-rel-tabs">
            <?php foreach ($tabs as $key => [$label, $count]): ?>
                <a class="db-rel-tab <?= $key === $active ? 'active' : '' ?>" data-rt="<?= e($key) ?>">
                    <?= e($label) ?> (<?= $count ?>)
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="db-rel-panels">
            <div class="db-rel-panel <?= $active === 'disenchants' ? '' : 'is-hidden' ?>" data-rpanel="disenchants">
                <?php if ($rel['disenchants']): ?>
                    <div class="db-table-wrap">
                        <table class="db-table">
                            <thead><tr>
                                <th class="db-th-name"><?= t('db.col_name') ?></th>
                                <th><?= t('db.level') ?></th>
                                <th><?= t('db.chance') ?></th>
                                <th><?= t('db.quantity') ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($rel['disenchants'] as $r): ?>
                                <tr>
                                    <td>
                                        <a class="db-name <?= Wow::qualityClass((int) $r['quality']) ?>"
                                           data-item="<?= (int) $r['id'] ?>"
                                           href="<?= url('/db/item/' . (int) $r['id']) ?>">
                                            <span class="db-icon-wrap">
                                                <?php if ($r['fdid'] && ($u = Wow::iconUrl((int) $r['fdid']))): ?>
                                                    <img class="db-icon" src="<?= e($u) ?>" loading="lazy" alt="">
                                                <?php endif; ?>
                                            </span>
                                            <?= e($r['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= (int) $r['ilvl'] ?: '' ?></td>
                                    <td><?= e($fmtChance($r['Chance'])) ?>%</td>
                                    <td><?= (int) $r['MinCount'] === (int) $r['MaxCount']
                                        ? (int) $r['MinCount']
                                        : (int) $r['MinCount'] . '–' . (int) $r['MaxCount'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="db-empty"><?= t('db.empty_tab') ?></p>
                <?php endif; ?>
            </div>

            <div class="db-rel-panel <?= $active === 'rewards' ? '' : 'is-hidden' ?>" data-rpanel="rewards">
                <?php if ($rel['rewards']): ?>
                    <div class="db-table-wrap">
                        <table class="db-table">
                            <thead><tr>
                                <th class="db-th-name"><?= t('db.quest') ?></th>
                                <th><?= t('db.quantity') ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($rel['rewards'] as $r): ?>
                                <tr>
                                    <td><a class="db-name" href="<?= url('/db/quest/' . (int) $r['id']) ?>"><?= e($r['title'] ?: ('Quest ' . $r['id'])) ?></a> <span class="db-muted">(#<?= (int) $r['id'] ?>)</span></td>
                                    <td><?= (int) $r['qty'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="db-empty"><?= t('db.empty_tab') ?></p>
                <?php endif; ?>
            </div>

            <div class="db-rel-panel <?= $active === 'droppedBy' ? '' : 'is-hidden' ?>" data-rpanel="droppedBy">
                <?php if ($rel['droppedBy']): ?>
                    <div class="db-table-wrap">
                        <table class="db-table">
                            <thead><tr>
                                <th class="db-th-name"><?= t('db.creature') ?></th>
                                <th><?= t('db.chance') ?></th>
                                <th><?= t('db.quantity') ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($rel['droppedBy'] as $r): ?>
                                <tr>
                                    <td><?= e($r['name']) ?></td>
                                    <td><?= e($fmtChance($r['Chance'])) ?>%</td>
                                    <td><?= (int) $r['MinCount'] === (int) $r['MaxCount']
                                        ? (int) $r['MinCount']
                                        : (int) $r['MinCount'] . '–' . (int) $r['MaxCount'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="db-empty"><?= t('db.empty_tab') ?></p>
                <?php endif; ?>
            </div>

            <div class="db-rel-panel <?= $active === 'sameModel' ? '' : 'is-hidden' ?>" data-rpanel="sameModel">
                <?php if ($rel['sameModel']): ?>
                    <div class="db-table-wrap">
                        <table class="db-table">
                            <thead><tr>
                                <th class="db-th-name"><?= t('db.col_name') ?></th>
                                <th><?= t('db.level') ?></th>
                                <th><?= t('db.type') ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($rel['sameModel'] as $r): ?>
                                <tr>
                                    <td>
                                        <a class="db-name <?= Wow::qualityClass((int) $r['quality']) ?>"
                                           data-item="<?= (int) $r['id'] ?>"
                                           href="<?= url('/db/item/' . (int) $r['id']) ?>">
                                            <span class="db-icon-wrap">
                                                <?php if ($r['fdid'] && ($u = Wow::iconUrl((int) $r['fdid']))): ?>
                                                    <img class="db-icon" src="<?= e($u) ?>" loading="lazy" alt="">
                                                <?php endif; ?>
                                            </span>
                                            <?= e($r['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= (int) $r['ilvl'] ?: '' ?></td>
                                    <td class="db-muted"><?= e($r['subclassName'] ?? $r['className'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="db-empty"><?= t('db.empty_tab') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
