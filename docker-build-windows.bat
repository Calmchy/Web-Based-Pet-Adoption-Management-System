@echo off
echo Docker Build Script
echo ===================

:: Check if docker-compose.yml exists
if not exist "docker-compose.yml" (
    echo ERROR: docker-compose.yml not found!
    pause
    exit /b 1
)

:: Wait for Docker to be ready
echo Waiting for Docker to be ready...
:wait
docker info >nul 2>&1
if errorlevel 1 (
    timeout /t 2 /nobreak >nul
    goto wait
)

:: Stop existing containers if running
echo Stopping existing containers...
docker compose down

:: Pull latest base images
echo Pulling latest base images...
docker compose pull

:: Build images
echo Building images...
docker compose build --no-cache

:: Start containers
echo Starting containers...
docker compose up -d

:: Show status
echo.
echo Build complete!
echo.
docker compose ps
echo.
echo PHP App    : http://localhost:6060
echo phpMyAdmin : http://localhost:6061
echo.
pause