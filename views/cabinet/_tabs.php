<?php
/**
 * Навигация-вкладки личного кабинета.
 * Требует переменную $active: 'cabinet' | 'cars' | 'profile' | 'security'.
 *
 * @var string $active
 */
$active = $active ?? 'cabinet';
$tabs = [
    'cabinet'  => ['Мои записи', '/cabinet'],
    'cars'     => ['Мои автомобили', '/cabinet/cars'],
    'profile'  => ['Профиль', '/cabinet/profile'],
    'security' => ['Безопасность', '/cabinet/security'],
];
?>
<div class="cabinet-tabs">
    <?php foreach ($tabs as $key => [$label, $href]): ?>
        <a href="<?= e(url($href)) ?>" class="btn <?= $key === $active ? 'btn-accent' : 'btn-outline' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>
