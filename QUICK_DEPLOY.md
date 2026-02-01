# Quick Start: Deploy to Railway in 5 Minutes

## Prerequisites
- ✅ GitHub account dengan repo yang sudah created
- ✅ Railway account (https://railway.app)
- ✅ Project sudah di-commit ke GitHub

## 5-Minute Deployment

### 1️⃣ Railway Setup (1 min)
```
1. Go to https://railway.app/dashboard
2. New Project → PostgreSQL 15 → Create
3. New Service → GitHub → Select repo → Branch main
4. Deploy start automatically
```

### 2️⃣ Wait for Build (3 min)
```
Railway Dashboard → Web Service → Logs → Build Logs
Wait for: "Build completed successfully"
```

### 3️⃣ Initialize Database (1 min)

**Option A: Automatic (Recommended)**
- Access: https://your-app.up.railway.app/
- Wait 10 seconds
- System auto-creates tables

**Option B: Manual (If needed)**
- Railway → PostgreSQL → Data
- Copy-paste from RAILWAY_MANUAL_FIX.md
- Execute

## ✅ Verify

```
1. Open: https://your-app.up.railway.app/login
2. Login: admin / admin123
3. See dashboard = SUCCESS! 🎉
```

## 🐛 If Error

Check in this order:
1. Runtime Logs (most common: DB credentials)
2. Build Logs (docker issues)
3. PostgreSQL service (running?)

See TROUBLESHOOTING.md for full guide.

---

## Default Credentials

| User | Password | Role |
|------|----------|------|
| admin | admin123 | Administrator |
| kasir | kasir123 | Cashier |

---

## Important Files

- `DEPLOYMENT_GUIDE.md` - Full step-by-step
- `RAILWAY_ENVIRONMENT.md` - Environment variables
- `TROUBLESHOOTING.md` - Problem solving
- `RAILWAY_MANUAL_FIX.md` - Manual database setup
