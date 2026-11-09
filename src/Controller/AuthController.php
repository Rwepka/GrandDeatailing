<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\LoginAttempt;
use App\Model\User;
use App\Model\RateLimit;

class AuthController
{
    public function showRegister(): void
    {
        $_SESSION['register_form_time'] = time();
        View::render('register', [
            'title' => 'Регистрация',
            'errors' => [],
            'old' => [],
        ]);
    }

    public function register(): void
    {
        // --- Honeypot: скрытое поле-ловушка для ботов ---
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            redirect('/');
        }

        // --- Time-trap: форма не может быть заполнена быстрее 3 секунд ---
        $formTime = (int) ($_SESSION['register_form_time'] ?? 0);
        if ($formTime === 0 || (time() - $formTime) < 3) {
            redirect('/');
        }

        // --- Лимит отправок по IP ---
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (RateLimit::tooMany($ip)) {
            View::render('register', [
                'title' => 'Регистрация',
                'errors' => ['Слишком много попыток. Попробуйте позже.'],
                'old' => [],
            ]);
            return;
        }

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
            $errors['name'] = 'Укажите ваше имя.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($name) : strlen($name)) > 100) {
            $errors['name'] = 'Имя не должно быть длиннее 100 символов.';
        }

        // --- Телефон ---
        $phoneRaw = trim((string) ($_POST['phone'] ?? ''));
        $phone = '';
        if ($phoneRaw === '') {
            $errors['phone'] = 'Укажите номер телефона.';
        } else {
            $phone = $this->normalizePhone($phoneRaw);
            $digits = preg_replace('/\D+/', '', $phone);
            if (preg_match('/^\d{10,15}$/', $digits) !== 1) {
                $errors['phone'] = 'Номер телефона должен содержать от 10 до 15 цифр.';
            }
        }

        // --- Email (необязательно) ---
        $emailRaw = trim((string) ($_POST['email'] ?? ''));
        $email = null;
        if ($emailRaw !== '') {
            if (filter_var($emailRaw, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Укажите корректный email.';
            } else {
                $email = $emailRaw;
            }
        }

        // --- Пароль ---
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        if ($password === '') {
            $errors['password'] = 'Укажите пароль.';
        } elseif ((function_exists('mb_strlen') ? mb_strlen($password) : strlen($password)) < 8) {
            $errors['password'] = 'Пароль должен быть не короче 8 символов.';
        }
        if ($passwordConfirmation !== $password) {
            $errors['password_confirmation'] = 'Пароли не совпадают.';
        }

        // --- Уникальность телефона и email (нейтральное сообщение — без user enumeration) ---
        if ($errors === []) {
            $phoneTaken = User::findByPhone($phone) !== null;
            $emailTaken = $email !== null && User::findByEmail($email) !== null;
            if ($phoneTaken || $emailTaken) {
                $errors['general'] = 'Не удалось завершить регистрацию: возможно, аккаунт с такими данными уже существует. Войдите или восстановите доступ.';
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

        RateLimit::record($ip);
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
        // Rate limit: не более 10 попыток входа за 15 минут с одного IP.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (RateLimit::tooMany($ip, 10, 900)) {
            http_response_code(429);
            View::render('login', [
                'title' => 'Вход',
                'errors' => ['Слишком много попыток входа. Попробуйте позже.'],
                'old' => [],
            ]);
            return;
        }

        // Throttle по аккаунту: не более 5 неудачных попыток за 15 минут.
        $loginKey = $this->normalizeLoginKey((string) ($_POST['login'] ?? ''));
        $loginHash = $loginKey === '' ? '' : hash('sha256', $loginKey);
        if ($loginHash !== '' && LoginAttempt::tooMany($loginHash, 5, 900)) {
            http_response_code(429);
            View::render('login', [
                'title' => 'Вход',
                'errors' => ['Слишком много попыток входа для этого аккаунта. Попробуйте позже.'],
                'old' => [],
            ]);
            return;
        }

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
            $errors['login'] = 'Укажите телефон или email.';
        }

        $password = (string) ($_POST['password'] ?? '');
        if ($password === '') {
            $errors['password'] = 'Укажите пароль.';
        }

        $user = null;
        if ($errors === []) {
            if (str_contains($login, '@')) {
                $user = User::findByEmail($login);
            } else {
                $user = User::findByPhone($this->normalizePhone($login));
            }

            if ($user === null || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
                RateLimit::record($ip);
                if ($loginHash !== '') {
                    LoginAttempt::record($loginHash, $ip);
                }
                error_log('GRAND DETAILING: failed login from ' . $ip);
                $errors['general'] = 'Неверный логин или пароль';
            } elseif (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
                // Пароль устаревшего формата — перехешируем прозрачно.
                User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
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

        // Успешный вход — сбрасываем счётчик неудач для аккаунта.
        if ($loginHash !== '') {
            LoginAttempt::clear($loginHash);
        }

        login((int) $user['id']);

        // Временный пароль — принудительная смена при первом входе.
        if (!empty($user['must_change_password'])) {
            redirect('/cabinet/security/password-change');
        }

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
     * Ключ аккаунта для throttle: email в нижнем регистре или нормализованный телефон.
     */
    private function normalizeLoginKey(string $login): string
    {
        $login = trim($login);
        if ($login === '') {
            return '';
        }

        return str_contains($login, '@')
            ? strtolower($login)
            : $this->normalizePhone($login);
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
