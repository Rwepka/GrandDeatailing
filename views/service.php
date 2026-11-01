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
    $hours = intdiv($totalMin, 60);
    $minutes = $totalMin % 60;
    $durationText = $minutes === 0
        ? '~' . $hours . ' ч'
        : '~' . $hours . ' ч ' . $minutes . ' мин';
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
