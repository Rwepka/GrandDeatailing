<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\Booking;
use App\Model\BookingItem;
use App\Model\RateLimit;
use App\Model\Service;

class BookingController
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

    public function show(): void
    {
        // Авторизованному пользователю подставляем его имя и телефон в квиз.
        $user = auth();
        // Точка отсчёта для time-trap: замер времени заполнения формы.
        $_SESSION['booking_form_time'] = time();
        View::render('booking', [
            'title' => 'Запись онлайн',
            'services' => Service::active(),
            'carClasses' => self::CAR_CLASSES,
            'old' => [
                'name' => $user['name'] ?? '',
                'phone' => $user['phone'] ?? '',
            ],
        ]);
    }

    public function store(): void
    {
        // --- Honeypot: скрытое поле-ловушка для ботов ---
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            redirect('/booking/success');
        }

        // --- Time-trap: заявка не может быть заполнена быстрее 3 секунд ---
        $formTime = (int) ($_SESSION['booking_form_time'] ?? 0);
        if ($formTime === 0 || (time() - $formTime) < 3) {
            redirect('/booking/success');
        }

        // --- Лимит отправок по IP ---
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (RateLimit::tooMany($ip)) {
            View::render('booking', [
                'title' => 'Запись онлайн',
                'services' => Service::active(),
                'carClasses' => self::CAR_CLASSES,
                'errors' => ['Слишком много заявок. Попробуйте позже.'],
                'old' => [],
            ]);
            return;
        }

        $services = Service::active();

        // Исходные значения для повторного заполнения формы при ошибках.
        $old = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'car_class' => trim((string) ($_POST['car_class'] ?? '')),
            'brand_model' => trim((string) ($_POST['brand_model'] ?? '')),
            'pref_date' => trim((string) ($_POST['pref_date'] ?? '')),
            'pref_time' => trim((string) ($_POST['pref_time'] ?? '')),
            'comment' => trim((string) ($_POST['comment'] ?? '')),
            'service_ids' => is_array($_POST['service_ids'] ?? null)
                ? array_map('intval', $_POST['service_ids'])
                : [],
        ];

        // CSRF-защита.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('booking', [
                'title' => 'Запись онлайн',
                'services' => $services,
                'carClasses' => self::CAR_CLASSES,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        $errors = [];

        // --- Имя ---
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Укажите ваше имя.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($name) : strlen($name)) > 100) {
            $errors[] = 'Имя не должно быть длиннее 100 символов.';
        }

        // --- Телефон ---
        $phoneRaw = trim((string) ($_POST['phone'] ?? ''));
        $phone = '';
        if ($phoneRaw === '') {
            $errors[] = 'Укажите номер телефона.';
        } else {
            $phone = $this->normalizePhone($phoneRaw);
            $digits = preg_replace('/\D+/', '', $phone);
            if (preg_match('/^\d{10,15}$/', $digits) !== 1) {
                $errors[] = 'Номер телефона должен содержать от 10 до 15 цифр.';
            }
        }

        // --- Услуги (только активные, без дубликатов) ---
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
        $carClass = trim((string) ($_POST['car_class'] ?? ''));
        if (!in_array($carClass, self::CAR_CLASSES, true)) {
            $errors[] = 'Выберите класс автомобиля.';
        }

        // --- Марка/модель (необязательно) ---
        $brandModel = trim((string) ($_POST['brand_model'] ?? ''));

        // --- Желаемая дата (необязательно, Y-m-d) ---
        $prefDate = null;
        $prefDateRaw = trim((string) ($_POST['pref_date'] ?? ''));
        if ($prefDateRaw !== '') {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $prefDateRaw);
            $dateErr = \DateTime::getLastErrors();
            $dateClean = $dateErr === false
                || ($dateErr['warning_count'] === 0 && $dateErr['error_count'] === 0);
            if ($dateObj === false || !$dateClean || $dateObj->format('Y-m-d') !== $prefDateRaw) {
                $errors[] = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
            } else {
                $prefDate = $dateObj->format('Y-m-d');
            }
        }

        // --- Желаемое время (необязательно, H:i или H:i:s) ---
        $prefTime = null;
        $prefTimeRaw = trim((string) ($_POST['pref_time'] ?? ''));
        if ($prefTimeRaw !== '') {
            $timeObj = null;
            foreach (['H:i', 'H:i:s'] as $format) {
                $candidate = \DateTime::createFromFormat($format, $prefTimeRaw);
                $timeErr = \DateTime::getLastErrors();
                $timeClean = $timeErr === false
                    || ($timeErr['warning_count'] === 0 && $timeErr['error_count'] === 0);
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

        // --- Комментарий (необязательно) ---
        $comment = trim((string) ($_POST['comment'] ?? ''));
        if ($comment === '') {
            $comment = null;
        }

        // car_info: «Класс — Марка/модель».
        $carInfo = $carClass;
        if ($brandModel !== '') {
            $carInfo .= ' — ' . $brandModel;
        }

        if ($errors !== []) {
            View::render('booking', [
                'title' => 'Запись онлайн',
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
                'user_id' => auth()['id'] ?? null,
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
            View::render('booking', [
                'title' => 'Запись онлайн',
                'services' => $services,
                'carClasses' => self::CAR_CLASSES,
                'errors' => ['Не удалось сохранить заявку. Попробуйте ещё раз позже.'],
                'old' => $old,
            ]);
            return;
        }

        RateLimit::record($ip);
        redirect('/booking/success');
    }

    public function success(): void
    {
        View::render('booking_success', ['title' => 'Заявка отправлена']);
    }

    /**
     * Очистка телефона: сохраняем ведущий «+», убираем всё, кроме цифр.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $leadingPlus = '';
        if (str_starts_with($phone, '+')) {
            $leadingPlus = '+';
            $phone = substr($phone, 1);
        }

        return $leadingPlus . (string) preg_replace('/\D+/', '', $phone);
    }
}
