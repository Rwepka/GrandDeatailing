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
$prices = $prices ?? [];
$bodyTypes = car_classes();

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

        <div class="field-group">
            <label for="short_description">Краткое описание для карточки <small>(до 255 символов)</small></label>
            <input type="text" name="short_description" id="short_description" maxlength="255"
                   placeholder="Бережная ручная мойка кузова и колёс"
                   value="<?= e((string) ($old['short_description'] ?? $service['short_description'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="description">Полное описание для страницы услуги</label>
            <textarea name="description" id="description" rows="6"
                      placeholder="Расскажите, что делает мастер и какой результат получает клиент"><?= e((string) ($old['description'] ?? $service['description'] ?? '')) ?></textarea>
        </div>

        <div class="field-group">
            <label for="includes">Что входит в услугу <small>(каждый пункт с новой строки)</small></label>
            <textarea name="includes" id="includes" rows="5"
                      placeholder="Мойка и обезжиривание кузова&#10;Оценка толщины ЛКП"><?= e((string) ($old['includes'] ?? $service['includes'] ?? '')) ?></textarea>
        </div>

        <div class="field-group">
            <label for="process">Как проходит работа <small>(каждый шаг с новой строки)</small></label>
            <textarea name="process" id="process" rows="5"
                      placeholder="Осматриваем автомобиль&#10;Согласовываем объём работ"><?= e((string) ($old['process'] ?? $service['process'] ?? '')) ?></textarea>
        </div>

        <div class="field-group">
            <label for="price_from">Цена от, ₽ <small>(если цены по кузовам не заданы)</small></label>
            <input type="number" name="price_from" id="price_from" step="0.01" min="0" placeholder="8000"
                   value="<?= e((string) ($old['price_from'] ?? $service['price_from'] ?? '')) ?>">
        </div>

        <fieldset class="fieldset">
            <legend>Цены по классам кузова</legend>
            <?php foreach ($bodyTypes as $i => $bodyType): ?>
                <div class="field-group">
                    <label for="price-<?= (int) $i ?>"><?= e($bodyType) ?></label>
                    <input type="number" name="prices[<?= e($bodyType) ?>]" id="price-<?= (int) $i ?>" step="0.01" min="0" placeholder="8000"
                           value="<?= e((string) ($old['prices'][$bodyType] ?? $prices[$bodyType] ?? '')) ?>">
                </div>
            <?php endforeach; ?>
        </fieldset>

        <div class="field-group">
            <label for="duration_min">Длительность, мин</label>
            <input type="number" name="duration_min" id="duration_min" step="1" min="0" placeholder="120" value="<?= e((string) ($old['duration_min'] ?? $service['duration_min'] ?? '')) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-accent">Сохранить</button>
            <a href="<?= e(url('/admin/services')) ?>" class="btn btn-outline">Отмена</a>
        </div>
    </form>
</div>
