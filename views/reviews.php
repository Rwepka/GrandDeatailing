<?php
/**
 * Публичная страница отзывов.
 *
 * @var array<int, array<string, mixed>> $reviews
 */
$crumbs = [
    ['label' => 'Отзывы'],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <h1>Отзывы</h1>
        <p class="lead">Что говорят о нас клиенты студии.</p>

        <?php if (empty($reviews)): ?>
            <p>Отзывов пока нет</p>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($reviews as $review): ?>
                    <?php
                        $rating = (int)($review['rating'] ?? 0);
                        $stars = str_repeat('★', min(5, max(0, $rating))) . str_repeat('☆', max(0, 5 - $rating));
                        $author = !empty($review['user_name']) ? $review['user_name'] : 'Клиент';
                        $createdAt = (string)($review['created_at'] ?? '');
                        $ts = $createdAt !== '' ? strtotime($createdAt) : false;
                        $reviewDate = $ts !== false ? date('d.m.Y', $ts) : '—';
                    ?>
                    <div class="card">
                        <p class="stars"><?= e($stars) ?></p>
                        <p><?= e((string)($review['text'] ?? '')) ?></p>
                        <p class="review-author"><?= e($author) ?></p>
                        <p class="review-date"><?= e($reviewDate) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="lead">Чтобы оставить отзыв, воспользуйтесь нашими услугами хотя бы один раз, перейдите в личный кабинет и нажмите кнопку «Оставить отзыв» под оказанной услугой.</p>
    </div>
</section>
<?php
$reviewCount = count($reviews);
$avgRating = 0;
if ($reviewCount > 0) {
    $sum = 0;
    foreach ($reviews as $r) {
        $sum += (int) ($r['rating'] ?? 0);
    }
    $avgRating = round($sum / $reviewCount, 1);
}
?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "AutoWash",
    "name": "<?= e(config('app.name')) ?>",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?= e((string) $avgRating) ?>",
        "reviewCount": "<?= e((string) $reviewCount) ?>"
    }
}
</script>
