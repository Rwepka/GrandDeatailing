<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

class Car extends Model
{
    protected static string $table = 'cars';

    public static function findByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM cars WHERE user_id = ? ORDER BY id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO cars (user_id, brand, model, year, vin, plate) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['user_id'],
            $data['brand'],
            $data['model'],
            $data['year'] ?? null,
            $data['vin'] ?? null,
            $data['plate'] ?? null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }
}
