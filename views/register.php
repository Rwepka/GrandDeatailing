<?php
/**
 * Форма регистрации.
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
            <h1>Регистрация</h1>
            <p class="lead">Создайте аккаунт, чтобы управлять записями и получать персональные скидки.</p>

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

            <form method="POST" action="<?= e(url('/register')) ?>" novalidate>
                <?= csrf_field() ?>

                <div class="field-group">
                    <label for="name">Имя</label>
                    <input type="text" name="name" id="name" placeholder="Иван" value="<?= e((string) ($old['name'] ?? '')) ?>">
                </div>

                <div class="field-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" placeholder="+7 900 000-00-00" value="<?= e((string) ($old['phone'] ?? '')) ?>">
                </div>

                <div class="field-group">
                    <label for="email">Email <small>(необязательно)</small></label>
                    <input type="email" name="email" id="email" placeholder="you@example.com" value="<?= e((string) ($old['email'] ?? '')) ?>">
                </div>

                <div class="field-group">
                    <label for="password">Пароль <small>(минимум 8 символов)</small></label>
                    <input type="password" name="password" id="password" placeholder="Придумайте пароль">
                </div>

                <div class="field-group">
                    <label for="password_confirmation">Повторите пароль</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Повторите пароль">
                </div>

                <button type="submit" class="btn btn-gold">Зарегистрироваться</button>
            </form>

            <p>Уже есть аккаунт? <a href="<?= e(url('/login')) ?>">Войти</a></p>
        </div>
    </div>
</section>
