<!DOCTYPE html>
<html lang="<?= e(App\Core\Lang::current()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? t('meta.title')) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
<?php if ($bg = App\Core\Theme::background()): ?>
<style>body{--bg-image:url('<?= e(asset($bg)) ?>')}</style>
<?php endif; ?>
</head>
<body<?= App\Core\Theme::background() ? ' class="has-bg"' : '' ?>>
<?php App\Core\Theme::partial('header'); ?>
<main><?= $content ?></main>
<?php App\Core\Theme::partial('footer'); ?>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
