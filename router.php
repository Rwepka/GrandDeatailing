<?php
/**
 * Router для встроенного сервера PHP (только для локальной разработки).
 *
 * Запуск из папки site/:
 *   php -S 127.0.0.1:8000 -t public router.php
 *
 * Отдаёт существующие статические файлы (css/js/img) напрямую,
 * остальные запросы направляет во фронт-контроллер public/index.php.
 */
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Существующий статический файл внутри public/ — отдаём как есть.
$file = __DIR__ . '/public' . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/public/index.php';
