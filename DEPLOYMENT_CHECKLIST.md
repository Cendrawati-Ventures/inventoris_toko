# Railway Deployment Checklist

Gunakan checklist ini untuk memastikan deployment ke Railway berhasil.

## 🔴 PRE-DEPLOYMENT (Lakukan sebelum deploy)

### GitHub Repository
- [ ] Semua file sudah di-commit
- [ ] Tidak ada uncommitted changes
  ```bash
  git status
  # Output harus: "nothing to commit"
  ```
- [ ] Branch adalah `main`
  ```bash
  git branch
  # Output: "* main"
  ```
- [ ] Latest commit pushed to GitHub
  ```bash
  git log -1 --oneline
  # Verify di GitHub web interface
  ```

### Environment Setup
- [ ] `.env` file dibuat dari `.env.example`
- [ ] `.env` NOT in git
  ```bash
  cat .gitignore | grep ".env"
  # Should exist
  ```
- [ ] Local `.env` has values
  ```bash
  grep "DB_" .env
  # Should show DB_HOST, DB_USER, etc.
  ```

### Local Testing
- [ ] PostgreSQL running locally
  ```bash
  psql -U postgres -l
  # Should list databases
  ```
- [ ] Database `toko_inventori` exists
  ```bash
  psql -U postgres -d toko_inventori -c "\dt"
  # Should show tables
  ```
- [ ] PHP local server works
  ```bash
  php -S localhost:8000 -t public
  # Should start without error
  ```
- [ ] Can access login page
  ```
  http://localhost:8000/login
  # Should load
  ```
- [ ] Can login with default credentials
  ```
  Username: admin
  Password: admin123
  # Should login successfully
  ```

### Docker (Optional but recommended)
- [ ] Docker Desktop running
- [ ] Docker build succeeds
  ```bash
  docker build -t test-build .
  # Should complete successfully
  ```

---

## 🟡 DEPLOYMENT (Di Railway)

### Account & Project Setup
- [ ] Railway account created (railway.app)
- [ ] GitHub connected to Railway account
- [ ] GitHub repository visible in Railway

### Create PostgreSQL Database
- [ ] Login to railway.app dashboard
- [ ] New Project → PostgreSQL 15
- [ ] Wait for database created
- [ ] Copy credentials (note if auto-provided)

### Connect GitHub Repository
- [ ] Add new service → GitHub Repo
- [ ] Select correct repository
- [ ] Select branch: `main`
- [ ] Deploy button clicked

### Monitor Build
- [ ] Go to Logs → Build Logs
- [ ] Wait for build completion (2-3 min)
  ```
  Expected: "Build completed successfully"
  ```
- [ ] Verify no build errors
- [ ] Copy application URL when ready

### Check Service Status
- [ ] Go to Logs → Runtime Logs
- [ ] Verify PHP-FPM started
  ```
  Expected log: "Starting PHP-FPM..."
  ```
- [ ] Verify Nginx started
  ```
  Expected log: "Starting Nginx..."
  ```
- [ ] No critical errors in logs

---

## 🟢 POST-DEPLOYMENT (Setelah deploy)

### Application Availability
- [ ] Access application URL
  ```
  https://your-app.up.railway.app/
  # Should load (might show error if no DB yet)
  ```
- [ ] Check HTTP status
  ```bash
  curl -I https://your-app.up.railway.app/login
  # Should be: HTTP/2 200
  ```

### Database Initialization
- [ ] Wait 30 seconds after first access
- [ ] Check Runtime Logs for migration
  ```
  Expected: "✓ Migration: Starting table creation..."
  Expected: "✓ Migration completed..."
  ```
- [ ] If no migration logs, do manual SQL setup

### Manual Database Setup (If needed)
- [ ] Go to Railway → PostgreSQL service
- [ ] Click Data/Query tab
- [ ] Copy SQL from RAILWAY_MANUAL_FIX.md
- [ ] Execute SQL
- [ ] Verify: SELECT COUNT(*) FROM users; → should return 2

### Environment Variables Verification
- [ ] Railway → Web Service → Variables
- [ ] Verify DATABASE_URL present
- [ ] Verify DB_HOST set correctly
- [ ] Verify DB_USER = postgres (or correct user)
- [ ] Verify DB_PASS not empty

### Login Test
- [ ] Open: https://your-app.up.railway.app/login
- [ ] Login with:
  ```
  Username: admin
  Password: admin123
  ```
- [ ] Should redirect to dashboard
- [ ] Dashboard shows without errors

### Dashboard Verification
- [ ] Dashboard page loads
- [ ] Statistics section visible
- [ ] Menu items visible (Barang, Pembelian, etc.)
- [ ] No 500 errors
- [ ] No database connection errors

