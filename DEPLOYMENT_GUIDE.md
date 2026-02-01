# 📋 PANDUAN DEPLOYMENT TOKO INVENTORI KE RAILWAY

Panduan lengkap step-by-step untuk deploy project ini ke Railway dengan benar.

---

## ✅ PRE-DEPLOYMENT CHECKLIST

Sebelum deploy, pastikan hal berikut sudah done:

### Local Development
- [ ] `.env` file sudah dibuat (copy dari `.env.example`)
- [ ] PostgreSQL local berjalan dan database `toko_inventori` created
- [ ] Test `php -S localhost:8000 -t public` berjalan tanpa error
- [ ] Login berhasil dengan `admin/admin123`

### Git Repository
- [ ] Semua perubahan sudah di-commit ke GitHub
- [ ] Remote `origin` menunjuk ke GitHub repo
- [ ] Branch `main` adalah branch yang akan di-deploy

### Docker (Optional untuk test)
- [ ] Docker Desktop terinstall dan berjalan
- [ ] `docker build -t toko-inventori .` berhasil build

---

## 🚀 STEP-BY-STEP DEPLOYMENT

### STEP 1: Persiapkan Repository GitHub

Pastikan project sudah di-GitHub dengan struktur yang benar:

```bash
# Verify git remote
git remote -v

# Output should show:
# origin  https://github.com/YOUR_USERNAME/inventoris_toko.git (fetch)
# origin  https://github.com/YOUR_USERNAME/inventoris_toko.git (push)

# Commit semua perubahan
git add .
git commit -m "Final: Prepare for Railway deployment"
git push origin main
```

### STEP 2: Setup Railway Project

1. **Login ke Railway Dashboard**
   - Buka: https://railway.app/dashboard
   - Login dengan akun Anda (GitHub recommended)

2. **Create New Project**
   - Klik tombol "New Project"
   - Pilih "Create Database" → "PostgreSQL 15"
   - Tunggu database selesai dibuat (~30 detik)

3. **Add Web Service**
   - Klik "Add Service" (atau "+")
   - Pilih "GitHub Repo"
   - Select repository: `inventoris_toko` (atau nama repo Anda)
   - Select branch: `main`
   - Deploy berarti auto-trigger dari kode di GitHub

### STEP 3: Environment Variables Configuration

Di Railway, set environment variables:

1. **Navigate ke Settings**
   - Klik tab "Variables" atau "Environment"

2. **Add Variables** (Railway auto-provides PostgreSQL vars)
   - Railway automatic variables (akan appear):
     ```
     DATABASE_URL=postgresql://user:pass@host:port/railway
     ```

3. **Railway PostgreSQL Variables** (auto-set, verify):
   - `DB_HOST` → Your PostgreSQL host
   - `DB_PORT` → Usually 5432
   - `DB_NAME` → Database name (usually `railway`)
   - `DB_USER` → PostgreSQL user
   - `DB_PASS` → PostgreSQL password

4. **Application Variables** (add if needed):
   ```
   PORT=8080
   APP_ENV=production
   APP_DEBUG=false
   ```

**Catatan**: Railway otomatis parse `DATABASE_URL` dan set `DB_HOST`, `DB_PORT`, etc.

### STEP 4: Deploy Application

1. **Initial Deploy**
   - Setelah connect GitHub, Railway otomatis start build
   - Tunggu Build Complete (~2-3 menit)
   - Check Deployment Logs untuk error

2. **Verify Build Success**
   - Logs should show:
     ```
     Building Docker image...
     Build completed successfully
     Starting services...
     ```

3. **Check Runtime Logs**
   - Klik "Runtime Logs" tab
   - Harus ada log dari start.sh:
     ```
     ==================================================
     Starting Toko Inventori Server
     ==================================================
     Port: 8080
     ...
     Configuring Nginx...
     Starting PHP-FPM...
     Starting Nginx...
     ```

### STEP 5: Initialize Database

Setelah deployment berhasil, database perlu di-initialize dengan tables:

#### Option A: Automatic (Recommended)
Sistem akan otomatis create tables saat aplikasi pertama kali start:

