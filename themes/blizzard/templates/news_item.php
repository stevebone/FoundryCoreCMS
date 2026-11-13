<section class="news-article">
    <div class="container">
        <nav class="db-breadcrumb">
            <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <a href="<?= url('/news') ?>"><?= t('news.title') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <span><?= e($a['title']) ?></span>
        </nav>

        <article class="news-article-body">
            <div class="news-article-meta">
                <span class="tag"><?= e($a['tag']) ?></span>
                <time class="muted"><?= e(date('F j, Y', strtotime($a['created_at']))) ?></time>
            </div>
            <h1 class="auth-title"><?= e($a['title']) ?></h1>

            <?php if ($a['image']): ?>
                <img class="news-article-cover" src="<?= e($a['image']) ?>" alt="" loading="lazy">
            <?php endif; ?>

            <div class="news-article-text db-qblock">
                <?= App\Core\Markdown::render((string) $a['long_desc']) ?>
            </div>

            <a class="link-more" href="<?= url('/news') ?>">&larr; <?= t('news.back') ?></a>
        </article>
    </div>
</section>
