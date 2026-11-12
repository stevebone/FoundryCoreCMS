<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1 class="auth-title"><?= t('auth.register_title') ?></h1>

            <?php if (!empty($error)): ?>
                <div class="auth-error"><?= t('auth.' . $error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('/register') ?>" class="auth-form">
                <label class="auth-label" for="email"><?= t('auth.email') ?></label>
                <input class="auth-input" type="email" id="email" name="email" required
                       value="<?= e($email ?? '') ?>" autocomplete="email">

                <label class="auth-label" for="password"><?= t('auth.password') ?></label>
                <input class="auth-input" type="password" id="password" name="password" required
                       minlength="8" autocomplete="new-password">

                <label class="auth-label" for="password_confirm"><?= t('auth.password_confirm') ?></label>
                <input class="auth-input" type="password" id="password_confirm" name="password_confirm" required
                       autocomplete="new-password">

                <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('auth.register_submit') ?></button>
            </form>

            <p class="auth-alt">
                <?= t('auth.have_account') ?>
                <a class="link-more" href="<?= url('/login') ?>"><?= t('auth.login_link') ?></a>
            </p>
        </div>
    </div>
</section>
