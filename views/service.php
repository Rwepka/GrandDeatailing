<?php
/**
 * Страница услуги. Весь контент приходит из БД (services),
 * цены по кузовам — из service_prices.
 *
 * @var array<string, mixed>        $service
 * @var string|null                 $categoryTitle
 * @var array<string, float|string> $prices
 */
$durationMin = $service['duration_min'] ?? null;
if ($durationMin === null) {
    $durationText = 'длительность уточняется';
} else {
    $totalMin = (int) $durationMin;
    if ($totalMin >= 1440) {
        // 24 часа и больше — показываем в днях.
        $days = intdiv($totalMin, 1440);
        $rest = $totalMin % 1440;
        $hours = intdiv($rest, 60);
        $minutes = $rest % 60;

        $dayWord = match (true) {
            $days % 100 >= 11 && $days % 100 <= 14 => 'дней',
            $days % 10 === 1 => 'день',
            $days % 10 >= 2 && $days % 10 <= 4 => 'дня',
            default => 'дней',
        };

        $durationText = '~' . $days . ' ' . $dayWord;
        if ($rest !== 0) {
            $durationText .= $minutes === 0
                ? ' ' . $hours . ' ч'
                : ' ' . $hours . ' ч ' . $minutes . ' мин';
        }
    } else {
        $hours = intdiv($totalMin, 60);
        $minutes = $totalMin % 60;
        $durationText = $minutes === 0
            ? '~' . $hours . ' ч'
            : '~' . $hours . ' ч ' . $minutes . ' мин';
    }
}

$slug = (string) ($service['slug'] ?? '');
$title = (string) ($service['title'] ?? 'Услуга');
$priceText = price($service['price_from'] === null ? null : (float) $service['price_from']);

$shortDescription = trim((string) ($service['short_description'] ?? ''));
$fullDescription = trim((string) ($service['description'] ?? ''));
$lead = $fullDescription !== '' ? $fullDescription : $shortDescription;
$includes = text_lines($service['includes'] ?? null);
$process = text_lines($service['process'] ?? null);
$prices = $prices ?? [];

$serviceSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $title,
    'serviceType' => 'Детейлинг',
    'description' => $lead !== '' ? $lead : $title,
    'provider' => [
        '@type' => 'AutoWash',
        'name' => (string) config('app.name'),
        'telephone' => normalize_phone((string) setting('phone', '+7 (988) 413-57-77')),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => (string) setting('address', 'г. Туапсе, ул. Богдана Хмельницкого, 16'),
            'addressLocality' => 'Туапсе',
            'addressRegion' => 'Краснодарский край',
            'addressCountry' => 'RU',
        ],
    ],
    'areaServed' => ['@type' => 'City', 'name' => 'Туапсе'],
];
if ($service['price_from'] !== null) {
    $serviceSchema['offers'] = [
        '@type' => 'Offer',
        'priceCurrency' => 'RUB',
        'price' => (float) $service['price_from'],
        'availability' => 'https://schema.org/InStock',
        'url' => rtrim((string) config('app.url'), '/') . url('/services/' . $slug),
    ];
}

$crumbs = [
    ['label' => 'Услуги', 'url' => url('/services')],
    ['label' => $title],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <div class="service-layout">
            <div class="service-main">
                <h1><?= e($title) ?></h1>
                <?php if ($lead !== ''): ?>
                    <p class="lead"><?= nl2br(e($lead)) ?></p>
                <?php endif; ?>

                <div class="service-meta">
                    <span><?= e($durationText) ?></span>
                    <span><?= e($categoryTitle ?? 'Детейлинг') ?></span>
                </div>

                <?php if ($includes !== []): ?>
                    <h2>Что входит в услугу</h2>
                    <ul class="prose-list">
                        <?php foreach ($includes as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($process !== []): ?>
                    <h2>Как проходит работа</h2>
                    <ol class="prose-list prose-list-numbered">
                        <?php foreach ($process as $step): ?>
                            <li><?= e($step) ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>

                <?php if ($prices !== []): ?>
                    <h2>Цены по классам автомобиля</h2>
                    <div class="table-responsive">
                        <table class="price-table">
                            <thead>
                                <tr><th>Класс автомобиля</th><th>Стоимость</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prices as $bodyType => $price): ?>
                                    <tr>
                                        <td><?= e((string) $bodyType) ?></td>
                                        <td><?= e(price((float) $price, '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="service-aside">
                <div class="service-aside-card">
                    <p class="aside-label">Стоимость</p>
                    <p class="price-xl"><?= e($priceText) ?></p>
                    <p class="aside-note">Итоговая цена зависит от класса автомобиля и состояния кузова. Подтвердим после осмотра.</p>
                    <a href="<?= e(url('/booking')) ?>" class="btn btn-accent">Записаться</a>
                    <a href="<?= e(url('/contacts')) ?>" class="btn btn-outline">Контакты</a>
                </div>
            </aside>
        </div>
    </div>
</section>
<script type="application/ld+json">
<?= json_encode($serviceSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>

</script>
