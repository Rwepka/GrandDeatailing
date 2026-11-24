<?php
/**
 * Production-шаблон конфигурации GRAND DETAILING.
 *
 * Скопируйте в config/config.php на сервере и заполните доступы к БД:
 *   cp config/config.production.example.php config/config.php
 *
 * Файл config/config.php не коммитится и не попадает в релизный архив.
 */
declare(strict_types=1);

return [
    'app' => [
        'name' => 'GRAND DETAILING',
        // Боевой URL без слэша на конце.
        'url' => 'https://grand-detailing.ru',
        // На production ошибки не показываем пользователю, только пишем в лог.
        'debug' => false,
        // true, если TLS терминируется на прокси/хостинге.
        'force_https' => true,
    ],
    'db' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'grand_detailing',
        'user' => 'db_user',
        'pass' => 'db_password',
        'charset' => 'utf8mb4',
    ],
];
