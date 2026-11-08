<?php
/**
 * @var array<string, mixed> $service
 * @var string|null $categoryTitle
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

$crumbs = [
    ['label' => 'Услуги', 'url' => url('/services')],
    ['label' => (string) ($service['title'] ?? 'Услуга')],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <h1><?= e($service['title']) ?></h1>

        <p class="price"><?= e(price($service['price_from'] === null ? null : (float) $service['price_from'])) ?></p>

        <p>Длительность: <?= e($durationText) ?></p>

        <p>Категория: <?= e($categoryTitle ?? '—') ?></p>

        <p><a href="<?= e(url('/services')) ?>">← Все услуги</a></p>

        <a href="<?= e(url('/booking')) ?>" class="btn btn-accent">Записаться</a>
    </div>
</section>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "<?= e((string) $service['title']) ?>",
    "serviceType": "<?= e($categoryTitle ?? 'Детейлинг') ?>",
    "provider": { "@type": "AutoWash", "name": "<?= e(config('app.name')) ?>" },
    "areaServed": "Туапсе"
}
</script>
