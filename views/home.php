<?php
/** @var array<int, array{slug: string, title: string, price: string}> $services */
/** @var array<int, array<string, mixed>> $servicePrices */
$servicePrices = $servicePrices ?? [];
?>
<section class="hero">
    <picture>
        <source
            type="image/webp"
            srcset="<?= e(asset('img/hero-768.webp')) ?> 768w, <?= e(asset('img/hero-1280.webp')) ?> 1280w, <?= e(asset('img/hero-1920.webp')) ?> 1920w"
            sizes="100vw">
        <img class="hero-bg"
             src="<?= e(asset('img/hero-1280.jpg')) ?>"
             srcset="<?= e(asset('img/hero-768.jpg')) ?> 768w, <?= e(asset('img/hero-1280.jpg')) ?> 1280w, <?= e(asset('img/hero-1920.jpg')) ?> 1920w"
             sizes="100vw"
             alt=""
             width="2560" height="1920"
             fetchpriority="high"
             decoding="async">
    </picture>
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
        <p style="margin-top: 16px"><a href="<?= e(url('/services')) ?>" class="link-light">Все услуги →</a></p>
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
                            <input type="checkbox" name="service_ids[]" value="<?= e((string)$s['id']) ?>" data-price="<?= e((string)($s['price_from'] ?? '')) ?>" data-prices="<?= e(json_encode($servicePrices[(int)$s['id']] ?? [], JSON_UNESCAPED_UNICODE)) ?>">
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
