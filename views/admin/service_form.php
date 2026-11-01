<?php
/**
 * Форма создания/редактирования услуги (админ-панель).
 *
 * @var array<int, array<string, mixed>> $categories
 * @var array<string, mixed>|null $service
 * @var array<int, string> $errors
 * @var array<string, mixed> $old
 */
$categories = $categories ?? [];
$service = $service ?? null;
$errors = $errors ?? [];
$old = $old ?? [];

$isEdit = $service !== null;
$selectedCategoryId = $old['category_id'] ?? $service['category_id'] ?? '';
?>
<div class="card">
    <h1><?= $isEdit ? 'Редактирование услуги' : 'Новая услуга' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="form-errors" role="alert">
            <strong>Пожалуйста, исправьте ошибки:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url($isEdit ? '/admin/services/' . $service['id'] : '/admin/services')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="field-group">
            <label for="title">Название</label>
            <input type="text" name="title" id="title" placeholder="Детейлинг кузова" value="<?= e((string) ($old['title'] ?? $service['title'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="slug">Slug <small>(латиница, цифры и дефис)</small></label>
            <input type="text" name="slug" id="slug" placeholder="deteyling-kuzova" value="<?= e((string) ($old['slug'] ?? $service['slug'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="price_from">Цена, ₽ <small>(необязательно)</small></label>
            <input type="number" name="price_from" id="price_from" step="0.01" min="0" placeholder="8000" value="<?= e((string) ($old['price_from'] ?? $service['price_from'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="duration_min">Длительность, мин <small>(необязательно)</small></label>
            <input type="number" name="duration_min" id="duration_min" step="1" min="0" placeholder="120" value="<?= e((string) ($old['duration_min'] ?? $service['duration_min'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="category_id">Категория</label>
            <select name="category_id" id="category_id">
                <option value="">— Выберите категорию —</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e((string) $category['id']) ?>" <?= (int) $selectedCategoryId === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e((string) $category['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-accent">Сохранить</button>
        <a href="<?= e(url('/admin/services')) ?>" class="btn btn-outline">Отмена</a>
    </form>
</div>
