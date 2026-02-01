# Railway Configuration Quick Reference

## Environment Variables (Auto-Set)

Railway automatically provides these for PostgreSQL:

```
DATABASE_URL=postgresql://user:password@host:port/database
DB_HOST=maglev.proxy.rlwy.net (or similar)
DB_PORT=5432
DB_NAME=railway (default)
DB_USER=postgres
DB_PASS=your_generated_password
PORT=8080 (or assigned port)
```

## Required Environment Variables

Add to Railway Variables tab:

| Variable | Value | Notes |
|----------|-------|-------|
| `DB_HOST` | From DATABASE_URL | Auto-provided by Railway |
| `DB_PORT` | 5432 | Default PostgreSQL port |
| `DB_NAME` | railway | Default from Railway |
| `DB_USER` | postgres | Default from Railway |
| `DB_PASS` | Auto-generated | From Railway PostgreSQL |

## Optional Environment Variables

```
APP_ENV=production
APP_DEBUG=false
PORT=8080 (Railway sets this automatically)
```

## How Database Connection Works

1. **App starts** → `Database::getConnection()` called
2. **Check env vars** → Read from Railway environment
3. **Connect to PostgreSQL** → Using provided credentials
4. **Auto-migration** → Creates tables if needed
5. **Auto-seeding** → Inserts default data if needed

## Verifying Variables in Railway

1. Go to Railway Dashboard
2. Click your Web Service
3. Click "Variables" tab
4. All DB_* variables should be visible

## Setting Custom Variables

If you need to add/modify variables:

1. Railway Dashboard → Variables
2. Click "Edit Raw" or use form
3. Add new variable
4. Save changes
5. Railway auto-redeploys with new variables

## Notes

- Variables are encrypted by Railway
- Changes trigger automatic redeploy
- DATABASE_URL is the combined connection string
- Never expose credentials in logs or code
