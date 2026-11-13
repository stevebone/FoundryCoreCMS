<section class="news-edit-page">
    <div class="container">
        <nav class="db-breadcrumb">
            <a href="<?= url('/') ?>"><?= t('nav.home') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <a href="<?= url('/news') ?>"><?= t('news.title') ?></a>
            <span class="db-crumb-sep">&rsaquo;</span>
            <span><?= t('news.new') ?></span>
        </nav>

        <div class="admin-card">
            <h1 class="auth-title"><?= t('news.new') ?></h1>

            <form method="post" action="<?= url('/news/new') ?>" class="auth-form" data-md-editor>
                <label class="auth-label" for="newsTitle"><?= t('news.f_title') ?></label>
                <input class="auth-input" type="text" id="newsTitle" name="title" required
                       maxlength="255" value="<?= e($old['title'] ?? '') ?>">

                <label class="auth-label" for="newsTag"><?= t('news.f_tag') ?></label>
                <input class="auth-input" type="text" id="newsTag" name="tag" maxlength="64"
                       list="newsTags" placeholder="Update"
                       value="<?= e($old['tag'] ?? '') ?>">
                <datalist id="newsTags">
                    <?php foreach (['Update', 'Hotfix', 'Event', 'Announcement'] as $dt): ?>
                        <option value="<?= e($dt) ?>">
                    <?php endforeach; ?>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?= e($tag) ?>">
                    <?php endforeach; ?>
                </datalist>

                <label class="auth-label" for="newsShort"><?= t('news.f_short') ?></label>
                <input class="auth-input" type="text" id="newsShort" name="short_desc"
                       maxlength="512" placeholder="<?= e(t('news.f_short_ph')) ?>"
                       value="<?= e($old['short_desc'] ?? '') ?>">

                <label class="auth-label" for="newsCover"><?= t('news.cover') ?></label>
                <div class="admin-pick">
                    <input class="auth-input" type="text" id="newsCover" name="image"
                           maxlength="255" placeholder="/uploads/news/… or https://…"
                           value="<?= e($old['image'] ?? '') ?>">
                    <button type="button" class="btn db-tool-btn" data-md-upload="cover">
                        <?= t('news.upload') ?>
                    </button>
                </div>

                <label class="auth-label" for="newsBody"><?= t('news.body') ?></label>
                <div class="md-toolbar">
                    <button type="button" class="md-tool" data-md="bold" title="Bold"><strong>B</strong></button>
                    <button type="button" class="md-tool" data-md="italic" title="Italic"><em>I</em></button>
                    <button type="button" class="md-tool" data-md="h2" title="Heading">H2</button>
                    <button type="button" class="md-tool" data-md="h3" title="Subheading">H3</button>
                    <button type="button" class="md-tool" data-md="link" title="Link">🔗</button>
                    <button type="button" class="md-tool" data-md="ul" title="List">• List</button>
                    <button type="button" class="md-tool" data-md="ol" title="Numbered list">1. List</button>
                    <button type="button" class="md-tool" data-md="quote" title="Quote">❝</button>
                    <button type="button" class="md-tool" data-md="hr" title="Divider">―</button>
                    <button type="button" class="md-tool" data-md-upload="body" title="Insert image">🖼 +</button>
                </div>
                <textarea class="auth-input md-body" id="newsBody" name="long_desc" rows="14"
                          required><?= e($old['long_desc'] ?? '') ?></textarea>

                <h3 class="md-preview-title"><?= t('news.preview') ?></h3>
                <div class="md-preview news-article-text" id="mdPreview"></div>

                <input type="file" id="mdFile" accept="image/png,image/jpeg,image/webp,image/gif,image/avif" hidden>

                <button class="btn btn-gold btn-lg auth-submit" type="submit"><?= t('news.publish') ?></button>
            </form>
        </div>
    </div>
</section>
