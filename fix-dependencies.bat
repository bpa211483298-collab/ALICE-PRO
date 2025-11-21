@echo off
echo Fixing DEEDS Dependencies...
echo ===============================

:: Backup current dependencies
echo Backing up current dependencies...
if exist composer.json copy composer.json composer.json.bak
if exist composer.lock copy composer.lock composer.lock.bak

:: Clear composer cache
echo Clearing Composer cache...
composer clear-cache

:: Install dependencies
echo Installing dependencies...
composer install --no-scripts

:: Update autoloader
echo Updating autoloader...
composer dump-autoload --optimize

:: Generate application key
echo Generating application key...
if not exist .env (
    copy .env.example .env
)
php artisan key:generate

:: Clear configuration cache
echo Clearing configuration cache...
php artisan config:clear

:: Install node dependencies
echo Installing Node.js dependencies...
if exist package.json (
    npm install
    npm run build
)

echo.
echo ===============================
echo Dependencies fixed successfully!
echo Please run: php artisan serve
echo ===============================
pause
