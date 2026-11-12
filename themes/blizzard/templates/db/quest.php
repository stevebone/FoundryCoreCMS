<?php
use App\Core\Wow;

App\Core\Theme::partial('db_nav', ['section' => 'quests']);

$q = $quest;
$rw = $q['rewards'];
$sideIcons = ['alliance' => 'A', 'horde' => 'H', 'both' => 'A/H'];

$fact = function (string $label, string $value, string $cls = ''): void {
    if ($value === '') {
        return;
    }
    echo '<div class="qf"><span class="qf-label">' . e($label) . '</span>'
        . '<span class="qf-val ' . $cls . '">' . $value . '</span></div>';
};

$tabs = [
    'starters' => [t('db.started_by'), count($q['starters'])],
    'enders' => [t('db.ended_by'), count($q['enders'])],
];
$active = $q['starters'] ? 'starters' : 'enders';

$block = function (string $label, ?string $text): void {
    if (!$text || !trim($text)) {
        return;
    }
    echo '<div class="db-qblock"><span class="qf-label">' . e($label) . '</span>'
        . '<p class="db-qtext">' . nl2br(e(trim($text))) . '</p></div>';
};

$rewardItems = function (array $items): void {
    foreach ($items as $it): ?>
        <a class="db-reward-line" data-item="<?= (int) $it['id'] ?>"
           href="<?= url('/db/item/' . (int) $it['id']) ?>">
            <span class="db-icon-wrap db-reward">
                <?php if ($it['iconUrl']): ?>
                    <img class="db-icon" src="<?= e($it['iconUrl']) ?>" loading="lazy" alt=""
                         onerror="this.style.visibility='hidden'">
                <?php endif; ?>
                <?php if ($it['qty'] > 1): ?>
                    <span class="db-reward-qty"><?= (int) $it['qty'] ?></span>
                <?php endif; ?>
            </span>
            <span class="<?= e(Wow::qualityClass((int) $it['quality'])) ?>"><?= e($it['name']) ?></span>
        </a>
    <?php endforeach;
};
?>
<section class="db-page">
    <div class="container">
        <div class="db-item-head">
            <span class="q-badge q-<?= e($q['icon']) ?> db-quest-badge">!</span>
            <h1 class="db-item-name"><?= e($q['name']) ?></h1>
            <div class="db-item-actions">
                <button type="button" class="db-tool-btn" data-copy
                        data-copied="<?= e(t('db.copied')) ?>"><?= t('db.copy_link') ?></button>
            </div>
        </div>

        <nav class="db-breadcrumb">
            <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <a href="<?= url('/db/quests') ?>"><?= t('db.quests') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <span class="db-crumb-here"><?= e($q['name']) ?></span>
        </nav>

        <div class="db-qfacts db-qfacts-quest">
            <?php $fact(t('db.qf_name'), e($q['name'])); ?>
            <?php $fact(t('db.zone'), e($q['zoneName'] ?? '—')); ?>
            <?php $fact(t('db.expansion'), e($q['expansionName'] ?? '—')); ?>
            <?php $fact(t('db.side'), e(t('db.side_' . $q['side'])), 'side-' . $q['side']); ?>
            <?php $fact(t('db.level'), $q['level'] ? (int) $q['level'] : '—'); ?>
            <?php $fact(t('db.req_level'), $q['reqLevel'] ? (int) $q['reqLevel'] : '—'); ?>
            <?php if ($q['typeName']): $fact(t('db.type'), e($q['typeName'])); endif; ?>
            <?php if ($q['tags']): $fact(t('db.tags'), e(implode(', ', $q['tags']))); endif; ?>
        </div>

        <div class="db-quest-grid">
        <div class="db-quest-body">
            <?php if ($q['starters']): ?>
                <div class="db-qblock">
                    <span class="qf-label"><?= t('db.starts') ?></span>
                    <p class="db-qtext db-qlist"><?= e(implode(', ', array_column($q['starters'], 'name'))) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($q['enders']): ?>
                <div class="db-qblock">
                    <span class="qf-label"><?= t('db.ends') ?></span>
                    <p class="db-qtext db-qlist"><?= e(implode(', ', array_column($q['enders'], 'name'))) ?></p>
                </div>
            <?php endif; ?>

            <?php $block(t('db.log_desc'), $q['logDesc']); ?>
            <?php $block(t('db.quest_desc'), $q['description']); ?>
            <?php $block(t('db.progress'), $q['progress']); ?>
            <?php $block(t('db.completion'), $q['completion']); ?>

            <?php if ($q['objectives']): ?>
                <div class="db-qblock">
                    <span class="qf-label"><?= t('db.objectives') ?></span>
                    <ul class="db-obj-list">
                        <?php foreach ($q['objectives'] as $o): ?>
                            <li>
                                <?php if ($o['descr']): ?>
                                    <?= e($o['descr']) ?>
                                <?php else: ?>
                                    <?= e($o['target'] ?? '#' . (int) $o['objectId']) ?>
                                    <?php if ((int) $o['amount'] > 1): ?>(<?= (int) $o['amount'] ?>)<?php endif; ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($rw['choice'] || $rw['fixed'] || $rw['xp'] || $rw['rep'] || $rw['title'] || $rw['spell']): ?>
                <div class="db-qblock">
                    <span class="qf-label"><?= t('db.rewards') ?></span>
                    <?php if ($rw['choice']): ?>
                        <p class="db-choose"><?= t('db.choose_one') ?></p>
                        <div class="db-reward-rows"><?php $rewardItems($rw['choice']); ?></div>
                    <?php endif; ?>
                    <?php if ($rw['fixed']): ?>
                        <div class="db-reward-rows"><?php $rewardItems($rw['fixed']); ?></div>
                    <?php endif; ?>
                    <div class="db-reward-extra">
                        <?php if ($rw['xp']): ?>
                            <div class="db-xp"><?= number_format((float) $rw['xp']) ?> <?= t('db.xp') ?></div>
                        <?php endif; ?>
                        <?php foreach ($rw['rep'] as $rp): ?>
                            <div class="db-rep">+<?= (int) $rp['value'] ?> <?= t('db.rep_with') ?> <?= e($rp['name']) ?></div>
                        <?php endforeach; ?>
                        <?php if ($rw['title']): ?>
                            <div class="db-rep"><?= t('db.reward_title') ?>: <?= e($rw['title']) ?></div>
                        <?php endif; ?>
                        <?php if ($rw['spell']): ?>
                            <div class="db-rep"><?= t('db.reward_spell') ?>: <?= e($rw['spell']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($q['maps']): ?>
            <aside class="db-quest-aside">
                <?php foreach ($q['maps'] as $m): ?>
                    <div class="db-objmap">
                        <span class="qf-label"><?= t('db.objective_map') ?></span>
                        <div class="db-map-canvas" role="button" tabindex="0" data-map-zoom
                             title="<?= e(t('db.objective_map')) ?>">
                            <img class="db-map-img" src="<?= e($m['img']) ?>"
                                 alt="<?= e($m['name'] !== '' ? $m['name'] : 'map') ?>" loading="lazy">
                            <?php foreach ($m['points'] as $p): ?>
                                <span class="db-poi<?= !empty($p['turnin']) ? ' db-poi-turnin' : '' ?>"
                                      style="left:<?= round($p['u'] * 100, 2) ?>%;top:<?= round($p['v'] * 100, 2) ?>%"
                                    ><?= !empty($p['turnin']) ? '?' : '' ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($m['name'] !== ''): ?>
                            <div class="db-map-name"><?= e($m['name']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </aside>
        <?php endif; ?>
        </div>

        <div class="db-relations">
            <nav class="db-rel-tabs">
                <?php foreach ($tabs as $key => [$label, $count]): ?>
                    <a class="db-rel-tab <?= $key === $active ? 'active' : '' ?>" data-rt="<?= e($key) ?>">
                        <?= e($label) ?> (<?= (int) $count ?>)
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php foreach (['starters' => 'starters', 'enders' => 'enders'] as $key => $rows): ?>
                <div class="db-rel-panel <?= $key === $active ? '' : 'is-hidden' ?>" data-rpanel="<?= e($key) ?>">
                    <?php if ($q[$rows]): ?>
                        <table class="db-table">
                            <thead>
                                <tr><th><?= t('db.col_name') ?></th><th><?= t('db.type') ?></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($q[$rows] as $g): ?>
                                    <tr>
                                        <td class="db-name"><?= e($g['name']) ?></td>
                                        <td class="db-muted"><?= e($g['kind']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="db-empty"><?= t('db.empty_tab') ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
