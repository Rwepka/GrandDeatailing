<?php
/**
 * @var int $newBookings
 * @var int $inWorkBookings
 * @var int $readyBookings
 * @var int $totalBookings
 * @var int $totalClients
 * @var int $totalServices
 * @var array<int, array<string, mixed>> $recentBookings
 */
$newBookings = $newBookings ?? 0;
$inWorkBookings = $inWorkBookings ?? 0;
$readyBookings = $readyBookings ?? 0;
$totalBookings = $totalBookings ?? 0;
$totalClients = $totalClients ?? 0;
$totalServices = $totalServices ?? 0;
$recentBookings = $recentBookings ?? [];

$statusLabels = [
    'new' => 'Новая',
    'confirmed' => 'Подтверждена',
    'in_work' => 'В работе',
    'done' => 'Выполнена',
    'ready' => 'Готова к выдаче',
    'cancelled' => 'Отменена',
];
?>
<h1>Дашборд</h1>

<div class="admin-kpis">
    <div class="admin-kpi">
        <div class="value"><?= e($newBookings) ?></div>
        <div class="label">Новые заявки</div>
    </div>
    <div class="admin-kpi">
        <div class="value"><?= e($inWorkBookings) ?></div>
        <div class="label">В работе</div>
    </div>
    <div class="admin-kpi">
        <div class="value"><?= e($readyBookings) ?></div>
        <div class="label">Готовы к выдаче</div>
    </div>
    <div class="admin-kpi">
        <div class="value"><?= e($totalBookings) ?></div>
        <div class="label">Всего заявок</div>
    </div>
    <div class="admin-kpi">
        <div class="value"><?= e($totalClients) ?></div>
        <div class="label">Всего клиентов</div>
    </div>
    <div class="admin-kpi">
        <div class="value"><?= e($totalServices) ?></div>
        <div class="label">Всего услуг</div>
    </div>
</div>

<h2>Последние заявки</h2>
<?php if (empty($recentBookings)): ?>
    <p>Заявок пока нет</p>
<?php else: ?>
    <div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>№</th>
                <th>Клиент</th>
                <th>Автомобиль</th>
                <th>Статус</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentBookings as $booking): ?>
                <?php $status = (string) ($booking['status'] ?? ''); ?>
                <tr>
                    <td><?= e($booking['id']) ?></td>
                    <td><?= e((string) ($booking['client_name'] ?: '—')) ?></td>
                    <td><?= e((string) ($booking['car_info'] ?: '—')) ?></td>
                    <td><span class="badge badge-<?= e($status) ?>"><?= e($statusLabels[$status] ?? $status) ?></span></td>
                    <td><?= e((string) ($booking['created_at'] ?: '—')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
