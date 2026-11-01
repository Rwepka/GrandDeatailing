<?php
/**
 * Публичная страница новостей.
 *
 * @var array<int, array<string, mixed>> $posts
 */
$posts = $posts ?? [];
?>
<section class="section">
    <div class="container">
        <h1>Новости</h1>

        <?php if (empty($posts)): ?>
            <p>Новостей пока нет</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="news-item">
                    <h2><?= e((string) ($post['title'] ?? '')) ?></h2>
                    <div class="news-card card">
                        <div class="news-content">
                            <?= nl2br(e((string) ($post['content'] ?? ''))) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
