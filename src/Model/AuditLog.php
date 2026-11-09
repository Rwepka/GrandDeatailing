<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Журнал действий (таблица audit_log).
 */
class AuditLog extends Model
{
    protected static string $table = 'audit_log';

    /**
     * Записать действие в журнал.
     *
     * @param string      $action     код действия, например booking.status
     * @param string|null $entityType тип объекта (booking, client, service, ...)
     * @param int|null    $entityId   id объекта
     * @param string|null $meta       краткие детали
     */
    public static function record(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $meta = null
    ): void {
        $user = auth();
        $actorId = $user !== null ? (int) $user['id'] : null;
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        $stmt = Database::connection()->prepare(
            'INSERT INTO audit_log (actor_id, action, entity_type, entity_id, meta, ip)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$actorId, $action, $entityType, $entityId, $meta, $ip]);
    }

    /**
     * Последние записи журнала с именем автора.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function latest(int $limit = 200): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, u.name AS actor_name
             FROM audit_log a
             LEFT JOIN users u ON u.id = a.actor_id
             ORDER BY a.id DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
