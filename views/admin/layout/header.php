<?php
/** @var string|null $title */
$title = $title ?? 'Админ-панель';
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" href="<?= e(url('/favicon.ico')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(url('/favicon-32x32.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(url('/apple-touch-icon.png')) ?>">
    <link rel="manifest" href="<?= e(url('/site.webmanifest')) ?>">
    <meta name="theme-color" content="#5e5c64">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="admin-body">
<div class="admin-layout">
    <button class="admin-nav-toggle" id="admin-nav-toggle" aria-label="Открыть меню">☰</button>
    <div class="admin-overlay" id="admin-overlay" hidden></div>
    <aside class="admin-sidebar" id="admin-sidebar">
        <a href="<?= e(url('/')) ?>" class="logo">GRAND <span>DETAILING</span></a>
        <nav class="admin-nav">
            <?php if (isMaster()): ?>
                <a href="<?= e(url('/admin/bookings')) ?>" class="<?= str_starts_with($current, '/admin/bookings') ? 'active' : '' ?>">Заявки</a>
            <?php else: ?>
                <a href="<?= e(url('/admin')) ?>" class="<?= $current === '/admin' ? 'active' : '' ?>">Дашборд</a>
                <a href="<?= e(url('/admin/bookings')) ?>" class="<?= str_starts_with($current, '/admin/bookings') ? 'active' : '' ?>">Заявки</a>
                <a href="<?= e(url('/admin/services')) ?>" class="<?= str_starts_with($current, '/admin/services') ? 'active' : '' ?>">Услуги</a>
                <a href="<?= e(url('/admin/clients')) ?>" class="<?= str_starts_with($current, '/admin/clients') ? 'active' : '' ?>">Клиенты</a>
                <a href="<?= e(url('/admin/masters')) ?>" class="<?= str_starts_with($current, '/admin/masters') ? 'active' : '' ?>">Мастера</a>
                <a href="<?= e(url('/admin/reviews')) ?>" class="<?= str_starts_with($current, '/admin/reviews') ? 'active' : '' ?>">Отзывы</a>
                <a href="<?= e(url('/admin/posts')) ?>" class="<?= str_starts_with($current, '/admin/posts') ? 'active' : '' ?>">Новости</a>
                <a href="<?= e(url('/admin/messages')) ?>" class="<?= str_starts_with($current, '/admin/messages') ? 'active' : '' ?>">Сообщения</a>
                <a href="<?= e(url('/admin/settings')) ?>" class="<?= str_starts_with($current, '/admin/settings') ? 'active' : '' ?>">Настройки</a>
            <?php endif; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <?php if (!isMaster()): ?>
                <a href="<?= e(url('/')) ?>">← На сайт</a>
            <?php endif; ?>
            <form method="POST" action="<?= e(url('/logout')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline">Выйти</button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
