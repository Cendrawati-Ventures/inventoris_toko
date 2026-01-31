#!/bin/bash

# Deployment Setup Script untuk Toko Inventori
# Usage: bash setup-deployment.sh

set -e

echo "================================"
echo "Toko Inventori - Setup Deployment"
echo "================================"
echo ""

# Check if running as root for production
if [ "$EUID" -ne 0 ] && [ "$1" != "--dev" ]; then 
    echo "⚠️  Sebaiknya jalankan script ini dengan sudo untuk production"
    echo "Gunakan: sudo bash setup-deployment.sh"
    read -p "Lanjutkan tanpa sudo? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Get project root
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
echo "📁 Project root: $PROJECT_ROOT"
echo ""

# 1. Create necessary directories
echo "1️⃣  Creating directories..."
mkdir -p "$PROJECT_ROOT/logs"
mkdir -p "$PROJECT_ROOT/app/uploads"
mkdir -p "$PROJECT_ROOT/public/uploads"
mkdir -p "$PROJECT_ROOT/database/backups"
echo "   ✓ Directories created"
echo ""

# 2. Setup file permissions
echo "2️⃣  Setting up file permissions..."
chmod 755 "$PROJECT_ROOT/logs"
chmod 755 "$PROJECT_ROOT/app/uploads"
chmod 755 "$PROJECT_ROOT/public/uploads"
chmod 755 "$PROJECT_ROOT/database/backups"

# Set file permissions for all files and directories
find "$PROJECT_ROOT" -type f -exec chmod 644 {} \;
find "$PROJECT_ROOT" -type d -exec chmod 755 {} \;

# Make public folder readable
chmod 755 "$PROJECT_ROOT/public"
echo "   ✓ File permissions set"
echo ""

# 3. Setup .env file
echo "3️⃣  Setting up environment variables..."
if [ ! -f "$PROJECT_ROOT/.env" ]; then
    if [ -f "$PROJECT_ROOT/.env.example" ]; then
        cp "$PROJECT_ROOT/.env.example" "$PROJECT_ROOT/.env"
        echo "   ✓ .env file created from template"
        echo "   ⚠️  PENTING: Edit .env dengan database credentials Anda!"
    else
        echo "   ✗ .env.example tidak ditemukan"
    fi
else
    echo "   ✓ .env file already exists"
fi
echo ""

# 4. Check PHP requirements
echo "4️⃣  Checking PHP requirements..."
PHP_VERSION=$(php -v | grep -oP 'PHP \K[0-9]+\.[0-9]+')
echo "   PHP Version: $PHP_VERSION"

# Check required extensions
REQUIRED_EXTENSIONS=("pdo" "pdo_pgsql" "json")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -qi "$ext"; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    echo "   ✗ Missing extensions: ${MISSING_EXTENSIONS[*]}"
    echo "   Install missing extensions terlebih dahulu"
    exit 1
else
    echo "   ✓ All required extensions present"
fi
echo ""

# 5. Create log file
echo "5️⃣  Creating log file..."
touch "$PROJECT_ROOT/logs/error.log"
chmod 666 "$PROJECT_ROOT/logs/error.log"
echo "   ✓ Log file created"
echo ""

# 6. Database configuration
echo "6️⃣  Database Setup"
echo "   Sebelum lanjut, pastikan:"
echo "   ✓ PostgreSQL server sudah running"
echo "   ✓ Database 'toko_inventori' sudah dibuat"
echo "   ✓ User database sudah dibuat"
echo ""

read -p "   Jalankan schema import sekarang? (y/n) " -n 1 -r IMPORT_DB
echo
if [[ $IMPORT_DB =~ ^[Yy]$ ]]; then
    read -p "   Database host (default: localhost): " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "   Database user (default: postgres): " DB_USER
    DB_USER=${DB_USER:-postgres}
    
    read -p "   Database name (default: toko_inventori): " DB_NAME
    DB_NAME=${DB_NAME:-toko_inventori}
    
    echo "   Importing schema..."
    if psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f "$PROJECT_ROOT/database/skema_postgresql.sql" > /dev/null 2>&1; then
        echo "   ✓ Schema imported successfully"
    else
        echo "   ✗ Failed to import schema"
        echo "   Manual import: psql -h $DB_HOST -U $DB_USER -d $DB_NAME < database/skema_postgresql.sql"
    fi
else
    echo "   ⚠️  Manual import needed:"
    echo "   psql -h <host> -U <user> -d toko_inventori < database/skema_postgresql.sql"
fi
echo ""

# 7. Web server configuration
echo "7️⃣  Web Server Configuration"
if command -v nginx &> /dev/null; then
    echo "   ✓ Nginx detected"
    echo "   Copy nginx.conf.example ke /etc/nginx/sites-available/"
elif command -v apache2 &> /dev/null; then
    echo "   ✓ Apache detected"
    echo "   Pastikan mod_rewrite enabled: a2enmod rewrite"
else
    echo "   ⚠️  Web server tidak terdeteksi"
fi
echo ""

# 8. Summary
echo "================================"
echo "✅ Setup selesai!"
echo "================================"
echo ""
echo "📋 Checklist berikutnya:"
echo "   [ ] Edit .env dengan database credentials"
echo "   [ ] Configure web server (Apache/Nginx)"
echo "   [ ] Setup SSL/TLS certificate"
echo "   [ ] Test database connection"
echo "   [ ] Akses aplikasi dan verify"
echo ""
echo "📖 Dokumentasi: DEPLOYMENT.md"
echo ""
