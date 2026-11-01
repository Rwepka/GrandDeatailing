<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\User;
use App\Model\Car;
use App\Model\Booking;

class AdminClientController
{
    /**
     * Список клиентов с количеством автомобилей и заявок.
     */
    public function index(): void
    {
        requireAdmin();

        $roleId = (int) (Database::connection()->query("SELECT id FROM roles WHERE code = 'client'")->fetchColumn() ?: 1);
        $clients = User::findByRole($roleId);

        foreach ($clients as &$client) {
            $client['cars_count'] = count(Car::findByUser((int) $client['id']));
            $client['bookings_count'] = count(Booking::findByUser((int) $client['id']));
        }
        unset($client);

        View::renderAdmin('clients', ['title' => 'Клиенты', 'clients' => $clients]);
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
            setFlash('Клиент удалён.');
        }

        redirect('/admin/clients');
    }
}
