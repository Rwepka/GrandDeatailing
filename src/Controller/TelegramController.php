<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\TelegramLink;

/**
 * Обработка вебхука Telegram (приём /start TOKEN для привязки чата).
 */
class TelegramController
{
    public function webhook(): void
    {
        $input = json_decode((string) file_get_contents('php://input'), true);
        $message = $input['message'] ?? null;
        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = (string) ($message['text'] ?? '');

        if ($chatId !== '' && str_starts_with($text, '/start')) {
            $parts = explode(' ', trim($text), 2);
            $token = $parts[1] ?? '';
            if ($token !== '' && preg_match('/^[a-f0-9]{32}$/', $token)) {
                $link = TelegramLink::findByToken($token);
                if ($link !== null && ($link['chat_id'] ?? null) === null) {
                    TelegramLink::linkChat($token, $chatId);
                    telegram_send($chatId, 'Уведомления подключены. Теперь вы будете получать сообщения о статусе вашей заявки.');
                }
            }
        }

        http_response_code(200);
        echo 'ok';
    }
}
