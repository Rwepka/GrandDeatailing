<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

class RememberToken extends Model
{
    protected static string $table = 'remember_tokens';

    public static function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $tokenHash, $expiresAt]);
    }

    public static function findUserIdByHash(string $tokenHash): ?int
    {
        $stmt = Database::connection()->prepare(
            'SELECT user_id FROM remember_tokens WHERE token_hash = ? AND expires_at > NOW()'
        );
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();
        return $row === false ? null : (int) $row['user_id'];
    }

    public static function deleteByHash(string $tokenHash): void
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM remember_tokens WHERE token_hash = ?'
        );
        $stmt->execute([$tokenHash]);
    }
}
