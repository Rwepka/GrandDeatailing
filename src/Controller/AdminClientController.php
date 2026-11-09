<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\AuditLog;
use App\Model\User;

class AdminClientController
{
    /**
     * Список клиентов с количеством автомобилей и заявок.
     */
    public function index(): void
    {
        requireAdmin();

        // Автоудаление неподтверждённых клиентов старше 30 дней.
        $removed = User::deleteUnverifiedOlderThan(30);
        if ($removed > 0) {
            setFlash('Удалено неподтверждённых клиентов (старше 30 дней): ' . $removed . '.');
        }

        $roleId = (int) (Database::connection()->query("SELECT id FROM roles WHERE code = 'client'")->fetchColumn() ?: 1);
        $clients = User::findByRoleWithCounts($roleId);

        View::renderAdmin('clients', [
            'title' => 'Клиенты',
            'clients' => $clients,
            'flash' => flash(),
        ]);
    }

    /**
     * Отметить клиента как подтверждённого администратором.
     */
    public function verify(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/clients');
        }

        $clientRoleId = (int) (Database::connection()->query("SELECT id FROM roles WHERE code = 'client'")->fetchColumn() ?: 1);
        $user = User::find($id);

        if ($user !== null && (int) $user['role_id'] === $clientRoleId) {
            User::setVerified($id);
            AuditLog::record('client.verify', 'client', $id);
            setFlash('Клиент подтверждён.');
        }

        redirect('/admin/clients');
    }

    /**
     * Удаление клиента (каскад в БД отвяжет заявки/отзывы и удалит автомобили).
     */
    public function delete(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/clients');
        }

        $clientRoleId = (int) (Database::connection()->query("SELECT id FROM roles WHERE code = 'client'")->fetchColumn() ?: 1);
        $user = User::find($id);

        if ($user !== null && (int) $user['role_id'] === $clientRoleId) {
            User::delete($id);
            AuditLog::record('client.delete', 'client', $id);
            setFlash('Клиент удалён.');
        }

        redirect('/admin/clients');
    }
}
