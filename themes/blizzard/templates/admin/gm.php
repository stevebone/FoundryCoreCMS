<section class="auth-page support-page">
    <div class="container">
        <div class="auth-card">
            <h1 class="auth-title"><?= t('admin.gm_title') ?></h1>
            <p class="support-text"><?= t('admin.gm_text') ?></p>
            <p class="support-text muted"><?= t('admin.level', ['level' => $level]) ?></p>
            <a class="btn btn-gold" href="<?= url('/') ?>"><?= t('support.back') ?></a>
        </div>
    </div>
</section>
