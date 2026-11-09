<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Неудачные попытки входа (таблица login_attempts).
 * Храним только sha256 от нормализованного логина — без телефона/email в открытом виде.
 */
class LoginAttempt extends Model
{
    protected static string $table = 'login_attempts';

    /**
     * Слишком ли много неудач для аккаунта за окно времени.
     */
    public static function tooMany(string $loginHash, int $limit = 5, int $windowSeconds = 900): bool
    {
        $pdo = Database::connection();
        $cutoff = date('Y-m-d H:i:s', time() - $windowSeconds);

        // Удаляем устаревшие записи — они не влияют на лимит.
        $pdo->prepare('DELETE FROM login_attempts WHERE created_at < ?')->execute([$cutoff]);

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE login_hash = ? AND created_at >= ?'
        );
        $stmt->execute([$loginHash, $cutoff]);

        return (int) $stmt->fetchColumn() >= $limit;
    }

    /**
     * Зафиксировать неудачную попытку входа.
     */
    public static function record(string $loginHash, string $ip): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO login_attempts (login_hash, ip) VALUES (?, ?)'
        );
        $stmt->execute([$loginHash, $ip]);
    }

    /**
     * Сбросить счётчик неудач после успешного входа.
     */
    public static function clear(string $loginHash): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM login_attempts WHERE login_hash = ?');
        $stmt->execute([$loginHash]);
    }
}
