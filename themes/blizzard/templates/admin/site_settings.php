<?php $tab = $tab ?? 'main'; ?>
<section class="admin-page">
    <div class="container">
        <h1 class="auth-title"><?= t('admin.site_settings') ?></h1>

        <nav class="admin-tabs">
            <a class="admin-tab <?= $tab === 'main' ? 'active' : '' ?>"
               href="<?= url('/admin/settings?tab=main') ?>"><?= t('admin.tab_main') ?></a>
            <a class="admin-tab <?= $tab === 'footer' ? 'active' : '' ?>"
               href="<?= url('/admin/settings?tab=footer') ?>"><?= t('admin.tab_footer') ?></a>
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
