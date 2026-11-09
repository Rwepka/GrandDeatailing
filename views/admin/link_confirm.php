<?php
/**
 * Подтверждение привязки заявки к существующему аккаунту (AUTH-01).
 *
 * @var array<string, mixed> $booking
 * @var array<string, mixed> $account
 * @var string $phone
 */
$booking = $booking ?? [];
$account = $account ?? [];
$phone = $phone ?? '';

$bookingId = (int) ($booking['id'] ?? 0);
$verified = !empty($account['verified_at']);
?>
<h1>Подтверждение привязки</h1>

<div class="card">
    <p>
        Заявка №<?= e((string) $bookingId) ?>:
        <strong><?= e((string) ($booking['client_name'] ?? '')) ?></strong>,
        <?= e($phone) ?>
    </p>
    <p>
        Уже есть аккаунт с этим телефоном:
        <strong><?= e((string) ($account['name'] ?? '')) ?></strong>
        (<?= e((string) ($account['phone'] ?? '')) ?>),
        создан <?= e((string) ($account['created_at'] ?? '')) ?>.
        Статус: <?= $verified ? 'подтверждён' : 'не подтверждён' ?>.
    </p>
    <p>
        Автоматическая привязка отключена. Убедитесь, что телефон принадлежит этому
        клиенту, прежде чем давать аккаунту доступ к заявке.
    </p>
    <form method="POST" action="<?= e(url('/admin/bookings/' . $bookingId . '/link-existing')) ?>" class="inline-form">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-accent">Да, это тот же клиент — привязать</button>
    </form>
    <a href="<?= e(url('/admin/bookings')) ?>" class="btn btn-outline">Отмена</a>
</div>
