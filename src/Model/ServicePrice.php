<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Цена услуги по типу кузова — таблица service_prices.
 */
class ServicePrice extends Model
{
    protected static string $table = 'service_prices';

    /**
     * Цены услуги по типам кузова в виде карты ['Седан' => 8000.0, ...].
     *
     * @return array<string, mixed>
     */
    public static function forService(int $serviceId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT body_type, price FROM service_prices WHERE service_id = ? ORDER BY id'
        );
        $stmt->execute([$serviceId]);

        $prices = [];
        foreach ($stmt->fetchAll() as $row) {
            $prices[(string) $row['body_type']] = $row['price'];
        }
        return $prices;
    }

    /**
     * Цены по типам кузова сразу для списка услуг (один запрос).
     *
     * @param array<int, int> $serviceIds
     * @return array<int, array<string, mixed>> карта service_id => [body_type => price]
     */
    public static function mapForServices(array $serviceIds): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $serviceIds),
            static fn ($id) => $id > 0
        )));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            'SELECT service_id, body_type, price FROM service_prices WHERE service_id IN (' . $placeholders . ') ORDER BY id'
        );
        $stmt->execute($ids);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['service_id']][(string) $row['body_type']] = $row['price'];
        }
        return $map;
    }

    /**
     * Перезапись цен услуги: удаляет прежние и вставляет только непустые.
     *
     * @param array<string, float|null> $prices карта «тип кузова => цена»
     */
    public static function save(int $serviceId, array $prices): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $delete = $pdo->prepare('DELETE FROM service_prices WHERE service_id = ?');
            $delete->execute([$serviceId]);

            $insert = $pdo->prepare(
                'INSERT INTO service_prices (service_id, body_type, price) VALUES (?, ?, ?)'
            );
            foreach ($prices as $bodyType => $price) {
                if ($price === null || $price === '') {
                    continue;
                }
                $insert->execute([$serviceId, (string) $bodyType, (float) $price]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
