@echo off
setlocal

:: Use the directory where this script is located
set PROJECT_DIR=%~dp0

set PHP_URL=http://localhost:6060
set PMA_URL=http://localhost:6061

:menu
cls
echo.
echo   +===============================+
echo   ^|     Docker Manager           ^|
echo   +===============================+
echo.
echo   Project: %PROJECT_DIR%
echo.
echo   [1] Build
echo   [2] Start
echo   [3] Stop
echo   [4] Status
echo   [5] Exit
echo.
set /p choice="  Enter choice: "

if "%choice%"=="1" goto build
if "%choice%"=="2" goto start
if "%choice%"=="3" goto stop
if "%choice%"=="4" goto status
if "%choice%"=="5" goto exit
echo   Invalid option, try again.
timeout /t 1 /nobreak >nul
goto menu

:check_compose
cd /d "%PROJECT_DIR%"
if not exist "docker-compose.yml" (
    echo.
    echo   ERROR: docker-compose.yml not found in %PROJECT_DIR%
    echo.
    pause
    goto menu
)
docker info >nul 2>&1
if errorlevel 1 (
    echo.
    echo   ERROR: Docker is not running. Start Docker Desktop first.
    echo.
    pause
    goto menu
)
goto :eof

:build
cls
echo.
echo   Building Docker containers...
echo.
call :check_compose
docker compose down
docker compose pull
docker compose build --no-cache
docker compose up -d
echo.
echo   Build complete!
echo   PHP App    : %PHP_URL%
echo   phpMyAdmin : %PMA_URL%
echo.
pause
goto menu

:start
cls
echo.
echo   Starting containers...
echo.
call :check_compose
docker compose up -d
echo.
echo   Containers started!
echo   PHP App    : %PHP_URL%
echo   phpMyAdmin : %PMA_URL%
echo.
pause
goto menu

:stop
cls
echo.
echo   Stopping containers...
echo.
call :check_compose
docker compose down
echo.
echo   Containers stopped.
echo.
pause
goto menu

:status
cls
echo.
echo   Container Status:
echo.
call :check_compose
docker compose ps
echo.
docker stats --no-stream
echo.
pause
goto menu

:exit
cls
echo.
echo   Bye!
echo.
exit /b 0