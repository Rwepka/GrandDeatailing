<?php
/**
 * Список мастеров в админ-панели.
 *
 * @var array<int, array<string, mixed>> $masters
 * @var string|null $flash
 */
$masters = $masters ?? [];
$flash = $flash ?? null;
?>
<h1>Мастера</h1>
<a href="<?= e(url('/admin/masters/create')) ?>" class="btn btn-accent">Добавить мастера</a>

<?php if (!empty($flash)): ?>
    <div class="flash" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>

<div class="table-responsive">
<table class="admin-table">
    <thead>
        <tr>
            <th>№</th>
            <th>ФИО</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Дата создания</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($masters as $master): ?>
            <tr>
                <td><?= e($master['id']) ?></td>
                <td><?= e((string) ($master['name'] ?? '')) ?></td>
                <td><?= e((string) ($master['email'] ?? '')) ?></td>
                <td><?= e((string) ($master['phone'] ?? '')) ?></td>
                <td><?= e((string) ($master['created_at'] ?? '')) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ($masters === []): ?>
            <tr>
                <td colspan="5">Мастеров пока нет.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
