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

    /**
     * Опубликованные отзывы (для публичной страницы), с именем клиента.
     * @return array<int, array<string, mixed>>
     */
    public static function published(): array
    {
        $stmt = Database::connection()->query(
            'SELECT r.*, u.name AS user_name
             FROM reviews r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.is_published = 1
             ORDER BY r.created_at DESC, r.id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Отзыв по конкретной заявке (для проверки, оставлен ли уже отзыв).
     */
    public static function findByBooking(int $bookingId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM reviews WHERE booking_id = ?');
        $stmt->execute([$bookingId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Отзывы с деталями для админки: имя клиента и названия услуг.
     * @return array<int, array<string, mixed>>
     */
    public static function latestWithDetails(): array
    {
        $stmt = Database::connection()->query(
            'SELECT r.*, u.name AS user_name,
                    (SELECT GROUP_CONCAT(s.title SEPARATOR \', \')
                     FROM booking_items bi
                     JOIN services s ON s.id = bi.service_id
                     WHERE bi.booking_id = r.booking_id) AS service_titles
             FROM reviews r
             LEFT JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC, r.id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Создать отзыв (по умолчанию на модерации).
     *
     * @param array{user_id?: int|null, booking_id?: int|null, rating: int, text?: string|null} $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO reviews (user_id, booking_id, rating, text, is_published) VALUES (?, ?, ?, ?, 0)'
        );
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['booking_id'] ?? null,
            (int) $data['rating'],
            $data['text'] ?? null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }
}
