<?php
/**
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, array<int, array<string, mixed>>> $servicesByCategory
 */
$crumbs = [
    ['label' => 'Услуги'],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <h1>Услуги детейлинга</h1>
        <p class="lead">Полировка, керамика, защитная плёнка, тонировка, химчистка и мойка. Цены указаны «от» — итоговую стоимость подтверждаем после осмотра автомобиля.</p>

        <?php foreach ($categories as $cat): ?>
            <?php $catServices = $servicesByCategory[(int) $cat['id']] ?? []; ?>
            <?php if ($catServices === []) { continue; } ?>
            <h2><?= e($cat['title']) ?></h2>
            <div class="cards">
                <?php foreach ($catServices as $service): ?>
                    <?php $short = trim((string) ($service['short_description'] ?? '')); ?>
                    <div class="card">
                        <h3>
                            <a href="<?= e(url('/services/' . $service['slug'])) ?>"><?= e($service['title']) ?></a>
                        </h3>
                        <?php if ($short !== ''): ?>
                            <p class="card-text"><?= e($short) ?></p>
                        <?php endif; ?>
                        <p class="price"><?= e(price($service['price_from'] === null ? null : (float) $service['price_from'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
