<?php
/**
 * Список клиентов в админ-панели.
 *
 * @var array<int, array<string, mixed>> $clients
 * @var string $title
 * @var string|null $flash
 */
$clients = $clients ?? [];
$flash = $flash ?? null;
?>
<h1>Клиенты</h1>

<?php if (!empty($flash)): ?>
    <div class="flash" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>

<?php if (empty($clients)): ?>
    <p>Клиентов пока нет</p>
<?php else: ?>
    <input type="text" class="table-search" placeholder="Поиск: имя, телефон, email..." data-table-search="#clients-table">
    <div class="table-responsive">
    <table class="admin-table" id="clients-table">
        <caption class="visually-hidden">Клиенты</caption>
        <thead>
            <tr>
                <th scope="col">№</th>
                <th scope="col">Имя</th>
                <th scope="col">Телефон</th>
                <th scope="col">Email</th>
                <th scope="col">Автомобили</th>
                <th scope="col">Заявки</th>
                <th scope="col">Статус</th>
                <th scope="col">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <?php $isVerified = !empty($client['verified_at']); ?>
                <tr>
                    <td><?= e($client['id']) ?></td>
                    <td><?= e((string) ($client['name'] ?? '')) ?></td>
                    <td><?= e((string) ($client['phone'] ?? '')) ?></td>
                    <td><?= e((string) ($client['email'] ?? '')) ?></td>
                    <td><?= e((string) ($client['cars_count'] ?? 0)) ?></td>
                    <td><?= e((string) ($client['bookings_count'] ?? 0)) ?></td>
                    <td>
                        <?php if ($isVerified): ?>
                            <span class="badge badge-done">Подтверждён</span>
                        <?php else: ?>
                            <span class="badge badge-in_work">Не подтверждён</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <?php if (!$isVerified): ?>
                            <form method="POST" action="<?= e(url('/admin/clients/' . $client['id'] . '/verify')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-accent btn-sm">Подтвердить</button>
                            </form>
                        <?php endif; ?>
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
