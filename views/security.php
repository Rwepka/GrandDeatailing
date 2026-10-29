<?php
/**
 * @var array<string, mixed> $user
 * @var array<int, string>   $errors
 * @var array<string, mixed> $old
 * @var string|null          $flash
 */
$user = $user ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$flash = $flash ?? null;
?>
<section class="section">
    <div class="container">
        <?php $active = 'security'; require BASE_PATH . '/views/cabinet/_tabs.php'; ?>

        <?php if (!empty($flash)): ?>
            <div class="flash" role="status"><?= e((string) $flash) ?></div>
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

        <div class="card">
            <h2>Телефон</h2>
            <form method="POST" action="<?= e(url('/cabinet/security/phone')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" value="<?= e((string) ($old['phone'] ?? $user['phone'] ?? '')) ?>" placeholder="+7 900 000-00-00">
                </div>
                <button type="submit" class="btn btn-gold">Сохранить</button>
            </form>
        </div>

        <div class="card">
            <h2>Email</h2>
            <form method="POST" action="<?= e(url('/cabinet/security/email')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="email">Email <small>(оставьте пустым, чтобы убрать)</small></label>
                    <input type="email" name="email" id="email" value="<?= e((string) ($old['email'] ?? $user['email'] ?? '')) ?>" placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-gold">Сохранить</button>
            </form>
        </div>

        <div class="card">
            <h2>Пароль</h2>
            <form method="POST" action="<?= e(url('/cabinet/security/password')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="current_password">Текущий пароль</label>
                    <input type="password" name="current_password" id="current_password">
                </div>
                <div class="field-group">
                    <label for="password">Новый пароль <small>(минимум 8 символов)</small></label>
                    <input type="password" name="password" id="password">
                </div>
                <div class="field-group">
                    <label for="password_confirmation">Повторите новый пароль</label>
                    <input type="password" name="password_confirmation" id="password_confirmation">
                </div>
                <button type="submit" class="btn btn-gold">Сохранить</button>
            </form>
        </div>
    </div>
</section>
