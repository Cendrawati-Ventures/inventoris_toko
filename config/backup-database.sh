#!/bin/bash

#############################################
# Automated Database Backup Script
# Untuk Sistem Inventori Toko
#############################################

# Konfigurasi
DB_USER="toko_user"
DB_PASS="password_anda"
DB_NAME="toko_inventori"
BACKUP_DIR="/var/backups/toko-inventori"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Buat direktori backup jika belum ada
mkdir -p "$BACKUP_DIR"

# Nama file backup
BACKUP_FILE="$BACKUP_DIR/toko_inventori_$DATE.sql.gz"

# Lakukan backup dan compress
echo "Starting backup of database: $DB_NAME"
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_FILE"

# Check jika backup berhasil
if [ $? -eq 0 ]; then
    echo "Backup successful: $BACKUP_FILE"
    
    # Hapus backup lama (lebih dari RETENTION_DAYS hari)
    find "$BACKUP_DIR" -name "toko_inventori_*.sql.gz" -mtime +$RETENTION_DAYS -delete
    echo "Old backups (>$RETENTION_DAYS days) deleted"
else
    echo "Backup failed!"
    exit 1
fi

# Optional: Kirim notifikasi email
# echo "Database backup completed" | mail -s "Toko Inventori Backup $(date)" admin@yourdomain.com

echo "Backup process completed at $(date)"
