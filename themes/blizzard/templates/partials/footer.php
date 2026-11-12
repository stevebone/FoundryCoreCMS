<footer class="site-footer">
    <div class="container">
        <?php if (App\Core\Footer::socialOn() && ($socials = App\Core\Footer::socialLinks())): ?>
            <div class="footer-social">
                <span class="social-title"><?= t('social.title') ?></span>
                <div class="social-icons">
                    <?php foreach ($socials as $s): ?>
                        <a href="<?= e($s['url']) ?>" aria-label="<?= e($s['label']) ?>" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><?= $s['svg'] ?></svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="footer-cols">
            <?php foreach (App\Core\Footer::columns() as $n => $col): ?>
                <?php if (!App\Core\Footer::colOn($n)) {
                    continue;
                } ?>
                <div class="fcol">
                    <h5><?= e(App\Core\Footer::colTitle($n, $col['title_key'])) ?></h5>
                    <?php foreach ($col['links'] as $m => $link): ?>
                        <?php if (!App\Core\Footer::linkOn($n, $m + 1)) {
                            continue;
                        } ?>
                        <a href="<?= e($link['url']) ?>"><?= t($link['t']) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="footer-legal">
            <p class="footer-text"><?= t('footer.text') ?></p>
            <p class="footer-copy"><?= e(config('app.name')) ?> &mdash; <?= date('Y') ?></p>
        </div>
    </div>
</footer>
