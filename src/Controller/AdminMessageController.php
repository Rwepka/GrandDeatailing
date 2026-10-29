<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Message;

class AdminMessageController
{
    public function index(): void
    {
        requireAdmin();
        $messages = Message::latest();
        View::renderAdmin('messages', ['messages' => $messages]);
    }

    public function markRead(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/messages');
        }

        Message::markRead($id);

        redirect('/admin/messages');
    }
}
