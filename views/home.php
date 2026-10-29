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

<section class="section calculator-section">
    <div class="container">
        <h2>Калькулятор стоимости</h2>
        <p class="lead">Выберите класс автомобиля и услуги — покажем примерную стоимость «от».</p>
        <form method="GET" action="<?= e(url('/booking')) ?>" class="calculator">
            <div class="field-group">
                <label>Класс автомобиля</label>
                <div class="option-grid">
                    <?php foreach ($carClasses ?? [] as $carClass): ?>
                        <label class="option"><input type="radio" name="car_class" value="<?= e($carClass) ?>"><span><?= e($carClass) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="field-group">
                <label>Услуги</label>
                <div class="calculator-services">
                    <?php foreach ($calculatorServices ?? [] as $s): ?>
                        <label class="calculator-service">
                            <input type="checkbox" name="service_ids[]" value="<?= e((string)$s['id']) ?>" data-price="<?= e((string)($s['price_from'] ?? '')) ?>">
                            <span><?= e((string)$s['title']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="calculator-total">Итого: <strong id="calc-total">от 0 ₽</strong></div>
            <button type="submit" class="btn btn-accent">Записаться</button>
        </form>
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
