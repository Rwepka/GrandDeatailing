<?php
/**
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, array<int, array<string, mixed>>> $servicesByCategory
 */
?>
<section class="section">
    <div class="container">
        <h1>Услуги</h1>

        <?php foreach ($categories as $cat): ?>
            <?php $catServices = $servicesByCategory[(int) $cat['id']] ?? []; ?>
            <h2><?= e($cat['title']) ?></h2>
            <div class="cards">
                <?php foreach ($catServices as $service): ?>
                    <div class="card">
                        <h3>
                            <a href="<?= e(url('/services/' . $service['slug'])) ?>"><?= e($service['title']) ?></a>
                        </h3>
                        <p class="price"><?= e(price($service['price_from'] === null ? null : (float) $service['price_from'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
