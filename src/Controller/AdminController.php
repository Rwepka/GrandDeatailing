<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\Booking;

class AdminController
{
    /**
     * Дашборд админ-панели: статистика и последние заявки.
     */
    public function dashboard(): void
    {
        requireAdmin();

        $pdo = Database::connection();

        View::renderAdmin('dashboard', [
            'title' => 'Дашборд',
            'newBookings' => Booking::countByStatus('new'),
            'inWorkBookings' => Booking::countByStatus('in_work'),
            'readyBookings' => Booking::countByStatus('ready'),
            'totalBookings' => (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
            'totalClients' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role_id = 1')->fetchColumn(),
            'totalServices' => (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn(),
            'recentBookings' => array_slice(Booking::latest(), 0, 5),
        ]);
    }

    /**
     * Список всех заявок.
     */
    public function bookings(): void
    {
        requireStaff();

        View::renderAdmin('bookings', [
            'title' => 'Заявки',
            'bookings' => Booking::latest(),
        ]);
    }

    /**
     * Смена статуса заявки (только POST, с CSRF-защитой и белым списком).
     */
    public function updateStatus(int $id): void
    {
        requireStaff();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            redirect('/admin/bookings');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/bookings');
        }

        $status = (string) ($_POST['status'] ?? '');
        $allowed = ['new', 'confirmed', 'in_work', 'done', 'ready', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            redirect('/admin/bookings');
        }

        Booking::updateStatus($id, $status);

        redirect('/admin/bookings');
    }
}
