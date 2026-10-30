<?php
/**
 * Форма настроек сайта в админ-панели.
 *
 * @var array<string, array{label: string, default: string}> $fields
 * @var array<string, string|null> $values
 * @var string|null $flash
 */
$fields = $fields ?? [];
$values = $values ?? [];
$flash = $flash ?? null;
?>
<h1>Настройки</h1>

<?php if ($flash !== null): ?>
    <div class="flash" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<form method="POST" action="<?= e(url('/admin/settings')) ?>" id="settings-form" novalidate>
    <?= csrf_field() ?>

    <?php foreach ($fields as $key => $meta): ?>
        <div class="field-group">
            <label for="<?= e($key) ?>"><?= e((string) ($meta['label'] ?? $key)) ?></label>
            <input type="text" name="<?= e($key) ?>" id="<?= e($key) ?>"
                   value="<?= e((string) ($values[$key] ?? '')) ?>"
                   data-label="<?= e((string) ($meta['label'] ?? $key)) ?>"
                   data-old="<?= e((string) ($values[$key] ?? '')) ?>">
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-gold">Сохранить</button>
</form>

<div class="modal-overlay" id="settings-modal" hidden>
    <div class="modal">
        <h2>Подтвердите изменения</h2>
        <p class="modal-warning">Внимание: изменения применяются сразу и необратимы.</p>
        <ul class="modal-changes" id="settings-changes"></ul>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="settings-cancel">Отмена</button>
            <button type="button" class="btn btn-gold" id="settings-confirm">Подтвердить</button>
        </div>
    </div>
</div>
