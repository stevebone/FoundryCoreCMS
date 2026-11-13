<?php use App\Core\Theme; ?>
<section class="guilds-page">
    <div class="container">
        <div class="news-page-head">
            <div>
                <h1 class="auth-title"><?= t('guild.title') ?></h1>
                <nav class="db-breadcrumb">
                    <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
                    <span class="db-crumb-sep">&rsaquo;</span>
                    <span><?= t('guild.title') ?></span>
                </nav>
            </div>
        </div>

        <?php if (!$guilds): ?>
            <p class="muted"><?= t('guild.none') ?></p>
        <?php else: ?>
            <div class="guild-card-grid">
                <?php foreach ($guilds as $g): ?>
                    <a class="guild-card" href="<?= url('/guild/' . (int) $g['guildid']) ?>">
                        <?php Theme::partial('guild_emblem', ['g' => $g]); ?>
                        <span class="guild-card-body">
                            <span class="guild-card-name">&lt;<?= e($g['name']) ?>&gt;</span>
                            <span class="guild-card-sub">
                                <span class="fac fac-<?= e($g['faction']) ?>">
                                    <?= e(t('db.side_' . ($g['faction'] === 'neutral' ? 'both' : $g['faction']))) ?>
                                </span>
                                <?php if ($realm): ?> &middot; <?= e($realm) ?><?php endif; ?>
                            </span>
                            <span class="guild-card-meta">
                                <?= t('guild.members', ['n' => (int) $g['members']]) ?>
                                <?php if ($g['leader']): ?> &middot; <?= e($g['leader']) ?><?php endif; ?>
                            </span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
