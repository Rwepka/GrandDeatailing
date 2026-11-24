<?php
/** @var string|null $title */
/** @var string|null $metaDescription */
$title = $title ?? (string) config('app.name');
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$isHome = $current === '/';
$metaDescription = $metaDescription ?? 'Детейлинг-студия GRAND DETAILING в Туапсе: полировка кузова, химчистка салона, керамика, оклейка плёнкой и тонировка. Онлайн-запись, гарантия на работы.';
$baseUrl = rtrim((string) config('app.url'), '/');
$absoluteUrl = $baseUrl . ($current === '/' ? '/' : $current);
$phone = (string) setting('phone', '+7 (988) 413-57-77');
$address = (string) setting('address', 'г. Туапсе, ул. Богдана Хмельницкого, 16');
$mapLat = (string) setting('map_lat', '44.104854');
$mapLng = (string) setting('map_lng', '39.087091');
$ogImage = $baseUrl . url('/assets/img/hero-1280.jpg');
$googleVerification = trim((string) setting('google_verification', ''));
$yandexVerification = trim((string) setting('yandex_verification', ''));
$noindex = str_starts_with($current, '/cabinet')
    || in_array($current, ['/login', '/register', '/booking/success', '/reviews/create'], true);
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
    <meta name="theme-color" content="#252633">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="<?= $noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' ?>">
    <?php if ($googleVerification !== ''): ?>
        <meta name="google-site-verification" content="<?= e($googleVerification) ?>">
    <?php endif; ?>
    <?php if ($yandexVerification !== ''): ?>
        <meta name="yandex-verification" content="<?= e($yandexVerification) ?>">
    <?php endif; ?>
    <meta name="geo.region" content="RU-KDA">
    <meta name="geo.placename" content="Туапсе">
    <meta name="geo.position" content="<?= e($mapLat) ?>;<?= e($mapLng) ?>">
    <meta name="ICBM" content="<?= e($mapLat) ?>, <?= e($mapLng) ?>">
    <link rel="canonical" href="<?= e($absoluteUrl) ?>">
    <link rel="preload" href="<?= e(url('/assets/fonts/inter-cyrillic.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="<?= e(config('app.name')) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($absoluteUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:width" content="1280">
    <meta property="og:image:height" content="960">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AutoWash",
        "@id": <?= json_encode($baseUrl . '/#business', JSON_UNESCAPED_UNICODE) ?>,
        "name": <?= json_encode((string) config('app.name'), JSON_UNESCAPED_UNICODE) ?>,
        "alternateName": ["Grand Detailing", "Гранд Детейлинг"],
        "description": "Детейлинг-студия в Туапсе: полировка кузова, керамика, оклейка плёнкой PPF, тонировка, химчистка салона и мойка.",
        "url": <?= json_encode($baseUrl . '/', JSON_UNESCAPED_UNICODE) ?>,
        "image": <?= json_encode($ogImage, JSON_UNESCAPED_UNICODE) ?>,
        "logo": <?= json_encode($baseUrl . url('/android-chrome-512x512.png'), JSON_UNESCAPED_UNICODE) ?>,
        "telephone": <?= json_encode(normalize_phone($phone), JSON_UNESCAPED_UNICODE) ?>,
        "priceRange": "₽₽",
        "currenciesAccepted": "RUB",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": <?= json_encode($address, JSON_UNESCAPED_UNICODE) ?>,
            "addressLocality": "Туапсе",
            "addressRegion": "Краснодарский край",
            "postalCode": "352800",
            "addressCountry": "RU"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": <?= json_encode((float) $mapLat) ?>,
            "longitude": <?= json_encode((float) $mapLng) ?>
        },
        "areaServed": [
            { "@type": "City", "name": "Туапсе" },
            { "@type": "AdministrativeArea", "name": "Краснодарский край" }
        ],
        "openingHoursSpecification": [{
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            "opens": "10:00",
            "closes": "19:00"
        }],
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
<a class="skip-link" href="#main">Перейти к содержимому</a>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= e(url('/')) ?>" class="logo logo-gd" aria-label="GRAND DETAILING — на главную">GD</a>
        <button type="button" class="nav-toggle" id="nav-toggle" aria-label="Открыть меню" aria-expanded="false" aria-controls="header-menu">
            <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
        </button>
        <div class="header-menu" id="header-menu">
            <div class="header-menu-inner">
            <nav class="main-nav" aria-label="Основное меню">
                <?php if (isMaster()): ?>
                    <a href="<?= e(url('/contacts')) ?>"<?= nav_attrs(str_starts_with($current, '/contacts')) ?>>Контакты</a>
                <?php else: ?>
                    <a href="<?= e(url('/')) ?>"<?= nav_attrs($isHome) ?>>Главная</a>
                    <a href="<?= e(url('/services')) ?>"<?= nav_attrs(str_starts_with($current, '/services')) ?>>Услуги</a>
                    <a href="<?= e(url('/news')) ?>"<?= nav_attrs(str_starts_with($current, '/news')) ?>>Новости</a>
                    <a href="<?= e(url('/reviews')) ?>"<?= nav_attrs(str_starts_with($current, '/reviews')) ?>>Отзывы</a>
                    <a href="<?= e(url('/contacts')) ?>"<?= nav_attrs(str_starts_with($current, '/contacts')) ?>>Контакты</a>
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
                <?php else: ?>
                    <a href="<?= e(url('/login')) ?>">Войти</a>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>
</header>
<div class="nav-scrim" id="nav-scrim" aria-hidden="true"></div>
<main class="site-main" id="main" tabindex="-1">
