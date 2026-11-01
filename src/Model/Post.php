<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Модель новости (таблица posts).
 */
class Post extends Model
{
    protected static string $table = 'posts';

    /**
     * Создание новости и возврат нового id.
     * Слаг генерируется из заголовка автоматически.
     *
     * @param array{title: string, content?: string|null, is_published?: int|bool} $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO posts (title, slug, content, is_published) VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['title'],
            self::makeSlug((string) $data['title']),
            $data['content'] ?? null,
            (int) ($data['is_published'] ?? 1),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    /**
     * Опубликованные новости, от новых к старым.
     * @return array<int, array<string, mixed>>
     */
    public static function published(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM posts WHERE is_published = 1 ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Все новости для админ-панели, от новых к старым.
     * @return array<int, array<string, mixed>>
     */
    public static function allForAdmin(): array
    {
        $stmt = Database::connection()->query(
            'SELECT * FROM posts ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Переключение видимости новости (опубликована/скрыта).
     */
    public static function toggle(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE posts SET is_published = 1 - is_published WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    /**
     * Удаление новости.
     */
    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Уникальный слаг: транслитерация кириллицы → латиница, замена не-латиницы/цифр
     * на «-», убирание повторов, обрезка и суффикс uniqid для гарантии уникальности.
     */
    private static function makeSlug(string $title): string
    {
        $title = function_exists('mb_strtolower')
            ? mb_strtolower($title, 'UTF-8')
            : strtolower($title);

        $title = strtr($title, [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ]);

        // После транслитерации осталась только латиница/цифры — заменяем прочее на «-».
        $slug = (string) preg_replace('/[^a-z0-9]+/', '-', $title);
        $slug = trim($slug, '-');

        // Столбец slug — VARCHAR(160). Обрезаем основу так, чтобы «-».uniqid() влезало.
        $suffix = uniqid();
        $maxBase = 160 - 1 - strlen($suffix);
        if (strlen($slug) > $maxBase) {
            $slug = substr($slug, 0, $maxBase);
        }

        return $slug . '-' . $suffix;
    }
}