1. **Trigger Application**
   - Akses: `https://your-railway-app.up.railway.app/`
   - System akan auto-trigger migration saat `Database::getConnection()` dipanggil
   - Check Railway logs untuk verify:
     ```
     ✓ Migration: Starting table creation...
     → Migration: Found X statements to execute
     ✓ Migration completed: X statements executed
     ```

2. **Verify Tables Created**
   - Akses: `https://your-railway-app.up.railway.app/test_db.php`
   - Seharusnya show status OK dengan table counts

#### Option B: Manual SQL (If automatic fails)

Jika automatic migration tidak bekerja, run manual SQL:

1. **Access PostgreSQL Console**
   - Buka Railway Dashboard
   - Klik PostgreSQL service
   - Klik tab "Data" atau "Query"
   - Paste SQL berikut:

```sql
-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Other Tables
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS satuan (
    id_satuan SERIAL PRIMARY KEY,
    nama_satuan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS barang (
    id_barang SERIAL PRIMARY KEY,
    kode_barang VARCHAR(50) UNIQUE NOT NULL,
    nama_barang VARCHAR(200) NOT NULL,
    id_kategori INTEGER REFERENCES kategori(id_kategori),
    id_satuan INTEGER REFERENCES satuan(id_satuan),
    harga_beli DECIMAL(15,2) DEFAULT 0,
    harga_jual DECIMAL(15,2) DEFAULT 0,
    stok INTEGER DEFAULT 0,
    stok_minimum INTEGER DEFAULT 0,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Users
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir')
ON CONFLICT (username) DO NOTHING;

-- Insert Categories
INSERT INTO kategori (nama_kategori) VALUES
('Makanan'), ('Minuman'), ('Elektronik'), ('Pakaian'), ('Alat Tulis'), ('Lainnya')
ON CONFLICT DO NOTHING;

-- Insert Units
INSERT INTO satuan (nama_satuan) VALUES
('pcs'), ('kg'), ('liter'), ('botol'), ('bungkus'), ('box'), ('pack'), ('unit'), ('gram'), ('meter'), ('lusin')
ON CONFLICT DO NOTHING;
```

2. **Execute SQL**
   - Click "Execute" atau paste di console
   - Wait untuk completion

3. **Verify**
   - Run: `SELECT COUNT(*) FROM users;`
   - Should return: `2` (admin + kasir)

---

## 🔐 LOGIN CREDENTIALS

Setelah database initialized, login dengan:

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: Administrator (full access)

### Kasir Account
- **Username**: `kasir`
- **Password**: `kasir123`
- **Role**: Cashier (limited access)

---

## ✅ VERIFICATION CHECKLIST

Setelah deployment, verify semuanya berjalan:

### Application Status
- [ ] Homepage loads: `https://your-app.up.railway.app/`
- [ ] Login page accessible: `https://your-app.up.railway.app/login`
- [ ] Can login dengan admin/admin123
- [ ] Dashboard shows categories, items, stats

### Database Verification
Akses PostgreSQL Query tab dan run:

```sql
-- Check all tables exist
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public' 
ORDER BY table_name;

-- Check users
SELECT id_user, username, role FROM users;

-- Check data count
SELECT 'users' as table, COUNT(*) as count FROM users
UNION ALL
SELECT 'kategori', COUNT(*) FROM kategori
UNION ALL
SELECT 'satuan', COUNT(*) FROM satuan
UNION ALL
SELECT 'barang', COUNT(*) FROM barang;
```

### Application Features
- [ ] Dashboard: Shows statistics correctly
- [ ] Barang: Can view/add/edit items
- [ ] Pembelian: Can create purchase transactions
- [ ] Penjualan: Can create sales transactions
- [ ] Laporan: Can view reports

---

## 🐛 TROUBLESHOOTING

### Problem: Build Failed

**Check Logs:**
```
Railway Dashboard → Web Service → Logs → Build Logs
```

**Common Issues:**
- Docker base image not available: Wait 5 min and retry
- File permissions: Ensure Dockerfile has `RUN chmod +x`

**Solution:**
```bash
git commit --allow-empty -m "Trigger rebuild"
git push origin main
```

