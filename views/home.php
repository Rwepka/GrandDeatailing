<?php
/** @var array<int, array{slug: string, title: string, short_description: string, price: string}> $services */
/** @var array<int, array<string, mixed>> $servicePrices */
/** @var array<int, array<string, mixed>> $faqs */
$servicePrices = $servicePrices ?? [];
$faqs = $faqs ?? [];

$faqSchema = null;
if ($faqs !== []) {
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static fn(array $item): array => [
            '@type' => 'Question',
            'name' => (string) ($item['question'] ?? ''),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => (string) ($item['answer'] ?? ''),
            ],
        ], $faqs),
    ];
}
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
             alt="Детейлинг GRAND DETAILING — полировка кузова автомобиля"
             width="2560" height="1920"
             fetchpriority="high"
             decoding="async">
    </picture>
    <div class="container">
        <h1>Детейлинг премиум-класса</h1>
        <p class="hero-sub">Мойка, полировка, керамика, защитная плёнка и химчистка салона. Премиальный уход за автомобилем с гарантией на работы.</p>
        <a href="<?= e(url('/booking')) ?>" class="btn btn-gold">Записаться онлайн</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Услуги детейлинга</h2>
            <a href="<?= e(url('/services')) ?>" class="section-link">Все услуги и цены</a>
        </div>
        <div class="cards">
            <?php foreach ($services as $service): ?>
                <div class="card">
                    <h3><a href="<?= e(url('/services/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h3>
                    <?php if ($service['short_description'] !== ''): ?>
                        <p class="card-text"><?= e($service['short_description']) ?></p>
                    <?php endif; ?>
                    <p class="price"><?= e($service['price']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section calculator-section">
    <div class="container">
        <h2>Калькулятор стоимости</h2>
        <p class="lead">Выберите класс автомобиля и услуги — покажем примерную стоимость «от».</p>
        <form method="GET" action="<?= e(url('/booking')) ?>" class="calculator">
            <div class="field-group">
                <label>Класс автомобиля</label>
                <div class="segmented">
                    <?php foreach ($carClasses ?? [] as $carClass): ?>
                        <label class="option"><input type="radio" name="car_class" value="<?= e($carClass) ?>"><span><?= e($carClass) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="field-group">
                <label>Услуги</label>
                <div class="calculator-services">
                    <?php foreach ($calculatorServices ?? [] as $s): ?>
                        <label class="calculator-service"
                               data-price="<?= e((string) ($s['price_from'] ?? '')) ?>"
                               data-prices="<?= e(json_encode($servicePrices[(int) $s['id']] ?? [], JSON_UNESCAPED_UNICODE)) ?>">
                            <input type="checkbox" name="service_ids[]" value="<?= e((string) $s['id']) ?>">
                            <span><?= e((string) $s['title']) ?></span>
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
        <h2>Детейлинг-студия GRAND DETAILING в Туапсе</h2>
        <div class="prose">
            <p>GRAND DETAILING (Grand Detailing, «Гранд Детейлинг») — студия детейлинга премиум-класса в Туапсе. Мы занимаемся полировкой кузова, нанесением керамики, оклейкой защитной плёнкой PPF, тонировкой, химчисткой салона и мойкой автомобилей. Работаем с любыми классами: от компактных хэтчбеков до кроссоверов и минивэнов.</p>
            <p>Детейлинг — это не только внешний вид. Полировка возвращает глубину цвета и убирает следы эксплуатации, керамика и плёнка защищают лак от песка, реагентов и ультрафиолета, а химчистка салона устраняет пятна и запахи. Каждую работу начинаем с осмотра и согласовываем объём до начала работ.</p>
            <p>К нам приезжают из Джубги, Новомихайловского, Шепси, Небуга и др.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2>Почему выбирают GRAND DETAILING</h2>
        <ul class="features">
            <li>Опыт 10+ лет</li>
            <li>Гарантия на работы</li>
            <li>Сертифицированные материалы</li>
            <li>Своя зона приёмки</li>
        </ul>
    </div>
</section>

<?php if ($faqs !== []): ?>
<section class="section section-muted">
    <div class="container">
        <h2>Частые вопросы о детейлинге</h2>
        <div class="faq">
            <?php foreach ($faqs as $item): ?>
                <details>
                    <summary><?= e((string) ($item['question'] ?? '')) ?></summary>
                    <div class="faq-answer"><?= e((string) ($item['answer'] ?? '')) ?></div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($faqSchema !== null): ?>
<script type="application/ld+json">
<?= json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>

</script>
<?php endif; ?>
