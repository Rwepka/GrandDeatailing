<?php
/** @var string|null $title */
/** @var string|null $metaDescription */
$title = $title ?? (string) config('app.name');
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$metaDescription = $metaDescription ?? 'Детейлинг-студия GRAND DETAILING в Туапсе: полировка кузова, химчистка салона, керамика и защитные покрытия. Онлайн-запись.';
$baseUrl = rtrim((string) config('app.url'), '/');
$absoluteUrl = $baseUrl . ($current === '/' ? '/' : $current);
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
    <meta name="description" content="<?= e($metaDescription) ?>">
    <link rel="canonical" href="<?= e($absoluteUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(config('app.name')) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($absoluteUrl) ?>">
    <meta property="og:image" content="<?= e($baseUrl . url('/apple-touch-icon.png')) ?>">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AutoWash",
        "name": "<?= e(config('app.name')) ?>",
        "image": "<?= e($baseUrl . url('/apple-touch-icon.png')) ?>",
        "url": "<?= e($baseUrl . url('/')) ?>",
        "telephone": "<?= e(normalize_phone((string) setting('phone', '+7 (988) 413-07-77'))) ?>",
        "priceRange": "₽₽",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?= e((string) setting('address', 'г. Туапсе, ул. Богдана Хмельницкого, 16')) ?>",
            "addressLocality": "Туапсе",
            "addressRegion": "Краснодарский край",
            "addressCountry": "RU"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": <?= e((string) setting('map_lat', '44.104854')) ?>,
            "longitude": <?= e((string) setting('map_lng', '39.087091')) ?>
        },
        "openingHours": "Mo-Su 09:00-20:00",
        "sameAs": [
            "https://t.me/Grant0201",
            "https://vk.ru/granddeta1ling",
            "https://www.instagram.com/avjihanter/"
        ]
    }
    </script>
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
                    <a href="<?= e(url('/news')) ?>" class="<?= str_starts_with($current, '/news') ? 'active' : '' ?>">Новости</a>
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
