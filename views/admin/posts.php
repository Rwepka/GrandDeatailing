<?php
/**
 * Управление новостями в админ-панели.
 *
 * @var array<int, array<string, mixed>> $posts
 * @var string|null                     $flash
 * @var array<int, string>              $errors
 * @var array<string, string>           $old
 */
$posts = $posts ?? [];
$flash = $flash ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
?>
<h1>Новости</h1>

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

<div class="card card-plain">
    <h2>Новая новость</h2>
    <form method="POST" action="<?= e(url('/admin/posts')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="field-group">
            <label for="post-title">Заголовок</label>
            <input type="text" name="title" id="post-title" maxlength="160"
                   value="<?= e((string) ($old['title'] ?? '')) ?>">
        </div>
        <div class="field-group">
            <label for="post-content">Текст новости</label>
            <textarea name="content" id="post-content" rows="5"><?= e((string) ($old['content'] ?? '')) ?></textarea>
        </div>
        <button type="submit" class="btn btn-accent">Опубликовать</button>
    </form>
</div>

<h2 style="margin-top: 32px;">Список новостей</h2>

<?php if (empty($posts)): ?>
    <p>Новостей пока нет.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <?php
            $postId = (int) ($post['id'] ?? 0);
            $published = (int) ($post['is_published'] ?? 0) === 1;
        ?>
        <div class="post-item">
            <div class="post-item-title">
                <strong><?= e((string) ($post['title'] ?? '')) ?></strong>
                <?php if (!$published): ?>
                    <span class="badge badge-cancelled">скрыта</span>
                <?php endif; ?>
            </div>
            <div class="post-item-actions">
                <form class="inline-form" method="POST" action="<?= e(url('/admin/posts/' . $postId . '/toggle')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm">
                        <?= $published ? 'Скрыть' : 'Показать' ?>
                    </button>
                </form>
                <form class="inline-form" method="POST" action="<?= e(url('/admin/posts/' . $postId . '/delete')) ?>"
                      onsubmit="return confirm('Удалить новость?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
