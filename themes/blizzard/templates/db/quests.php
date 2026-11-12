<?php
App\Core\Theme::partial('db_nav', ['section' => $section ?? 'quests', 'filters' => $filters]);

$qs = function (array $extra = []) use ($filters): string {
    return http_build_query(array_filter(
        array_merge($filters, $extra),
        fn($v) => $v !== '' && $v !== null
    ));
};

$pages = $result['pages'];
$cur = $result['page'];
$set = [1, $pages];
for ($i = $cur - 2; $i <= $cur + 2; $i++) {
    if ($i > 1 && $i < $pages) {
        $set[] = $i;
    }
}
$set = array_values(array_unique($set));
sort($set);

// Sortable header: label + link that flips direction when already sorting by it.
$th = function (string $key, string $label) use ($filters, $qs): string {
    $dir = ($filters['sort'] === $key && ($filters['dir'] ?? 'asc') === 'asc') ? 'desc' : 'asc';
    $mark = $filters['sort'] === $key
        ? (($filters['dir'] ?? 'asc') === 'asc' ? ' &#9650;' : ' &#9660;')
        : ' <span class="db-sort-dim">&#8597;</span>';
    return '<a class="db-th-sort" href="' . e(url('/db/quests?' . $qs(['sort' => $key, 'dir' => $dir, 'page' => 1])))
        . '">' . e($label) . $mark . '</a>';
};

