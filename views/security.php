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

$crumbs = [
    ['label' => 'Личный кабинет', 'url' => url('/cabinet')],
    ['label' => 'Безопасность'],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <div class="cabinet-layout">
            <?php $active = 'security'; require BASE_PATH . '/views/cabinet/_sidebar.php'; ?>
            <div class="cabinet-content">
        <h1>Безопасность</h1>
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

        <div class="card card-plain">
            <h2>Телефон</h2>
            <form method="POST" action="<?= e(url('/cabinet/security/phone')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" value="<?= e((string) ($old['phone'] ?? $user['phone'] ?? '')) ?>" placeholder="+7 900 000-00-00"<?= field_aria($errors, 'phone', 'err-phone') ?>>
                    <?php if ($err = field_error($errors, 'phone')): ?><p class="field-error" id="err-phone"><?= e($err) ?></p><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-accent">Сохранить</button>
            </form>
        </div>

        <div class="card card-plain">
            <h2>Email</h2>
            <form method="POST" action="<?= e(url('/cabinet/security/email')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="email">Email <small>(оставьте пустым, чтобы убрать)</small></label>
                    <input type="email" name="email" id="email" value="<?= e((string) ($old['email'] ?? $user['email'] ?? '')) ?>" placeholder="you@example.com"<?= field_aria($errors, 'email', 'err-email') ?>>
                    <?php if ($err = field_error($errors, 'email')): ?><p class="field-error" id="err-email"><?= e($err) ?></p><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-accent">Сохранить</button>
            </form>
        </div>

        <div class="card card-plain">
            <h2>Пароль</h2>
            <form method="POST" action="<?= e(url('/cabinet/security/password')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="current_password">Текущий пароль</label>
                    <input type="password" name="current_password" id="current_password" autocomplete="current-password"<?= field_aria($errors, 'current_password', 'err-current_password') ?>>
                    <?php if ($err = field_error($errors, 'current_password')): ?><p class="field-error" id="err-current_password"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="password">Новый пароль <small>(минимум 8 символов)</small></label>
                    <input type="password" name="password" id="password" autocomplete="new-password"<?= field_aria($errors, 'password', 'err-password') ?>>
                    <?php if ($err = field_error($errors, 'password')): ?><p class="field-error" id="err-password"><?= e($err) ?></p><?php endif; ?>
                </div>
                <div class="field-group">
                    <label for="password_confirmation">Повторите новый пароль</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"<?= field_aria($errors, 'password_confirmation', 'err-password_confirmation') ?>>
                    <?php if ($err = field_error($errors, 'password_confirmation')): ?><p class="field-error" id="err-password_confirmation"><?= e($err) ?></p><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-accent">Сохранить</button>
            </form>
        </div>
            </div>
        </div>
    </div>
</section>
