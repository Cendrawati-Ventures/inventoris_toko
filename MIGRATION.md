# Automatic Table Migration untuk Railway

## Penjelasan Masalah

Saat deploy ke Railway, database hanya ada kosong. Application tidak bisa membuat table secara otomatis, sehingga seed.php tidak bekerja.

## Solusi

Sistem otomatis migration telah dibuat untuk membuat semua table pada saat pertama kali aplikasi terhubung ke database.

### File yang Ditambahkan/Dimodifikasi

#### 1. **app/config/migrate.php** (Baru)
File ini berisi fungsi `runMigration()` yang:
- Mengecek apakah table `users` sudah ada
- Jika belum ada, membaca file schema PostgreSQL
- Menjalankan semua CREATE TABLE statements
- Menangani error jika table sudah ada (menggunakan IF NOT EXISTS)

```php
function runMigration(PDO $conn): void {
    // Cek table users
    // Jika tidak ada, jalankan schema dari skema_postgresql.sql
}
```

#### 2. **database/skema_postgresql.sql** (Diupdate)
Diubah untuk:
- Menghapus `CREATE DATABASE` dan `\c` (tidak support di Railway)
- Menambahkan `IF NOT EXISTS` ke semua `CREATE TABLE`
- Sesuaikan dengan PostgreSQL syntax

```sql
CREATE TABLE IF NOT EXISTS users (...)
CREATE TABLE IF NOT EXISTS kategori (...)
-- dst
```

#### 3. **app/config/database.php** (Dimodifikasi)
Flow baru:
1. Koneksi ke database
2. **Jalankan migration** ← NEW
3. Seed data awal

```php
// Run migration to create tables if needed
require_once __DIR__ . '/migrate.php';
runMigration($this->conn);

// Seed initial data if needed
require_once __DIR__ . '/seed.php';
seedIfNeeded($this->conn);
```

## Alur Kerja pada Deploy di Railway

### Scenario 1: Deploy Pertama Kali
```
Request pertama ke aplikasi
    ↓
Database connection (database sudah ada, table kosong)
    ↓
runMigration() dijalankan
    ↓
Deteksi table users tidak ada
    ↓
Baca skema_postgresql.sql
    ↓
Jalankan CREATE TABLE untuk semua table
    ↓
seedIfNeeded() dijalankan
    ↓
Insert default data (users, kategori, satuan, barang, dll)
    ↓
✅ Aplikasi siap digunakan
```

### Scenario 2: Deploy Update (Table Sudah Ada)
```
Request pertama ke aplikasi
    ↓
Database connection
    ↓
runMigration() dijalankan
    ↓
Deteksi table users sudah ada
    ↓
Skip migration, tidak ada yang dibuat
    ↓
seedIfNeeded() dijalankan
    ↓
Check setiap table, insert hanya jika kosong
    ↓
✅ Data existing tetap aman
```

## Testing Lokal

Untuk test di local sebelum deploy ke Railway:

### 1. Buat database PostgreSQL baru (kosong):
```bash
createdb toko_inventori_test
```

### 2. Edit .env untuk koneksi ke database test:
```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=toko_inventori_test
DB_USER=postgres
DB_PASS=password
```

### 3. Jalankan aplikasi:
```bash
php -S localhost:8000 -t public
```

### 4. Akses aplikasi di browser:
```
http://localhost:8000
```

**Expected hasil:**
- Tidak ada error "table doesn't exist"
- Table secara otomatis dibuat
- Data seed tersimpan
- Login dengan admin/admin123 berhasil

## Di Railway

Tidak ada konfigurasi tambahan yang perlu dilakukan. Cukup deploy seperti biasa:

1. Push ke GitHub
2. Railway otomatis detect, build, dan deploy
3. Aplikasi akan otomatis membuat table pada request pertama

## Keamanan & Best Practice

✅ Menggunakan `IF NOT EXISTS` - aman untuk re-deploy
✅ Migration berjalan sebelum seed - konsistensi data terjamin
✅ Error handling - jika schema bermasalah tetap di-log, tidak crash
✅ Performance - migration hanya check 1 table (users) untuk deteksi

## Troubleshooting

### Jika table masih tidak terbuat:

**1. Cek error log di Railway:**
- Buka Railway dashboard → Project → Logs
- Cari message yang mulai dengan "Migration" atau "error"

**2. Cek apakah database.php dipanggil:**
Tambahkan di `public/index.php` sebelum route handling:
```php
error_log("Database connection started at " . date('Y-m-d H:i:s'));
```

**3. Manual check database di Railway:**
```bash
# Connect ke Railway PostgreSQL
psql postgresql://username:password@host:port/toko_inventori

# List tables
\dt
```

**4. Force reset (optional):**
Set environment variable di Railway:
```
SEED_FORCE=true
```
Lalu redeploy. Ini akan reset user passwords ke default.
