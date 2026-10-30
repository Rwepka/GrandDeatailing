<?php
/** @var array<int, array{label: string, url?: string}> $crumbs */
$crumbs = $crumbs ?? [];
?>
<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <a href="<?= e(url('/')) ?>">Главная</a>
    <?php foreach ($crumbs as $crumb): ?>
        <span class="sep">→</span>
        <?php if (!empty($crumb['url'])): ?>
            <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
        <?php else: ?>
            <span><?= e($crumb['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
