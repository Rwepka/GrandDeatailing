<?php
/**
 * Форма отзыва о выполненной заявке.
 *
 * @var array<string, mixed> $booking
 * @var array<int, string>   $errors
 * @var array<string, mixed> $old
 * @var string|null          $flash
 */
$booking = $booking ?? [];
$errors = $errors ?? [];
$old = $old ?? [];
$flash = $flash ?? null;

$oldRating = (string) ($old['rating'] ?? '');
?>
<section class="section">
    <div class="container">
        <h1>Оставить отзыв</h1>

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

        <div class="card">
            <form method="POST" action="<?= e(url('/reviews')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <input type="hidden" name="booking_id" value="<?= e((int) ($booking['id'] ?? 0)) ?>">

                <div class="field-group">
                    <label>Оценка</label>
                    <div class="option-grid">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="option">
                                <input type="radio" name="rating" value="<?= e((string) $i) ?>" <?= $oldRating === (string) $i ? 'checked' : '' ?>>
                                <span><?= e(str_repeat('★', $i)) ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="field-group">
                    <label for="text">Отзыв <small>(необязательно)</small></label>
                    <textarea name="text" id="text" rows="4" placeholder="Поделитесь впечатлениями"><?= e((string) ($old['text'] ?? '')) ?></textarea>
                </div>

                <button type="submit" class="btn btn-gold">Отправить</button>
            </form>
        </div>
    </div>
</section>
