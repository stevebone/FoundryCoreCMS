<?php
// Shared database sub-header: search box + section tabs. Included at the
// top of every /db/* template. Expects $section ('items'|'quests'|...).
$section = $section ?? 'items';
$tabs = [
    'items' => '<svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M8 1l5 2v4.5c0 3.4-2.2 6-5 7.5-2.8-1.5-5-4.1-5-7.5V3l5-2zm0 2.2L5 4.4v3.1c0 2.2 1.4 4.1 3 5.1 1.6-1 3-2.9 3-5.1V4.4L8 3.2z"/></svg>',
    'quests' => '<svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M4 2h8a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1zm1 2v1.5h6V4H5zm0 3v1.5h6V7H5zm0 3V11.5h4V10H5z"/></svg>',
    'creatures' => '<svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M8 2a5.5 5.5 0 00-5.5 5.5c0 1.6.7 3 1.8 4L3 14l2.6-1.3c.7.2 1.5.3 2.4.3 3 0 5.5-2.5 5.5-5.5S11 2 8 2zM5.8 7.2a1.1 1.1 0 110-2.2 1.1 1.1 0 010 2.2zm4.4 0a1.1 1.1 0 110-2.2 1.1 1.1 0 010 2.2z"/></svg>',
    'objects' => '<svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M8 2L2.5 5v6L8 14l5.5-3V5L8 2zm0 2.2l3.2 1.8L8 7.7 4.8 6 8 4.2zM4 6.9l3.5 1.9v3.7L4 10.6V6.9zm8 0v3.7l-3.5 1.9V8.8L12 6.9z"/></svg>',
];
?>
<div class="db-nav">
    <div class="container db-nav-inner">
        <form class="db-search" method="get"
              action="<?= url('/db/' . (in_array($section, ['items', 'quests'], true) ? $section : 'items')) ?>">
            <svg class="db-search-glyph" viewBox="0 0 16 16" width="14" height="14" fill="currentColor"><path d="M7 2a5 5 0 104.4 7.4l3 3 1.2-1.2-3-3A5 5 0 007 2zm0 1.6a3.4 3.4 0 110 6.8 3.4 3.4 0 010-6.8z"/></svg>
            <input class="db-search-input" type="text" name="q" maxlength="100"
                   placeholder="<?= e(t('db.search_ph')) ?>"
                   value="<?= e($filters['q'] ?? '') ?>">
        </form>
        <nav class="db-tabs">
            <?php foreach ($tabs as $s => $glyph): ?>
                <a class="db-tab <?= $section === $s ? 'active' : '' ?>"
                   href="<?= url('/db/' . $s) ?>"><?= $glyph ?><span><?= t('db.' . $s) ?></span></a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
