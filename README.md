# Madhu Spices Japan

E-commerce website for Madhu Spices Japan built with WordPress and WooCommerce.

## Tech Stack

- **CMS:** WordPress 6.9
- **E-commerce:** WooCommerce 10.4.3
- **Theme:** OceanWP with custom child theme
- **Page Builder:** Elementor
- **Database:** MySQL 8.0
- **Caching:** WP Super Cache + OPcache (file-based caching for production)

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

5. Import database (if you have a backup):
```bash
docker exec -i madhu-db mysql -uwordpress -pwordpress_password wordpress < your-backup.sql
```

6. Access the site:
- Frontend: http://localhost:8080
- Admin: http://localhost:8080/wp-admin
- phpMyAdmin: http://localhost:8081

### Docker Services

| Service | Port | Description |
|---------|------|-------------|
| WordPress | 8080 | Main application |
| MySQL | 3306 | Database |
| phpMyAdmin | 8081 | Database management |

## Project Structure

```
web/
├── wp-admin/           # WordPress admin
├── wp-content/
│   ├── plugins/        # WordPress plugins
│   ├── themes/
│   │   ├── oceanwp/           # Parent theme
│   │   └── oceanwp-child/     # Custom child theme
│   └── uploads/        # Media files (not in git)
├── wp-includes/        # WordPress core
├── docker-compose.yml  # Docker configuration
├── Dockerfile          # Custom WordPress image
└── .htaccess           # Apache configuration
```

## Custom Child Theme

The `oceanwp-child` theme includes UI/UX improvements:

- Sticky header with shrink effect
- Consistent button styles
- Mobile-optimized touch targets (48px min)
- Floating cart button for mobile
- Checkout progress indicator
- Accessibility improvements
- Typography enhancements

## Deployment

For GoDaddy WordPress hosting:

1. Upload files via FTP/SFTP
2. Import database via phpMyAdmin
3. Update `wp-config.php` with production credentials
4. Run search-replace for URLs if needed

## Environment Variables

Create a `.env` file or set in `wp-config.php`:

```
WORDPRESS_DB_HOST=your-db-host
WORDPRESS_DB_USER=your-db-user
WORDPRESS_DB_PASSWORD=your-db-password
WORDPRESS_DB_NAME=your-db-name
```
