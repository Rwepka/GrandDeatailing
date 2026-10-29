<?php
/**
 * Форма онлайн-записи — квиз из 3 шагов.
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
$skipToStep3 = $skipToStep3 ?? false;

$oldServiceIds = array_map('intval', (array) ($old['service_ids'] ?? []));
$oldCarClass = (string) ($old['car_class'] ?? '');
?>
<section class="section">
    <div class="container">
        <h1>Запись онлайн</h1>
        <p class="lead">Заполните форму за 3 шага — мы перезвоним и подтвердим запись.</p>

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

        <form method="POST" action="<?= e(url('/booking')) ?>" id="booking-form" novalidate<?= !empty($skipToStep3) ? ' data-skip-to="3"' : '' ?>>
            <?= csrf_field() ?>
            <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

            <ol class="booking-steps">
                <li data-step="1" class="active"><span>1</span> Автомобиль</li>
                <li data-step="2"><span>2</span> Услуги</li>
                <li data-step="3"><span>3</span> Контакт</li>
            </ol>

            <!-- Шаг 1: Автомобиль -->
            <div class="booking-step" data-step="1">
                <h2>Автомобиль</h2>
                <div class="field-group">
                    <label>Класс автомобиля</label>
                    <div class="option-grid">
                        <?php foreach ($carClasses as $carClass): ?>
                            <label class="option">
                                <input type="radio" name="car_class" value="<?= e($carClass) ?>" <?= $oldCarClass === $carClass ? 'checked' : '' ?>>
                                <span><?= e($carClass) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="field-group">
                    <label for="brand_model">Марка и модель <small>(необязательно)</small></label>
                    <input type="text" name="brand_model" id="brand_model" placeholder="Например: BMW X5" value="<?= e((string) ($old['brand_model'] ?? '')) ?>">
                </div>
                <div class="step-actions">
                    <span></span>
                    <button type="button" class="btn btn-accent" data-next>Далее</button>
                </div>
            </div>

            <!-- Шаг 2: Услуги -->
            <div class="booking-step" data-step="2">
                <h2>Услуги</h2>
                <div class="cards">
                    <?php foreach ($services as $service): ?>
                        <label class="card service-card">
                            <input type="checkbox" name="service_ids[]" value="<?= e((string) $service['id']) ?>" <?= in_array((int) $service['id'], $oldServiceIds, true) ? 'checked' : '' ?>>
                            <h3><?= e($service['title']) ?></h3>
                            <p class="price"><?= e(price($service['price_from'] === null ? null : (float) $service['price_from'])) ?></p>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-outline" data-prev>Назад</button>
                    <button type="button" class="btn btn-accent" data-next>Далее</button>
                </div>
            </div>

            <!-- Шаг 3: Контакт -->
            <div class="booking-step" data-step="3">
                <h2>Контакт</h2>
                <div class="field-group">
                    <label for="name">Ваше имя</label>
                    <input type="text" name="name" id="name" placeholder="Иван" value="<?= e((string) ($old['name'] ?? '')) ?>">
                </div>
                <div class="field-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" placeholder="+7 900 000-00-00" value="<?= e((string) ($old['phone'] ?? '')) ?>">
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
                    <textarea name="comment" id="comment" rows="4" placeholder="Дополнительные пожелания"><?= e((string) ($old['comment'] ?? '')) ?></textarea>
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-outline" data-prev>Назад</button>
                    <button type="submit" class="btn btn-gold">Отправить заявку</button>
                </div>
            </div>
        </form>
    </div>
</section>
