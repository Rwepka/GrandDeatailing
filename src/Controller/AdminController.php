<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\Booking;
use App\Model\User;

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
     * Доход по дням (JSON) для графика на дашборде.
     */
    public function income(): void
    {
        requireAdmin();

        $from = (string) ($_GET['from'] ?? '');
        $to = (string) ($_GET['to'] ?? '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-d');
        }

        $data = Booking::incomeByPeriod($from, $to);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['from' => $from, 'to' => $to, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Список всех заявок.
     */
    public function bookings(): void
    {
        requireStaff();

        View::renderAdmin('bookings', [
            'title' => 'Заявки',
            'bookings' => Booking::latestWithCost(),
            'flash' => flash(),
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

        if ($status === 'in_work') {
            $cost = (float) str_replace(',', '.', (string) ($_POST['cost'] ?? ''));
            if ($cost <= 0) {
                setFlash('Введите корректную согласованную стоимость.');
                redirect('/admin/bookings');
            }
            Booking::setAgreedCost($id, $cost);
        }

        if ($status === 'done') {
            $cost = (float) str_replace(',', '.', (string) ($_POST['cost'] ?? ''));
            if ($cost <= 0) {
                setFlash('Введите корректную итоговую стоимость.');
                redirect('/admin/bookings');
            }
            Booking::setFinalCost($id, $cost);
        }

        Booking::updateStatus($id, $status);

        redirect('/admin/bookings');
    }

    /**
     * Создание учётной записи клиента из заявки (только администратор).
     */
    public function createClient(int $id): void
    {
        requireAdmin();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            redirect('/admin/bookings');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/bookings');
        }

        $booking = Booking::find($id);
        if ($booking === null) {
            redirect('/admin/bookings');
        }

        if (!empty($booking['user_id'])) {
            setFlash('У заявки уже есть клиент.');
            redirect('/admin/bookings');
        }

        $name = trim((string) ($booking['client_name'] ?? ''));
        $phone = normalize_phone((string) ($booking['client_phone'] ?? ''));

        if ($name === '' || $phone === '') {
            setFlash('Нельзя создать клиента: в заявке нет имени или телефона.');
            redirect('/admin/bookings');
        }

        // Если пользователь с таким телефоном уже существует — привязываем заявку к нему.
        $existing = User::findByPhone($phone);
        if ($existing !== null) {
            Booking::linkUser($id, (int) $existing['id']);
            setFlash('Заявка привязана к существующему клиенту «' . $existing['name'] . '».');
            redirect('/admin/bookings');
        }

        // Иначе создаём нового клиента с временным паролем.
        $password = substr(bin2hex(random_bytes(6)), 0, 10);

        $clientRoleId = (int) (Database::connection()->query("SELECT id FROM roles WHERE code = 'client'")->fetchColumn() ?: 1);

        $userId = User::create([
            'role_id' => $clientRoleId,
            'name' => $name,
            'email' => null,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        Booking::linkUser($id, $userId);

        setFlash('Клиент создан: ' . $phone . '. Временный пароль: ' . $password . ' (сообщите клиенту).');
        redirect('/admin/bookings');
    }

    /**
     * Удаление заявки (только администратор, не мастер).
     */
    public function deleteBooking(int $id): void
    {
        requireAdmin();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            redirect('/admin/bookings');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/bookings');
        }

        Booking::delete($id);
        setFlash('Заявка удалена.');
        redirect('/admin/bookings');
    }
}
