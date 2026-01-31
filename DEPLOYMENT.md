# 🚀 Panduan Deployment - Sistem Inventori Toko

## 📋 Requirements Server

### Minimum Requirements:
- **PHP**: 7.4 atau lebih tinggi (Recommended: PHP 8.1+)
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **Web Server**: Apache 2.4+ atau Nginx 1.18+
- **Memory**: 512MB RAM minimum (1GB recommended)
- **Storage**: 500MB free space

### PHP Extensions:
```bash
sudo apt install php8.1-cli php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd
```

---

## 🛠️ Step 1: Persiapan Server

### A. Update System (Ubuntu/Debian)
```bash
sudo apt update && sudo apt upgrade -y
```

### B. Install Apache (pilih salah satu)
```bash
sudo apt install apache2
sudo systemctl enable apache2
sudo systemctl start apache2
```

### Atau Install Nginx
```bash
sudo apt install nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### C. Install MySQL
```bash
sudo apt install mysql-server
sudo mysql_secure_installation
```

### D. Install PHP
```bash
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl
```

---

## 📦 Step 2: Upload Project

### Via Git (Recommended)
```bash
cd /var/www/
sudo git clone https://github.com/yourusername/toko-inventori.git
sudo chown -R www-data:www-data toko-inventori
```

### Via FTP/SFTP
1. Upload semua file ke `/var/www/toko-inventori/`
2. Pastikan struktur folder tetap utuh

---

## 🗄️ Step 3: Setup Database

### A. Login ke MySQL
```bash
sudo mysql -u root -p
```

### B. Buat Database & User
```sql
CREATE DATABASE toko_inventori CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'toko_user'@'localhost' IDENTIFIED BY 'password_kuat_anda';
GRANT ALL PRIVILEGES ON toko_inventori.* TO 'toko_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### C. Import Database Schema
```bash
mysql -u toko_user -p toko_inventori < /var/www/toko-inventori/database/skema.sql
```

---

## ⚙️ Step 4: Konfigurasi Aplikasi

### A. Copy File Environment
```bash
cd /var/www/toko-inventori
cp .env.example .env
nano .env
```

### B. Edit File .env
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=toko_inventori
DB_USER=toko_user
DB_PASS=password_kuat_anda

APP_TIMEZONE=Asia/Jakarta
```

### C. Update app/config/database.php
Edit file dan sesuaikan dengan kredensial database:
```bash
nano app/config/database.php
```

Ubah:
```php
private $host = 'localhost';
private $db_name = 'toko_inventori';
private $username = 'toko_user';
private $password = 'password_kuat_anda';
```

**PENTING**: Hapus bagian `unix_socket` di production:
```php
// Ganti ini:
$this->conn = new PDO(
    "mysql:host=" . $this->host . ";unix_socket=/tmp/mysql.sock;dbname=" . $this->db_name,
    ...
);

// Menjadi ini:
$this->conn = new PDO(
    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
    ...
);
```

---

## 🌐 Step 5: Konfigurasi Web Server

### APACHE

#### A. Copy Virtual Host Configuration
```bash
sudo cp /var/www/toko-inventori/config/apache-vhost.conf /etc/apache2/sites-available/toko-inventori.conf
```

#### B. Edit dan sesuaikan domain
```bash
sudo nano /etc/apache2/sites-available/toko-inventori.conf
```
Ubah `yourdomain.com` dengan domain Anda

#### C. Enable Site & Modules
```bash
sudo a2enmod rewrite
sudo a2ensite toko-inventori.conf
sudo systemctl restart apache2
```

### NGINX

#### A. Copy Nginx Configuration
```bash
sudo cp /var/www/toko-inventori/config/nginx-site.conf /etc/nginx/sites-available/toko-inventori
```

#### B. Edit dan sesuaikan
```bash
sudo nano /etc/nginx/sites-available/toko-inventori
```
- Ubah `yourdomain.com` dengan domain Anda
- Sesuaikan PHP-FPM socket version (php8.1-fpm.sock)

#### C. Enable Site
```bash
sudo ln -s /etc/nginx/sites-available/toko-inventori /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

---

## 🔐 Step 6: Set Permissions & Security

### A. Set Ownership
```bash
sudo chown -R www-data:www-data /var/www/toko-inventori
```

### B. Set Permissions
```bash
# Folder
sudo find /var/www/toko-inventori -type d -exec chmod 755 {} \;

# Files
sudo find /var/www/toko-inventori -type f -exec chmod 644 {} \;

# Config file (lebih ketat)
sudo chmod 640 /var/www/toko-inventori/app/config/database.php
sudo chmod 640 /var/www/toko-inventori/.env
```

