<?php
/**
 * Удаление неподтверждённых клиентов старше 30 дней.
 *
 * Запуск (например, по cron на REG.RU — раз в сутки):
 *   php /path/to/site/scripts/cleanup_unverified_clients.php
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/bootstrap.php';

$removed = \App\Model\User::deleteUnverifiedOlderThan(30);

echo date('Y-m-d H:i:s') . " cleanup_unverified_clients: deleted {$removed}\n";
