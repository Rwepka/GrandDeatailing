<?php
/**
 * @var array<int, array<string, mixed>> $messages
 */
$messages = $messages ?? [];
?>
<h1>Сообщения</h1>

<?php if (empty($messages)): ?>
    <p>Сообщений нет</p>
<?php else: ?>
    <div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Текст</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
                <?php $read = (bool) ($message['is_read'] ?? false); ?>
                <tr>
                    <td><?= e((string) ($message['name'] ?? '')) ?></td>
                    <td><?= e((string) ($message['phone'] ?? '')) ?></td>
                    <td><?= e((string) ($message['email'] ?? '')) ?></td>
                    <td><?= e((string) ($message['text'] ?? '')) ?></td>
                    <td>
                        <?php if ($read): ?>
                            <span class="badge badge-new">Прочитано</span>
                        <?php else: ?>
                            <span class="badge badge-in_work">Новое</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <?php if (!$read): ?>
                            <form class="inline-form" method="POST" action="<?= e(url('/admin/messages/' . (int) $message['id'] . '/read')) ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm">Отметить прочитанным</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
