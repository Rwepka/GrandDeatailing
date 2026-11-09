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
$servicePrices = $servicePrices ?? [];

$oldServiceIds = array_map('intval', (array) ($old['service_ids'] ?? []));
$oldCarClass = (string) ($old['car_class'] ?? '');

// Начальный шаг квиза: при ошибках открываем шаг с первым проблемным полем.
$initialStep = 1;
if ($skipToStep3) {
    $initialStep = 3;
} elseif (!empty($errors)) {
    if (isset($errors['car_class'])) {
        $initialStep = 1;
    } elseif (isset($errors['service_ids'])) {
        $initialStep = 2;
    } else {
        $initialStep = 3;
    }
}

$crumbs = [
    ['label' => 'Запись онлайн'],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
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

        <form method="POST" action="<?= e(url('/booking')) ?>" id="booking-form" novalidate<?= $initialStep > 1 ? ' data-skip-to="' . (int) $initialStep . '"' : '' ?>>
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
                <fieldset class="field-group"<?= field_aria($errors, 'car_class', 'err-car_class') ?>>
                    <legend>Класс автомобиля</legend>
                    <div class="option-grid">
                        <?php foreach ($carClasses as $carClass): ?>
                            <label class="option">
                                <input type="radio" name="car_class" value="<?= e($carClass) ?>" <?= $oldCarClass === $carClass ? 'checked' : '' ?>>
                                <span><?= e($carClass) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <?php if ($err = field_error($errors, 'car_class')): ?><p class="field-error" id="err-car_class"><?= e($err) ?></p><?php endif; ?>
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
                <fieldset class="field-group"<?= field_aria($errors, 'service_ids', 'err-service_ids') ?>>
                    <legend class="visually-hidden">Услуги</legend>
                    <div class="cards">
                    <?php foreach ($services as $service): ?>
                        <div class="card service-card"
                             data-price="<?= e((string) ($service['price_from'] ?? '')) ?>"
                             data-prices="<?= e(json_encode($servicePrices[(int) $service['id']] ?? [], JSON_UNESCAPED_UNICODE)) ?>">
                            <label class="service-card-label">
                                <input type="checkbox" name="service_ids[]" value="<?= e((string) $service['id']) ?>" <?= in_array((int) $service['id'], $oldServiceIds, true) ? 'checked' : '' ?>>
                                <span class="service-card-title"><?= e($service['title']) ?></span>
                                <span class="price"><?= e(price($service['price_from'] === null ? null : (float) $service['price_from'])) ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                </fieldset>
                <?php if ($err = field_error($errors, 'service_ids')): ?><p class="field-error" id="err-service_ids"><?= e($err) ?></p><?php endif; ?>
                <div class="quiz-total">Итого: <strong id="quiz-total">от 0 ₽</strong></div>
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
                    <input type="text" name="name" id="name" placeholder="Иван" value="<?= e((string) ($old['name'] ?? '')) ?>"<?= field_aria($errors, 'name', 'err-name') ?>>
                    <?php if ($err = field_error($errors, 'name')): ?><p class="field-error" id="err-name"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" placeholder="+7 900 000-00-00" value="<?= e((string) ($old['phone'] ?? '')) ?>"<?= field_aria($errors, 'phone', 'err-phone') ?>>
                    <?php if ($err = field_error($errors, 'phone')): ?><p class="field-error" id="err-phone"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="pref_date">Желаемая дата <small>(необязательно)</small></label>
                    <input type="date" name="pref_date" id="pref_date" value="<?= e((string) ($old['pref_date'] ?? '')) ?>"<?= field_aria($errors, 'pref_date', 'err-pref_date') ?>>
                    <?php if ($err = field_error($errors, 'pref_date')): ?><p class="field-error" id="err-pref_date"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="pref_time">Желаемое время <small>(необязательно)</small></label>
                    <input type="time" name="pref_time" id="pref_time" value="<?= e((string) ($old['pref_time'] ?? '')) ?>"<?= field_aria($errors, 'pref_time', 'err-pref_time') ?>>
                    <?php if ($err = field_error($errors, 'pref_time')): ?><p class="field-error" id="err-pref_time"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="comment">Комментарий <small>(необязательно)</small></label>
                    <textarea name="comment" id="comment" rows="4" placeholder="Дополнительные пожелания"><?= e((string) ($old['comment'] ?? '')) ?></textarea>
                </div>
                <div class="step-actions">
                    <button type="button" class="btn btn-outline" data-prev>Назад</button>
                    <button type="submit" class="btn btn-accent">Отправить заявку</button>
                </div>
            </div>
        </form>
    </div>
</section>
