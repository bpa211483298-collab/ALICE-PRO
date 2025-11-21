@echo off
:: DEEDS Deployment Script
:: This script prepares the application for production deployment

setlocal enabledelayedexpansion

:: Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges...
) else (
    echo Please run this script as administrator!
    pause
    exit /b 1
)

echo ===== DEEDS Deployment =====
echo.

:: Step 1: Install/Update Dependencies
echo [1/7] Installing/Updating Composer Dependencies...
call composer install --no-dev --optimize-autoloader --no-interaction
if %errorLevel% neq 0 (
    echo Error installing dependencies. Check composer.json and try again.
    pause
    exit /b 1
)

:: Step 2: Generate Application Key
echo.
echo [2/7] Generating Application Key...
if not exist .env (
    copy .env.example .env
)
call php artisan key:generate --force

:: Step 3: Optimize Configuration
echo.
echo [3/7] Optimizing Configuration...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

:: Step 4: Database Migrations
echo.
echo [4/7] Running Database Migrations...
call php artisan migrate --force

:: Step 5: Storage Link
echo.
echo [5/7] Creating Storage Link...
if not exist public\storage (
    call php artisan storage:link
)

:: Step 6: Set Permissions
echo.
echo [6/7] Setting File Permissions...
icacls . /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls bootstrap/cache /grant "IIS_IUSRS:(OI)(CI)F" /T

:: Step 7: Restart Services
echo.
echo [7/7] Restarting Web Server...
net stop W3SVC
net start W3SVC

:: Complete
echo.
echo ===== Deployment Complete! =====
echo.
echo Application deployed successfully.
echo URL: http://deeds.local
echo.
pause
