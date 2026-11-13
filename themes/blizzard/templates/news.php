<section class="news-page">
    <div class="container">
        <div class="news-page-head">
            <div>
                <h1 class="auth-title"><?= t('news.title') ?></h1>
                <nav class="db-breadcrumb">
                    <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
                    <span class="db-crumb-sep">&rsaquo;</span>
                    <span><?= t('news.title') ?></span>
                </nav>
            </div>
            <?php if ($canWrite): ?>
                <a class="btn btn-gold" href="<?= url('/news/new') ?>">+ <?= t('news.new') ?></a>
            <?php endif; ?>
        </div>

        <?php if (!$result['rows']): ?>
            <p class="muted"><?= t('news.no_news') ?></p>
        <?php else: ?>
            <div class="news-card-grid">
                <?php foreach ($result['rows'] as $n): ?>
                    <article class="news-card">
                        <a class="news-card-art<?= $n['image'] ? ' has-img' : '' ?>" href="<?= url('/news/' . (int) $n['id']) ?>"
                           <?php if ($n['image']): ?>style="--img:url('<?= e($n['image']) ?>')"<?php endif; ?>></a>
                        <div class="news-card-body">
                            <span class="tag"><?= e($n['tag']) ?></span>
                            <h3><a href="<?= url('/news/' . (int) $n['id']) ?>"><?= e($n['title']) ?></a></h3>
                            <p><?= e($n['short_desc']) ?></p>
                            <a class="link-more" href="<?= url('/news/' . (int) $n['id']) ?>">
                                <?= t('news.read') ?> &rarr;
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($result['pages'] > 1): ?>
                <nav class="db-pager news-pager">
                    <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                        <a class="db-page-btn <?= $p === $result['page'] ? 'current' : '' ?>"
                           href="<?= url('/news?page=' . $p) ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
