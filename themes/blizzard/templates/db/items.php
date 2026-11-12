<?php
use App\Core\Wow;

App\Core\Theme::partial('db_nav', ['section' => $section ?? 'items', 'filters' => $filters]);

$qs = function (array $extra = []) use ($filters): string {
    return http_build_query(array_filter(
        array_merge($filters, $extra),
        fn($v) => $v !== '' && $v !== null
    ));
};

// Window of page numbers around the current one: 1 … c-2 c-1 c c+1 c+2 … last
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

$cols = [
    'level' => t('db.level'),
    'req' => t('db.req'),
    'source' => t('db.source'),
    'type' => t('db.type'),
    'slot' => t('db.col_slot'),
];
$visible = ['level', 'req', 'source', 'type']; // 'slot' off by default
?>
<section class="db-page">
    <div class="container">
        <h1 class="db-title"><?= t('db.items') ?></h1>
        <nav class="db-breadcrumb">
            <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <span><?= t('db.items') ?></span>
        </nav>

        <form class="db-toolbar" method="get" action="<?= url('/db/items') ?>">
            <div class="db-ac">
                <input class="db-tool-search" type="text" name="q" maxlength="100" autocomplete="off"
                       placeholder="<?= e(t('db.search')) ?>…" value="<?= e($filters['q']) ?>"
                       data-ac="<?= url('/api/db/item-names') ?>" data-ac-goto="<?= url('/db/item/{id}') ?>">
            </div>

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
                    <label class="auth-label" for="f_quality"><?= t('db.f_quality') ?></label>
                    <select class="auth-input" id="f_quality" name="quality">
                        <option value=""><?= t('db.any') ?></option>
                        <?php for ($q = 0; $q <= 7; $q++): ?>
                            <option value="<?= $q ?>" <?= (string) $q === (string) $filters['quality'] ? 'selected' : '' ?>>
                                <?= e(Wow::qualityName($q)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="db-filter">
                    <label class="auth-label" for="f_class"><?= t('db.f_class') ?></label>
                    <select class="auth-input" id="f_class" name="class" onchange="this.form.submit()">
                        <option value=""><?= t('db.any') ?></option>
                        <?php foreach ($classes as $id => $name): ?>
                            <option value="<?= (int) $id ?>" <?= (string) $id === (string) $filters['class'] ? 'selected' : '' ?>>
                                <?= e($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($subclasses): ?>
                    <div class="db-filter">
                        <label class="auth-label" for="f_subclass"><?= t('db.f_subclass') ?></label>
                        <select class="auth-input" id="f_subclass" name="subclass">
                            <option value=""><?= t('db.any') ?></option>
                            <?php foreach ($subclasses as $id => $name): ?>
                                <option value="<?= (int) $id ?>" <?= (string) $id === (string) $filters['subclass'] ? 'selected' : '' ?>>
                                    <?= e($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="db-filter">
                    <label class="auth-label" for="f_slot"><?= t('db.f_slot') ?></label>
                    <select class="auth-input" id="f_slot" name="slot">
                        <option value=""><?= t('db.any') ?></option>
                        <?php foreach ($slots as $id => $name): ?>
                            <?php if ($id === 0) {
                                continue;
                            } ?>
                            <option value="<?= (int) $id ?>" <?= (string) $id === (string) $filters['slot'] ? 'selected' : '' ?>>
                                <?= e(Wow::invTypeName((int) $id)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="db-filter">
                    <label class="auth-label"><?= t('db.f_ilvl') ?></label>
                    <div class="db-ilvl-range">
                        <input class="auth-input" type="number" name="ilvl_min" min="0" placeholder="min"
                               value="<?= e($filters['ilvl_min']) ?>">
                        <input class="auth-input" type="number" name="ilvl_max" min="0" placeholder="max"
                               value="<?= e($filters['ilvl_max']) ?>">
                    </div>
                </div>
                <div class="db-filter">
                    <label class="auth-label">&nbsp;</label>
                    <a class="btn db-tool-btn db-reset" href="<?= url('/db/items') ?>"><?= t('db.reset') ?></a>
                </div>
            </div>
        </form>

        <div class="db-resultbar">
            <nav class="db-pager">
                <?php if ($pages > 1): ?>
                    <a class="db-page-btn <?= $cur === 1 ? 'disabled' : '' ?>"
                       href="<?= url('/db/items?' . $qs(['page' => 1])) ?>">&laquo;</a>
                    <a class="db-page-btn <?= $cur === 1 ? 'disabled' : '' ?>"
                       href="<?= url('/db/items?' . $qs(['page' => max(1, $cur - 1)])) ?>">&lsaquo;</a>
                    <?php $prev = 0;
                    foreach ($set as $p): ?>
                        <?php if ($prev && $p - $prev > 1): ?><span class="db-ellipsis">…</span><?php endif; ?>
                        <a class="db-page-btn <?= $p === $cur ? 'current' : '' ?>"
                           href="<?= url('/db/items?' . $qs(['page' => $p])) ?>"><?= $p ?></a>
                        <?php $prev = $p;
                    endforeach; ?>
                    <a class="db-page-btn <?= $cur === $pages ? 'disabled' : '' ?>"
                       href="<?= url('/db/items?' . $qs(['page' => min($pages, $cur + 1)])) ?>">&rsaquo;</a>
                    <a class="db-page-btn <?= $cur === $pages ? 'disabled' : '' ?>"
                       href="<?= url('/db/items?' . $qs(['page' => $pages])) ?>">&raquo;</a>
                <?php endif; ?>
            </nav>
            <span class="db-total"><?= t('db.results', ['n' => number_format($result['total'])]) ?></span>
        </div>

        <div class="db-table-wrap">
            <table class="db-table">
                <thead>
                    <tr>
                        <th class="db-th-name"><?= t('db.col_name') ?></th>
                        <th data-colcell="level"><?= t('db.level') ?></th>
                        <th data-colcell="req"><?= t('db.req') ?></th>
                        <th data-colcell="source"><?= t('db.source') ?></th>
                        <th data-colcell="type"><?= t('db.type') ?></th>
                        <th data-colcell="slot" hidden><?= t('db.col_slot') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['rows'] as $row): ?>
                        <?php $q = Wow::qualityClass((int) $row['quality']); ?>
                        <tr>
                            <td class="db-td-name">
                                <a class="db-name <?= $q ?>" data-item="<?= (int) $row['id'] ?>"
                                   href="<?= url('/db/item/' . (int) $row['id']) ?>">
                                    <span class="db-icon-wrap">
                                        <?php if ($row['iconUrl']): ?>
                                            <img class="db-icon" src="<?= e($row['iconUrl']) ?>"
                                                 loading="lazy" alt="" onerror="this.style.visibility='hidden'">
                                        <?php endif; ?>
                                        <?php if ((int) $row['ilvl'] > 1): ?>
                                            <span class="db-icon-ilvl"><?= (int) $row['ilvl'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?= e($row['name']) ?>
                                </a>
                            </td>
                            <td data-colcell="level"><?= (int) $row['ilvl'] ?: '' ?></td>
                            <td data-colcell="req"><?= (int) $row['reqLevel'] ?: '' ?></td>
                            <td data-colcell="source" class="db-muted"><?= e($row['className'] ?? '') ?></td>
                            <td data-colcell="type" class="db-muted"><?= e($row['subclassName'] ?? '') ?></td>
                            <td data-colcell="slot" class="db-muted" hidden><?= e(Wow::invTypeName((int) $row['invType'])) ?></td>
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
