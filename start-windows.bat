@echo off
echo Starting Docker containers...

:: Wait for Docker Desktop to be ready
echo Waiting for Docker to be ready...
:wait
docker info >nul 2>&1
if errorlevel 1 (
    timeout /t 2 /nobreak >nul
    goto wait
)

:: Start containers
docker compose up -d

echo.
echo Containers are up!
echo.
docker compose ps
echo.
echo PHP App    : http://localhost:6060
echo phpMyAdmin : http://localhost:6061
echo.
pause