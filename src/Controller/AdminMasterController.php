<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Database;
use App\Core\View;
use App\Model\User;

/**
 * Учётные записи мастеров в админ-панели.
 */
class AdminMasterController
{
    public function index(): void
    {
        requireAdmin();

        $stmt = Database::connection()->query("SELECT id FROM roles WHERE code = 'master'");
        $masterRoleId = (int) ($stmt->fetchColumn() ?: 3);
        $masters = User::findByRole($masterRoleId);

        View::renderAdmin('masters', [
            'title' => 'Мастера',
            'masters' => $masters,
            'flash' => flash(),
        ]);
    }

    public function create(): void
    {
        requireAdmin();

        View::renderAdmin('master_form', [
            'title' => 'Новый мастер',
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/masters');
        }

        $old = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
        ];

        $errors = [];

        // --- ФИО ---
        $name = $old['name'];
        $nameLen = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        if ($name === '') {
            $errors[] = 'Укажите ФИО мастера.';
        } elseif ($nameLen > 100) {
            $errors[] = 'ФИО не должно быть длиннее 100 символов.';
        }

        // --- Email ---
        $email = $old['email'];
        if ($email === '') {
            $errors[] = 'Укажите email.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Укажите корректный email.';
        }

        // --- Телефон ---
        $phone = normalize_phone($old['phone']);
        $digits = (string) preg_replace('/\D+/', '', $phone);
        if ($phone === '' || $old['phone'] === '') {
            $errors[] = 'Укажите номер телефона.';
        } elseif (preg_match('/^\d{10,15}$/', $digits) !== 1) {
            $errors[] = 'Укажите корректный номер телефона (10–15 цифр).';
        }

        // --- Пароль ---
        $password = (string) ($_POST['password'] ?? '');
        if ($password === '') {
            $errors[] = 'Укажите пароль.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Пароль должен содержать не менее 8 символов.';
        }

        // --- Уникальность ---
        if ($errors === []) {
            if (User::findByEmail($email) !== null) {
                $errors[] = 'Этот email уже используется.';
            }
            if (User::findByPhone($phone) !== null) {
                $errors[] = 'Этот телефон уже используется.';
            }
        }

        if ($errors !== []) {
            View::renderAdmin('master_form', [
                'title' => 'Новый мастер',
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $stmt = Database::connection()->query("SELECT id FROM roles WHERE code = 'master'");
        $masterRoleId = (int) ($stmt->fetchColumn() ?: 3);

        User::create([
            'role_id' => $masterRoleId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        setFlash('Мастер добавлен.');
        redirect('/admin/masters');
    }

    public function edit(int $id): void
    {
        requireAdmin();

        $master = User::find($id);
        if ($master === null || (int) $master['role_id'] !== $this->masterRoleId()) {
            redirect('/admin/masters');
        }

        View::renderAdmin('master_form', [
            'title' => 'Редактировать мастера',
            'master' => $master,
            'errors' => [],
            'old' => [
                'name' => (string) ($master['name'] ?? ''),
                'email' => (string) ($master['email'] ?? ''),
                'phone' => (string) ($master['phone'] ?? ''),
            ],
        ]);
    }

    public function update(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/masters');
        }

        $master = User::find($id);
        if ($master === null || (int) $master['role_id'] !== $this->masterRoleId()) {
            redirect('/admin/masters');
        }

        $old = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
        ];

        $errors = [];

        // --- ФИО ---
        $name = $old['name'];
        $nameLen = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        if ($name === '') {
            $errors[] = 'Укажите ФИО мастера.';
        } elseif ($nameLen > 100) {
            $errors[] = 'ФИО не должно быть длиннее 100 символов.';
        }

        // --- Email ---
        $email = $old['email'];
        if ($email === '') {
            $errors[] = 'Укажите email.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Укажите корректный email.';
        }

        // --- Телефон ---
        $phone = normalize_phone($old['phone']);
        $digits = (string) preg_replace('/\D+/', '', $phone);
        if ($phone === '' || $old['phone'] === '') {
            $errors[] = 'Укажите номер телефона.';
        } elseif (preg_match('/^\d{10,15}$/', $digits) !== 1) {
            $errors[] = 'Укажите корректный номер телефона (10–15 цифр).';
        }

        // --- Пароль (опционально при редактировании) ---
        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '' && strlen($password) < 8) {
            $errors[] = 'Пароль должен содержать не менее 8 символов.';
        }

        // --- Уникальность (исключая текущую запись) ---
        if ($errors === []) {
            $byEmail = User::findByEmail($email);
            if ($byEmail !== null && (int) $byEmail['id'] !== $id) {
                $errors[] = 'Этот email уже используется.';
            }
            $byPhone = User::findByPhone($phone);
            if ($byPhone !== null && (int) $byPhone['id'] !== $id) {
                $errors[] = 'Этот телефон уже используется.';
            }
        }

        if ($errors !== []) {
            View::renderAdmin('master_form', [
                'title' => 'Редактировать мастера',
                'master' => $master,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        User::updateProfile($id, $name, $email, $phone);
        if ($password !== '') {
            User::updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
        }

        setFlash('Данные мастера обновлены.');
        redirect('/admin/masters');
    }

    private function masterRoleId(): int
    {
        $stmt = Database::connection()->query("SELECT id FROM roles WHERE code = 'master'");
        return (int) ($stmt->fetchColumn() ?: 3);
    }
}