### C. Proteksi File Sensitif
Pastikan `.htaccess` di root folder sudah ada dan berisi:
```apache
# Deny access ke folder app, config, database
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
```

---

## 🔒 Step 7: Install SSL Certificate (HTTPS)

### Menggunakan Let's Encrypt (GRATIS)

#### A. Install Certbot
```bash
sudo apt install certbot python3-certbot-apache  # Untuk Apache
# atau
sudo apt install certbot python3-certbot-nginx   # Untuk Nginx
```

#### B. Generate SSL Certificate
```bash
# Apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

#### C. Auto-renewal
```bash
sudo certbot renew --dry-run
```

Certbot akan otomatis membuat cron job untuk renewal.

---

## 🧪 Step 8: Testing

### A. Test Koneksi Database
```bash
cd /var/www/toko-inventori
php -r "require 'app/config/database.php'; \$db = new Database(); \$conn = \$db->getConnection(); echo \$conn ? 'Database Connected!' : 'Connection Failed';"
```

### B. Test Web Server
Buka browser dan akses:
```
http://yourdomain.com
```

### C. Test Login
Default user (sesuai database):
- Username/Email dari database
- Password dari database

---

## 📊 Step 9: Monitoring & Maintenance

### A. Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/toko-inventori
```

Isi:
```
/var/log/nginx/toko-inventori-*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    sharedscripts
    postrotate
        systemctl reload nginx > /dev/null
    endscript
}
```

### B. Database Backup Otomatis
```bash
sudo nano /usr/local/bin/backup-toko-db.sh
```

Isi:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/toko-inventori"
mkdir -p $BACKUP_DIR
mysqldump -u toko_user -p'password_anda' toko_inventori | gzip > $BACKUP_DIR/toko_inventori_$DATE.sql.gz
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-toko-db.sh
```

Tambah ke crontab (backup harian jam 2 pagi):
```bash
sudo crontab -e
```
Tambahkan:
```
0 2 * * * /usr/local/bin/backup-toko-db.sh
```

### C. Monitoring Error Logs
```bash
# Apache
sudo tail -f /var/log/apache2/toko-inventori-error.log

# Nginx
sudo tail -f /var/log/nginx/toko-inventori-error.log

# PHP
sudo tail -f /var/log/php/error.log
```

---

## 🔧 Troubleshooting

### Error: "500 Internal Server Error"
1. Check error log
2. Pastikan permissions sudah benar
3. Cek `display_errors = Off` di php.ini

### Error: "Database connection failed"
1. Cek kredensial di `app/config/database.php`
2. Pastikan MySQL service running: `sudo systemctl status mysql`
3. Test koneksi: `mysql -u toko_user -p`

### Error: "404 Not Found" untuk semua URL
1. Pastikan mod_rewrite enabled (Apache)
2. Cek `.htaccess` file di folder `public/`
3. Pastikan `AllowOverride All` di virtual host config

### Page Loading Lambat
1. Enable opcache di php.ini
2. Enable gzip compression
3. Optimize database dengan index
4. Setup caching

---

## 📱 Bonus: Setup di Shared Hosting (cPanel)

### 1. Upload Files
- Upload via File Manager atau FTP ke `public_html/`

### 2. Setup Database
- Buat database via **MySQL Databases** di cPanel
- Import `skema.sql` via **phpMyAdmin**

### 3. Edit Configuration
- Edit `app/config/database.php` dengan DB credentials

### 4. Setup .htaccess
- Pastikan file `.htaccess` ada di root folder
- Isi:
```apache
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]
```

### 5. SSL Certificate
- Enable SSL via **SSL/TLS Status** di cPanel (biasanya gratis AutoSSL)

---

## ✅ Checklist Final

- [ ] Database imported & user created
- [ ] File `.env` dan `database.php` sudah dikonfigurasi
- [ ] Virtual host configured
- [ ] Permissions sudah benar (755 folder, 644 files)
- [ ] SSL certificate installed
- [ ] Test login berhasil
- [ ] Backup schedule setup
- [ ] Error logging aktif
- [ ] PHP opcache enabled
- [ ] Firewall configured (port 80, 443)

---

## 📞 Support

Jika ada masalah, check:
1. Error logs server
2. PHP error logs
3. Database connection
4. File permissions

---

**Good luck dengan deployment! 🎉**
