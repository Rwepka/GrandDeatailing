<?php
/**
 * Глобальные функции-помощники.
 */
declare(strict_types=1);

/**
 * Чтение значения из конфигурации по точечному ключу: config('db.host').
 */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/config/config.php';
    }
    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

/**
 * Экранирование строки для вывода в HTML (защита от XSS).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Форматирование цены: «от 8 000 ₽» или «цена по запросу».
 */
function price(?float $value, string $prefix = 'от '): string
{
    return $value === null
        ? 'цена по запросу'
        : $prefix . number_format($value, 0, ',', ' ') . ' ₽';
}

/**
 * URL внутри сайта (относительно корня): url('/booking') → «/booking».
 * Относительные ссылки корректно работают на любом хосте и порту.
 */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/**
 * Перенаправление (POST/Redirect/Get) с завершением скрипта.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * URL до статического ресурса: asset('css/style.css').
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * CSRF-токен текущей сессии (создаётся при первом обращении).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Скрытое поле формы с CSRF-токеном.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Проверка CSRF-токена (защита от подделки POST-запросов).
 */
function csrf_verify(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Текущий авторизованный пользователь (или null).
 * @return array<string, mixed>|null
 */
function auth(): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId === null) {
        return null;
    }
    $user = \App\Model\User::find((int) $userId);
    return $user ?: null;
}

/**
 * Авторизован ли пользователь.
 */
function isAuth(): bool
{
    return auth() !== null;
}

/**
 * Вход: сохраняем id пользователя в сессии (с регенерацией id сессии).
 */
function login(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

/**
 * Выход из учётной записи.
 */
function logout(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Защита маршрута: если пользователь не авторизован — перенаправляем на /login.
 */
function requireAuth(): void
{
    if (!isAuth()) {
        redirect('/login');
    }
}

/**
 * Является ли текущий пользователь администратором (role_id = 2).
 */
function isAdmin(): bool
{
    $user = auth();
    return $user !== null && (int) ($user['role_id'] ?? 0) === 2;
}

/**
 * Защита маршрута админ-панели: требует авторизации и роли администратора.
 */
function requireAdmin(): void
{
    requireAuth();
    if (!isAdmin()) {
        redirect('/cabinet');
    }
}

/**
 * Является ли текущий пользователь мастером (role_id = 3).
 */
function isMaster(): bool
{
    $user = auth();
    return $user !== null && (int) ($user['role_id'] ?? 0) === 3;
}

/**
 * Защита маршрута для персонала (администратор или мастер).
 */
function requireStaff(): void
{
    requireAuth();
    if (!isAdmin() && !isMaster()) {
        redirect('/cabinet');
    }
}

/**
 * «Запомнить меня»: генерируем токен, храним в БД только его sha256-хэш,
 * сам токен отдаём пользователю в cookie.
 */
function rememberUser(int $userId): void
{
    $token = bin2hex(random_bytes(32));
    \App\Model\RememberToken::create(
        $userId,
        hash('sha256', $token),
        date('Y-m-d H:i:s', time() + 2592000)
    );
    setcookie('remember_me', $token, [
        'expires' => time() + 2592000,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
}

/**
 * Отмена «запомнить меня»: удаляем токен из БД и очищаем cookie.
 */
function forgetRememberUser(): void
{
    $token = $_COOKIE['remember_me'] ?? '';
    if (is_string($token) && $token !== '') {
        \App\Model\RememberToken::deleteByHash(hash('sha256', $token));
    }
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
}

/**
 * Автоматический вход по cookie «запомнить меня».
 */
function autoLoginFromRemember(): void
{
    if (isAuth()) {
        return;
    }

    $token = $_COOKIE['remember_me'] ?? '';
    if (!is_string($token) || $token === '') {
        return;
    }

    $userId = \App\Model\RememberToken::findUserIdByHash(hash('sha256', $token));
    if ($userId !== null) {
        login((int) $userId);
    }
}

/**
 * Нормализация номера телефона: сохраняем ведущий «+», убираем всё нецифровое.
 */
function normalize_phone(string $phone): string
{
    $phone = trim($phone);
    $leadingPlus = '';
    if (str_starts_with($phone, '+')) {
        $leadingPlus = '+';
        $phone = substr($phone, 1);
    }
    return $leadingPlus . (string) preg_replace('/\D+/', '', $phone);
}

/**
 * Установить одноразовое сообщение (flash) для показа после редиректа.
 */
function setFlash(string $message): void
{
    $_SESSION['flash'] = $message;
}

/**
 * Получить и удалить одноразовое сообщение.
 */
function flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}
