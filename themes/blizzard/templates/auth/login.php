<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1 class="auth-title"><?= t('auth.login_title') ?></h1>

            <?php if (!empty($error)): ?>
                <div class="auth-error"><?= t('auth.' . $error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('/login') ?>" class="auth-form">
                <label class="auth-label" for="email"><?= t('auth.email') ?></label>
                <input class="auth-input" type="email" id="email" name="email" required
                       value="<?= e($email ?? '') ?>" autocomplete="email">

                <label class="auth-label" for="password"><?= t('auth.password') ?></label>
                <input class="auth-input" type="password" id="password" name="password" required
                       autocomplete="current-password">

                <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('auth.login_submit') ?></button>
            </form>

            <p class="auth-alt">
                <?= t('auth.no_account') ?>
                <a class="link-more" href="<?= url('/register') ?>"><?= t('auth.signup_link') ?></a>
            </p>
        </div>
    </div>
</section>
