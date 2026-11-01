<?php
/**
 * Список услуг в админ-панели.
 *
 * @var array<int, array<string, mixed>> $services
 * @var array<int, string> $categoryTitles
 */
$services = $services ?? [];
$categoryTitles = $categoryTitles ?? [];
?>
<h1>Услуги</h1>
<a href="<?= e(url('/admin/services/create')) ?>" class="btn btn-accent" style="margin-bottom: 16px;">Добавить услугу</a>

<div class="table-responsive">
<table class="admin-table">
    <thead>
        <tr>
            <th>Название</th>
            <th>Категория</th>
            <th>Цена</th>
            <th>Длительность (мин)</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($services as $service): ?>
            <?php
            $categoryId = (int) ($service['category_id'] ?? 0);
            $categoryTitle = $categoryTitles[$categoryId] ?? '—';
            $isActive = (int) ($service['is_active'] ?? 0) === 1;
            $priceFrom = $service['price_from'] ?? null;
            $durationMin = $service['duration_min'] ?? null;
            ?>
            <tr>
                <td><?= e((string) ($service['title'] ?? '')) ?></td>
                <td><?= e((string) $categoryTitle) ?></td>
                <td>
                    <?php if ($priceFrom === null || $priceFrom === ''): ?>
                        —
                    <?php else: ?>
                        <?= e(price((float) $priceFrom)) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($durationMin === null || $durationMin === ''): ?>
                        —
                    <?php else: ?>
                        <?= e((string) $durationMin) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($isActive): ?>
                        <span class="badge badge-done">Активна</span>
                    <?php else: ?>
                        <span class="badge badge-cancelled">Скрыта</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/services/' . $service['id'] . '/edit')) ?>">Изменить</a>
                    <form class="inline-form" method="POST" action="<?= e(url('/admin/services/' . $service['id'] . '/toggle')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline btn-sm">
                            <?= $isActive ? 'Скрыть' : 'Показать' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if ($services === []): ?>
            <tr>
                <td colspan="6">Услуг пока нет.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
