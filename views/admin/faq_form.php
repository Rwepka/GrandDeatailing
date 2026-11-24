<?php
/**
 * Форма вопроса для главной страницы.
 *
 * @var array<string, mixed>|null $faq
 * @var array<int, string> $errors
 * @var array<string, mixed> $old
 */
$faq = $faq ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$isEdit = $faq !== null;
$isActive = isset($old['is_active'])
    ? (string) $old['is_active'] === '1'
    : (int) ($faq['is_active'] ?? 1) === 1;
?>
<div class="card">
    <h1><?= $isEdit ? 'Редактирование вопроса' : 'Новый вопрос' ?></h1>

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

    <form method="POST" action="<?= e(url($isEdit ? '/admin/faqs/' . $faq['id'] : '/admin/faqs')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="field-group">
            <label for="question">Вопрос</label>
            <input type="text" name="question" id="question" maxlength="255"
                   placeholder="Сколько стоит детейлинг?"
                   value="<?= e((string) ($old['question'] ?? $faq['question'] ?? '')) ?>">
        </div>

        <div class="field-group">
            <label for="answer">Ответ</label>
            <textarea name="answer" id="answer" rows="6"
                      placeholder="Ответ, который увидит посетитель"><?= e((string) ($old['answer'] ?? $faq['answer'] ?? '')) ?></textarea>
        </div>

        <div class="field-group">
            <label for="sort_order">Порядок <small>(чем меньше — тем выше)</small></label>
            <input type="number" name="sort_order" id="sort_order" step="1"
                   value="<?= e((string) ($old['sort_order'] ?? $faq['sort_order'] ?? 0)) ?>">
        </div>

        <label class="checkbox-label">
            <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
            <span class="checkbox-ui" aria-hidden="true"></span>
            <span>Показывать на главной</span>
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn-accent">Сохранить</button>
            <a href="<?= e(url('/admin/faqs')) ?>" class="btn btn-outline">Отмена</a>
        </div>
    </form>
</div>
