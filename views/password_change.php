<?php
/**
 * Принудительная смена временного пароля при первом входе.
 *
 * @var array<string, mixed> $user
 * @var array<int, string>   $errors
 */
$user = $user ?? [];
$errors = $errors ?? [];
?>
<section class="section">
    <div class="container">
        <h1>Смена временного пароля</h1>
        <p class="lead">Вы вошли с временным паролем. Задайте новый пароль, чтобы продолжить.</p>

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
            <form method="POST" action="<?= e(url('/cabinet/security/password-change')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field-group">
                    <label for="password">Новый пароль <small>(минимум 8 символов)</small></label>
                    <input type="password" name="password" id="password">
                </div>
                <div class="field-group">
                    <label for="password_confirmation">Повторите новый пароль</label>
                    <input type="password" name="password_confirmation" id="password_confirmation">
                </div>
                <button type="submit" class="btn btn-accent">Сохранить и продолжить</button>
            </form>
        </div>
    </div>
</section>
