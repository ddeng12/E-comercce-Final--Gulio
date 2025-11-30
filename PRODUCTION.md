# Production Deployment Guide

This guide will help you deploy Gulio from prototype to production.

**🌐 Live Website**: [https://ghostwhite-dog-517526.hostingersite.com](https://ghostwhite-dog-517526.hostingersite.com)

## 🚀 Quick Start

### 1. Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (optional, for future dependencies)

### 2. Installation Steps

#### Step 1: Configure Environment

1. Copy the environment example:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` file with your production settings:
   ```env
   APP_ENV=production
   DB_HOST=your_db_host
   DB_NAME=gulio_production
   DB_USER=your_db_user
   DB_PASS=your_secure_password
   DB_PASS=your_secure_password
   ```

#### Step 2: Set Permissions

```bash
chmod 755 logs/
chmod 644 .env
chmod 644 config/config.php
```

#### Step 3: Run Database Setup

1. Visit `https://ghostwhite-dog-517526.hostingersite.com/setup.php` in your browser
2. Follow the setup wizard to:
   - Test database connection
   - Run migrations
   - Create admin user (optional)

Alternatively, run migrations manually:
```sql
-- Connect to MySQL
mysql -u root -p gulio_production

-- Run each migration file from database/migrations/
source database/migrations/001_create_users_table.sql;
source database/migrations/002_create_vendors_table.sql;
-- ... etc
```

#### Step 4: Security Checklist

- [ ] Change default admin password
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Verify `display_errors = 0` in PHP config
- [ ] Enable HTTPS/SSL
- [ ] Configure secure session settings
- [ ] Set up firewall rules
- [ ] Enable database backups

### 3. Production Configuration

#### PHP Settings

Ensure these settings in `php.ini`:

```ini
display_errors = Off
log_errors = On
error_log = /path/to/logs/php_errors.log
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

#### Apache Configuration

Example `.htaccess` for Apache:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(\.env|\.git|config\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

#### Nginx Configuration

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name ghostwhite-dog-517526.hostingersite.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ghostwhite-dog-517526.hostingersite.com;
    
    root /path/to/prototype;
    index index.php;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(\.env|config\.php|setup\.php)$ {
        deny all;
    }
}
```

### 4. Security Features Implemented

✅ **CSRF Protection**: All forms protected with tokens  
✅ **Input Validation**: All user inputs sanitized  
✅ **XSS Prevention**: Output escaping on all displayed data  
✅ **SQL Injection Prevention**: Prepared statements only  
✅ **Rate Limiting**: API endpoints protected  
✅ **Secure Sessions**: HttpOnly, Secure, SameSite cookies  
✅ **Error Logging**: Comprehensive logging system  
✅ **Password Hashing**: Bcrypt with cost factor 12  

### 5. Database Management

#### Backup Database

```bash
mysqldump -u root -p gulio_production > backup_$(date +%Y%m%d).sql
```

#### Restore Database

```bash
mysql -u root -p gulio_production < backup_20240101.sql
```

### 6. Monitoring & Logging

Logs are stored in the `logs/` directory:
- `app_YYYY-MM-DD.log` - Application logs
- PHP error log (configured in php.ini)

Monitor logs regularly:
```bash
tail -f logs/app_$(date +%Y-%m-%d).log
```

### 7. Performance Optimization

#### Enable Opcode Caching

For PHP-FPM with OPcache:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```

#### Database Indexing

Ensure indexes are created (see migration files):
- `vendors.category`
- `vendors.status`
- `users.email`
- `reviews.vendor_id`

### 8. Updates & Maintenance

#### Updating the Application

1. Backup database
2. Backup code files
3. Pull latest changes
4. Run new migrations if any
5. Clear cache (if implemented)
6. Test thoroughly

#### Regular Maintenance Tasks

- [ ] Review error logs weekly
- [ ] Database backups daily
- [ ] Security updates monthly
- [ ] Performance monitoring
- [ ] User feedback review

### 9. Troubleshooting

#### Database Connection Issues

Check:
1. Database credentials in `.env`
2. Database server is running
3. Firewall allows connections
4. User has proper permissions

#### Session Issues

Check:
1. Session directory is writable
2. `session.save_path` is configured
3. Cookie settings in `config.php`

#### Permission Issues

```bash
# Fix directory permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Logs directory
chmod 775 logs/
```

### 10. API Endpoints

All API endpoints require:
- POST method
- `action` parameter
- CSRF token (for state-changing operations)
- Rate limiting applied

Protected endpoints:
- `update_profile`
- `update_location`
- `update_trust_pref`
- `update_starter_pack`
- `submit_review`

### 11. Support & Contact

For production support:
- Check logs first: `logs/app_*.log`
- Review error messages
- Check database connectivity
- Verify configuration files

---

## 📋 Post-Deployment Checklist

- [ ] Database migrations completed
- [ ] Admin user created and password changed
- [ ] HTTPS enabled
- [ ] Error reporting disabled
- [ ] Logging configured
- [ ] Backups scheduled
- [ ] Security headers set
- [ ] Performance monitoring enabled
- [ ] Documentation updated

---

**Congratulations! Your Gulio application is now production-ready!** 🎉

