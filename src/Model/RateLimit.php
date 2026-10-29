<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель лимита отправок форм (таблица rate_limits).
 */
class RateLimit extends Model
{
    protected static string $table = 'rate_limits';

    /**
     * Слишком ли много отправок с данного IP за окно времени.
     */
    public static function tooMany(string $ip, int $limit = 5, int $windowSeconds = 900): bool
    {
        $pdo = Database::connection();

        // Удаляем записи старше окна — они не влияют на лимит.
        $cutoff = date('Y-m-d H:i:s', time() - $windowSeconds);
        $delete = $pdo->prepare('DELETE FROM rate_limits WHERE created_at < ?');
        $delete->execute([$cutoff]);

        // Считаем актуальные отправки этого IP за окно.
        $select = $pdo->prepare(
            'SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND created_at >= ?'
        );
        $select->execute([$ip, $cutoff]);

        return (int) $select->fetchColumn() >= $limit;
    }

    /**
     * Зафиксировать отправку с IP.
     */
    public static function record(string $ip): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO rate_limits (ip) VALUES (?)');
        $stmt->execute([$ip]);
    }
}
