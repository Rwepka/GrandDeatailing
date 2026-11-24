<?php
/**
 * Скопируйте этот файл в config.php и заполните реальные значения доступа к БД.
 *
 * Шаблон конфигурации GRAND DETAILING.
 * Файл config/config.php не коммитится (см. .gitignore) — он содержит
 * реальные пароли базы данных.
 */
declare(strict_types=1);

return [
    'app' => [
        'name' => 'GRAND DETAILING',
        // Базовый URL без слэша на конце (например http://localhost или https://grand.ru)
        'url' => 'http://localhost',
        'debug' => true,
        // На production: debug=false; force_https=true, если TLS терминируется прокси.
        'force_https' => false,
    ],
    'db' => [
        'host' => '127.0.0.1', // на Windows-локально без IPv6-таймаута; на Linux-хостинге допустим localhost
        'port' => '3306',
        'name' => 'grand_detailing',
        'user' => 'db_user',
        'pass' => 'db_password',
        'charset' => 'utf8mb4',
    ],
];
