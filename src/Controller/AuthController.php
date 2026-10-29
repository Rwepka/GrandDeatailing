<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\User;

class AuthController
{
    public function showRegister(): void
    {
        View::render('register', [
            'title' => 'Регистрация',
            'errors' => [],
            'old' => [],
        ]);
    }

    public function register(): void
    {
        // Исходные значения для повторного заполнения формы при ошибках.
        $old = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
        ];

        // CSRF-защита.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('register', [
                'title' => 'Регистрация',
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

        // --- Email (необязательно) ---
        $emailRaw = trim((string) ($_POST['email'] ?? ''));
        $email = null;
        if ($emailRaw !== '') {
            if (filter_var($emailRaw, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = 'Укажите корректный email.';
            } else {
                $email = $emailRaw;
            }
        }

        // --- Пароль ---
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        if ($password === '') {
            $errors[] = 'Укажите пароль.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($password) : strlen($password)) < 8) {
            $errors[] = 'Пароль должен быть не короче 8 символов.';
        }
        if ($passwordConfirmation !== $password) {
            $errors[] = 'Пароли не совпадают.';
        }

        // --- Уникальность телефона и email ---
        if ($errors === []) {
            if (User::findByPhone($phone) !== null) {
                $errors[] = 'Телефон уже зарегистрирован';
            }
            if ($email !== null && User::findByEmail($email) !== null) {
                $errors[] = 'Email уже зарегистрирован';
            }
        }

        if ($errors !== []) {
            View::render('register', [
                'title' => 'Регистрация',
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        // Роль «Клиент»: seed гарантирует client=1, но подстрахуемся запросом.
        $stmt = Database::connection()->prepare("SELECT id FROM roles WHERE code = 'client'");
        $stmt->execute();
        $role = $stmt->fetch();
        $roleId = $role === false ? 1 : (int) $role['id'];

        $userId = User::create([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        login($userId);
        redirect('/cabinet');
    }

    public function showLogin(): void
    {
        View::render('login', [
            'title' => 'Вход',
            'errors' => [],
            'old' => [],
        ]);
    }

    public function login(): void
    {
        $old = [
            'login' => trim((string) ($_POST['login'] ?? '')),
        ];

        // CSRF-защита.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('login', [
                'title' => 'Вход',
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        $errors = [];

        $login = trim((string) ($_POST['login'] ?? ''));
        if ($login === '') {
            $errors[] = 'Укажите телефон или email.';
        }

        $password = (string) ($_POST['password'] ?? '');
        if ($password === '') {
            $errors[] = 'Укажите пароль.';
        }

        $user = null;
        if ($errors === []) {
            if (str_contains($login, '@')) {
                $user = User::findByEmail($login);
            } else {
                $user = User::findByPhone($this->normalizePhone($login));
            }

            if ($user === null || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
                $errors[] = 'Неверный логин или пароль';
            }
        }

        if ($errors !== []) {
            View::render('login', [
                'title' => 'Вход',
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        if (!empty($_POST['remember'])) {
            rememberUser((int) $user['id']);
        }

        login((int) $user['id']);
        redirect(isMaster() ? '/admin/bookings' : '/cabinet');
    }

    public function logout(): void
    {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/');
        }

        forgetRememberUser();
        logout();
        redirect('/');
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
