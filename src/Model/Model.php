<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Базовый класс моделей. Каждая модель задаёт имя таблицы и получает базовые
 * методы выборки. Пример использования:
 *   Service::all()      — все записи таблицы;
 *   Service::find($id)  — одна запись по id (через prepared statement).
 */
abstract class Model
{
    protected static string $table = '';

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM ' . static::$table);
        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $pdo = Database::connection();
        // Prepared statement: $id передаётся параметром, а не конкатенацией.
        $stmt = $pdo->prepare('SELECT * FROM ' . static::$table . ' WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
