<?php
/**
 * Список клиентов в админ-панели.
 *
 * @var array<int, array<string, mixed>> $clients
 * @var string $title
 */
$clients = $clients ?? [];
?>
<h1>Клиенты</h1>

<?php if (empty($clients)): ?>
    <p>Клиентов пока нет</p>
<?php else: ?>
    <input type="text" class="table-search" placeholder="Поиск: имя, телефон, email..." data-table-search="#clients-table">
    <div class="table-responsive">
    <table class="admin-table" id="clients-table">
        <thead>
            <tr>
                <th>№</th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Автомобили</th>
                <th>Заявки</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= e($client['id']) ?></td>
                    <td><?= e((string) ($client['name'] ?? '')) ?></td>
                    <td><?= e((string) ($client['phone'] ?? '')) ?></td>
                    <td><?= e((string) ($client['email'] ?? '')) ?></td>
                    <td><?= e((string) ($client['cars_count'] ?? 0)) ?></td>
                    <td><?= e((string) ($client['bookings_count'] ?? 0)) ?></td>
                    <td>
                        <form method="POST" action="<?= e(url('/admin/clients/' . $client['id'] . '/delete')) ?>" class="inline-form" onsubmit="return confirm('Удалить клиента? Действие необратимо.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
