<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель сообщения обратной связи (таблица messages).
 */
class Message extends Model
{
    protected static string $table = 'messages';

    /**
     * Сообщения от новых к старым.
     * @return array<int, array<string, mixed>>
     */
    public static function latest(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM messages ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Отметить сообщение прочитанным.
     */
    public static function markRead(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE messages SET is_read = 1 WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    /**
     * Количество непрочитанных сообщений.
     */
    public static function countUnread(): int
    {
        $stmt = Database::connection()->query(
            'SELECT COUNT(*) FROM messages WHERE is_read = 0'
        );
        return (int) $stmt->fetchColumn();
    }
}
