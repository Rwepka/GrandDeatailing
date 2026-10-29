<?php
/** @var array<int, array{slug: string, title: string, price: string}> $services */
?>
<section class="hero">
    <div class="container">
        <h1>Детейлинг премиум-класса</h1>
        <p class="hero-sub">Мойка, полировка, защитные покрытия и химчистка салона — вернём автомобилю заводской вид.</p>
        <a href="<?= e(url('/booking')) ?>" class="btn btn-gold">Записаться онлайн</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2>Наши услуги</h2>
        <div class="cards">
            <?php foreach ($services as $service): ?>
                <div class="card">
                    <h3><a href="<?= e(url('/services/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h3>
                    <p class="price"><?= e($service['price']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top: 16px"><a href="<?= e(url('/services')) ?>">Все услуги →</a></p>
    </div>
</section>

<section class="section section-muted">
    <div class="container">
        <h2>Почему мы</h2>
        <ul class="features">
            <li>Опыт 10+ лет</li>
            <li>Гарантия на работы</li>
            <li>Сертифицированные материалы</li>
            <li>Своя зона приёмки</li>
        </ul>
    </div>
</section>