### Problem: Application Starts but No Database Tables

**Check Runtime Logs:**
```
Railway Dashboard → Web Service → Runtime Logs
```

**Look for Migration Logs:**
```
✓ Migration: Starting table creation...
```

**Solution:**
1. Check if environment variables set correctly
2. Manual SQL execution (see Step 5 Option B)
3. Force redeploy:
   ```bash
   git commit --allow-empty -m "Force redeploy"
   git push origin main
   ```

### Problem: Login Failed

**Possible Causes:**
1. Tables not created yet
2. Users table empty
3. Password hash mismatch

**Fix:**
1. Verify users table has data:
   ```sql
   SELECT COUNT(*) FROM users;
   ```
2. If empty, insert users (Step 5 Option B - SQL)
3. Ensure passwords are correct (admin123, kasir123)

### Problem: 502 Bad Gateway

**Cause:** Nginx/PHP-FPM communication issue

**Check:**
1. PHP-FPM status: `pgrep -a php-fpm` in Railway shell
2. Nginx status: Check Nginx logs
3. DB connection: Test database connection

**Fix:**
1. Restart container: Railway Dashboard → "Redeploy"
2. Check env variables are set correctly
3. Verify database credentials in Railway

### Problem: Slow Response Times

**Optimization:**
- Check Railway CPU/Memory allocation
- Database query performance
- Nginx cache settings

---

## 📊 MONITORING

Setelah deployment, monitor dengan:

### Railway Dashboard
- **Metrics**: CPU, Memory, Network
- **Logs**: Application logs real-time
- **Deployments**: Deployment history and status

### Application Logs
```
Railway → Web Service → Runtime Logs
```

Look for errors:
```
ERROR: ...
Exception: ...
Fatal: ...
```

---

## 🔄 UPDATING APPLICATION

Untuk update code:

1. **Make Changes Locally**
   ```bash
   # Edit files
   code app/controllers/BarangController.php
   ```

2. **Test Locally**
   ```bash
   php -S localhost:8000 -t public
   # Test functionality
   ```

3. **Commit and Push**
   ```bash
   git add .
   git commit -m "feat: Add new feature"
   git push origin main
   ```

4. **Railway Auto-Deploy**
   - GitHub webhook triggers automatic build
   - Railway rebuilds and redeploys automatically
   - Check deployment status in dashboard

---

## 🛡️ SECURITY BEST PRACTICES

### Before Production

1. **Change Default Passwords**
   ```sql
   UPDATE users SET password = ? WHERE username = 'admin';
   -- Use strong password and hash with password_hash()
   ```

2. **Environment Variables**
   - Never commit `.env` to git
   - Use Railway's secure variable storage
   - Rotate database passwords regularly

3. **Database Security**
   - Restrict database access to Railway network only
   - Use strong PostgreSQL passwords
   - Regular backups

4. **Application Security**
   - Enable HTTPS (Railway provides free SSL)
   - Use security headers in nginx.conf
   - Regular dependency updates

---

## 📞 SUPPORT & DEBUGGING

### Getting Help

1. **Check Logs First**
   - Railway → Runtime Logs
   - PostgreSQL Query Editor

2. **Common Files to Check**
   - `/app/config/database.php` - DB connection
   - `/app/config/migrate.php` - Table creation
   - `/app/config/seed.php` - Initial data

3. **Enable Debug Mode** (Temporary)
   ```
   Railway Variables → Add: APP_DEBUG=true
   ```

---

## ✨ FINAL CHECKLIST

Before considering deployment complete:

- [ ] Application loads without error
- [ ] Login works with both admin and kasir accounts
- [ ] All database tables exist
- [ ] Can navigate all main features (Barang, Pembelian, Penjualan, Laporan)
- [ ] No 500 errors in logs
- [ ] Database backups configured (if needed)

---

## 🎉 DEPLOYMENT COMPLETE!

Sistem Inventori Toko Anda sudah live di Railway!

**URL**: `https://your-app.up.railway.app`
**Admin Panel**: `https://your-app.up.railway.app/login`

Untuk informasi lebih lanjut, lihat dokumentasi di repo atau hubungi developer.
