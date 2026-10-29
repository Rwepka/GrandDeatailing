<?php
/**
 * Точка загрузки приложения: хелперы + автозагрузчик классов.
 */
declare(strict_types=1);

require_once BASE_PATH . '/src/helpers.php';

// Режим отладки: в production (debug = false) не показываем детали ошибок пользователю,
// а пишем их в лог сервера.
$debug = (bool) config('app.debug', false);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Старт сессии с безопасными параметрами cookie (нужна для CSRF-защиты форм).
if (session_status() === PHP_SESSION_NONE) {
    // Кастомный каталог сессий: системный путь может быть недоступен для записи.
    session_save_path(BASE_PATH . '/storage/sessions');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

spl_autoload_register(function (string $class): void {
    // App\Core\Router -> src/Core/Router.php
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = BASE_PATH . '/src/' . $relative . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

// Автоматический вход по «запомнить меня» (если пользователь ещё не в сессии).
autoLoginFromRemember();
