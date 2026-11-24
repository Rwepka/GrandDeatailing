<?php
/**
 * Проверка готовности сайта к production.
 *
 * Запуск на сервере (после создания config/config.php):
 *   php scripts/preflight.php
 *
 * Локальная проверка (URL/debug проверяются мягко):
 *   php scripts/preflight.php --local
 *
 * Код выхода: 0 — всё критичное в порядке; 1 — есть блокирующие ошибки.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$isLocal = in_array('--local', $argv ?? [], true);
$configArg = null;
foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--config=')) {
        $configArg = substr($arg, strlen('--config='));
    }
}

$results = [];
$add = static function (string $name, bool $ok, string $detail = '', bool $warning = false) use (&$results): void {
    $results[] = [
        'name' => $name,
        'ok' => $ok,
        'detail' => $detail,
        'warning' => $warning,
    ];
};

$configFile = $configArg !== null
    ? (str_starts_with($configArg, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $configArg) ? $configArg : $root . '/' . $configArg)
    : $root . '/config/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR, "FAIL: config/config.php не найден. Скопируйте config/config.production.example.php.\n");
    exit(1);
}

try {
    $config = require $configFile;
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: config/config.php не загружается: ' . $e->getMessage() . "\n");
    exit(1);
}

$app = is_array($config['app'] ?? null) ? $config['app'] : [];
$db = is_array($config['db'] ?? null) ? $config['db'] : [];

// --- PHP и расширения ---
$add('PHP 8.3+', PHP_VERSION_ID >= 80300, PHP_VERSION);
$requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'curl'];
foreach ($requiredExtensions as $extension) {
    $add('Расширение ' . $extension, extension_loaded($extension), extension_loaded($extension) ? 'загружено' : 'не загружено');
}

// --- Приложение ---
$appUrl = rtrim((string) ($app['url'] ?? ''), '/');
$urlHost = parse_url($appUrl, PHP_URL_HOST);
$urlScheme = parse_url($appUrl, PHP_URL_SCHEME);
$add('APP URL задан', $appUrl !== '', $appUrl !== '' ? $appUrl : 'пусто');
if ($isLocal) {
    $add('APP URL production-формат', true, 'пропущено в --local', true);
} else {
    $add(
        'APP URL production-формат',
        $urlScheme === 'https' && is_string($urlHost) && !in_array($urlHost, ['localhost', '127.0.0.1'], true),
        'нужен https:// на боевом домене'
    );
}
$add('APP URL без слэша на конце', !str_ends_with((string) ($app['url'] ?? ''), '/'), 'слэш на конце не нужен');
if ($isLocal) {
    $add('APP DEBUG выключен', true, 'пропущено в --local', true);
} else {
    $add('APP DEBUG выключен', ($app['debug'] ?? true) === false, 'на проде debug=false');
}

// --- Подключение к БД ---
$pdo = null;
if (!is_array($db) || empty($db['host']) || empty($db['name']) || empty($db['user'])) {
    $add('Параметры БД', false, 'не заполнены host/name/user');
} else {
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'] ?? '3306',
            $db['name'],
            $db['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, (string) $db['user'], (string) ($db['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $add('Подключение к БД', true, 'OK');
    } catch (Throwable $e) {
        $add('Подключение к БД', false, 'проверьте host/name/user/pass');
    }
}

if ($pdo instanceof PDO) {
    $tables = [];
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $add('Список таблиц', false, 'не удалось прочитать SHOW TABLES');
    }

    foreach (['services', 'service_prices', 'faqs', 'bookings'] as $table) {
        $add('Таблица ' . $table, in_array($table, $tables, true), in_array($table, $tables, true) ? 'есть' : 'нет');
    }

    if (in_array('services', $tables, true)) {
        try {
            $columns = $pdo->query('SHOW COLUMNS FROM services')->fetchAll(PDO::FETCH_COLUMN);
            foreach (['short_description', 'description', 'includes', 'process', 'price_from'] as $column) {
                $add('services.' . $column, in_array($column, $columns, true), in_array($column, $columns, true) ? 'есть' : 'нет');
            }
        } catch (Throwable $e) {
            $add('Колонки services', false, 'не удалось прочитать структуру');
        }
    }

    if (in_array('faqs', $tables, true)) {
        try {
            $columns = $pdo->query('SHOW COLUMNS FROM faqs')->fetchAll(PDO::FETCH_COLUMN);
            foreach (['question', 'answer', 'sort_order', 'is_active'] as $column) {
                $add('faqs.' . $column, in_array($column, $columns, true), in_array($column, $columns, true) ? 'есть' : 'нет');
            }
        } catch (Throwable $e) {
            $add('Колонки faqs', false, 'не удалось прочитать структуру');
        }
    }
}

// --- Файлы и права ---
$requiredFiles = [
    'public/index.php',
    'public/.htaccess',
    'public/139cc868f917b8ceb8beb05e7f239773.txt',
    'src/bootstrap.php',
];
foreach ($requiredFiles as $file) {
    $path = $root . '/' . $file;
    $add('Файл ' . $file, is_file($path), is_file($path) ? 'есть' : 'нет');
}

foreach (['storage/sessions', 'storage/uploads', 'public/uploads'] as $dir) {
    $path = $root . '/' . $dir;
    $add('Каталог на запись ' . $dir, is_dir($path) && is_writable($path), is_dir($path) ? (is_writable($path) ? 'запись OK' : 'нет записи') : 'нет каталога');
}

// --- Вывод ---
$failed = 0;
$warnings = 0;
foreach ($results as $result) {
    if ($result['ok']) {
        $status = $result['warning'] ? 'SKIP' : 'OK';
    } else {
        $status = 'FAIL';
        $failed++;
    }
    if ($result['warning']) {
        $warnings++;
    }
    printf("[%s] %s%s\n", $status, $result['name'], $result['detail'] !== '' ? ' — ' . $result['detail'] : '');
}

printf("\nИтог: %d проверок, ошибок: %d, предупреждений: %d.\n", count($results), $failed, $warnings);
if ($failed > 0) {
    fwrite(STDERR, "Preflight не пройден. Исправьте FAIL-пункты перед выкладкой.\n");
    exit(1);
}

echo "Preflight пройден.\n";
exit(0);
