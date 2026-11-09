<?php
/**
 * @var array<int, array<string, mixed>> $bookings
 * @var string|null                     $flash
 */
$bookings = $bookings ?? [];
$flash = $flash ?? null;
$masters = $masters ?? [];

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
<a href="<?= e(url('/admin/bookings/create')) ?>" class="btn btn-accent btn-create">Создать заявку</a>

<?php if (!empty($flash)): ?>
    <div class="flash" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>

<?php if (empty($bookings)): ?>
    <p>Заявок пока нет</p>
<?php else: ?>
    <input type="text" class="table-search" placeholder="Поиск: имя, телефон, авто..." data-table-search="#bookings-table">
    <div class="table-responsive">
    <table class="admin-table" id="bookings-table">
        <caption class="visually-hidden">Заявки</caption>
        <thead>
            <tr>
                <th scope="col">№</th>
                <th scope="col">Клиент</th>
                <th scope="col">Мастер</th>
                <th scope="col">Автомобиль</th>
                <th scope="col">Дата/время</th>
                <th scope="col">Стоимость</th>
                <th scope="col">Статус</th>
                <th scope="col">Действия</th>
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
                    <td>
                        <div class="master-cell">
                            <span class="master-name"><?= e((string) (($booking['master_name'] ?? '') ?: '—')) ?></span>
                            <?php if (empty($booking['master_id'])): ?>
                                <form method="POST" action="<?= e(url('/admin/bookings/' . $booking['id'] . '/take')) ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline btn-sm">Взять на себя</button>
                                </form>
                            <?php endif; ?>
                            <?php if (isAdmin()): ?>
                                <button type="button" class="btn btn-outline btn-sm js-assign"
                                        data-booking-id="<?= e($booking['id']) ?>"
                                        data-master-id="<?= (int) ($booking['master_id'] ?? 0) ?>"
                                        aria-haspopup="dialog">Назначить</button>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= e((string) ($booking['car_info'] ?: '—')) ?></td>
                    <td><?= e($dateTime !== '' ? $dateTime : '—') ?></td>
                    <td><?= e($costText) ?></td>
                    <td><span class="badge badge-<?= e($status) ?>"><?= e($statuses[$status] ?? $status) ?></span></td>
                    <td class="actions">
                        <form method="POST" action="<?= e(url('/admin/bookings/' . $booking['id'] . '/status')) ?>" class="inline-form" id="status-form-<?= e($booking['id']) ?>" data-cost-form>
                            <?= csrf_field() ?>
                            <input type="hidden" name="cost" value="">
                            <select name="status" id="status-select-<?= e($booking['id']) ?>" aria-label="Статус заявки №<?= e($booking['id']) ?>">
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

<?php if (isAdmin()): ?>
<div class="modal-overlay" id="assign-modal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="assign-modal-title" tabindex="-1">
        <h2 id="assign-modal-title">Назначить мастера</h2>
        <form method="POST" action="" id="assign-form">
            <?= csrf_field() ?>
            <div class="assign-list">
                <label class="assign-option">
                    <input type="radio" name="master_id" value="0">
                    <span>— не назначен —</span>
                </label>
                <?php foreach ($masters as $m): ?>
                    <label class="assign-option">
                        <input type="radio" name="master_id" value="<?= e((string) $m['id']) ?>">
                        <span><?= e((string) $m['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" id="assign-cancel">Отмена</button>
                <button type="submit" class="btn btn-accent">Назначить</button>
            </div>
        </form>
    </div>
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
