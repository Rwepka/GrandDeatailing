<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByPhone(string $phone): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function findByRole(int $roleId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE role_id = ? ORDER BY id DESC');
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (role_id, name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['role_id'],
            $data['name'],
            $data['email'] ?? null,
            $data['phone'],
            $data['password_hash'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function updatePhone(int $id, string $phone): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET phone = ? WHERE id = ?');
        $stmt->execute([$phone, $id]);
    }

    public static function updateEmail(int $id, ?string $email): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->execute([$email, $id]);
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $id]);
    }
}
