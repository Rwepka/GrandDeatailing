<?php
/** @var string|null $title */
$title = $title ?? (string) config('app.name');
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= e(url('/')) ?>" class="logo">GRAND <span>DETAILING</span></a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Открыть меню">☰</button>
        <div class="header-menu" id="header-menu">
            <nav class="main-nav">
                <?php if (isMaster()): ?>
                    <a href="<?= e(url('/contacts')) ?>" class="<?= str_starts_with($current, '/contacts') ? 'active' : '' ?>">Контакты</a>
                <?php else: ?>
                    <a href="<?= e(url('/')) ?>" class="<?= $current === '/' ? 'active' : '' ?>">Главная</a>
                    <a href="<?= e(url('/services')) ?>" class="<?= str_starts_with($current, '/services') ? 'active' : '' ?>">Услуги</a>
                    <a href="#">Портфолио</a>
                    <a href="<?= e(url('/reviews')) ?>" class="<?= str_starts_with($current, '/reviews') ? 'active' : '' ?>">Отзывы</a>
                    <a href="<?= e(url('/contacts')) ?>" class="<?= str_starts_with($current, '/contacts') ? 'active' : '' ?>">Контакты</a>
                <?php endif; ?>
            </nav>
            <div class="header-actions">
                <?php if (isAuth()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?= e(url('/admin')) ?>">Админ-панель</a>
                    <?php elseif (isMaster()): ?>
                        <a href="<?= e(url('/admin/bookings')) ?>">Заявки</a>
                    <?php endif; ?>
                    <a href="<?= e(url('/cabinet')) ?>">Личный кабинет</a>
                    <form method="POST" action="<?= e(url('/logout')) ?>" class="logout-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline">Выйти</button>
                    </form>
                <?php else: ?>
                    <a href="<?= e(url('/login')) ?>">Войти</a>
                <?php endif; ?>
                <a href="<?= e(url('/booking')) ?>" class="btn btn-accent">Записаться</a>
            </div>
        </div>
    </div>
</header>
<main class="site-main">
