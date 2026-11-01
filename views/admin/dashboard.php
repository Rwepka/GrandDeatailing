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

<h2>Доход</h2>

<div class="income-panel">
    <button type="button" class="btn btn-outline btn-sm" data-range="today">Сегодня</button>
    <button type="button" class="btn btn-outline btn-sm" data-range="week">Неделя</button>
    <button type="button" class="btn btn-outline btn-sm" data-range="month">Месяц</button>
    <button type="button" class="btn btn-outline btn-sm" data-range="year">Год</button>
    <input type="date" id="income-from">
    <input type="date" id="income-to">
    <button type="button" class="btn btn-outline btn-sm" data-range="custom">Показать</button>
</div>

<canvas id="income-chart" height="100"></canvas>
<div class="income-total">Итого за выбранный период: <strong id="income-total-value">0 ₽</strong></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    'use strict';

    const fromInput = document.getElementById('income-from');
    const toInput = document.getElementById('income-to');
    const ctx = document.getElementById('income-chart').getContext('2d');
    let chart = null;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function fmtDate(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function rangeFor(kind) {
        const today = new Date();
        const from = new Date(today);
        const to = new Date(today);

        if (kind === 'today') {
            // от сегодня до сегодня
        } else if (kind === 'week') {
            const mondayOffset = (today.getDay() + 6) % 7;
            from.setDate(today.getDate() - mondayOffset);
        } else if (kind === 'month') {
            from.setDate(1);
        } else if (kind === 'year') {
            from.setMonth(0, 1);
        }

        return { from: fmtDate(from), to: fmtDate(to) };
    }

    function loadIncome(from, to) {
        fetch('/admin/dashboard/income?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to))
            .then(function (resp) {
                if (!resp.ok) {
                    throw new Error('HTTP ' + resp.status);
                }
                return resp.json();
            })
            .then(function (json) {
                const byDay = {};
                (json.data || []).forEach(function (row) {
                    byDay[row.day] = parseFloat(row.total) || 0;
                });

                const labels = [];
                const values = [];

                const cursor = new Date(from + 'T00:00:00');
                const end = new Date(to + 'T00:00:00');
                while (cursor <= end) {
                    const key = fmtDate(cursor);
                    labels.push(pad(cursor.getDate()) + '.' + pad(cursor.getMonth() + 1));
                    values.push(byDay[key] || 0);
                    cursor.setDate(cursor.getDate() + 1);
                }

                const total = values.reduce(function (sum, v) { return sum + v; }, 0);
                document.getElementById('income-total-value').textContent = total.toLocaleString('ru-RU') + ' ₽';

                if (chart !== null) {
                    chart.destroy();
                }

                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Доход, ₽',
                            data: values,
                            backgroundColor: '#1D4ED8'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            })
            .catch(function (err) {
                console.error('Ошибка загрузки графика дохода:', err);
            });
    }

    document.querySelectorAll('[data-range]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const kind = btn.getAttribute('data-range');
            if (kind === 'custom') {
                const from = fromInput.value;
                const to = toInput.value;
                if (!from || !to) {
                    alert('Укажите даты диапазона.');
                    return;
                }
                loadIncome(from, to);
                return;
            }
            const range = rangeFor(kind);
            fromInput.value = range.from;
            toInput.value = range.to;
            loadIncome(range.from, range.to);
        });
    });

    // При загрузке — месяц и заполняем поля.
    const initial = rangeFor('month');
    fromInput.value = initial.from;
    toInput.value = initial.to;
    loadIncome(initial.from, initial.to);
})();
</script>

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
