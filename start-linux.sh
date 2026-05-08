#!/bin/bash

echo "🐳 Starting Docker containers..."

# Start Docker service if not running
if ! systemctl is-active --quiet docker; then
    echo "🔧 Starting Docker service..."
    sudo systemctl start docker
fi

# Start containers
sudo docker compose up -d

echo ""
echo "✅ Containers are up!"
echo ""
sudo docker compose ps
echo ""
echo "🌐 PHP App    → http://localhost:6060"
echo "🛠️  phpMyAdmin → http://localhost:6061"