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
    <input type="text" class="table-search" placeholder="Поиск: имя, телефон, авто..." data-table-search="#bookings-table">
    <div class="table-responsive">
    <table class="admin-table" id="bookings-table">
        <thead>
            <tr>
                <th>№</th>
                <th>Клиент</th>
                <th>Автомобиль</th>
                <th>Дата/время</th>
                <th>Стоимость</th>
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

                    $finalCost = $booking['final_cost'] ?? null;
                    $agreedCost = $booking['agreed_cost'] ?? null;
                    $estimatedCost = $booking['estimated_cost'] ?? null;
                    $costText = '—';
                    if ($finalCost !== null && $finalCost !== '') {
                        $costText = price((float) $finalCost, '');
                    } elseif ($agreedCost !== null && $agreedCost !== '') {
                        $costText = price((float) $agreedCost, '');
                    } elseif ($estimatedCost !== null && $estimatedCost !== '') {
                        $costText = price((float) $estimatedCost, '');
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
                    <td><?= e($costText) ?></td>
                    <td><span class="badge badge-<?= e($status) ?>"><?= e($statuses[$status] ?? $status) ?></span></td>
                    <td class="actions">
                        <form method="POST" action="<?= e(url('/admin/bookings/' . $booking['id'] . '/status')) ?>" class="inline-form" id="status-form-<?= e($booking['id']) ?>" data-cost-form>
                            <?= csrf_field() ?>
                            <input type="hidden" name="cost" value="">
                            <select name="status" id="status-select-<?= e($booking['id']) ?>">
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
                        <?php if (isAdmin()): ?>
                            <form method="POST" action="<?= e(url('/admin/bookings/' . $booking['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Удалить заявку? Это действие необратимо.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>

<script>
    document.querySelectorAll('form[data-cost-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const select = form.querySelector('select[name="status"]');
            const hiddenCost = form.querySelector('input[name="cost"]');
            const status = select ? select.value : '';
            if (status !== 'in_work' && status !== 'done') {
                return;
            }

            const promptText = status === 'in_work'
                ? 'Введите согласованную стоимость (₽)'
                : 'Введите итоговую стоимость (₽)';
            const input = prompt(promptText);
            if (input === null) {
                event.preventDefault();
                return;
            }

            const normalized = input.replace(',', '.');
            const value = parseFloat(normalized);
            if (normalized.trim() === '' || Number.isNaN(value)) {
                alert('Введите корректную стоимость.');
                event.preventDefault();
                return;
            }

            hiddenCost.value = normalized;
        });
    });
</script>
