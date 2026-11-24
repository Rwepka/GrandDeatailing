<?php
/**
 * Отправка URL сайта в IndexNow (Яндекс, Bing).
 *
 * Запуск после деплоя:
 *   php scripts/indexnow.php
 *
 * Скрипт берёт адреса из живого sitemap.xml, ключ — из файла
 * public/<key>.txt (он же доступен поисковикам для проверки).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$root = dirname(__DIR__);
$config = require $root . '/config/config.php';
$baseUrl = rtrim((string) ($config['app']['url'] ?? ''), '/');
if ($baseUrl === '') {
    fwrite(STDERR, "Не задан app.url в config/config.php\n");
    exit(1);
}

$keyFiles = glob($root . '/public/*.txt') ?: [];
$key = '';
foreach ($keyFiles as $file) {
    $name = basename($file, '.txt');
    if (preg_match('/^[a-f0-9]{32}$/', $name) === 1) {
        $key = trim((string) file_get_contents($file));
        break;
    }
}
if ($key === '') {
    fwrite(STDERR, "Не найден ключ IndexNow: public/<32 hex>.txt\n");
    exit(1);
}

$sitemap = @file_get_contents($baseUrl . '/sitemap.xml');
$urls = [];
if (is_string($sitemap) && $sitemap !== '') {
    if (preg_match_all('#<loc>([^<]+)</loc>#', $sitemap, $matches) > 0) {
        $urls = array_map('trim', $matches[1]);
    }
}
if ($urls === []) {
    $urls = [$baseUrl . '/'];
    fwrite(STDERR, "Не удалось прочитать sitemap, отправляю только главную.\n");
}

$payload = [
    'host' => (string) parse_url($baseUrl, PHP_URL_HOST),
    'key' => $key,
    'keyLocation' => $baseUrl . '/' . $key . '.txt',
    'urlList' => array_values(array_unique($urls)),
];
$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (in_array('--dry-run', $argv ?? [], true)) {
    echo $json . PHP_EOL;
    echo 'URLs: ' . count($payload['urlList']) . PHP_EOL;
    exit(0);
}

$endpoints = [
    'Yandex' => 'https://yandex.com/indexnow',
    'Bing' => 'https://api.indexnow.org/indexnow',
];

foreach ($endpoints as $name => $endpoint) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo $name . ': HTTP ' . $status;
    if ($error !== '') {
        echo ' (' . $error . ')';
    }
    if (is_string($response) && trim($response) !== '') {
        echo ' — ' . trim($response);
    }
    echo PHP_EOL;
}

echo 'URLs: ' . count($payload['urlList']) . PHP_EOL;
