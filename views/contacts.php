<?php
/**
 * Контакты — адрес, телефон, Яндекс-карта и форма обратной связи.
 *
 * @var array<int, string>   $errors
 * @var array<string, mixed> $old
 * @var string|null          $flash
 */
$errors = $errors ?? [];
$old = $old ?? [];
$flash = $flash ?? null;

$crumbs = [
    ['label' => 'Контакты'],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <h1>Контакты</h1>

        <?php if ($flash !== null): ?>
            <div class="flash" role="status"><?= e($flash) ?></div>
        <?php endif; ?>

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

        <?php
        $phone = (string) setting('phone', '+7 (988) 413-07-77');
        $telHref = (string) preg_replace('/\D+/', '', $phone);
        $workHours = (string) setting('work_hours', 'Ежедневно с 9:00 до 20:00');
        ?>
        <div class="card">
            <div class="field-group">
                <label>Адрес</label>
                <p><?= e(setting('address', 'г. Туапсе, ул. Богдана Хмельницкого, 16')) ?></p>
            </div>
            <div class="field-group">
                <label>Телефон</label>
                <p><a href="tel:<?= e($telHref) ?>"><?= e($phone) ?></a></p>
            </div>
            <?php if ($workHours !== ''): ?>
            <div class="field-group">
                <label>Режим работы</label>
                <p><?= e($workHours) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <?php
        $mapLng = (string) setting('map_lng', '39.087091');
        $mapLat = (string) setting('map_lat', '44.104854');
        $mapSrc = 'https://yandex.ru/map-widget/v1/?ll=' . $mapLng . '%2C' . $mapLat
            . '&z=17&mode=whatshere&whatshere%5Bpoint%5D=' . $mapLng . '%2C' . $mapLat
            . '&whatshere%5Bzoom%5D=17';
        ?>
        <div class="map" id="map-wrap">
            <div class="map-placeholder" id="map-placeholder">
                <p>Карта загрузится по нажатию — сторонние cookies не подключаются заранее.</p>
                <button type="button" class="btn btn-accent" id="map-load" data-src="<?= e($mapSrc) ?>">
                    Показать карту
                </button>
            </div>
        </div>

        <h2>Обратная связь</h2>
        <div class="card">
            <form method="POST" action="<?= e(url('/contacts')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="field-group">
                    <label for="name">Имя</label>
                    <input type="text" name="name" id="name" placeholder="Иван" value="<?= e((string) ($old['name'] ?? '')) ?>"<?= field_aria($errors, 'name', 'err-name') ?>>
                    <?php if ($err = field_error($errors, 'name')): ?><p class="field-error" id="err-name"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" placeholder="+7 900 000-00-00" value="<?= e((string) ($old['phone'] ?? '')) ?>"<?= field_aria($errors, 'phone', 'err-phone') ?>>
                    <?php if ($err = field_error($errors, 'phone')): ?><p class="field-error" id="err-phone"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="email">Email <small>(необязательно)</small></label>
                    <input type="email" name="email" id="email" placeholder="you@example.com" value="<?= e((string) ($old['email'] ?? '')) ?>"<?= field_aria($errors, 'email', 'err-email') ?>>
                    <?php if ($err = field_error($errors, 'email')): ?><p class="field-error" id="err-email"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="text">Сообщение <small>(необязательно)</small></label>
                    <textarea name="text" id="text" rows="4" placeholder="Ваш вопрос или пожелание"><?= e((string) ($old['text'] ?? '')) ?></textarea>
                </div>

                <button type="submit" class="btn btn-accent">Отправить</button>
            </form>
        </div>
    </div>
</section>
