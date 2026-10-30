<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель настроек сайта (таблица settings).
 */
class Setting extends Model
{
    protected static string $table = 'settings';

    public static function get(string $key, ?string $default = null): ?string
    {
        $stmt = Database::connection()->prepare('SELECT value FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $stmt->execute([$key, $value]);
    }

    /**
     * Все настройки в виде ассоциативного массива key => value.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $rows = Database::connection()->query('SELECT `key`, value FROM settings')->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['key']] = (string) $row['value'];
        }
        return $map;
    }
}
