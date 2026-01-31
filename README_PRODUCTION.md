# 🏪 Toko Inventori - Sistem Inventory Management

Aplikasi modern untuk mengelola inventory toko dengan fitur lengkap: pembelian, penjualan, laporan, dan dashboard.

## ✨ Fitur Utama

- 📊 **Dashboard** - Ringkasan penjualan & inventory
- 📦 **Manajemen Barang** - CRUD barang, kategorisasi, tracking stok
- 🛒 **Penjualan** - Input transaksi, tracking hutang, print nota
- 🏭 **Pembelian** - Manajemen pembelian, supplier tracking
- 📈 **Laporan** - Penjualan, pembelian, stok, keuntungan (dengan pagination)
- 💰 **Pelacakan Hutang** - Manage customer debts dengan due date
- 📱 **Responsive UI** - Mobile-friendly interface dengan Tailwind CSS

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 7.4+ |
| **Database** | PostgreSQL 12+ |
| **Frontend** | HTML5, CSS3 (Tailwind), JavaScript |
| **Server** | Apache/Nginx |
| **Version Control** | Git |

## 📋 System Requirements

```
- PHP: 7.4 or higher
  Extensions: pdo, pdo_pgsql, json, mbstring
  
- PostgreSQL: 12 or higher

- Web Server: Apache 2.4+ (mod_rewrite) or Nginx

- RAM: 512MB minimum (1GB recommended)

- Disk Space: 1GB (+ backup storage)

- Browser: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
```

## 🚀 Quick Start

### Development Setup (Local)

```bash
# 1. Clone repository
git clone <repo-url>
cd toko-inventori

# 2. Copy environment file
cp .env.example .env

# 3. Edit .env dengan database local Anda
nano .env

# 4. Create database
createdb toko_inventori

# 5. Import schema
psql -U postgres -d toko_inventori < database/skema_postgresql.sql

# 6. Start development server
cd public
php -S localhost:8000

# Access: http://localhost:8000
```

### Production Deployment

Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk panduan lengkap.

**Quick steps:**
```bash
# 1. Run setup script
bash setup-deployment.sh

# 2. Configure .env
nano .env

# 3. Import database
psql -h <host> -U <user> -d toko_inventori < database/skema_postgresql.sql

# 4. Setup web server (Apache/Nginx)

# 5. Test production readiness
php test-production.php

# 6. Access https://yourdomain.com
```

## 📁 Project Structure

```
toko-inventori/
├── app/
│   ├── config/          # Database & application config
│   ├── controllers/     # Request handlers
│   ├── models/          # Database models
│   ├── views/           # UI templates
│   ├── helpers/         # Utility functions
│   └── uploads/         # File uploads (if any)
├── public/
│   ├── index.php        # Application entry point
│   ├── assets/          # CSS, JS, images
│   ├── uploads/         # Public uploads
│   └── .htaccess        # Apache routing
├── database/
│   ├── skema_postgresql.sql    # Database schema
│   ├── backups/         # Database backups
│   └── migrations/      # Database changes (if any)
├── logs/                # Application logs
├── .env                 # Environment variables (production)
├── .env.example         # Environment template
├── .gitignore           # Git ignore rules
├── DEPLOYMENT.md        # Deployment guide
├── PRODUCTION_CHECKLIST.md  # Pre-deployment checklist
└── README.md            # This file
```

## 🔐 Security Best Practices

✅ **Implemented:**
- Environment variables for credentials
- Prepared statements (SQL injection prevention)
- Error handling (no stack traces in production)
- Security headers (X-Frame-Options, CSP, etc)
- HTTPS enforcement
- File permission restrictions

⚠️ **To implement:**
- [ ] Add CSRF tokens to forms
- [ ] Add input validation/sanitization
- [ ] Setup rate limiting
- [ ] Enable API authentication (if needed)
- [ ] Regular security audits

## 📊 Database Architecture

### Main Tables:
- `users` - Admin accounts
- `barang` - Product inventory
- `kategori` - Product categories
- `satuan` - Unit of measurement
- `pembelian` - Purchase transactions
- `detail_pembelian` - Purchase details
- `penjualan` - Sales transactions
- `detail_penjualan` - Sales details
- `hutang` - Debt tracking
- `konfigurasi_nota` - Receipt configuration

**Note:** Schema optimized for PostgreSQL with proper indexes and constraints.

## 🔧 Common Operations

### Add New Product
```
1. Go to Barang → Tambah Barang Baru
2. Fill product details (name, code, category, price)
3. Set initial stock
4. Save
```

### Record Purchase
```
1. Go to Pembelian → Tambah Pembelian Baru
2. Select products and quantities
3. Enter supplier name
4. Save
```

### Record Sales
```
1. Go to Penjualan → Tambah Penjualan Baru
2. Select products and quantities
3. Enter payment details
4. Track debt if needed
5. Print nota
6. Save
```

### View Reports
```
1. Go to Laporan
2. Select report type (Penjualan, Pembelian, Stok, Keuntungan)
3. Filter by date range
4. View with pagination
```

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| White screen | Check `logs/error.log` and `APP_DEBUG=false` |
| Database error | Verify connection in .env, check PostgreSQL running |
| 404 Not Found | Ensure mod_rewrite enabled, check .htaccess |
| Permission denied | Run `chmod 755 logs/` and check web server user |
| Slow performance | Check database indexes, enable caching |

**Full troubleshooting:** See [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md#-common-issues--solutions)

## 📞 Support & Maintenance

### Daily Tasks
- [ ] Monitor error logs
- [ ] Check disk space
- [ ] Verify database connections

### Weekly Tasks
- [ ] Review sales/purchase reports
- [ ] Check inventory accuracy
- [ ] Backup database

### Monthly Tasks
- [ ] Security patches
- [ ] Performance optimization
- [ ] Archive old data

## 📝 Change Log

### v1.0 (31 Jan 2026) - Production Release
- ✅ PostgreSQL database migration complete
- ✅ Pagination for all listing pages
- ✅ Stok validation for sales
- ✅ Security hardening
- ✅ Production-ready configuration
- ✅ Comprehensive documentation

## 📄 Documentation

- [DEPLOYMENT.md](DEPLOYMENT.md) - Step-by-step deployment guide
- [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) - Pre-deployment verification
- `.env.example` - Configuration template
- `nginx.conf.example` - Nginx configuration template

## 👨‍💼 Admin Access

**Default Admin:**
- Email: admin (or check database for actual account)
- Password: (Set during first setup)

**To create admin:**
```bash
php bin/create-admin.php  # If script available
# Or manually insert into database
```

## 📦 Backup & Recovery

### Backup Database
```bash
pg_dump -h localhost -U postgres -d toko_inventori | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Restore Database
```bash
gunzip -c backup_20260131.sql.gz | psql -h localhost -U postgres -d toko_inventori
```

## 🚢 Deployment Checklist

Before going to production, ensure:

- [ ] All tests pass
- [ ] Error logs reviewed and clean
- [ ] Database backed up
- [ ] SSL/HTTPS enabled
- [ ] .env configured
- [ ] Firewall rules set
- [ ] Monitoring enabled
- [ ] Backup strategy in place
- [ ] Team trained on operation

Complete checklist: [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)

## 📧 License

Private Project - UD. BERSAUDARA

## 👥 Contributors

- Development: Risky Duha
- Documentation: Copilot AI

---

**Last Updated:** 31 Januari 2026  
**Status:** ✅ Production Ready  
**Version:** 1.0
