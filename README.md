# Madhu Spices Japan

E-commerce website for Madhu Spices Japan built with WordPress and WooCommerce.

## Tech Stack

- **CMS:** WordPress 6.9
- **E-commerce:** WooCommerce 10.4.3
- **Theme:** OceanWP 4.1.4
- **Page Builder:** Elementor
- **Database:** MySQL 8.0
- **Caching:** WP Super Cache + OPcache
- **SSL:** Let's Encrypt (automatic)

## Local Development

### Prerequisites

- Docker Desktop
- Git

### Setup

1. Clone the repository:
```bash
git clone https://github.com/ankurgla22/madhu.git
cd madhu/web
```

2. Create `wp-config.php` from sample:
```bash
cp wp-config-sample.php wp-config.php
```

3. Update database credentials in `wp-config.php`:
```php
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress_password');
define('DB_HOST', 'db:3306');
```

4. Start Docker containers:
```bash
docker-compose up -d
```

5. Access the site:
- Frontend: http://localhost:8080
- Admin: http://localhost:8080/wp-admin
- phpMyAdmin: http://localhost:8081

## Production Deployment (Oracle Cloud)

### Server Requirements

- Ubuntu 22.04 LTS
- Docker & Docker Compose installed
- Domain pointed to server IP
- Ports 80 and 443 open in security rules

### Initial Server Setup

1. **Install Docker on Ubuntu:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Logout and login again for group changes
```

2. **Clone repository:**
```bash
git clone https://github.com/ankurgla22/madhu.git
cd madhu/web
```

3. **Configure environment:**
```bash
cp .env.example .env
nano .env  # Edit with your values
```

4. **Configure wp-config.php:**
```bash
cp wp-config-sample.php wp-config.php
nano wp-config.php
```

Update with production database credentials and add:
```php
// Force HTTPS
define('FORCE_SSL_ADMIN', true);
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Site URLs
define('WP_HOME', 'https://yourdomain.com');
define('WP_SITEURL', 'https://yourdomain.com');
```

5. **Deploy:**
```bash
chmod +x deploy.sh
./deploy.sh setup
```

### Updating Production

After making changes locally and pushing to GitHub:

```bash
# On server
cd ~/madhu/web
./deploy.sh update
```

### Deployment Commands

| Command | Description |
|---------|-------------|
| `./deploy.sh setup` | Initial server setup |
| `./deploy.sh update` | Pull latest & redeploy |
| `./deploy.sh backup` | Create backup |
| `./deploy.sh restore <dir>` | Restore from backup |
| `./deploy.sh logs` | View container logs |
| `./deploy.sh status` | Show container status |
| `./deploy.sh stop` | Stop all containers |

### Oracle Cloud Security Rules

Open these ports in your VCN Security List:
- Port 80 (HTTP) - for SSL certificate verification
- Port 443 (HTTPS) - for secure traffic

### SSL Certificates

SSL certificates are automatically managed by Let's Encrypt via nginx-proxy-acme-companion. Certificates will auto-renew before expiration.

## Project Structure

```
web/
├── wp-content/
│   ├── plugins/        # WordPress plugins
│   ├── themes/
│   │   └── oceanwp/    # OceanWP theme
│   └── uploads/        # Media files (not in git)
├── docker-compose.yml      # Local development
├── docker-compose.prod.yml # Production with SSL
├── Dockerfile              # Custom WordPress image
├── deploy.sh               # Deployment script
├── .env.example            # Environment template
└── nginx-custom.conf       # Nginx configuration
```

## Workflow

1. **Local Development:**
   - Make changes locally
   - Test at http://localhost:8080
   - Commit and push to GitHub

2. **Deploy to Production:**
   - SSH to Oracle Cloud server
   - Run `./deploy.sh update`
   - Changes are live!

## Backup Strategy

- Database backups: `./deploy.sh backup`
- Backups stored in `backups/` directory
- Recommended: Set up automated backups via cron

```bash
# Add to crontab (daily backup at 2 AM)
0 2 * * * cd ~/madhu/web && ./deploy.sh backup
```
