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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
