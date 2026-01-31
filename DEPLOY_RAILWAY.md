# 🚀 Deploy ke Railway - Panduan Lengkap

## Prasyarat

✓ Git repository sudah tersedia di GitHub: https://github.com/Riskyduha/inventoris_toko.git
✓ Account Railway sudah dibuat
✓ Environment variables sudah dikonfigurasi (atau siap dikonfigurasi)

## Step-by-Step Deploy

### 1️⃣ Connect Repository ke Railway

1. **Buka Railway Dashboard**: https://railway.app/dashboard
2. **Klik "New Project"**
3. **Pilih "Deploy from GitHub"**
4. **Authorize Railway** dengan GitHub account Anda
5. **Select Repository**: Pilih `Riskyduha/inventoris_toko`
6. **Klik "Deploy"**

Railway akan:
- ✅ Auto-detect Dockerfile
- ✅ Build Docker image
- ✅ Deploy application

### 2️⃣ Configure Database (PostgreSQL)

Railway menyediakan plugin database. Ikuti langkah ini:

1. **Di Railway Project Dashboard**
2. **Klik "Add"** → **"Add Service"**
3. **Pilih "PostgreSQL"**
4. **Klik "Create"**
5. **Copy environment variables** yang dihasilkan

### 3️⃣ Configure Environment Variables

1. **Di Railway Dashboard**, buka project Anda
2. **Klik tab "Variables"**
3. **Setup environment variables berikut:**

```
DB_HOST=<dari PostgreSQL plugin>
DB_PORT=<dari PostgreSQL plugin>
DB_NAME=<database name dari PostgreSQL plugin>
DB_USER=<username dari PostgreSQL plugin>
DB_PASS=<password dari PostgreSQL plugin>
```

**Cara mendapatkan nilai:**
- Buka PostgreSQL service di Railway
- Tab "Connect" → Copy connection details

### 4️⃣ Automatic Features di Deploy

Saat deploy berjalan, sistem akan **otomatis**:

```
Railway Deploy
    ↓
Docker build & push
    ↓
Container start
    ↓
[AUTOMATIC] Create database tables
    ↓
[AUTOMATIC] Seed initial data (users, categories, etc)
    ↓
✅ Application ready
```

**Tidak perlu manual setup database!**

### 5️⃣ Verify Deployment

1. **Check deployment logs** di Railway
2. **Buka aplikasi** (URL akan muncul di Railway dashboard)
3. **Login dengan default credentials**:
   - Username: `admin`
   - Password: `admin123`

Atau:
   - Username: `kasir`
   - Password: `kasir123`

## ⚠️ Troubleshooting

### Error: "Database connection failed"

**Solution**:
1. Pastikan PostgreSQL service sudah "Running" di Railway
2. Cek environment variables sudah benar: `DB_HOST`, `DB_PORT`, dll
3. Cek Railway logs untuk detail error

### Error: "table users doesn't exist"

**Solution** (sangat jarang terjadi):
1. Manual trigger migration dengan set env var:
   ```
   FORCE_MIGRATION=true
   ```
2. Redeploy project

### Login tidak berhasil

**Solution**:
1. Pastikan seed sudah berjalan (check dalam logs)
2. Manual seed dengan:
   ```
   SEED_FORCE=true
   ```
3. Redeploy

## 📊 Post-Deploy Checklist

- [ ] Aplikasi accessible di Railway URL
- [ ] Login berhasil dengan admin/admin123
- [ ] Database tables terlihat di PostgreSQL plugin
- [ ] Bisa membuat/edit data (barang, pembelian, penjualan)
- [ ] No errors di Railway logs

## 🔄 Update Aplikasi

Untuk deploy update:

1. **Local development**: Buat changes
2. **Git push ke GitHub**:
   ```bash
   git add .
   git commit -m "feature: description"
   git push origin main
   ```
3. **Railway otomatis redeploy**

Selesai! Tidak perlu akses Railway secara manual.

## 📝 Useful Railway Commands

**View Logs:**
- Railway Dashboard → Project → Logs

**SSH into Container:**
```bash
railway shell
```

**View Database:**
```bash
railway database shell postgresql
```

## 🎯 Production Recommendations

Sebelum production, lakukan:

1. **Change default passwords** di database
2. **Set APP_DEBUG=false** untuk hide error details
3. **Setup SSL/HTTPS** (Railway handle ini automatically)
4. **Backup database regularly**
5. **Monitor logs** untuk unusual activity

---

**Questions?** Check [MIGRATION.md](MIGRATION.md) untuk technical details.
