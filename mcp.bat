@echo off
setlocal enabledelayedexpansion

:: MCP Service Manager for Windows
:: A simple script to manage MCP services

:: Colors
set RED=
set GREEN=
set YELLOW=
set NC=

:: Check if Docker is running
:check_docker
docker info >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Docker is not running. Please start Docker Desktop and try again.
    exit /b 1
)
goto :eof

:: Start services
:start
    call :check_docker
    echo [INFO] Starting MCP services...
    docker-compose up -d
    if %ERRORLEVEL% equ 0 (
        echo [SUCCESS] MCP services started!
        echo.
        echo Access the MCP Gateway at: http://localhost:8080
    ) else (
        echo [ERROR] Failed to start MCP services
    )
goto :eof

:: Stop services
:stop
    echo [INFO] Stopping MCP services...
    docker-compose down
    if %ERRORLEVEL% equ 0 (
        echo [SUCCESS] MCP services stopped.
    ) else (
        echo [ERROR] Failed to stop MCP services
    )
goto :eof

:: Restart services
:restart
    call :stop
    call :start
goto :eof

:: Show status
:status
    call :check_docker
    echo [INFO] MCP Services Status:
    docker-compose ps
    
    echo.
    echo [INFO] Service URLs:
    echo - MCP Gateway: http://localhost:8080
    echo - Git MCP:     http://localhost:8080/git/
    echo - Filesystem:  http://localhost:8080/filesystem/
    echo - Terminal:    http://localhost:8080/terminal/
    echo - Docker:      http://localhost:8080/docker/
    echo - MinIO:       http://localhost:9001 (admin/minioadmin)
    echo - PostgreSQL:  localhost:5432 (alice/alice123)
goto :eof

:: Show logs
:logs
    if "%~1"=="" (
        docker-compose logs -f
    ) else (
        docker-compose logs -f %1
    )
goto :eof

:: Run tests
:test
    php test-mcp-local.php
goto :eof

:: Show help
:help
    echo MCP Service Manager
    echo Usage: mcp.bat [command]
    echo.
    echo Commands:
    echo   start     Start all MCP services
    echo   stop      Stop all MCP services
    echo   restart   Restart all MCP services
    echo   status    Show status of MCP services
    echo   logs      Show logs ^(use: mcp.bat logs [service]^)
    echo   test      Run connectivity tests
    echo   help      Show this help message
    echo.
goto :eof

:: Main script
if "%~1"=="" (
    call :help
    exit /b 0
)

if "%~1"=="start" call :start
if "%~1"=="stop" call :stop
if "%~1"=="restart" call :restart
if "%~1"=="status" call :status
if "%~1"=="logs" call :logs %2
if "%~1"=="test" call :test
if "%~1"=="help" call :help

exit /b 0
