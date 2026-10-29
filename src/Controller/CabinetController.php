<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Booking;
use App\Model\Car;
use App\Model\User;

class CabinetController
{
    public function index(): void
    {
        requireAuth();
        $user = auth();
        $bookings = Booking::findByUser((int) $user['id']);
        View::render('cabinet', [
            'title' => 'Личный кабинет',
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }

    public function cars(): void
    {
        requireAuth();
        $user = auth();
        $cars = Car::findByUser((int) $user['id']);
        View::render('cars', [
            'title' => 'Мои автомобили',
            'cars' => $cars,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function storeCar(): void
    {
        requireAuth();

        // Исходные значения для повторного заполнения формы при ошибках.
        $old = [
            'brand' => trim((string) ($_POST['brand'] ?? '')),
            'model' => trim((string) ($_POST['model'] ?? '')),
            'year' => trim((string) ($_POST['year'] ?? '')),
            'vin' => trim((string) ($_POST['vin'] ?? '')),
            'plate' => trim((string) ($_POST['plate'] ?? '')),
        ];

        // CSRF-защита.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            $cars = Car::findByUser((int) auth()['id']);
            View::render('cars', [
                'title' => 'Мои автомобили',
                'cars' => $cars,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        $errors = [];

        // --- Марка ---
        $brand = trim((string) ($_POST['brand'] ?? ''));
        if ($brand === '') {
            $errors[] = 'Укажите марку автомобиля.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($brand) : strlen($brand)) > 60) {
            $errors[] = 'Марка не должна быть длиннее 60 символов.';
        }

        // --- Модель ---
        $model = trim((string) ($_POST['model'] ?? ''));
        if ($model === '') {
            $errors[] = 'Укажите модель автомобиля.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($model) : strlen($model)) > 60) {
            $errors[] = 'Модель не должна быть длиннее 60 символов.';
        }

        // --- Год (необязательно, 4 цифры, 1950–2026) ---
        $year = null;
        $yearRaw = trim((string) ($_POST['year'] ?? ''));
        if ($yearRaw !== '') {
            if (preg_match('/^\d{4}$/', $yearRaw) !== 1) {
                $errors[] = 'Год должен состоять из 4 цифр.';
            } else {
                $yearInt = (int) $yearRaw;
                if ($yearInt < 1950 || $yearInt > 2026) {
                    $errors[] = 'Год должен быть в диапазоне 1950–2026.';
                } else {
                    $year = $yearRaw;
                }
            }
        }

        // --- VIN и гос. номер (необязательные) ---
        $vin = trim((string) ($_POST['vin'] ?? ''));
        $plate = trim((string) ($_POST['plate'] ?? ''));

        if ($errors !== []) {
            $cars = Car::findByUser((int) auth()['id']);
            View::render('cars', [
                'title' => 'Мои автомобили',
                'cars' => $cars,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        Car::create([
            'user_id' => (int) auth()['id'],
            'brand' => $brand,
            'model' => $model,
            'year' => $year ?: null,
            'vin' => $vin,
            'plate' => $plate,
        ]);

        redirect('/cabinet/cars');
    }

    public function profile(): void
    {
        requireAuth();
        $user = auth();
        View::render('profile', [
            'title' => 'Профиль',
            'user' => $user,
        ]);
    }

    public function security(): void
    {
        requireAuth();
        $user = auth();
        View::render('security', [
            'title' => 'Безопасность',
            'user' => $user,
            'errors' => [],
            'old' => [],
            'flash' => flash(),
        ]);
    }

    public function updatePhone(): void
    {
        requireAuth();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/cabinet/security');
        }

        $phoneRaw = trim((string) ($_POST['phone'] ?? ''));
        $phone = normalize_phone($phoneRaw);
        $digits = preg_replace('/\D+/', '', $phone);

        $errors = [];

        if ($phoneRaw === '' || preg_match('/^\d{10,15}$/', $digits) !== 1) {
            $errors[] = 'Укажите корректный номер телефона (10–15 цифр)';
        }

        $existing = User::findByPhone($phone);
        if ($existing !== null && (int) $existing['id'] !== (int) auth()['id']) {
            $errors[] = 'Этот телефон уже используется';
        }

        if ($errors !== []) {
            View::render('security', [
                'title' => 'Безопасность',
                'user' => auth(),
                'errors' => $errors,
                'old' => ['phone' => $phoneRaw],
                'flash' => null,
            ]);
            return;
        }

        User::updatePhone((int) auth()['id'], $phone);
        setFlash('Телефон обновлён.');
        redirect('/cabinet/security');
    }

    public function updateEmail(): void
    {
        requireAuth();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/cabinet/security');
        }

        $emailRaw = trim((string) ($_POST['email'] ?? ''));
        $email = $emailRaw === '' ? null : $emailRaw;

        $errors = [];

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Укажите корректный email';
        }

        if ($email !== null) {
            $existing = User::findByEmail($email);
            if ($existing !== null && (int) $existing['id'] !== (int) auth()['id']) {
                $errors[] = 'Этот email уже используется';
            }
        }

        if ($errors !== []) {
            View::render('security', [
                'title' => 'Безопасность',
                'user' => auth(),
                'errors' => $errors,
                'old' => ['email' => $emailRaw],
                'flash' => null,
            ]);
            return;
        }

        User::updateEmail((int) auth()['id'], $email);
        setFlash('Email обновлён.');
        redirect('/cabinet/security');
    }

    public function updatePassword(): void
    {
        requireAuth();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/cabinet/security');
        }

        $current = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        $user = auth();

        $errors = [];

        if ($current === '' || !password_verify($current, (string) $user['password_hash'])) {
            $errors[] = 'Текущий пароль указан неверно';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Новый пароль должен быть не короче 8 символов';
        }

        if ($password !== $confirmation) {
            $errors[] = 'Пароли не совпадают';
        }

        if ($errors !== []) {
            View::render('security', [
                'title' => 'Безопасность',
                'user' => $user,
                'errors' => $errors,
                'old' => [],
                'flash' => null,
            ]);
            return;
        }

        User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        setFlash('Пароль изменён.');
        redirect('/cabinet/security');
    }
}
