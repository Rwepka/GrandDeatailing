<?php
/**
 * @var array<string, mixed>             $user
 * @var array<int, array<string, mixed>> $bookings
 */
$user = $user ?? [];
$bookings = $bookings ?? [];

$statusLabels = [
    'new' => 'Новая',
    'confirmed' => 'Подтверждена',
    'in_work' => 'В работе',
    'done' => 'Выполнена',
    'ready' => 'Готова к выдаче',
    'cancelled' => 'Отменена',
];
?>
<section class="section">
    <div class="container">
        <?php $active = 'cabinet'; require BASE_PATH . '/views/cabinet/_tabs.php'; ?>
        <p class="lead">Здравствуйте, <?= e((string) ($user['name'] ?? '')) ?>!</p>

        <?php if (empty($bookings)): ?>
            <p>Записей пока нет</p>
            <a href="<?= e(url('/booking')) ?>" class="btn btn-gold">Записаться</a>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($bookings as $booking): ?>
                    <?php
                        $status = (string) ($booking['status'] ?? '');
                        $statusLabel = $statusLabels[$status] ?? $status;
                    ?>
                    <div class="card">
                        <h3>Статус: <?= e($statusLabel) ?></h3>
                        <p>Желаемая дата: <?= e((string) ($booking['pref_date'] ?: '—')) ?></p>
                        <p>Желаемое время: <?= e((string) ($booking['pref_time'] ?: '—')) ?></p>
                        <p>Автомобиль: <?= e((string) ($booking['car_info'] ?: '—')) ?></p>
                        <?php if (!empty($booking['comment'])): ?>
                            <p>Комментарий: <?= e((string) $booking['comment']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
