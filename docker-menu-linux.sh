#!/bin/bash

mkdir -p www/assets/uploads/profiles
docker exec -it php_app ls -ld /var/www/html/assets/uploads/profiles

# Use the directory where this script is located
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

PHP_URL="http://localhost:6060"
PMA_URL="http://localhost:6061"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

check_compose() {
    if [ ! -f "$PROJECT_DIR/docker-compose.yml" ]; then
        echo -e "${RED}❌ docker-compose.yml not found in $PROJECT_DIR${NC}"
        exit 1
    fi
    cd "$PROJECT_DIR"
}

print_header() {
    clear
    echo -e "${CYAN}"
    echo "  ╔═══════════════════════════════╗"
    echo "  ║     🐳 Docker Manager         ║"
    echo "  ╚═══════════════════════════════╝"
    echo -e "${NC}"
    echo -e "  📁 Project: ${YELLOW}$PROJECT_DIR${NC}\n"
}

print_menu() {
    echo -e "  ${YELLOW}[1]${NC} 🔨 Build"
    echo -e "  ${YELLOW}[2]${NC} 🚀 Start"
    echo -e "  ${YELLOW}[3]${NC} 🛑 Stop"
    echo -e "  ${YELLOW}[4]${NC} 📊 Status"
    echo -e "  ${YELLOW}[5]${NC} ❌ Exit"
    echo ""
    echo -n "  Enter choice: "
}

do_build() {
    echo -e "\n${CYAN}🔨 Building Docker containers...${NC}\n"
    check_compose

    if ! systemctl is-active --quiet docker; then
        echo "🔧 Starting Docker service..."
        sudo systemctl start docker
    fi

    sudo docker compose down
    sudo docker compose pull
    sudo docker compose build --no-cache
    sudo docker compose up -d

    echo -e "\n${GREEN}✅ Build complete!${NC}"
    echo -e "  🌐 PHP App    → $PHP_URL"
    echo -e "  🛠️  phpMyAdmin → $PMA_URL"
}

do_start() {
    echo -e "\n${CYAN}🚀 Starting containers...${NC}\n"
    check_compose

    if ! systemctl is-active --quiet docker; then
        echo "🔧 Starting Docker service..."
        sudo systemctl start docker
    fi

    sudo docker compose up -d
    echo -e "\n${GREEN}✅ Containers started!${NC}"
    echo -e "  🌐 PHP App    → $PHP_URL"
    echo -e "  🛠️  phpMyAdmin → $PMA_URL"
}

do_stop() {
    echo -e "\n${CYAN}🛑 Stopping containers...${NC}\n"
    check_compose
    sudo docker compose down
    echo -e "\n${GREEN}✅ Containers stopped.${NC}"
}

do_status() {
    echo -e "\n${CYAN}📊 Container Status:${NC}\n"
    check_compose
    sudo docker compose ps
    echo ""
    sudo docker stats --no-stream
}

while true; do
    print_header
    print_menu
    read -r choice

    case $choice in
        1) do_build ;;
        2) do_start ;;
        3) do_stop ;;
        4) do_status ;;
        5) echo -e "\n${GREEN}👋 Bye!${NC}\n"; exit 0 ;;
        *) echo -e "\n${RED}❌ Invalid option.${NC}" ;;
    esac

    echo -e "\n  Press Enter to return to menu..."
    read -r
done
