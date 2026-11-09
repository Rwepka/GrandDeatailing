<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\AuditLog;

/**
 * Журнал действий (только администратор).
 */
class AdminAuditController
{
    public function index(): void
    {
        requireAdmin();

        View::renderAdmin('audit', [
            'title' => 'Журнал действий',
            'entries' => AuditLog::latest(200),
        ]);
    }
}
