<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Message;
use App\Model\RateLimit;

class ContactController
{
    public function index(): void
    {
        // Точка отсчёта для time-trap: замер времени заполнения формы.
        $_SESSION['contact_form_time'] = time();
        View::render('contacts', [
            'title' => 'Контакты',
            'errors' => [],
            'old' => [],
            'flash' => flash(),
        ]);
    }

    public function store(): void
    {
        // --- Honeypot: скрытое поле-ловушка для ботов ---
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            redirect('/contacts');
        }

        // --- Time-trap: сообщение не может быть заполнено быстрее 3 секунд ---
        $formTime = (int) ($_SESSION['contact_form_time'] ?? 0);
        if ($formTime === 0 || (time() - $formTime) < 3) {
            redirect('/contacts');
        }

        // --- Лимит отправок по IP ---
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (RateLimit::tooMany($ip)) {
            View::render('contacts', [
                'title' => 'Контакты',
                'errors' => ['Слишком много сообщений. Попробуйте позже.'],
                'old' => [],
                'flash' => null,
            ]);
            return;
        }

        // Исходные значения для повторного заполнения формы при ошибках.
        $old = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'text' => trim((string) ($_POST['text'] ?? '')),
        ];

        // CSRF-защита.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('contacts', [
                'title' => 'Контакты',
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
                'flash' => null,
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
            $phone = normalize_phone($phoneRaw);
            $digits = preg_replace('/\D+/', '', $phone);
            if (preg_match('/^\d{10,15}$/', $digits) !== 1) {
                $errors[] = 'Номер телефона должен содержать от 10 до 15 цифр.';
            }
        }

        // --- Email (необязательно) ---
        $email = null;
        $emailRaw = trim((string) ($_POST['email'] ?? ''));
        if ($emailRaw !== '') {
            if (filter_var($emailRaw, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = 'Укажите корректный email.';
            } else {
                $email = $emailRaw;
            }
        }

        // --- Текст сообщения (необязательно) ---
        $textRaw = trim((string) ($_POST['text'] ?? ''));
        $text = $textRaw === '' ? null : $textRaw;

        if ($errors !== []) {
            View::render('contacts', [
                'title' => 'Контакты',
                'errors' => $errors,
                'old' => $old,
                'flash' => null,
            ]);
            return;
        }

        Message::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'text' => $text,
        ]);

        RateLimit::record($ip);
        setFlash('Сообщение отправлено. Мы свяжемся с вами.');
        redirect('/contacts');
    }
}
