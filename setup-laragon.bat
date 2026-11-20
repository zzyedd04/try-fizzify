@echo off
REM ============================================================
REM  Fizzify - setup otomatis untuk Laragon (MySQL)
REM  Jalankan dengan klik dua kali, atau dari Terminal Laragon.
REM  Pastikan Laragon sudah "Start All" (Apache + MySQL aktif).
REM ============================================================
cd /d "%~dp0"

if not exist .env (
    copy .env.example .env >nul
    echo File .env dibuat dari .env.example
)

if not exist vendor (
    echo Menjalankan composer install...
    call composer install
)

findstr /b /c:"APP_KEY=base64" .env >nul || call php artisan key:generate --ansi

call php artisan config:clear
call php artisan fizzify:install

echo.
echo Selesai. Buka http://fizzify-laravel.test  (atau jalankan: php artisan serve)
pause
