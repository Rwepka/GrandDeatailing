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
    <div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Оценка</th>
                <th>Текст</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $review): ?>
                <?php
                    $rating = (int) ($review['rating'] ?? 0);
                    $stars = str_repeat('★', $rating) . str_repeat('☆', max(0, 5 - $rating));
                    $published = (bool) ($review['is_published'] ?? false);
                ?>
                <tr>
                    <td><?= e($stars) ?> (<?= e((string) $rating) ?>/5)</td>
                    <td><?= e((string) ($review['text'] ?? '')) ?></td>
                    <td>
                        <?php if ($published): ?>
                            <span class="badge badge-done">Опубликован</span>
                        <?php else: ?>
                            <span class="badge badge-new">На модерации</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <form class="inline-form" method="POST" action="<?= e(url('/admin/reviews/' . (int) $review['id'] . '/toggle')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-sm">
                                <?= $published ? 'Скрыть' : 'Опубликовать' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
