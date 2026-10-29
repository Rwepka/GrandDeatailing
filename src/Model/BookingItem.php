<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Позиция заявки (выбранная услуга) — таблица booking_items.
 */
class BookingItem extends Model
{
    protected static string $table = 'booking_items';

    /**
     * Добавление услуги в заявку с ценой на момент заказа.
     *
     * @param array{
     *     booking_id: int,
     *     service_id: int,
     *     price?: float|null
     * } $data
     */
    public static function create(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO booking_items (booking_id, service_id, price) VALUES (?, ?, ?)'
        );

        $stmt->execute([
            (int) $data['booking_id'],
            (int) $data['service_id'],
            $data['price'] ?? null,
        ]);
    }
}
