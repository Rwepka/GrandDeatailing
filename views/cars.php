<?php
/**
 * @var array<int, array<string, mixed>> $cars
 * @var array<int, string>               $errors
 * @var array<string, mixed>             $old
 */
$cars = $cars ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
?>
<section class="section">
    <div class="container">
        <?php $active = 'cars'; require BASE_PATH . '/views/cabinet/_tabs.php'; ?>

        <?php if (!empty($errors)): ?>
            <div class="form-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (empty($cars)): ?>
            <p>Автомобилей пока нет.</p>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($cars as $car): ?>
                    <div class="card">
                        <h3><?= e((string) $car['brand']) ?> <?= e((string) $car['model']) ?></h3>
                        <p>Год: <?= e((string) ($car['year'] ?: '—')) ?></p>
                        <p>VIN: <?= e((string) ($car['vin'] ?: '—')) ?></p>
                        <p>Гос. номер: <?= e((string) ($car['plate'] ?: '—')) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Добавить автомобиль</h2>
        <form method="POST" action="<?= e(url('/cabinet/cars')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="field-group">
                <label for="brand">Марка</label>
                <input type="text" name="brand" id="brand" value="<?= e((string) ($old['brand'] ?? '')) ?>" required>
            </div>
            <div class="field-group">
                <label for="model">Модель</label>
                <input type="text" name="model" id="model" value="<?= e((string) ($old['model'] ?? '')) ?>" required>
            </div>
            <div class="field-group">
                <label for="year">Год выпуска <small>(необязательно)</small></label>
                <input type="text" name="year" id="year" value="<?= e((string) ($old['year'] ?? '')) ?>" placeholder="Например: 2020">
            </div>
            <div class="field-group">
                <label for="vin">VIN <small>(необязательно)</small></label>
                <input type="text" name="vin" id="vin" value="<?= e((string) ($old['vin'] ?? '')) ?>">
            </div>
            <div class="field-group">
                <label for="plate">Гос. номер <small>(необязательно)</small></label>
                <input type="text" name="plate" id="plate" value="<?= e((string) ($old['plate'] ?? '')) ?>">
            </div>
            <button type="submit" class="btn btn-gold">Добавить</button>
        </form>
    </div>
</section>
