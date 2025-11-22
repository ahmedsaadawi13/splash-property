# SplashProperty - Production Deployment Guide

## Pre-Deployment Checklist

- [ ] All tests passing (see TESTING.md)
- [ ] Security audit completed
- [ ] Database backups configured
- [ ] SSL certificate obtained
- [ ] Domain configured
- [ ] Email service configured
- [ ] Error logging configured
- [ ] Performance baseline established

## Server Requirements

### Minimum Specifications
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 50GB SSD
- **OS**: Ubuntu 20.04 LTS or newer

### Recommended for Production
- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 100GB+ SSD
- **OS**: Ubuntu 22.04 LTS

## Step-by-Step Deployment

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install PHP and extensions
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-gd -y

# Install MySQL
sudo apt install mysql-server -y

# Secure MySQL
sudo mysql_secure_installation

# Install Git
sudo apt install git -y
```

### 2. Clone Application

```bash
cd /var/www
sudo git clone https://github.com/yourusername/SplashProperty.git
sudo chown -R www-data:www-data SplashProperty
```

### 3. Configure Environment

```bash
cd /var/www/SplashProperty
sudo cp .env.example .env
sudo nano .env
```

Update values:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_DATABASE=splashproperty_prod
DB_USERNAME=splash_user
DB_PASSWORD=STRONG_PASSWORD_HERE

MAIL_FROM=noreply@yourdomain.com
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
```

### 4. Create Database

```bash
sudo mysql
```

```sql
CREATE DATABASE splashproperty_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'splash_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON splashproperty_prod.* TO 'splash_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Import Database

```bash
mysql -u splash_user -p splashproperty_prod < database.sql
```

### 6. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/SplashProperty
sudo chmod -R 755 /var/www/SplashProperty
sudo chmod -R 775 /var/www/SplashProperty/storage
sudo chmod -R 775 /var/www/SplashProperty/storage/uploads
sudo chmod -R 775 /var/www/SplashProperty/storage/logs
```

### 7. Configure Apache

Create virtual host:

```bash
sudo nano /etc/apache2/sites-available/splashproperty.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    DocumentRoot /var/www/SplashProperty/public

    <Directory /var/www/SplashProperty/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashproperty_error.log
    CustomLog ${APACHE_LOG_DIR}/splashproperty_access.log combined

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite splashproperty.conf
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

### 8. Install SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Follow prompts to configure HTTPS.

### 9. Configure PHP

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Update:
```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### 10. Configure Firewall

```bash
sudo ufw allow 'Apache Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

### 11. Setup Cron Jobs

```bash
sudo crontab -e -u www-data
```

Add:
```
# Update overdue payment schedules
0 1 * * * cd /var/www/SplashProperty && php scripts/update_overdue_payments.php >> /var/www/SplashProperty/storage/logs/cron.log 2>&1

# Expire bookings
0 2 * * * cd /var/www/SplashProperty && php scripts/expire_bookings.php >> /var/www/SplashProperty/storage/logs/cron.log 2>&1

# Send payment reminders
0 9 * * * cd /var/www/SplashProperty && php scripts/send_payment_reminders.php >> /var/www/SplashProperty/storage/logs/cron.log 2>&1

# Clean old logs (monthly)
0 0 1 * * find /var/www/SplashProperty/storage/logs -name "*.log" -mtime +30 -delete
```

### 12. Configure Database Backups

```bash
sudo nano /usr/local/bin/backup_splash.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/splashproperty"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u splash_user -pYOUR_PASSWORD splashproperty_prod | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/SplashProperty/storage/uploads

# Keep only last 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup_splash.sh
```

Add to crontab:
```bash
sudo crontab -e
```

```
# Daily backup at 3 AM
0 3 * * * /usr/local/bin/backup_splash.sh
```

### 13. Configure Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y
```

Create monitoring script:
```bash
sudo nano /usr/local/bin/monitor_splash.sh
```

```bash
#!/bin/bash
# Check disk space
df -h | grep -v tmpfs

# Check MySQL status
systemctl status mysql --no-pager

# Check Apache status
systemctl status apache2 --no-pager

# Check recent errors
tail -n 50 /var/www/SplashProperty/storage/logs/errors.log
```

## Security Hardening

### 1. Disable Directory Listing
Already done in Apache config with `Options -Indexes`

### 2. Hide PHP Version
```bash
sudo nano /etc/php/8.1/apache2/php.ini
```
Set: `expose_php = Off`

### 3. Limit File Upload Directory Execution
```bash
sudo nano /var/www/SplashProperty/storage/uploads/.htaccess
```

```apache
php_flag engine off
Options -Indexes
```

### 4. Setup Fail2Ban

```bash
sudo apt install fail2ban -y
sudo nano /etc/fail2ban/jail.local
```

```ini
[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/error.log
maxretry = 3
bantime = 3600
```

```bash
sudo systemctl restart fail2ban
```

## Performance Optimization

### 1. Enable OPcache

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. Enable Apache Compression

```bash
sudo a2enmod deflate
sudo systemctl restart apache2
```

### 3. MySQL Optimization

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
max_connections = 200
query_cache_size = 64M
query_cache_limit = 2M
```

```bash
sudo systemctl restart mysql
```

## Monitoring & Maintenance

### Daily Checks
- [ ] Check error logs
- [ ] Check disk space
- [ ] Check backup success
- [ ] Monitor response times

### Weekly Checks
- [ ] Review access logs for suspicious activity
- [ ] Check database size growth
- [ ] Review slow query log
- [ ] Update system packages

### Monthly Checks
- [ ] Review and archive old data
- [ ] Analyze performance metrics
- [ ] Update SSL certificates if needed
- [ ] Review security patches

## Troubleshooting

### Application Not Loading
```bash
# Check Apache status
sudo systemctl status apache2

# Check error logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/www/SplashProperty/storage/logs/errors.log

# Check permissions
ls -la /var/www/SplashProperty
```

### Database Connection Issues
```bash
# Test connection
mysql -u splash_user -p splashproperty_prod

# Check MySQL status
sudo systemctl status mysql

# Check error log
sudo tail -f /var/log/mysql/error.log
```

### File Upload Issues
```bash
# Check permissions
ls -la /var/www/SplashProperty/storage/uploads

# Fix if needed
sudo chown -R www-data:www-data /var/www/SplashProperty/storage/uploads
sudo chmod -R 775 /var/www/SplashProperty/storage/uploads
```

## Rollback Procedure

If deployment fails:

```bash
# Restore database
gunzip < /var/backups/splashproperty/db_TIMESTAMP.sql.gz | mysql -u splash_user -p splashproperty_prod

# Restore files
cd /var/www
sudo rm -rf SplashProperty
tar -xzf /var/backups/splashproperty/files_TIMESTAMP.tar.gz

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

## Update Procedure

```bash
# Backup first
/usr/local/bin/backup_splash.sh

# Pull updates
cd /var/www/SplashProperty
sudo git pull origin main

# Run migrations (if any)
mysql -u splash_user -p splashproperty_prod < updates/migration_YYYYMMDD.sql

# Clear cache (if implemented)
# php scripts/clear_cache.php

# Restart Apache
sudo systemctl restart apache2
```

## Support Contacts

- **System Admin**: sysadmin@yourdomain.com
- **Database Admin**: dba@yourdomain.com
- **Emergency**: +xxx-xxx-xxxx

---

**Last Updated**: January 2025
