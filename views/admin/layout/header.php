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
<a class="skip-link" href="#main">Перейти к содержимому</a>
<div class="admin-layout">
    <button type="button" class="admin-nav-toggle" id="admin-nav-toggle" aria-label="Развернуть меню" aria-expanded="false" aria-controls="admin-sidebar">
        <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
    </button>
    <div class="admin-overlay" id="admin-overlay" hidden></div>
    <aside class="admin-sidebar" id="admin-sidebar">
        <nav class="admin-nav" aria-label="Меню админ-панели">
            <a href="<?= e(url('/')) ?>">
                <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                <span class="admin-nav-label">Главная</span>
            </a>
            <?php if (isMaster()): ?>
                <a href="<?= e(url('/admin/bookings')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/bookings')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                    <span class="admin-nav-label">Заявки</span>
                </a>
            <?php else: ?>
                <a href="<?= e(url('/admin')) ?>"<?= nav_attrs($current === '/admin') ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    <span class="admin-nav-label">Дашборд</span>
                </a>
                <a href="<?= e(url('/admin/bookings')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/bookings')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                    <span class="admin-nav-label">Заявки</span>
                </a>
                <a href="<?= e(url('/admin/services')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/services')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.8-2.8z"/></svg>
                    <span class="admin-nav-label">Услуги</span>
                </a>
                <a href="<?= e(url('/admin/clients')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/clients')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><circle cx="17" cy="9" r="2"/></svg>
                    <span class="admin-nav-label">Клиенты</span>
                </a>
                <a href="<?= e(url('/admin/masters')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/masters')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="8" r="3"/><path d="M4 20a6 6 0 0 1 12 0"/><path d="m16 17 2 2 4-4"/></svg>
                    <span class="admin-nav-label">Мастера</span>
                </a>
                <a href="<?= e(url('/admin/reviews')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/reviews')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.9 6 6.1.9-4.5 4.3 1 6.1L12 17l-5.5 3.3 1-6.1L3 10l6.1-.9z"/></svg>
                    <span class="admin-nav-label">Отзывы</span>
                </a>
                <a href="<?= e(url('/admin/posts')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/posts')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h10M7 17h6"/></svg>
                    <span class="admin-nav-label">Новости</span>
                </a>
                <a href="<?= e(url('/admin/messages')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/messages')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <span class="admin-nav-label">Сообщения</span>
                </a>
                <a href="<?= e(url('/admin/settings')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/settings')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1l2-1.5-2-3.4-2.4 1a7 7 0 0 0-1.7-1L14.5 3h-5l-.3 2.6a7 7 0 0 0-1.7 1l-2.4-1-2 3.4 2 1.5a7 7 0 0 0 0 2l-2 1.5 2 3.4 2.4-1a7 7 0 0 0 1.7 1l.3 2.6h5l.3-2.6a7 7 0 0 0 1.7-1l2.4 1 2-3.4-2-1.5a7 7 0 0 0 .1-1z"/></svg>
                    <span class="admin-nav-label">Настройки</span>
                </a>
                <a href="<?= e(url('/admin/audit')) ?>"<?= nav_attrs(str_starts_with($current, '/admin/audit')) ?>>
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span class="admin-nav-label">Аудит</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <?php if (!isMaster()): ?>
                <a href="<?= e(url('/')) ?>">
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    <span class="admin-nav-label">На сайт</span>
                </a>
            <?php endif; ?>
            <form method="POST" action="<?= e(url('/logout')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline">
                    <svg class="admin-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                    <span class="admin-nav-label">Выйти</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="admin-main" id="main" tabindex="-1">