### Data Verification
- [ ] Check SQL in PostgreSQL:
  ```sql
  SELECT COUNT(*) FROM users;
  -- Should return: 2
  
  SELECT COUNT(*) FROM kategori;
  -- Should return: 6
  
  SELECT COUNT(*) FROM satuan;
  -- Should return: 11
  
  SELECT COUNT(*) FROM barang;
  -- Should be > 0
  ```

### Feature Testing
- [ ] Barang page: Can view items
- [ ] Create new item: Form loads, can create
- [ ] Edit item: Can modify existing
- [ ] Delete item: Can delete
- [ ] Kategori page: Can view categories
- [ ] Satuan page: Can view units

---

## ⚠️ COMMON ISSUES DURING DEPLOYMENT

### Issue: Build Failed
- [ ] Check Build Logs
- [ ] Retry: git commit --allow-empty -m "retry"
- [ ] If persists: check Dockerfile for errors

### Issue: Application doesn't respond (502)
- [ ] Check if PHP-FPM running (Runtime Logs)
- [ ] Check database connection (environment variables)
- [ ] Force redeploy: Railway → Redeploy button
- [ ] Wait 5 minutes for container restart

### Issue: Database Connection Fails
- [ ] Verify env variables in Railway dashboard
- [ ] Check PostgreSQL service is running
- [ ] Verify DB credentials are correct
- [ ] Test connection manually in PostgreSQL console

### Issue: Tables Not Created
- [ ] Check Migration logs (Runtime Logs)
- [ ] If not shown: tables didn't auto-create
- [ ] Run manual SQL from RAILWAY_MANUAL_FIX.md
- [ ] Verify: SELECT * FROM users; → should have 2 rows

### Issue: Login Fails
- [ ] Verify users table has data
- [ ] Check password hash (SQL):
  ```sql
  SELECT username, role FROM users;
  ```
- [ ] If empty: run insert SQL from RAILWAY_MANUAL_FIX.md
- [ ] Test login again

---

## 📊 MONITORING CHECKLIST (After deployment)

### Daily
- [ ] Check if application loads
- [ ] Check for error messages
- [ ] Verify login works

### Weekly
- [ ] Review Runtime Logs for errors
- [ ] Check CPU/Memory usage
- [ ] Test features (create, edit, delete)
- [ ] Verify database connection stable

### Monthly
- [ ] Database backup (if available)
- [ ] Review deployment history
- [ ] Check for updates
- [ ] Performance review

---

## 🔐 SECURITY CHECKLIST

### Immediately After Deployment
- [ ] ⚠️ Change default admin password
  ```sql
  -- Generate new hash:
  php -r "echo password_hash('new_password', PASSWORD_BCRYPT);"
  
  -- Update in database:
  UPDATE users SET password = 'new_hash' WHERE username = 'admin';
  ```
- [ ] Change kasir password too
- [ ] Remove test accounts if any
- [ ] Verify no debug files in production

### Before Full Use
- [ ] Setup HTTPS (Railway provides free SSL)
- [ ] Review firewall rules
- [ ] Setup database backups
- [ ] Document access credentials (safely)
- [ ] Setup monitoring alerts

### Ongoing
- [ ] Regular password changes
- [ ] Review access logs
- [ ] Database integrity checks
- [ ] Dependency updates
- [ ] Security patches

---

## ✅ FINAL VERIFICATION

- [ ] All checklist items above completed
- [ ] No errors in logs
- [ ] Application responsive
- [ ] Database working
- [ ] Login successful
- [ ] Data showing correctly
- [ ] Features functional
- [ ] Passwords changed from default
- [ ] Monitoring active
- [ ] Team notified of deployment

---

## 🆘 IF DEPLOYMENT FAILS

### First Steps
1. Read TROUBLESHOOTING.md
2. Check all Runtime and Build Logs
3. Verify environment variables
4. Check database service running

### Debug Information to Collect
- [ ] Full build log (first 100 lines)
- [ ] Full runtime log (last 50 lines)
- [ ] Error messages (exact)
- [ ] Environment variables (sanitized)
- [ ] PostgreSQL status (running?)

### Rollback if Critical
```bash
# Revert last commit
git revert HEAD
git push origin main

# Railway auto-redeploys with previous version
```

---

## 📞 DEPLOYMENT SUPPORT

### Resources
- DEPLOYMENT_GUIDE.md - Detailed steps
- QUICK_DEPLOY.md - Quick reference
- TROUBLESHOOTING.md - Problem solving
- RAILWAY_ENVIRONMENT.md - Environment setup

### External Help
- Railway Docs: https://docs.railway.app
- PostgreSQL Docs: https://postgresql.org/docs
- Stack Overflow: Tag with railway
- GitHub Issues: If code problem

---

*Use this checklist for every deployment to ensure success!*

**Last Updated:** February 1, 2026
