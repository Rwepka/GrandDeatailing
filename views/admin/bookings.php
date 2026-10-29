<?php
/**
 * @var array<int, array<string, mixed>> $bookings
 * @var string|null                     $flash
 */
$bookings = $bookings ?? [];
$flash = $flash ?? null;

$statuses = [
    'new' => 'Новая',
    'confirmed' => 'Подтверждена',
    'in_work' => 'В работе',
    'done' => 'Выполнена',
    'ready' => 'Готова к выдаче',
    'cancelled' => 'Отменена',
];
?>
<h1>Заявки</h1>

<?php if (!empty($flash)): ?>
    <div class="flash" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>

<?php if (empty($bookings)): ?>
    <p>Заявок пока нет</p>
<?php else: ?>
    <div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>№</th>
                <th>Клиент</th>
                <th>Автомобиль</th>
                <th>Дата/время</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <?php
                    $status = (string) ($booking['status'] ?? '');
                    $dateTime = '';
                    if (!empty($booking['pref_date'])) {
                        $dateTime = (string) $booking['pref_date'];
                        if (!empty($booking['pref_time'])) {
                            $dateTime .= ' ' . (string) $booking['pref_time'];
                        }
                    }
                ?>
                <tr>
                    <td><?= e($booking['id']) ?></td>
                    <td>
                        <?= e((string) ($booking['client_name'] ?: '—')) ?><br>
                        <?= e((string) ($booking['client_phone'] ?: '')) ?>
                    </td>
                    <td><?= e((string) ($booking['car_info'] ?: '—')) ?></td>
                    <td><?= e($dateTime !== '' ? $dateTime : '—') ?></td>
                    <td><span class="badge badge-<?= e($status) ?>"><?= e($statuses[$status] ?? $status) ?></span></td>
                    <td class="actions">
                        <form method="POST" action="<?= e(url('/admin/bookings/' . $booking['id'] . '/status')) ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <select name="status">
                                <?php foreach ($statuses as $code => $label): ?>
                                    <option value="<?= e($code) ?>"<?= $code === $status ? ' selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-accent btn-sm">Сохранить</button>
                        </form>
                        <?php if (empty($booking['user_id'])): ?>
                            <form method="POST" action="<?= e(url('/admin/bookings/' . $booking['id'] . '/create-client')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm">Создать клиента</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
