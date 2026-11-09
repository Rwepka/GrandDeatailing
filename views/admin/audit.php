<?php
/**
 * Журнал действий в админ-панели.
 *
 * @var array<int, array<string, mixed>> $entries
 */
$entries = $entries ?? [];
?>
<h1>Журнал действий</h1>

<?php if (empty($entries)): ?>
    <p>Записей пока нет.</p>
<?php else: ?>
    <div class="table-responsive">
    <table class="admin-table">
        <caption class="visually-hidden">Журнал действий</caption>
        <thead>
            <tr>
                <th scope="col">Дата</th>
                <th scope="col">Кто</th>
                <th scope="col">Действие</th>
                <th scope="col">Объект</th>
                <th scope="col">Детали</th>
                <th scope="col">IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= e((string) ($entry['created_at'] ?? '')) ?></td>
                    <td><?= e((string) ($entry['actor_name'] ?? '—')) ?></td>
                    <td><?= e((string) ($entry['action'] ?? '')) ?></td>
                    <td>
                        <?= e((string) ($entry['entity_type'] ?? '')) ?>
                        <?= !empty($entry['entity_id']) ? ' #' . e((string) $entry['entity_id']) : '' ?>
                    </td>
                    <td><?= e((string) ($entry['meta'] ?? '')) ?></td>
                    <td><?= e((string) ($entry['ip'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