$cols = ['id' => 'ID'];
$visible = [];
?>
<section class="db-page">
    <div class="container">
        <h1 class="db-title"><?= t('db.quests') ?></h1>
        <nav class="db-breadcrumb">
            <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <span><?= t('db.quests') ?></span>
        </nav>

        <form class="db-toolbar" method="get" action="<?= url('/db/quests') ?>">
            <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">
            <input type="hidden" name="dir" value="<?= e($filters['dir']) ?>">
            <div class="db-ac">
                <input class="db-tool-search" type="text" name="q" maxlength="100" autocomplete="off"
                       placeholder="<?= e(t('db.search')) ?>…" value="<?= e($filters['q']) ?>"
                       data-ac="<?= url('/api/db/quest-names') ?>" data-ac-goto="<?= url('/db/quest/{id}') ?>">
            </div>
            <button type="submit" class="btn db-tool-btn"><?= t('db.search') ?></button>

            <div class="dropdown">
                <button type="button" class="btn db-tool-btn dropdown-btn"><?= t('db.columns') ?> &#9662;</button>
                <div class="dropdown-menu db-columns-menu">
                    <?php foreach ($cols as $key => $label): ?>
                        <label class="check-row">
                            <input type="checkbox" data-col="<?= e($key) ?>"
                                   <?= in_array($key, $visible, true) ? 'checked' : '' ?>>
                            <span><?= e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button" class="btn db-tool-btn" data-panel="#dbFilterPanel">
                + <?= t('db.add_filter') ?>
            </button>

            <div class="db-filter-panel is-hidden" id="dbFilterPanel">
                <div class="db-filter">
                    <label class="auth-label"><?= t('db.f_lvl') ?></label>
                    <div class="db-ilvl-range">
                        <input class="auth-input" type="number" name="lvl_min" min="0" placeholder="min"
                               value="<?= e($filters['lvl_min']) ?>">
                        <input class="auth-input" type="number" name="lvl_max" min="0" placeholder="max"
                               value="<?= e($filters['lvl_max']) ?>">
                    </div>
                </div>
                <div class="db-filter">
                    <label class="auth-label" for="f_qtype"><?= t('db.f_type') ?></label>
                    <select class="auth-input" id="f_qtype" name="type" onchange="this.form.submit()">
                        <option value=""><?= t('db.any') ?></option>
                        <?php foreach (['normal', 'daily', 'weekly', 'group'] as $ty): ?>
                            <option value="<?= e($ty) ?>" <?= $filters['type'] === $ty ? 'selected' : '' ?>>
                                <?= e(t('db.qtype_' . $ty)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="db-filter">
                    <label class="auth-label" for="f_zone"><?= t('db.f_zone') ?></label>
                    <div class="db-ac">
                        <input class="auth-input" id="f_zone" type="text" name="zone" maxlength="100" autocomplete="off"
                               value="<?= e($filters['zone']) ?>"
                               data-ac="<?= url('/api/db/zones') ?>">
                    </div>
                </div>
                <div class="db-filter">
                    <label class="auth-label">&nbsp;</label>
                    <button type="submit" class="btn db-tool-btn"><?= t('db.apply') ?></button>
                    <a class="btn db-tool-btn db-reset" href="<?= url('/db/quests') ?>"><?= t('db.reset') ?></a>
                </div>
            </div>
        </form>

        <div class="db-resultbar">
            <nav class="db-pager">
                <?php if ($pages > 1): ?>
                    <a class="db-page-btn <?= $cur === 1 ? 'disabled' : '' ?>"
                       href="<?= url('/db/quests?' . $qs(['page' => 1])) ?>">&laquo;</a>
                    <a class="db-page-btn <?= $cur === 1 ? 'disabled' : '' ?>"
                       href="<?= url('/db/quests?' . $qs(['page' => max(1, $cur - 1)])) ?>">&lsaquo;</a>
                    <?php $prev = 0;
                    foreach ($set as $p): ?>
                        <?php if ($prev && $p - $prev > 1): ?><span class="db-ellipsis">…</span><?php endif; ?>
                        <a class="db-page-btn <?= $p === $cur ? 'current' : '' ?>"
                           href="<?= url('/db/quests?' . $qs(['page' => $p])) ?>"><?= $p ?></a>
                        <?php $prev = $p;
                    endforeach; ?>
                    <a class="db-page-btn <?= $cur === $pages ? 'disabled' : '' ?>"
                       href="<?= url('/db/quests?' . $qs(['page' => min($pages, $cur + 1)])) ?>">&rsaquo;</a>
                    <a class="db-page-btn <?= $cur === $pages ? 'disabled' : '' ?>"
                       href="<?= url('/db/quests?' . $qs(['page' => $pages])) ?>">&raquo;</a>
                <?php endif; ?>
            </nav>
            <span class="db-total"><?= t('db.results', ['n' => number_format($result['total'])]) ?></span>
        </div>

        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th class="db-th-name"><?= $th('name', t('db.col_name')) ?></th>
                        <th><?= $th('level', t('db.req_level')) ?></th>
                        <th><?= $th('zone', t('db.zone')) ?></th>
                        <th><?= $th('type', t('db.type')) ?></th>
                        <th><?= t('db.rewards') ?></th>
                        <th data-colcell="id" hidden>ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['rows'] as $row): ?>
                        <tr>
                            <td class="db-td-name">
                                <span class="q-badge q-<?= e($row['icon']) ?>">!</span>
                                <a class="db-quest-name" href="<?= url('/db/quest/' . (int) $row['id']) ?>">
                                    <?= e($row['name'] !== '' ? $row['name'] : '#' . (int) $row['id']) ?>
                                </a>
                            </td>
                            <td><?= (int) $row['reqLevel'] > 0 ? (int) $row['reqLevel'] : '&mdash;' ?></td>
                            <td class="db-muted"><?= $row['zoneName'] !== null ? e($row['zoneName']) : '&mdash;' ?></td>
                            <td class="db-muted"><?= $row['typeName'] !== null ? e($row['typeName']) : '&mdash;' ?></td>
                            <td>
                                <div class="db-rewards">
                                    <?php foreach (array_slice($row['rewards'], 0, 8) as $rw): ?>
                                        <a class="db-icon-wrap db-reward" data-item="<?= (int) $rw['id'] ?>"
                                           href="<?= url('/db/item/' . (int) $rw['id']) ?>">
                                            <?php if ($rw['iconUrl']): ?>
                                                <img class="db-icon" src="<?= e($rw['iconUrl']) ?>"
                                                     loading="lazy" alt="" onerror="this.style.visibility='hidden'">
                                            <?php endif; ?>
                                            <?php if ($rw['qty'] > 1): ?>
                                                <span class="db-reward-qty"><?= (int) $rw['qty'] ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td data-colcell="id" class="db-muted" hidden><?= (int) $row['id'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$result['rows']): ?>
                        <tr><td colspan="6" class="db-empty"><?= t('db.no_results') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
