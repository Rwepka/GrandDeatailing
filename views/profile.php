<?php
/**
 * @var array<string, mixed> $user
 */
$user = $user ?? [];

$roleLabels = [
    1 => 'Клиент',
    2 => 'Администратор',
    3 => 'Мастер',
];
$roleId = (int) ($user['role_id'] ?? 0);
$roleLabel = $roleLabels[$roleId] ?? 'Неизвестно';

$crumbs = [
    ['label' => 'Личный кабинет', 'url' => url('/cabinet')],
    ['label' => 'Профиль'],
];
?>
<section class="section">
    <div class="container">
        <?php require BASE_PATH . '/views/partials/breadcrumbs.php'; ?>
        <div class="cabinet-layout">
            <?php $active = 'profile'; require BASE_PATH . '/views/cabinet/_sidebar.php'; ?>
            <div class="cabinet-content">
        <div class="card">
            <p>Имя: <?= e((string) ($user['name'] ?? '—')) ?></p>
            <p>Телефон: <?= e((string) ($user['phone'] ?? '—')) ?></p>
            <p>Email: <?= e((string) ($user['email'] ?: '—')) ?></p>
            <p>Скидка: <?= e((string) ($user['discount'] ?? '0')) ?>%</p>
            <p>Роль: <?= e($roleLabel) ?></p>
        </div>
            </div>
        </div>
    </div>
</section>
