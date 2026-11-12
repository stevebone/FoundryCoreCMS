<section class="hero">
    <div class="hero-bg"></div>
    <div class="container hero-inner">
        <img class="hero-emblem" src="<?= e(asset('img/favicon.svg')) ?>" alt="">
        <h1 class="hero-title"><?= t('hero.title') ?></h1>
        <p class="hero-sub"><?= t('hero.subtitle') ?></p>
        <div class="hero-cta">
            <a class="btn btn-gold btn-lg" href="#cta"><?= t('hero.cta_play') ?></a>
            <a class="btn btn-ghost btn-lg" href="#editions"><?= t('hero.cta_learn') ?></a>
        </div>
    </div>
</section>

<div class="realm-ribbon" id="status">
    <div class="container ribbon-inner">
        <span class="ribbon-label"><?= t('status.title') ?></span>
        <div id="realmStatus" class="realm-list">
            <span class="muted"><?= t('status.loading') ?></span>
        </div>
    </div>
</div>

<section class="editions" id="editions">
    <div class="container">
        <h2 class="section-title"><?= t('editions.title') ?></h2>
        <div class="edition-grid">
            <article class="edition-card">
                <div class="edition-art art-1"></div>
                <div class="edition-body">
                    <h3><?= t('edition.realm.title') ?></h3>
                    <p><?= t('edition.realm.text') ?></p>
                    <a class="link-more" href="#"><?= t('edition.cta') ?></a>
                </div>
            </article>
            <article class="edition-card">
                <div class="edition-art art-2"></div>
                <div class="edition-body">
                    <h3><?= t('edition.connect.title') ?></h3>
                    <p><?= t('edition.connect.text') ?></p>
                    <a class="link-more" href="#"><?= t('edition.cta') ?></a>
                </div>
            </article>
            <article class="edition-card">
                <div class="edition-art art-3"></div>
                <div class="edition-body">
                    <h3><?= t('edition.classic.title') ?></h3>
                    <p><?= t('edition.classic.text') ?></p>
                    <a class="link-more" href="#"><?= t('edition.cta') ?></a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="news" id="news">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title"><?= t('news.title') ?></h2>
            <a class="link-more" href="#"><?= t('news.all') ?></a>
        </div>
        <div class="news-grid">
            <article class="news-featured">
                <div class="news-art"></div>
                <div class="news-body">
                    <span class="tag"><?= t('news.tag_update') ?></span>
                    <h3><?= t('news.featured_title') ?></h3>
                    <p><?= t('news.featured_text') ?></p>
                    <a class="link-more" href="#"><?= t('news.read') ?></a>
                </div>
            </article>
            <div class="news-side">
                <a class="news-item" href="#">
                    <span class="tag tag-sm"><?= t('news.tag_hotfix') ?></span>
                    <span class="news-item-title"><?= t('news.item1') ?></span>
                </a>
                <a class="news-item" href="#">
                    <span class="tag tag-sm"><?= t('news.tag_update') ?></span>
                    <span class="news-item-title"><?= t('news.item2') ?></span>
                </a>
                <a class="news-item" href="#">
                    <span class="tag tag-sm"><?= t('news.tag_update') ?></span>
                    <span class="news-item-title"><?= t('news.item3') ?></span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="cta-tiles" id="cta">
    <div class="container tile-grid">
        <a class="big-tile tile-new" href="#">
            <h3><?= t('cta.new_title') ?></h3>
            <p><?= t('cta.new_text') ?></p>
            <span class="tile-link"><?= t('cta.new_btn') ?> &rarr;</span>
        </a>
        <a class="big-tile tile-return" href="#">
            <h3><?= t('cta.return_title') ?></h3>
            <p><?= t('cta.return_text') ?></p>
            <span class="tile-link"><?= t('cta.return_btn') ?> &rarr;</span>
        </a>
    </div>
</section>
