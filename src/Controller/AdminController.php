<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\Booking;
use App\Model\BookingItem;
use App\Model\Service;
use App\Model\User;

class AdminController
{
    /** @var array<int, string> */
    private const CAR_CLASSES = [
        'Седан',
        'Кроссовер / SUV',
        'Минивэн',
        'Купе / Спорт',
        'Универсал',
        'Хэтчбек',
    ];

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

    public function createBooking(): void
    {
        requireStaff();

        View::renderAdmin('booking_form', [
            'title' => 'Новая заявка',
            'services' => Service::active(),
            'carClasses' => self::CAR_CLASSES,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function storeBooking(): void
    {
        requireStaff();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/bookings');
        }

        $services = Service::active();
        $old = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'car_class' => trim((string) ($_POST['car_class'] ?? '')),
            'brand_model' => trim((string) ($_POST['brand_model'] ?? '')),
            'pref_date' => trim((string) ($_POST['pref_date'] ?? '')),
            'pref_time' => trim((string) ($_POST['pref_time'] ?? '')),
            'comment' => trim((string) ($_POST['comment'] ?? '')),
            'service_ids' => is_array($_POST['service_ids'] ?? null) ? array_map('intval', $_POST['service_ids']) : [],
        ];

        $errors = [];

        // --- Имя ---
        $name = $old['name'];
        if ($name === '') {
            $errors[] = 'Укажите имя клиента.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($name) : strlen($name)) > 100) {
            $errors[] = 'Имя не должно быть длиннее 100 символов.';
        }

        // --- Телефон ---
        $phoneRaw = $old['phone'];
        $phone = '';
        if ($phoneRaw === '') {
            $errors[] = 'Укажите номер телефона.';
        } else {
            $phone = normalize_phone($phoneRaw);
            $digits = (string) preg_replace('/\D+/', '', $phone);
            if (preg_match('/^\d{10,15}$/', $digits) !== 1) {
                $errors[] = 'Номер телефона должен содержать от 10 до 15 цифр.';
            }
        }

        // --- Услуги ---
        $activeById = [];
        foreach ($services as $service) {
            $activeById[(int) $service['id']] = $service;
        }
        $serviceIds = [];
        $rawServiceIds = is_array($_POST['service_ids'] ?? null) ? $_POST['service_ids'] : [];
        foreach ($rawServiceIds as $sid) {
            $sid = (int) $sid;
            if ($sid > 0 && isset($activeById[$sid])) {
                $serviceIds[$sid] = $sid;
            }
        }
        if (count($serviceIds) === 0) {
            $errors[] = 'Выберите хотя бы одну услугу.';
        }

        // --- Класс авто ---
        $carClass = $old['car_class'];
        if (!in_array($carClass, self::CAR_CLASSES, true)) {
            $errors[] = 'Выберите класс автомобиля.';
        }

        // --- Марка/модель ---
        $brandModel = $old['brand_model'];

        // --- Дата ---
        $prefDate = null;
        $prefDateRaw = $old['pref_date'];
        if ($prefDateRaw !== '') {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $prefDateRaw);
            $dateErr = \DateTime::getLastErrors();
            $dateClean = $dateErr === false || ($dateErr['warning_count'] === 0 && $dateErr['error_count'] === 0);
            if ($dateObj === false || !$dateClean || $dateObj->format('Y-m-d') !== $prefDateRaw) {
                $errors[] = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
            } else {
                $prefDate = $dateObj->format('Y-m-d');
            }
        }

        // --- Время ---
        $prefTime = null;
        $prefTimeRaw = $old['pref_time'];
        if ($prefTimeRaw !== '') {
            $timeObj = null;
            foreach (['H:i', 'H:i:s'] as $format) {
                $candidate = \DateTime::createFromFormat($format, $prefTimeRaw);
                $timeErr = \DateTime::getLastErrors();
                $timeClean = $timeErr === false || ($timeErr['warning_count'] === 0 && $timeErr['error_count'] === 0);
                if ($candidate !== false && $timeClean) {
                    $timeObj = $candidate;
                    break;
                }
            }
            if ($timeObj === null) {
                $errors[] = 'Время должно быть в формате ЧЧ:ММ (например 14:30).';
            } else {
                $prefTime = $timeObj->format('H:i:s');
            }
        }

        // --- Комментарий ---
        $comment = trim((string) ($_POST['comment'] ?? ''));
        if ($comment === '') {
            $comment = null;
        }

        $carInfo = $carClass;
        if ($brandModel !== '') {
            $carInfo .= ' — ' . $brandModel;
        }

        if ($errors !== []) {
            View::renderAdmin('booking_form', [
                'title' => 'Новая заявка',
                'services' => $services,
                'carClasses' => self::CAR_CLASSES,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $pdo = Database::connection();
        try {
            $pdo->beginTransaction();

            $bookingId = Booking::create([
                'user_id' => null,
                'client_name' => $name,
                'client_phone' => $phone,
                'car_info' => $carInfo,
                'pref_date' => $prefDate,
                'pref_time' => $prefTime,
                'comment' => $comment,
            ]);

            foreach ($serviceIds as $sid) {
                $service = $activeById[$sid];
                $rawPrice = $service['price_from'] ?? null;
                BookingItem::create([
                    'booking_id' => $bookingId,
                    'service_id' => $sid,
                    'price' => ($rawPrice === null || $rawPrice === '') ? null : (float) $rawPrice,
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            View::renderAdmin('booking_form', [
                'title' => 'Новая заявка',
                'services' => $services,
                'carClasses' => self::CAR_CLASSES,
                'errors' => ['Не удалось сохранить заявку. Попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        setFlash('Заявка создана.');
        redirect('/admin/bookings');
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
