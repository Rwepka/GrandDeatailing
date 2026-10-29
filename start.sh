#!/usr/bin/env bash
# Запуск сайта GRAND DETAILING на встроенном сервере PHP.
# Использование: ./start.sh   (можно задать порт: PORT=8080 ./start.sh)
set -e
cd "$(dirname "$0")"

HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8000}"

if ! command -v php >/dev/null 2>&1; then
    echo "Ошибка: PHP не найден. Установите PHP 8.3+ и добавьте его в PATH." >&2
    exit 1
fi

echo "GRAND DETAILING — запуск встроенного сервера PHP"
echo "Откройте в браузере: http://${HOST}:${PORT}"
echo "Остановка: Ctrl+C"
exec php -S "${HOST}:${PORT}" -t public router.php
