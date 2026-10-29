<?php
/**
 * Форма входа.
 *
 * @var array<int, string> $errors
 * @var array<string, mixed> $old
 */
$errors = $errors ?? [];
$old = $old ?? [];
?>
<section class="section">
    <div class="container">
        <div class="card">
            <h1>Вход</h1>
            <p class="lead">Войдите в личный кабинет, чтобы управлять записями.</p>

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

            <form method="POST" action="<?= e(url('/login')) ?>" novalidate>
                <?= csrf_field() ?>

                <div class="field-group">
                    <label for="login">Телефон или email</label>
                    <input type="text" name="login" id="login" placeholder="+7 900 000-00-00 или you@example.com" value="<?= e((string) ($old['login'] ?? '')) ?>">
                </div>

                <div class="field-group">
                    <label for="password">Пароль</label>
                    <input type="password" name="password" id="password" placeholder="Ваш пароль">
                </div>

                <div class="field-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        Запомнить меня
                    </label>
                </div>

                <button type="submit" class="btn btn-gold">Войти</button>
            </form>

            <p>Нет аккаунта? <a href="<?= e(url('/register')) ?>">Зарегистрироваться</a></p>
        </div>
    </div>
</section>
