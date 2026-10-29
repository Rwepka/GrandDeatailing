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
     * Удаление заявки (состав услуг удаляется каскадно).
     */
    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM bookings WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Все заявки с расчётной стоимостью (сумма выбранных услуг).
     * @return array<int, array<string, mixed>>
     */
    public static function latestWithCost(): array
    {
        $stmt = Database::connection()->query(
            'SELECT b.*,
                    (SELECT SUM(bi.price) FROM booking_items bi WHERE bi.booking_id = b.id) AS estimated_cost
             FROM bookings b
             ORDER BY b.created_at DESC, b.id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Сохранить согласованную стоимость (при переводе в «в работе»).
     */
    public static function setAgreedCost(int $id, float $cost): void
    {
        $stmt = Database::connection()->prepare('UPDATE bookings SET agreed_cost = ? WHERE id = ?');
        $stmt->execute([$cost, $id]);
    }

    /**
     * Сохранить итоговую стоимость (при переводе в «выполнена»).
     */
    public static function setFinalCost(int $id, float $cost): void
    {
        $stmt = Database::connection()->prepare('UPDATE bookings SET final_cost = ? WHERE id = ?');
        $stmt->execute([$cost, $id]);
    }

    /**
     * Доход по дням: сумма итоговых стоимостей выполненных заявок за период.
     *
     * @return array<int, array{day: string, total: string}>
     */
    public static function incomeByPeriod(string $from, string $to): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT DATE(created_at) AS day, SUM(final_cost) AS total
             FROM bookings
             WHERE status = \'done\' AND final_cost IS NOT NULL
               AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY day'
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
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
