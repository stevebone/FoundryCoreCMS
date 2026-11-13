<header class="site-header" id="siteHeader">
    <div class="bar-top">
        <div class="container bar-top-inner">
            <a class="brand-mark" href="<?= url('/') ?>">
                <img src="<?= e(asset(App\Core\Theme::favicon())) ?>" alt="" class="logo-icon">
                <span><?= e(config('app.name')) ?></span>
            </a>
            <nav class="bar-top-nav">
                <a href="<?= url('/#games') ?>"><?= t('nav.games') ?></a>
                <a href="<?= url('/#shop') ?>"><?= t('nav.shop') ?></a>
                <a href="<?= url('/news') ?>"><?= t('nav.news') ?></a>
                <a href="<?= url('/#support') ?>"><?= t('nav.support') ?></a>
            </nav>
            <div class="bar-top-right">
                <?php if (is_staff()): ?>
                    <div class="account-selector admin-selector dropdown">
                        <button type="button" class="account-link dropdown-btn" aria-haspopup="true">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M8 1l6 2.2v4c0 3.9-2.8 6.6-6 7.8-3.2-1.2-6-3.9-6-7.8v-4L8 1zm0 2.1L4 4.8v2.4c0 2.8 1.9 4.7 4 5.6 2.1-.9 4-2.8 4-5.6V4.8L8 3.1z"/></svg>
                            <?= t('nav.admin') ?> &#9662;
                        </button>
                        <div class="account-menu dropdown-menu">
                            <?php if (is_admin()): ?>
                                <a href="<?= url('/admin/settings') ?>"><?= t('admin.site_settings') ?></a>
                                <a href="<?= url('/admin/dbtool') ?>"><?= t('nav.db_tool') ?></a>
                            <?php endif; ?>
                            <a href="<?= url('/admin/gm') ?>"><?= t('admin.gm_tool') ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="lang-selector dropdown" id="langSelector">
                    <button type="button" class="lang-btn dropdown-btn" id="langBtn" aria-haspopup="true">
                        <?= e(strtoupper(App\Core\Lang::current())) ?> &#9662;
                    </button>
                    <div class="lang-menu dropdown-menu" id="langMenu">
                        <?php foreach (App\Core\Lang::available() as $code): ?>
                            <a href="?lang=<?= e($code) ?>"
                               class="<?= $code === App\Core\Lang::current() ? 'active' : '' ?>">
                                <?= e(t('lang.' . $code)) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="account-selector dropdown">
                    <button type="button" class="account-link dropdown-btn" aria-haspopup="true">
                        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M8 8a3.5 3.5 0 1 0-3.5-3.5A3.5 3.5 0 0 0 8 8zm0 1.5c-2.9 0-6 1.5-6 4v.5h12v-.5c0-2.5-3.1-4-6-4z"/></svg>
                        <?= t('nav.account') ?> &#9662;
                    </button>
                    <div class="account-menu dropdown-menu">
                        <?php if ($u = current_user()): ?>
                            <span class="account-email"><?= e($u['email']) ?></span>
                            <a href="<?= url('/settings') ?>"><?= t('nav.settings') ?></a>
                            <a href="<?= url('/logout') ?>"><?= t('nav.logout') ?></a>
                            <a href="<?= url('/support') ?>"><?= t('nav.support') ?></a>
                        <?php else: ?>
                            <a href="<?= url('/login') ?>"><?= t('nav.login') ?></a>
                            <a href="<?= url('/register') ?>"><?= t('nav.signup') ?></a>
                            <a href="<?= url('/support') ?>"><?= t('nav.support') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bar-game">
        <div class="container bar-game-inner">
            <a class="game-logo" href="<?= url('/') ?>"><?= e(config('app.name')) ?></a>
            <nav class="game-nav">
                <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
                <div class="dropdown nav-dropdown">
                    <button type="button" class="dropdown-btn nav-drop-btn" aria-haspopup="true">
                        <?= t('nav.game_info') ?> &#9662;
                    </button>
                    <div class="dropdown-menu nav-menu">
                        <a href="<?= url('/#editions') ?>"><?= t('nav.editions') ?></a>
                        <a href="<?= url('/news') ?>"><?= t('nav.news') ?></a>
                    </div>
                </div>
                <a href="<?= url('/armory') ?>"><?= t('nav.armory') ?></a>
                <div class="dropdown nav-dropdown">
                    <button type="button" class="dropdown-btn nav-drop-btn" aria-haspopup="true">
                        <?= t('nav.community') ?> &#9662;
                    </button>
                    <div class="dropdown-menu nav-menu">
                        <a href="<?= url('/guilds') ?>"><?= t('nav.guilds') ?></a>
                        <a href="<?= url('/forums') ?>"><?= t('nav.forums') ?></a>
                        <a href="<?= url('/leaderboards') ?>"><?= t('nav.leaderboards') ?></a>
                    </div>
                </div>
                <?php if (App\Core\Database::allowed()): ?>
                    <div class="dropdown nav-dropdown">
                        <button type="button" class="dropdown-btn nav-drop-btn" aria-haspopup="true">
                            <?= t('nav.database') ?> &#9662;
                        </button>
                        <div class="dropdown-menu nav-menu">
                            <a href="<?= url('/db/items') ?>"><?= t('db.items') ?></a>
                            <a href="<?= url('/db/quests') ?>"><?= t('db.quests') ?></a>
                            <a href="<?= url('/db/creatures') ?>"><?= t('db.creatures') ?></a>
                            <a href="<?= url('/db/objects') ?>"><?= t('db.objects') ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            </nav>
            <a class="btn btn-gold btn-cta" href="<?= url('/#cta') ?>"><?= t('nav.play') ?></a>
        </div>
    </div>
</header>
