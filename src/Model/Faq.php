<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Вопросы и ответы для блока «Частые вопросы» на главной.
 */
class Faq extends Model
{
    protected static string $table = 'faqs';

    /**
     * Активные вопросы в порядке сортировки.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function active(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id'
        );
        return $stmt->fetchAll();
    }

    /**
     * Все вопросы для админки (включая скрытые).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function ordered(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM faqs ORDER BY sort_order, id'
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['question'],
            $data['answer'],
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE faqs SET question = ?, answer = ?, sort_order = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['question'],
            $data['answer'],
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $stmt = Database::connection()->prepare('UPDATE faqs SET is_active = ? WHERE id = ?');
        $stmt->execute([$active ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM faqs WHERE id = ?');
        $stmt->execute([$id]);
    }
}
