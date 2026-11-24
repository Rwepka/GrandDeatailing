<?php
/**
 * Список вопросов для главной страницы.
 *
 * @var array<int, array<string, mixed>> $faqs
 * @var string|null $flash
 */
$faqs = $faqs ?? [];
$flash = $flash ?? null;
?>
<div class="admin-page-head">
    <h1>Частые вопросы</h1>
    <a href="<?= e(url('/admin/faqs/create')) ?>" class="btn btn-accent">Добавить вопрос</a>
</div>

<?php if ($flash !== null): ?>
    <div class="flash" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<p class="admin-hint">Блок выводится только на главной странице. Порядок — по возрастанию значения «Порядок».</p>

<div class="table-responsive">
<table class="admin-table faq-table">
    <caption class="visually-hidden">Частые вопросы</caption>
    <thead>
        <tr>
            <th scope="col">Вопрос</th>
            <th scope="col">Порядок</th>
            <th scope="col">Статус</th>
            <th scope="col">Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($faqs as $faq): ?>
            <?php $isActive = (int) ($faq['is_active'] ?? 0) === 1; ?>
            <tr>
                <td><?= e((string) ($faq['question'] ?? '')) ?></td>
                <td><?= e((string) ($faq['sort_order'] ?? 0)) ?></td>
                <td>
                    <?php if ($isActive): ?>
                        <span class="badge badge-done">Активен</span>
                    <?php else: ?>
                        <span class="badge badge-cancelled">Скрыт</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/faqs/' . $faq['id'] . '/edit')) ?>">Изменить</a>
                    <form class="inline-form" method="POST" action="<?= e(url('/admin/faqs/' . $faq['id'] . '/toggle')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline btn-sm"><?= $isActive ? 'Скрыть' : 'Показать' ?></button>
                    </form>
                    <form class="inline-form" method="POST" action="<?= e(url('/admin/faqs/' . $faq['id'] . '/delete')) ?>" onsubmit="return confirm('Удалить вопрос?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if ($faqs === []): ?>
            <tr>
                <td colspan="4">Вопросов пока нет. Добавьте первый — он появится на главной.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
