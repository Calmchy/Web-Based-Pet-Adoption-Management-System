#!/bin/bash

echo "🐳 Docker Build Script"
echo "======================"

# Check if docker-compose.yml exists
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ docker-compose.yml not found in $PROJECT_DIR"
    exit 1
fi

# Stop existing containers if running
echo "🛑 Stopping existing containers..."
sudo docker compose down

# Pull latest base images
echo "📦 Pulling latest base images..."
sudo docker compose pull

# Build images
echo "🔨 Building images..."
sudo docker compose build --no-cache

# Start containers
echo "🚀 Starting containers..."
sudo docker compose up -d

# Show status
echo ""
echo "✅ Build complete!"
echo ""
sudo docker compose ps
echo ""
echo "🌐 PHP App    → http://localhost:6060"
echo "🛠️  phpMyAdmin → http://localhost:6061"