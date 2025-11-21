# DEEDS Deployment Guide

This document outlines the steps to deploy the DEEDS application to a production environment.

## Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.3+
- Node.js 16+ and NPM (for asset compilation)
- Web server (Apache/Nginx)
- SSL Certificate (recommended)

## Server Requirements

- PHP Extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## Deployment Steps

### 1. Server Setup

1. Update the system packages
2. Install required PHP extensions
3. Install and configure MySQL/MariaDB
4. Install and configure web server (Apache/Nginx)
5. Set up SSL certificate (Let's Encrypt recommended)

### 2. Application Deployment

1. Clone the repository:
   ```bash
   git clone https://your-repository-url/deeds.git /var/www/deeds
   cd /var/www/deeds
   ```

2. Install PHP dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction
   ```

3. Set up environment file:
   ```bash
   cp .env.production .env
   php artisan key:generate
   ```

4. Update environment variables in `.env`:
   - Database credentials
   - Mail server settings
   - Application URL
   - Any other environment-specific settings

5. Set proper permissions:
   ```bash
   chown -R www-data:www-data /var/www/deeds
   chmod -R 755 /var/www/deeds/storage
   chmod -R 755 /var/www/deeds/bootstrap/cache
   ```

6. Generate application key:
   ```bash
   php artisan key:generate
   ```

7. Run database migrations:
   ```bash
   php artisan migrate --force
   ```

8. Link storage:
   ```bash
   php artisan storage:link
   ```

9. Clear and cache configuration:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### 3. Web Server Configuration

#### Apache

```apache
<VirtualHost *:80>
    ServerName deeds.yourdomain.com
    ServerAdmin webmaster@deeds.yourdomain.com
    DocumentRoot /var/www/deeds/public

    <Directory /var/www/deeds/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/deeds-error.log
    CustomLog ${APACHE_LOG_DIR}/deeds-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name deeds.yourdomain.com;
    root /var/www/deeds/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4. Scheduled Tasks

Set up the Laravel scheduler to run the following command every minute:

```bash
* * * * * cd /var/www/deeds && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Queue Workers

For better performance, set up queue workers:

```bash
php artisan queue:work --queue=high,default --tries=3 --timeout=90
```

### 6. Monitoring

Set up monitoring for:
- Application errors
- Server resources
- Queue workers
- Scheduled tasks

### 7. Backup Strategy

1. Database backups (daily)
2. Application code backups (with version control)
3. File storage backups
4. Regular testing of restore procedures

## Maintenance

### Updating the Application

1. Pull the latest changes:
   ```bash
   git pull origin main
   ```

2. Install new dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction
   ```

3. Run migrations:
   ```bash
   php artisan migrate --force
   ```

4. Clear caches:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. Restart queue workers:
   ```bash
   php artisan queue:restart
   ```

## Security Considerations

- Keep all dependencies up to date
- Use environment variables for sensitive information
- Implement rate limiting
- Enable HTTPS
- Regularly review logs
- Keep server software updated
- Implement proper backup strategies
