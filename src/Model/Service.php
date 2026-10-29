<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель услуги детейлинга (таблица services).
 */
class Service extends Model
{
    protected static string $table = 'services';

    /**
     * Активные услуги (is_active = 1).
     * @return array<int, array<string, mixed>>
     */
    public static function active(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM services WHERE is_active = 1 ORDER BY id'
        );
        return $stmt->fetchAll();
    }

    /**
     * Поиск активной услуги по slug.
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM services WHERE slug = ? AND is_active = 1'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Создание услуги и возврат нового id.
     *
     * @param array{
     *     category_id: int,
     *     title: string,
     *     slug: string,
     *     price_from?: float|null,
     *     duration_min?: int|null,
     *     image?: string|null,
     *     is_active?: int|bool
     * } $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO services (category_id, title, slug, price_from, duration_min, image, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            (int) $data['category_id'],
            $data['title'],
            $data['slug'],
            $data['price_from'] ?? null,
            $data['duration_min'] ?? null,
            $data['image'] ?? null,
            $data['is_active'] ?? 1,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    /**
     * Обновление услуги (category_id, title, slug, price_from, duration_min).
     *
     * @param array{
     *     category_id: int,
     *     title: string,
     *     slug: string,
     *     price_from?: float|null,
     *     duration_min?: int|null
     * } $data
     */
    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE services SET category_id = ?, title = ?, slug = ?, price_from = ?, duration_min = ? WHERE id = ?'
        );

        $stmt->execute([
            (int) $data['category_id'],
            $data['title'],
            $data['slug'],
            $data['price_from'] ?? null,
            $data['duration_min'] ?? null,
            $id,
        ]);
    }

    /**
     * Переключение видимости услуги (is_active).
     */
    public static function setActive(int $id, bool $active): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE services SET is_active = ? WHERE id = ?'
        );
        $stmt->execute([$active ? 1 : 0, $id]);
    }

    /**
     * Все услуги в порядке добавления (сначала новые).
     * @return array<int, array<string, mixed>>
     */
    public static function latest(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM services ORDER BY id DESC');
        return $stmt->fetchAll();
    }
}
