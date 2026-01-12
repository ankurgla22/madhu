#!/bin/bash

# Madhu Spices Japan - Deployment Script
# Usage: ./deploy.sh [setup|update|backup|restore|logs|status]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Check if .env exists
check_env() {
    if [ ! -f .env ]; then
        log_error ".env file not found! Copy .env.example to .env and configure it."
        exit 1
    fi
}

# Initial server setup
setup() {
    log_info "Starting initial setup..."

    check_env

    # Create wp-config.php if not exists
    if [ ! -f wp-config.php ]; then
        log_info "Creating wp-config.php from sample..."
        if [ -f wp-config-sample.php ]; then
            cp wp-config-sample.php wp-config.php
            log_warn "Please edit wp-config.php with your database credentials!"
        fi
    fi

    # Build and start containers
    log_info "Building and starting containers..."
    docker-compose -f docker-compose.prod.yml up -d --build

    log_info "Waiting for services to be ready..."
    sleep 30

    # Show status
    status

    log_info "Setup complete!"
    log_info "Your site should be available at https://\${DOMAIN} (check .env for domain)"
}

# Update deployment (pull latest and restart)
update() {
    log_info "Updating deployment..."

    check_env

    # Pull latest code
    log_info "Pulling latest code from GitHub..."
    git pull origin main

    # Rebuild and restart containers
    log_info "Rebuilding containers..."
    docker-compose -f docker-compose.prod.yml up -d --build

    # Clear WordPress cache
    log_info "Clearing WordPress cache..."
    docker exec madhu-wordpress wp cache flush --allow-root 2>/dev/null || true

    log_info "Update complete!"
}

# Create backup
backup() {
    log_info "Creating backup..."

    BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"

    # Database backup
    log_info "Backing up database..."
    docker exec madhu-mysql mysqldump -uwordpress -p"${DB_PASSWORD:-wordpress_password}" --no-tablespaces wordpress > "$BACKUP_DIR/database.sql" 2>/dev/null

    # Uploads backup (if not too large)
    if [ -d "wp-content/uploads" ]; then
        log_info "Backing up uploads..."
        tar -czf "$BACKUP_DIR/uploads.tar.gz" wp-content/uploads 2>/dev/null || true
    fi

    log_info "Backup saved to $BACKUP_DIR"
}

# Restore from backup
restore() {
    if [ -z "$1" ]; then
        log_error "Usage: ./deploy.sh restore <backup_directory>"
        log_info "Available backups:"
        ls -la backups/ 2>/dev/null || echo "No backups found"
        exit 1
    fi

    BACKUP_DIR="$1"

    if [ ! -d "$BACKUP_DIR" ]; then
        log_error "Backup directory not found: $BACKUP_DIR"
        exit 1
    fi

    log_warn "This will overwrite the current database. Continue? (y/N)"
    read -r confirm
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        log_info "Restore cancelled."
        exit 0
    fi

    # Restore database
    if [ -f "$BACKUP_DIR/database.sql" ]; then
        log_info "Restoring database..."
        docker exec -i madhu-mysql mysql -uwordpress -p"${DB_PASSWORD:-wordpress_password}" wordpress < "$BACKUP_DIR/database.sql"
    fi

    # Restore uploads
    if [ -f "$BACKUP_DIR/uploads.tar.gz" ]; then
        log_info "Restoring uploads..."
        tar -xzf "$BACKUP_DIR/uploads.tar.gz"
    fi

    log_info "Restore complete!"
}

# Show logs
logs() {
    docker-compose -f docker-compose.prod.yml logs -f --tail=100
}

# Show status
status() {
    log_info "Container Status:"
    docker-compose -f docker-compose.prod.yml ps
    echo ""
    log_info "Disk Usage:"
    docker system df
}

# Stop all containers
stop() {
    log_info "Stopping containers..."
    docker-compose -f docker-compose.prod.yml down
    log_info "Containers stopped."
}

# Show help
help() {
    echo "Madhu Spices Japan - Deployment Script"
    echo ""
    echo "Usage: ./deploy.sh <command>"
    echo ""
    echo "Commands:"
    echo "  setup    - Initial server setup (first time deployment)"
    echo "  update   - Pull latest code and redeploy"
    echo "  backup   - Create database and uploads backup"
    echo "  restore  - Restore from backup"
    echo "  logs     - View container logs"
    echo "  status   - Show container status"
    echo "  stop     - Stop all containers"
    echo "  help     - Show this help message"
}

# Main
case "${1:-help}" in
    setup)  setup ;;
    update) update ;;
    backup) backup ;;
    restore) restore "$2" ;;
    logs)   logs ;;
    status) status ;;
    stop)   stop ;;
    help)   help ;;
    *)      log_error "Unknown command: $1"; help; exit 1 ;;
esac
