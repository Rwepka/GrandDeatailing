<?php
/**
 * @var array<int, array<string, mixed>> $reviews
 */
$reviews = $reviews ?? [];
?>
<h1>Отзывы</h1>

<?php if (empty($reviews)): ?>
    <p>Отзывов пока нет</p>
<?php else: ?>
    <?php foreach ($reviews as $review): ?>
        <?php
            $reviewId = (int) ($review['id'] ?? 0);
            $client = $review['user_name'] ?? null;
            $service = $review['service_titles'] ?? null;
            $date = $review['created_at'] ?? null;
            $rating = (int) ($review['rating'] ?? 0);
            $stars = str_repeat('★', min(5, max(0, $rating))) . str_repeat('☆', max(0, 5 - $rating));
            $published = (bool) ($review['is_published'] ?? false);
        ?>
        <details class="review-item">
            <summary>
                <span class="review-client"><?= e($client ?? 'Клиент') ?></span>
                <span class="review-service"><?= e($service ?? '—') ?></span>
                <span class="review-date"><?= e((string) ($date ?? '')) ?></span>
            </summary>
            <div class="review-body">
                <p class="review-rating"><?= e($stars) ?> (<?= e((string) $rating) ?>/5)</p>
                <p class="review-text"><?= e((string) ($review['text'] ?? '')) ?></p>
                <?php if ($published): ?>
                    <span class="badge badge-done">Опубликован</span>
                <?php else: ?>
                    <span class="badge badge-new">На модерации</span>
                <?php endif; ?>
                <form class="inline-form" method="POST" action="<?= e(url('/admin/reviews/' . $reviewId . '/toggle')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm">
                        <?= $published ? 'Скрыть' : 'Опубликовать' ?>
                    </button>
                </form>
            </div>
        </details>
    <?php endforeach; ?>
    <p class="review-note">Клиент, услуга и дата отзыва указаны в заголовке элемента.</p>
<?php endif; ?>
