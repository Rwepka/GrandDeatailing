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
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="<?= e(url('/')) ?>" class="logo">GRAND <span>DETAILING</span></a>
        <nav class="admin-nav">
            <?php if (isMaster()): ?>
                <a href="<?= e(url('/cabinet')) ?>" class="<?= str_starts_with($current, '/cabinet') ? 'active' : '' ?>">Личный кабинет</a>
                <a href="<?= e(url('/admin/bookings')) ?>" class="<?= str_starts_with($current, '/admin/bookings') ? 'active' : '' ?>">Заявки</a>
            <?php else: ?>
                <a href="<?= e(url('/admin')) ?>" class="<?= $current === '/admin' ? 'active' : '' ?>">Дашборд</a>
                <a href="<?= e(url('/admin/bookings')) ?>" class="<?= str_starts_with($current, '/admin/bookings') ? 'active' : '' ?>">Заявки</a>
                <a href="<?= e(url('/admin/services')) ?>" class="<?= str_starts_with($current, '/admin/services') ? 'active' : '' ?>">Услуги</a>
                <a href="<?= e(url('/admin/masters')) ?>" class="<?= str_starts_with($current, '/admin/masters') ? 'active' : '' ?>">Мастера</a>
                <a href="<?= e(url('/admin/reviews')) ?>" class="<?= str_starts_with($current, '/admin/reviews') ? 'active' : '' ?>">Отзывы</a>
                <a href="<?= e(url('/admin/messages')) ?>" class="<?= str_starts_with($current, '/admin/messages') ? 'active' : '' ?>">Сообщения</a>
            <?php endif; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= e(url('/')) ?>">← На сайт</a>
            <form method="POST" action="<?= e(url('/logout')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline">Выйти</button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
