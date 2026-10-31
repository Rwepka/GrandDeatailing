<?php
/** @var string $active */
$active = $active ?? 'cabinet';
$items = [
    'cabinet'  => ['Мои записи', '/cabinet'],
    'cars'     => ['Мои автомобили', '/cabinet/cars'],
    'profile'  => ['Профиль и скидки', '/cabinet/profile'],
    'security' => ['Безопасность', '/cabinet/security'],
];
?>
<aside class="cabinet-sidebar">
    <nav class="cabinet-menu">
        <?php foreach ($items as $key => [$label, $href]): ?>
            <a href="<?= e(url($href)) ?>" class="<?= $key === $active ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <form method="POST" action="<?= e(url('/logout')) ?>" class="cabinet-logout">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline">Выйти</button>
    </form>
</aside>
