# 🔧 Troubleshooting Guide

## Error: "relation 'users' does not exist"

### Gejala:
```
PostgreSQL error: relation "users" does not exist at character 22
STATEMENT: SELECT COUNT(*) FROM users
```

### Penyebab Umum:
1. ❌ Migration gagal/tidak dijalankan
2. ❌ Schema file tidak ditemukan
3. ❌ Exception ditangkap tapi tidak di-log

### Solusi:

#### 1️⃣ **Cek Application Logs di Railway**

Buka Railway Dashboard → Project → Logs dan cari:
```
Migration check: users table EXISTS/DOES NOT EXIST
Total statements to execute: X
Migration completed: X table statements executed
```

**Expected (SUCCESS):**
```
Starting Nginx + PHP-FPM
Migration check: users table DOES NOT EXIST
Looking for schema at: /app/database/skema_postgresql.sql
Schema file found, starting migration...
Total statements to execute: 45
Migration completed: 9 table statements executed
Seed check started (FORCE=false)
Seed check completed
```

**ERROR Signs:**
```
ERROR: Schema file not found
ERROR in seed: Users table not ready
CRITICAL: Migration check failed
```

#### 2️⃣ **Manual Test Locally**

Jalankan test script untuk verifikasi migration:
```bash
php test_migration.php
```

**Output yang baik:**
```
✓ Connected successfully
✓ users                ... EXIST
✓ kategori             ... EXIST
...
✓ ALL TESTS PASSED
```

#### 3️⃣ **Force Reset Tables di Railway**

Jika tables gagal dibuat, gunakan opsi force:

**Via Railway Dashboard:**
1. Go to Project → Variables
2. Add new variable:
   ```
   FORCE_MIGRATION=true
   ```
3. Redeploy project

**Atau manual:**
```bash
# Connect ke Railway PostgreSQL console
railway shell postgresql

# Drop dan recreate database
DROP DATABASE IF EXISTS toko_inventori;
CREATE DATABASE toko_inventori;
\c toko_inventori;

# Keluar
\q
```

Kemudian redeploy di Railway.

#### 4️⃣ **Check Database Structure Manually**

Connect ke Railway PostgreSQL dan list tables:

```bash
# Di Railway
railway shell postgresql

# Query
SELECT * FROM information_schema.tables 
WHERE table_schema = 'public';
```

Atau gunakan UI Railway PostgreSQL plugin:
- Railway Dashboard → PostgreSQL service → Console

---

## Error: "Schema file not found"

### Penyebab:
Path ke schema file salah atau file tidak di-copy ke Docker

### Solusi:

**1. Cek file ada di repo:**
```bash
ls -la database/skema_postgresql.sql
```

**2. Cek Dockerfile copy statement:**
```dockerfile
# Di Dockerfile, pastikan ada:
COPY . /app
COPY app/ /app/app/
COPY database/ /app/database/
```

**3. Cek .dockerignore jangan exclude database:**
```
# .dockerignore - pastikan TIDAK ada:
database/
```

---

## Error: "Migration check failed"

### Gejala:
```
CRITICAL: Migration check failed: PDO error...
```

### Penyebab:
PDO query untuk check table melempar exception yang tidak ditangani

### Solusi:

**Check PDO error mode:**
```php
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

Ini seharusnya sudah ada di `database.php`. Jika tidak:

1. Edit `app/config/database.php`
2. Cari line dengan `setAttribute`
3. Pastikan ada `PDO::ERRMODE_EXCEPTION`

---

## Error: "Seed check started but data still empty"

### Gejala:
- Logs menunjukkan seed berhasil
- Tapi data tidak ada di database

### Penyebab:
Transaction commit gagal

### Solusi:

**Check kategori_id yang direferensikan:**
```sql
-- Di PostgreSQL
SELECT * FROM kategori;  -- Harusnya ada 6 kategori
SELECT * FROM barang;    -- Harusnya ada 13 barang
```

Jika kosong, cek id_kategori yang diinsert sesuai dengan yang ada.

---

## Error: Login tidak bisa dengan admin/admin123

### Gejala:
- Tables ada
- Data ada
- Tapi login selalu gagal

### Penyebab:
Hashing password dengan algoritma berbeda

### Solusi:

**1. Reset password ke default:**
```bash
# Di Railway > Set Variable
SEED_FORCE=true

# Redeploy
```

**2. Atau manual update:**
```bash
# Railway PostgreSQL console
UPDATE users SET password = '$2y$12$DQTsuQb8XDQGfrKWyTCHQOlhrwWJW42PgSzFZk3D6B0rCTjgPwV8.' 
WHERE username = 'admin';
```

(Password hash ini untuk 'admin123')

---

## Error: "Database connection failed" at startup

### Gejala:
```
PDOException: SQLSTATE[HY000]...
Database connection failed. Please contact administrator.
```

### Penyebab:
PostgreSQL service tidak running atau credentials salah

### Solusi:

**1. Cek PostgreSQL status:**
- Railway Dashboard → PostgreSQL service
- Status harus "Running" (green)

**2. Cek environment variables:**
```
DB_HOST    = (dari Railway PostgreSQL)
DB_PORT    = 5432
DB_NAME    = toko_inventori
DB_USER    = postgres
DB_PASS    = (dari Railway PostgreSQL)
```

**3. Test connection locally:**
```bash
psql postgresql://user:pass@host:port/toko_inventori
```

---

## ✅ Quick Health Check

Jalankan ini setelah deploy:

```bash
# Test aplikasi
curl https://your-app-url.railway.app/

# Cek logs untuk:
# - "Migration check"
# - "Seed check"
# - "HTTP 200"

# Test login endpoint
curl -X POST https://your-app-url.railway.app/login \
  -d "username=admin&password=admin123"

# Expected: HTTP 302 (redirect ke dashboard)
```

---

## 📋 Debugging Checklist

- [ ] Dockerfile build successful? (check Railway Logs)
- [ ] Container started? (check Railway Status)
- [ ] Migration logs appear? (check Railway Logs untuk "Migration")
- [ ] Schema file path correct? (check Railway Logs untuk path)
- [ ] PostgreSQL service running? (check Railway dashboard)
- [ ] Environment variables set? (check Railway Variables)
- [ ] Tables exist in database? (query information_schema)
- [ ] Data seeded? (SELECT COUNT(*) FROM users)
- [ ] Application responsive? (curl atau browser)
- [ ] Login working? (test dengan admin/admin123)

---

## 🆘 Still Not Working?

1. **Gather logs:**
   ```bash
   # Railway logs
   railway logs --all
   ```

2. **Check recent commits:**
   ```bash
   git log --oneline -5
   ```

3. **Redeploy clean:**
   - Railway Dashboard → Trigger Redeploy
   - Wait for "Deployment succeeded"

4. **Contact support dengan info:**
   - Railway project ID
   - Last 50 lines of logs
   - Error message yang eksak
