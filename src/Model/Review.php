<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель отзыва клиента (таблица reviews).
 */
class Review extends Model
{
    protected static string $table = 'reviews';

    /**
     * Отзывы от новых к старым.
     * @return array<int, array<string, mixed>>
     */
    public static function latest(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM reviews ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Установить флаг публикации отзыва (1 — опубликован, 0 — на модерации).
     */
    public static function setPublished(int $id, bool $published): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE reviews SET is_published = ? WHERE id = ?'
        );
        $stmt->execute([(int) $published, $id]);
    }
}
