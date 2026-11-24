<?php
/**
 * Сборка production-архива без локального конфига, сессий и тестовых файлов.
 *
 * Запуск:
 *   php scripts/build-release.php
 *   php scripts/build-release.php C:\path\to\release
 *
 * В архив попадают только рабочие файлы, шаблоны конфигурации, миграции,
 * документация по деплою и служебные скрипты. config/config.php,
 * storage/sessions, storage/uploads, public/uploads и demo-seed исключены.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Только CLI.\n");
    exit(1);
}

if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "FAIL: расширение zip не установлено.\n");
    exit(1);
}

$root = dirname(__DIR__);
$outDir = $argv[1] ?? dirname($root) . '/release';
$outDir = rtrim($outDir, '/\\');
if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "FAIL: не удалось создать {$outDir}\n");
    exit(1);
}

$shortHash = 'nogit';
$lines = [];
$exitCode = 0;
@exec('git -C ' . escapeshellarg($root) . ' rev-parse --short HEAD 2>&1', $lines, $exitCode);
if ($exitCode === 0 && !empty($lines[0])) {
    $shortHash = trim((string) $lines[0]);
}

$stamp = date('Ymd-His');
$zipName = sprintf('grand-detailing-%s-%s.zip', $shortHash, $stamp);
$zipPath = $outDir . DIRECTORY_SEPARATOR . $zipName;

$include = [
    '.htaccess',
    'README.md',
    'public',
    'src',
    'views',
    'database/schema.sql',
    'database/migrations',
    'scripts/indexnow.php',
    'scripts/preflight.php',
    'scripts/cleanup_unverified_clients.php',
    'config/config.example.php',
    'config/config.production.example.php',
    'docs/SEO-DEPLOY.md',
    'docs/RELEASE-CHECKLIST.md',
    'storage/sessions/.gitkeep',
    'storage/uploads/.gitkeep',
    'public/uploads/.gitkeep',
];

$skipFile = static function (string $relative): bool {
    $relative = str_replace('\\', '/', $relative);
    if ($relative === 'config/config.php') {
        return true;
    }
    if ($relative === 'database/seed.sql') {
        return true;
    }
    if (preg_match('#^storage/(sessions|uploads)/#', $relative) && !str_ends_with($relative, '/.gitkeep')) {
        return true;
    }
    if (preg_match('#^public/uploads/#', $relative) && !str_ends_with($relative, '/.gitkeep')) {
        return true;
    }
    if (preg_match('#(^|/)(\.DS_Store|Thumbs\.db)$#', $relative)) {
        return true;
    }
    if (str_ends_with($relative, '.log')) {
        return true;
    }
    return false;
};

$files = [];
$collect = static function (string $relative) use (&$collect, &$files, $root, $skipFile): void {
    $relative = str_replace('\\', '/', $relative);
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (is_file($absolute)) {
        if (!$skipFile($relative)) {
            $files[] = $relative;
        }
        return;
    }
    if (!is_dir($absolute)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolute, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        $path = str_replace('\\', '/', $file->getPathname());
        $base = str_replace('\\', '/', $root) . '/';
        if (!str_starts_with($path, $base)) {
            continue;
        }
        $rel = substr($path, strlen($base));
        if (!$skipFile($rel)) {
            $files[] = $rel;
        }
    }
};

foreach ($include as $path) {
    $collect($path);
}
$files = array_values(array_unique($files));
sort($files, SORT_STRING);

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "FAIL: не удалось открыть {$zipPath}\n");
    exit(1);
}

$manifest = [
    'GRAND DETAILING — production release',
    'Commit: ' . $shortHash,
    'Built: ' . date('c'),
    '',
];
$totalBytes = 0;
foreach ($files as $relative) {
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($absolute)) {
        continue;
    }
    $zip->addFile($absolute, $relative);
    $size = filesize($absolute);
    $totalBytes += $size;
    $manifest[] = sprintf('%s  %s  %d bytes', hash_file('sha256', $absolute), $relative, $size);
}
$manifest[] = '';
$manifest[] = 'Files: ' . count($files);
$zip->addFromString('MANIFEST.txt', implode(PHP_EOL, $manifest));
$zip->close();

$zipHash = hash_file('sha256', $zipPath);
file_put_contents($zipPath . '.sha256', $zipHash . '  ' . $zipName . PHP_EOL);

printf("Release: %s\n", $zipPath);
printf("SHA256: %s\n", $zipHash);
printf("Files: %d, source size: %s\n", count($files), number_format($totalBytes / 1024, 1, '.', ' ') . ' KiB');
