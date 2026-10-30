<?php
declare(strict_types=1);

namespace App\Model;

use App\Core\Database;

/**
 * Привязка Telegram-чатов к пользователям/заявкам (таблица telegram_links).
 */
class TelegramLink extends Model
{
    protected static string $table = 'telegram_links';

    public static function create(string $token, ?int $userId, ?int $bookingId): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO telegram_links (token, user_id, booking_id) VALUES (?, ?, ?)'
        );
        $stmt->execute([$token, $userId, $bookingId]);
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM telegram_links WHERE token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function linkChat(string $token, string $chatId): void
    {
        $stmt = Database::connection()->prepare('UPDATE telegram_links SET chat_id = ? WHERE token = ?');
        $stmt->execute([$chatId, $token]);
    }

    public static function chatIdByBooking(int $bookingId): ?string
    {
        $stmt = Database::connection()->prepare(
            'SELECT chat_id FROM telegram_links WHERE booking_id = ? AND chat_id IS NOT NULL ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$bookingId]);
        $chatId = $stmt->fetchColumn();
        return $chatId === false ? null : (string) $chatId;
    }

    public static function chatIdByUser(int $userId): ?string
    {
        $stmt = Database::connection()->prepare(
            'SELECT chat_id FROM telegram_links WHERE user_id = ? AND chat_id IS NOT NULL ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        $chatId = $stmt->fetchColumn();
        return $chatId === false ? null : (string) $chatId;
    }

    /**
     * Получить (или создать) токен подписки для пользователя/заявки.
     */
    public static function tokenFor(?int $userId, ?int $bookingId): string
    {
        if ($userId !== null) {
            $stmt = Database::connection()->prepare('SELECT token FROM telegram_links WHERE user_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$userId]);
        } else {
            $stmt = Database::connection()->prepare('SELECT token FROM telegram_links WHERE booking_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$bookingId]);
        }
        $token = $stmt->fetchColumn();
        if ($token !== false) {
            return (string) $token;
        }

        $token = bin2hex(random_bytes(16));
        self::create($token, $userId, $bookingId);
        return $token;
    }
}
