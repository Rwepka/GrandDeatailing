<?php
/**
 * Форма создания мастера (админ-панель).
 *
 * @var array<int, string>   $errors
 * @var array<string, mixed> $old
 */
$errors = $errors ?? [];
$old = $old ?? [];
?>
<div class="card">
    <h1>Новый мастер</h1>

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

    <form method="POST" action="<?= e(url('/admin/masters')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="field-group">
            <label for="name">ФИО</label>
            <input type="text" name="name" id="name" placeholder="Иванов Иван Иванович" value="<?= e((string) ($old['name'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="master@example.com" value="<?= e((string) ($old['email'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="phone">Телефон</label>
            <input type="tel" name="phone" id="phone" placeholder="+7 900 000-00-00" value="<?= e((string) ($old['phone'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="password">Пароль <small>(минимум 8 символов)</small></label>
            <input type="password" name="password" id="password" minlength="8">
        </div>

        <button type="submit" class="btn btn-gold">Создать</button>
        <a href="<?= e(url('/admin/masters')) ?>" class="btn btn-outline">Отмена</a>
    </form>
</div>
