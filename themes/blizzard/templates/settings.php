<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1 class="auth-title"><?= t('settings.title') ?></h1>
            <p class="settings-email"><?= e($account['email']) ?></p>

            <?php if (!empty($saved)): ?>
                <div class="settings-notice"><?= t('settings.saved') ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('/settings') ?>" class="auth-form">
                <label class="auth-label" for="lang"><?= t('settings.language') ?></label>
                <select class="auth-input" id="lang" name="lang">
                    <option value=""><?= t('settings.default') ?></option>
                    <?php foreach ($languages as $code): ?>
                        <option value="<?= e($code) ?>" <?= ($prefs['lang'] ?? null) === $code ? 'selected' : '' ?>>
                            <?= e(t('lang.' . $code)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="auth-label" for="theme"><?= t('settings.theme') ?></label>
                <select class="auth-input" id="theme" name="theme">
                    <option value=""><?= t('settings.default') ?></option>
                    <?php foreach ($themes as $theme): ?>
                        <option value="<?= e($theme) ?>" <?= ($prefs['theme'] ?? null) === $theme ? 'selected' : '' ?>>
                            <?= e(ucfirst($theme)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('settings.save') ?></button>
            </form>
        </div>
    </div>
</section>
