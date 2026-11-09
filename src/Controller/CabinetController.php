<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\AuditLog;
use App\Model\Booking;
use App\Model\Car;
use App\Model\Review;
use App\Model\User;

class CabinetController
{
    public function index(): void
    {
        requireAuth();
        $user = auth();
        $bookings = Booking::findByUser((int) $user['id']);

        $reviewedBookingIds = [];
        foreach ($bookings as $b) {
            if (($b['status'] ?? '') === 'done' && Review::findByBooking((int) $b['id']) !== null) {
                $reviewedBookingIds[] = (int) $b['id'];
            }
        }

        View::render('cabinet', [
            'title' => 'Личный кабинет',
            'user' => $user,
            'bookings' => $bookings,
            'reviewedBookingIds' => $reviewedBookingIds,
            'flash' => flash(),
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
            $errors['brand'] = 'Укажите марку автомобиля.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($brand) : strlen($brand)) > 60) {
            $errors['brand'] = 'Марка не должна быть длиннее 60 символов.';
        }

        // --- Модель ---
        $model = trim((string) ($_POST['model'] ?? ''));
        if ($model === '') {
            $errors['model'] = 'Укажите модель автомобиля.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($model) : strlen($model)) > 60) {
            $errors['model'] = 'Модель не должна быть длиннее 60 символов.';
        }

        // --- Год (необязательно, 4 цифры, 1950–2026) ---
        $year = null;
        $yearRaw = trim((string) ($_POST['year'] ?? ''));
        if ($yearRaw !== '') {
            if (preg_match('/^\d{4}$/', $yearRaw) !== 1) {
                $errors['year'] = 'Год должен состоять из 4 цифр.';
            } else {
                $yearInt = (int) $yearRaw;
                if ($yearInt < 1950 || $yearInt > 2026) {
                    $errors['year'] = 'Год должен быть в диапазоне 1950–2026.';
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
            $errors['phone'] = 'Укажите корректный номер телефона (10–15 цифр)';
        }

        $existing = User::findByPhone($phone);
        if ($existing !== null && (int) $existing['id'] !== (int) auth()['id']) {
            $errors['phone'] = 'Этот телефон уже используется';
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
            $errors['email'] = 'Укажите корректный email';
        }

        if ($email !== null) {
            $existing = User::findByEmail($email);
            if ($existing !== null && (int) $existing['id'] !== (int) auth()['id']) {
                $errors['email'] = 'Этот email уже используется';
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
            $errors['current_password'] = 'Текущий пароль указан неверно';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Новый пароль должен быть не короче 8 символов';
        }

        if ($password !== $confirmation) {
            $errors['password_confirmation'] = 'Пароли не совпадают';
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
        AuditLog::record('password.change', 'user', (int) $user['id']);

        // Отзываем «запомнить меня» на всех устройствах и ротируем текущую сессию.
        \App\Model\RememberToken::deleteAllForUser((int) $user['id']);
        forgetRememberUser();
        session_regenerate_id(true);

        setFlash('Пароль изменён.');
        redirect('/cabinet/security');
    }

    public function forcePasswordChangeForm(): void
    {
        requireAuth();
        $user = auth();

        if (empty($user['must_change_password'])) {
            redirect('/cabinet');
        }

        View::render('password_change', [
            'title' => 'Смена временного пароля',
            'user' => $user,
            'errors' => [],
        ]);
    }

    public function forcePasswordChange(): void
    {
        requireAuth();
        $user = auth();

        if (empty($user['must_change_password'])) {
            redirect('/cabinet');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('password_change', [
                'title' => 'Смена временного пароля',
                'user' => $user,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
            ]);
            return;
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = 'Новый пароль должен быть не короче 8 символов';
        }
        if ($password !== $confirmation) {
            $errors[] = 'Пароли не совпадают';
        }

        if ($errors !== []) {
            View::render('password_change', [
                'title' => 'Смена временного пароля',
                'user' => $user,
                'errors' => $errors,
            ]);
            return;
        }

        User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        User::setMustChangePassword((int) $user['id'], false);
        AuditLog::record('password.change', 'user', (int) $user['id'], 'forced');

        // Отзываем «запомнить меня» и ротируем сессию.
        \App\Model\RememberToken::deleteAllForUser((int) $user['id']);
        forgetRememberUser();
        session_regenerate_id(true);

        setFlash('Пароль изменён. Теперь вы можете пользоваться кабинетом.');
        redirect('/cabinet');
    }
}
