<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель заявки (бронирования) — таблица bookings.
 */
class Booking extends Model
{
    protected static string $table = 'bookings';

    /**
     * Создание заявки и возврат нового id.
     *
     * @param array{
     *     client_name: string,
     *     client_phone: string,
     *     car_info: string,
     *     pref_date?: string|null,
     *     pref_time?: string|null,
     *     comment?: string|null
     * } $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO bookings
                (user_id, car_id, status, pref_date, pref_time, comment, client_name, client_phone, car_info)
             VALUES
                (?, NULL, \'new\', ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['user_id'] ?? null,
            $data['pref_date'] ?? null,
            $data['pref_time'] ?? null,
            $data['comment'] ?? null,
            $data['client_name'] ?? null,
            $data['client_phone'] ?? null,
            $data['car_info'] ?? null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function findByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC, id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Все заявки, от новых к старым.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function latest(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM bookings ORDER BY created_at DESC, id DESC');
        return $stmt->fetchAll();
    }

    /**
     * Обновление статуса заявки.
     */
    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    /**
     * Привязка заявки к учётной записи клиента.
     */
    public static function linkUser(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare('UPDATE bookings SET user_id = ? WHERE id = ?');
        $stmt->execute([$userId, $id]);
    }

    /**
     * Количество заявок с указанным статусом.
     */
    public static function countByStatus(string $status): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM bookings WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }
}
