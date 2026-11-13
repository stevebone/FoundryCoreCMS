<?php $tab = $tab ?? 'main'; ?>
<section class="admin-page">
    <div class="container">
        <h1 class="auth-title"><?= t('admin.site_settings') ?></h1>

        <nav class="admin-tabs">
            <a class="admin-tab <?= $tab === 'main' ? 'active' : '' ?>"
               href="<?= url('/admin/settings?tab=main') ?>"><?= t('admin.tab_main') ?></a>
            <a class="admin-tab <?= $tab === 'footer' ? 'active' : '' ?>"
               href="<?= url('/admin/settings?tab=footer') ?>"><?= t('admin.tab_footer') ?></a>
            <a class="admin-tab <?= $tab === 'database' ? 'active' : '' ?>"
               href="<?= url('/admin/settings?tab=database') ?>"><?= t('admin.tab_database') ?></a>
        </nav>

        <?php if (!empty($saved)): ?>
            <div class="settings-notice"><?= t('admin.saved') ?></div>
        <?php endif; ?>

        <?php if ($tab === 'main'): ?>
            <div class="admin-card slim">
                <form method="post" action="<?= url('/admin/settings') ?>" class="auth-form">
                    <label class="auth-label" for="site_name"><?= t('admin.site_name') ?></label>
                    <input class="auth-input" type="text" id="site_name" name="site_name" required
                           maxlength="64" value="<?= e($values['app.name']) ?>">

                    <label class="auth-label" for="site_theme"><?= t('admin.theme') ?></label>
                    <select class="auth-input" id="site_theme" name="site_theme">
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?= e($theme) ?>" <?= $values['app.theme'] === $theme ? 'selected' : '' ?>>
                                <?= e(ucfirst($theme)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php
                    $bgMeta = App\Core\Theme::meta()['background'] ?? 'img/mainBG.avif';
                    $bgCur = $bgDefault !== '' ? 'img/bg/' . $bgDefault : $bgMeta;
                    ?>
                    <label class="auth-label" for="site_bg"><?= t('admin.site_bg') ?></label>
                    <div class="admin-pick">
                        <select class="auth-input" id="site_bg" name="site_bg"
                                data-preview="#pv_bg" data-base="<?= e(asset('img/bg/')) ?>"
                                data-fallback="<?= e(asset($bgMeta)) ?>">
                            <option value=""><?= t('admin.bg_default') ?></option>
                            <?php foreach ($backgrounds as $file => $label): ?>
                                <option value="<?= e($file) ?>" <?= $bgDefault === $file ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <img class="admin-preview admin-preview-wide" id="pv_bg"
                             src="<?= e(asset($bgCur)) ?>" alt="">
                    </div>

                    <label class="auth-label" for="site_logo"><?= t('admin.site_logo') ?></label>
                    <div class="admin-pick">
                        <select class="auth-input" id="site_logo" name="site_logo"
                                data-preview="#pv_logo" data-base="<?= e(asset('img/logo/')) ?>"
                                data-fallback="<?= e(asset('img/favicon.svg')) ?>">
                            <option value=""><?= t('admin.logo_default') ?></option>
                            <?php foreach ($logos as $file => $label): ?>
                                <option value="<?= e($file) ?>" <?= $logoDefault === $file ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <img class="admin-preview" id="pv_logo"
                             src="<?= e(asset(App\Core\Theme::logo())) ?>" alt="">
                    </div>

                    <label class="auth-label" for="site_favicon"><?= t('admin.site_favicon') ?></label>
                    <div class="admin-pick">
                        <select class="auth-input" id="site_favicon" name="site_favicon"
                                data-preview="#pv_favicon" data-base="<?= e(asset('img/favicon/')) ?>"
                                data-fallback="<?= e(asset('img/favicon.svg')) ?>">
                            <option value=""><?= t('admin.favicon_default') ?></option>
                            <?php foreach ($favicons as $file => $label): ?>
                                <option value="<?= e($file) ?>" <?= $faviconDefault === $file ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <img class="admin-preview" id="pv_favicon"
                             src="<?= e(asset(App\Core\Theme::favicon())) ?>" alt="">
                    </div>

                    <label class="auth-label" for="site_hero"><?= t('admin.site_hero') ?></label>
                    <div class="admin-pick">
                        <select class="auth-input" id="site_hero" name="site_hero"
                                data-preview="#pv_hero" data-base="<?= e(asset('img/hero/')) ?>"
                                data-fallback="<?= e(asset('img/favicon.svg')) ?>">
                            <option value=""><?= t('admin.hero_default') ?></option>
                            <?php foreach ($heroes as $file => $label): ?>
                                <option value="<?= e($file) ?>" <?= $heroDefault === $file ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <img class="admin-preview" id="pv_hero"
                             src="<?= e(asset(App\Core\Theme::hero() ?? 'img/favicon.svg')) ?>" alt="">
                    </div>

                    <label class="auth-label" for="default_lang"><?= t('admin.default_lang') ?></label>
                    <select class="auth-input" id="default_lang" name="default_lang">
                        <?php foreach ($languages as $code): ?>
                            <option value="<?= e($code) ?>" <?= $values['app.default_lang'] === $code ? 'selected' : '' ?>>
                                <?= e(t('lang.' . $code)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('admin.save') ?></button>
                </form>
            </div>
        <?php elseif ($tab === 'database'): ?>
            <div class="admin-grid-2">
                <div class="admin-card">
                    <h2 class="admin-section-title admin-section-first"><?= t('admin.db_browsing') ?></h2>
                    <form method="post" action="<?= url('/admin/settings') ?>" class="auth-form">
                        <input type="hidden" name="f_db" value="1">
                        <label class="check-row">
                            <input type="checkbox" name="f_db_enabled"
                                   <?= App\Core\Database::enabled() ? 'checked' : '' ?>>
                            <span><?= t('admin.db_enable') ?></span>
                        </label>
                        <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('admin.save') ?></button>
                    </form>
                </div>

                <div class="admin-card">
                    <h2 class="admin-section-title admin-section-first"><?= t('admin.db2_import') ?></h2>
                    <form method="post" action="<?= url('/admin/db2-import') ?>" class="auth-form">
                        <label class="auth-label" for="db2_dir"><?= t('admin.db2_dir') ?></label>
                        <input class="auth-input" type="text" id="db2_dir" name="db2_dir"
                               maxlength="255" placeholder="tools/db2csv"
                               value="<?= e(App\Core\SiteSettings::get('db.db2_dir') ?? 'tools/db2csv') ?>">
                        <p class="admin-help"><?= t('admin.db2_dir_help') ?></p>

                        <label class="auth-label" for="db2_tables"><?= t('admin.db2_tables') ?></label>
                        <input class="auth-input" type="text" id="db2_tables" name="db2_tables"
                               maxlength="255" placeholder="ItemSparse, SpellName">
                        <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('admin.db2_run') ?></button>
                    </form>
                </div>

                <div class="admin-card">
                    <h2 class="admin-section-title admin-section-first"><?= t('admin.db2_fetch') ?></h2>
                    <form method="post" action="<?= url('/admin/db2-fetch') ?>" class="auth-form">
                        <label class="auth-label" for="db2_build"><?= t('admin.db2_build') ?></label>
                        <input class="auth-input" type="text" id="db2_build" name="db2_build"
                               maxlength="32" placeholder="12.1.7.70740" required
                               value="<?= e(App\Core\SiteSettings::get('db.db2_build') ?? '') ?>">
                        <p class="admin-help"><?= t('admin.db2_build_help') ?></p>

                        <label class="auth-label" for="db2_fetch_tables"><?= t('admin.db2_tables') ?></label>
                        <input class="auth-input" type="text" id="db2_fetch_tables" name="db2_tables"
                               maxlength="255" placeholder="ItemSparse, SpellName">
                        <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('admin.db2_fetch_run') ?></button>
                    </form>
                </div>
            </div>

            <?php if ($imp = ($_SESSION['db2_import'] ?? null)): unset($_SESSION['db2_import']); ?>
                <div class="admin-card db2-result">
                    <?php if ((int) $imp['code'] === 0): ?>
                        <div class="settings-notice"><?= t('admin.db2_done') ?></div>
                    <?php else: ?>
                        <div class="auth-error"><?= t('admin.db2_failed') ?></div>
                    <?php endif; ?>
                    <pre class="db2-out"><?= e($imp['output']) ?></pre>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="admin-card">
                <form method="post" action="<?= url('/admin/settings') ?>" class="auth-form">
                    <input type="hidden" name="f_footer" value="1">

                    <h2 class="admin-section-title admin-section-first"><?= t('admin.social_media') ?></h2>
                    <label class="check-row">
                        <input type="checkbox" name="f_social" data-toggle="#socialUrls"
                               <?= App\Core\Footer::socialOn() ? 'checked' : '' ?>>
                        <span><?= t('admin.social_bar') ?></span>
                    </label>
                    <div class="social-urls <?= App\Core\Footer::socialOn() ? '' : 'is-hidden' ?>" id="socialUrls">
                        <?php foreach (App\Core\Footer::socials() as $key => $s): ?>
                            <div class="social-field">
                                <label class="auth-label" for="f_su_<?= e($key) ?>"><?= e($s['label']) ?></label>
                                <input class="auth-input" type="text" id="f_su_<?= e($key) ?>"
                                       name="f_social_url_<?= e($key) ?>" maxlength="255"
                                       placeholder="https://" value="<?= e(App\Core\Footer::socialUrl($key) ?? '') ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h2 class="admin-section-title"><?= t('admin.footer_cols') ?></h2>
                    <div class="footer-admin-grid">
                        <?php foreach (App\Core\Footer::columns() as $n => $col): ?>
                            <div class="footer-admin-col">
                                <label class="check-row col-head">
                                    <input type="checkbox" name="f_col<?= $n ?>_show" <?= App\Core\Footer::colOn($n) ? 'checked' : '' ?>>
                                    <span><?= e(App\Core\Footer::colTitle($n, $col['title_key'])) ?></span>
                                </label>
                                <input class="auth-input col-title-input" type="text" name="f_col<?= $n ?>_title"
                                       maxlength="64" placeholder="<?= e(t($col['title_key'])) ?>"
                                       value="<?= e(App\Core\SiteSettings::get("footer.col{$n}.title") ?? '') ?>">
                                <div class="col-links">
                                    <?php foreach ($col['links'] as $m => $link): ?>
                                        <label class="check-row">
                                            <input type="checkbox" name="f_col<?= $n ?>_link<?= $m + 1 ?>"
                                                <?= App\Core\Footer::linkOn($n, $m + 1) ? 'checked' : '' ?>>
                                            <span><?= t($link['t']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('admin.save') ?></button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>
