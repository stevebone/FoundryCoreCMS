<?php
// Stub for db sections that are not implemented yet (quests/creatures/objects).
App\Core\Theme::partial('db_nav', ['section' => $section ?? 'items']);
?>
<section class="db-page">
    <div class="container">
        <div class="db-soon">
            <h1 class="auth-title"><?= t('db.' . ($section ?? 'items')) ?></h1>
            <p class="db-muted"><?= t('db.coming_soon') ?></p>
        </div>
    </div>
</section>
