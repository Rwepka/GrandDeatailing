<?php
/**
 * Форма создания заявки в админ-панели.
 *
 * @var array<int, array<string, mixed>> $services
 * @var array<int, string>               $carClasses
 * @var array<int, string>               $errors
 * @var array<string, mixed>             $old
 */
$services = $services ?? [];
$carClasses = $carClasses ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
?>
<div class="card card-plain">
    <h1>Новая заявка</h1>

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

    <form method="POST" action="<?= e(url('/admin/bookings')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="field-group">
            <label for="name">Имя клиента</label>
            <input type="text" name="name" id="name" placeholder="Иван" value="<?= e((string) ($old['name'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="phone">Телефон</label>
            <input type="tel" name="phone" id="phone" placeholder="+7 900 000-00-00" value="<?= e((string) ($old['phone'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label>Класс автомобиля</label>
            <div class="option-grid">
                <?php foreach ($carClasses as $carClass): ?>
                    <label class="option">
                        <input type="radio" name="car_class" value="<?= e($carClass) ?>" <?= ($old['car_class'] ?? '') === $carClass ? 'checked' : '' ?>>
                        <span><?= e($carClass) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="field-group">
            <label for="brand_model">Марка и модель <small>(необязательно)</small></label>
            <input type="text" name="brand_model" id="brand_model" placeholder="BMW X5" value="<?= e((string) ($old['brand_model'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label>Услуги</label>
            <div class="cards">
                <?php foreach ($services as $service): ?>
                    <label class="card service-card">
                        <input type="checkbox" name="service_ids[]" value="<?= e((string) $service['id']) ?>" <?= in_array((int) $service['id'], $old['service_ids'] ?? [], true) ? 'checked' : '' ?>>
                        <h3><?= e($service['title']) ?></h3>
                        <p class="price"><?= e(price($service['price_from'] === null ? null : (float) $service['price_from'])) ?></p>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="field-group">
            <label for="pref_date">Желаемая дата <small>(необязательно)</small></label>
            <input type="date" name="pref_date" id="pref_date" value="<?= e((string) ($old['pref_date'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="pref_time">Желаемое время <small>(необязательно)</small></label>
            <input type="time" name="pref_time" id="pref_time" value="<?= e((string) ($old['pref_time'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="comment">Комментарий <small>(необязательно)</small></label>
            <textarea name="comment" id="comment" rows="3" placeholder="Пожелания клиента"><?= e((string) ($old['comment'] ?? '')) ?></textarea>
        </div>

        <button type="submit" class="btn btn-accent">Создать</button>
        <a href="<?= e(url('/admin/bookings')) ?>" class="btn btn-outline">Отмена</a>
    </form>
</div>
