# Toko Inventori - Production Checklist

## 📋 Pre-Deployment Checklist

### 1. Application Setup
- [ ] `.env` sudah dikonfigurasi dengan credentials hosting
- [ ] `logs/` directory writable (chmod 755)
- [ ] `public/` directory readable
- [ ] `.env` file tidak di-commit ke git
- [ ] `.gitignore` sudah include sensitive files

### 2. Database Setup
- [ ] PostgreSQL installed di server hosting
- [ ] Database user dibuat
- [ ] Database `toko_inventori` dibuat
- [ ] Schema sudah di-import
- [ ] Database user memiliki proper permissions
- [ ] Backup database dijadwalkan

### 3. Security
- [ ] `APP_DEBUG=false` di .env production
- [ ] SSL/HTTPS sudah enabled
- [ ] Firewall rules configured
- [ ] `.env` file not publicly accessible
- [ ] `.git` directory tidak accessible
- [ ] `/logs` directory tidak publicly accessible
- [ ] `/database` directory tidak publicly accessible
- [ ] Error pages tidak show stack trace

### 4. Web Server Setup
- [ ] Mod_rewrite enabled (Apache) atau Nginx routing configured
- [ ] Virtual host/server block sudah setup
- [ ] Document root pointing ke `/public` directory
- [ ] URL rewriting working (test 404 page)
- [ ] Gzip compression enabled
- [ ] Cache headers configured
- [ ] Security headers set (X-Frame-Options, etc)

### 5. PHP Configuration
- [ ] PHP 7.4+ installed
- [ ] PDO extension enabled
- [ ] pdo_pgsql extension enabled
- [ ] `display_errors` = Off
- [ ] `log_errors` = On
- [ ] Error logging path writable
- [ ] `max_upload_size` sesuai kebutuhan
- [ ] Session directory writable

### 6. Performance
- [ ] Query caching configured (jika perlu)
- [ ] Database indexes created
- [ ] Gzip compression enabled
- [ ] Static assets caching configured
- [ ] CDN configured (optional)

### 7. Monitoring & Backup
- [ ] Error log monitoring setup
- [ ] Database backup dijadwalkan
- [ ] Backup storage configured
- [ ] Log rotation configured
- [ ] Monitoring alerts setup

### 8. Testing
- [ ] Homepage loads correctly
- [ ] Database connectivity working
- [ ] Login functionality tested
- [ ] CRUD operations tested
- [ ] Reports page tested
- [ ] Error handling tested (test 404, 500)
- [ ] File uploads tested (jika ada)
- [ ] Mobile responsiveness checked

### 9. Documentation
- [ ] README.md updated
- [ ] DEPLOYMENT.md created
- [ ] Database schema documented
- [ ] API endpoints documented (jika ada)
- [ ] Troubleshooting guide created

### 10. Handover
- [ ] Admin credentials documented securely
- [ ] Server access credentials shared
- [ ] Maintenance procedures documented
- [ ] Emergency contacts listed
- [ ] Training completed

---

## 🔧 Production Environment Variables

```
DB_HOST=<hosting-database-ip>
DB_PORT=5432
DB_NAME=toko_inventori
DB_USER=<database-user>
DB_PASS=<strong-password>
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
TIMEZONE=Asia/Jakarta
```

## 📊 Database Backup Strategy

### Daily Backup
```bash
# Crontab entry (jalankan setiap hari pukul 2 AM)
0 2 * * * pg_dump -h localhost -U postgres -d toko_inventori | gzip > /backup/toko_$(date +\%Y\%m\%d).sql.gz
```

### Weekly Full Backup dengan Retention
```bash
# Keep last 4 weeks
0 3 * * 0 pg_dump -h localhost -U postgres -d toko_inventori | gzip > /backup/weekly_$(date +\%Y\%m\%d).sql.gz && find /backup/weekly_* -mtime +28 -delete
```

## 🔐 Security Hardening Commands

```bash
# Setup file permissions
find /var/www/toko-inventori -type f -exec chmod 644 {} \;
find /var/www/toko-inventori -type d -exec chmod 755 {} \;
chmod 755 /var/www/toko-inventori/logs
chmod 755 /var/www/toko-inventori/app/uploads
chmod 755 /var/www/toko-inventori/public/uploads

# Protect sensitive files
chmod 600 /var/www/toko-inventori/.env

# Setup web server user ownership
chown -R www-data:www-data /var/www/toko-inventori
```

## 🚨 Common Issues & Solutions

### Issue: 404 Page Not Found
```
Solusi:
1. Check mod_rewrite enabled: a2enmod rewrite
2. Check .htaccess in public folder
3. Verify DocumentRoot pointing to public/
4. Restart Apache: systemctl restart apache2
```

### Issue: Database Connection Failed
```
Solusi:
1. Verify database server running
2. Test connection: psql -h <host> -U <user> -d toko_inventori
3. Check .env credentials
4. Check firewall rules
5. Verify pdo_pgsql extension: php -m | grep pdo_pgsql
```

### Issue: Permission Denied
```
Solusi:
1. Check directory permissions: ls -la
2. Fix permissions: chmod 755 logs/
3. Check web server user: ps aux | grep apache/nginx
4. Fix ownership: chown -R www-data:www-data /path
```

### Issue: White Screen / No Error
```
Solusi:
1. Check logs/error.log
2. Enable debug mode temporarily: APP_DEBUG=true
3. Check PHP error log: tail -f /var/log/php-fpm.log
4. Verify database connection
```

---

**Checklist Updated**: 31 Januari 2026
**Status**: Production Ready
