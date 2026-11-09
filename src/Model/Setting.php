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

    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        if (self::$cache === null) {
            self::$cache = self::all();
        }

        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $stmt->execute([$key, $value]);

        // Сбрасываем кэш, чтобы новые значения сразу были видны.
        self::$cache = null;
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
