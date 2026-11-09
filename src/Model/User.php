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

    /**
     * Пользователи роли с количеством автомобилей и заявок (один запрос вместо N+1).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function findByRoleWithCounts(int $roleId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT u.*,
                    (SELECT COUNT(*) FROM cars c WHERE c.user_id = u.id) AS cars_count,
                    (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS bookings_count
             FROM users u
             WHERE u.role_id = ?
             ORDER BY u.id DESC'
        );
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (role_id, name, email, phone, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $data['role_id'],
            $data['name'],
            $data['email'] ?? null,
            $data['phone'],
            $data['password_hash'],
            (int) ($data['must_change_password'] ?? 0),
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

    /**
     * Флаг «сменить пароль при следующем входе».
     */
    public static function setMustChangePassword(int $id, bool $mustChange): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET must_change_password = ? WHERE id = ?');
        $stmt->execute([$mustChange ? 1 : 0, $id]);
    }

    /**
     * Отметить клиента как проверенного администратором.
     */
    public static function setVerified(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET verified_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Удалить неподтверждённых клиентов, созданных раньше $days дней назад.
     * Связанные заявки анонимизируются, отзывы удаляются (P0-2/AUTH-02).
     *
     * @return int число удалённых пользователей
     */
    public static function deleteUnverifiedOlderThan(int $days): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - $days * 86400);
        $pdo = Database::connection();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "SELECT u.id FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE r.code = 'client'
                   AND u.verified_at IS NULL
                   AND u.created_at < ?"
            );
            $stmt->execute([$cutoff]);
            $ids = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));

            if ($ids === []) {
                $pdo->commit();
                return 0;
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // PII из заявок убираем, отзывы удаляем до удаления пользователей.
            $pdo->prepare(
                "UPDATE bookings
                 SET client_name = NULL, client_phone = NULL, car_info = NULL, comment = NULL
                 WHERE user_id IN ($placeholders)"
            )->execute($ids);

            $pdo->prepare("DELETE FROM reviews WHERE user_id IN ($placeholders)")->execute($ids);

            $delete = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders)");
            $delete->execute($ids);
            $removed = $delete->rowCount();

            $pdo->commit();
            return $removed;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function updateProfile(int $id, string $name, ?string $email, string $phone): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $email, $phone, $id]);
    }

    /**
     * Удалить клиента: связанные заявки анонимизируются, отзывы удаляются (P0-2/AUTH-02).
     */
    public static function delete(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                "UPDATE bookings
                 SET client_name = NULL, client_phone = NULL, car_info = NULL, comment = NULL
                 WHERE user_id = ?"
            )->execute([$id]);

            $pdo->prepare('DELETE FROM reviews WHERE user_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
