@echo off
rem Запуск сайта GRAND DETAILING на встроенном сервере PHP.
rem Дважды кликните файл или выполните: start.bat
chcp 65001 >nul
cd /d "%~dp0"

set HOST=127.0.0.1
set PORT=8000

where php >nul 2>nul
if errorlevel 1 (
    echo Ошибка: PHP не найден. Установите PHP 8.3+ и добавьте его в PATH.
    pause
    exit /b 1
)

echo GRAND DETAILING — запуск встроенного сервера PHP
echo Откройте в браузере: http://%HOST%:%PORT%
echo Остановка: Ctrl+C
echo.
php -S %HOST%:%PORT% -t public router.php
pause
