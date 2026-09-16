<?php

require_once __DIR__ . '/../config/database.php';

class StokMutasi {
    private $conn;

    public function __construct(?PDO $conn = null) {
        if ($conn instanceof PDO) {
            $this->conn = $conn;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS stok_mutasi (
                id_mutasi SERIAL PRIMARY KEY,
                id_barang INT NOT NULL REFERENCES barang(id_barang) ON DELETE CASCADE,
                jenis_mutasi VARCHAR(20) NOT NULL CHECK (jenis_mutasi IN ('masuk', 'keluar', 'penyesuaian', 'retur')),
                satuan VARCHAR(50) NOT NULL DEFAULT 'pcs',
                jumlah NUMERIC(12,2) NOT NULL,
                tanggal_mutasi TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                id_transaksi INT NULL,
                tipe_transaksi VARCHAR(30) NULL,
                keterangan TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_stok_mutasi_barang_tanggal ON stok_mutasi (id_barang, tanggal_mutasi)");
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_stok_mutasi_transaksi ON stok_mutasi (tipe_transaksi, id_transaksi)");
        } catch (Exception $e) {
            error_log('ensure stok_mutasi table error: ' . $e->getMessage());
        }
    }

    private function normalizeDateTime($tanggal): string {
        $raw = trim((string)$tanggal);
        if ($raw === '') {
            return date('Y-m-d H:i:s');
        }

        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
        if ($parsed !== false) {
            return $parsed->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        }

        try {
            $date = new DateTimeImmutable($raw);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }

    // Older transactions may predate the movement ledger. Include those once only.
    public static function movementSql(): string {
        return "SELECT id_barang, tanggal_mutasi AS tanggal,
                       CASE WHEN jenis_mutasi = 'keluar' THEN -jumlah ELSE jumlah END AS delta
                FROM stok_mutasi
                UNION ALL
                SELECT d.id_barang, p.tanggal, d.jumlah * COALESCE(NULLIF(d.nilai_satuan, 0), 1)
                FROM detail_pembelian d JOIN pembelian p ON p.id_pembelian = d.id_pembelian
                WHERE NOT EXISTS (SELECT 1 FROM stok_mutasi m WHERE m.tipe_transaksi = 'pembelian'
                    AND m.id_transaksi = p.id_pembelian AND m.id_barang = d.id_barang)
                UNION ALL
                SELECT d.id_barang, p.tanggal, -d.jumlah * COALESCE(NULLIF(d.nilai_satuan, 0), 1)
                FROM detail_penjualan d JOIN penjualan p ON p.id_penjualan = d.id_penjualan
                WHERE NOT EXISTS (SELECT 1 FROM stok_mutasi m WHERE m.tipe_transaksi = 'penjualan'
                    AND m.id_transaksi = p.id_penjualan AND m.id_barang = d.id_barang)";
    }

    public function getSaldoAt(int $idBarang, string $tanggalTarget): float {
        // A date includes the entire day; a timestamp represents an exact instant.
        $dateOnly = preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($tanggalTarget)) === 1;
        $tanggal = $dateOnly
            ? (new DateTimeImmutable($tanggalTarget))->modify('+1 day')->format('Y-m-d 00:00:00')
            : $this->normalizeDateTime($tanggalTarget);
        $comparison = $dateOnly ? '>=' : '>';
        // Current stock includes opening stock, imports and manual adjustments.
        // Reverse later recorded movements instead of assuming the ledger starts at zero.
        $movements = self::movementSql();
        $query = "WITH movements AS ($movements)
                  SELECT b.stok - COALESCE((
                    SELECT SUM(m.delta) FROM movements m
                    WHERE m.id_barang = b.id_barang AND m.tanggal $comparison :tanggal
                  ), 0) AS saldo FROM barang b WHERE b.id_barang = :id_barang";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_barang', $idBarang, PDO::PARAM_INT);
        $stmt->bindValue(':tanggal', $tanggal);
        $stmt->execute();
        $row = $stmt->fetch();

        return (float)($row['saldo'] ?? 0);
    }

    public function canFulfill(int $idBarang, float $jumlah, string $tanggal): bool {
        if ($jumlah <= 0) {
            return true;
        }

        return $this->getSaldoAt($idBarang, $tanggal) >= $jumlah;
    }

    public function recordMasuk(int $idBarang, float $jumlah, string $tanggalMutasi, ?int $idTransaksi = null, ?string $tipeTransaksi = null, ?string $satuan = null, ?string $keterangan = null): bool {
        if ($jumlah <= 0) {
            return true;
        }

        $query = "INSERT INTO stok_mutasi (id_barang, jenis_mutasi, satuan, jumlah, tanggal_mutasi, id_transaksi, tipe_transaksi, keterangan)
                  VALUES (:id_barang, 'masuk', :satuan, :jumlah, :tanggal_mutasi, :id_transaksi, :tipe_transaksi, :keterangan)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_barang', $idBarang, PDO::PARAM_INT);
        $stmt->bindValue(':satuan', trim((string)($satuan ?: 'pcs')) ?: 'pcs');
        $stmt->bindValue(':jumlah', $jumlah);
        $stmt->bindValue(':tanggal_mutasi', $this->normalizeDateTime($tanggalMutasi));
        $stmt->bindValue(':id_transaksi', $idTransaksi, $idTransaksi === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':tipe_transaksi', $tipeTransaksi ?: null, $tipeTransaksi === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':keterangan', $keterangan ?: null, $keterangan === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function recordKeluar(int $idBarang, float $jumlah, string $tanggalMutasi, ?int $idTransaksi = null, ?string $tipeTransaksi = null, ?string $satuan = null, ?string $keterangan = null): bool {
        if ($jumlah <= 0) {
            return true;
        }

        $query = "INSERT INTO stok_mutasi (id_barang, jenis_mutasi, satuan, jumlah, tanggal_mutasi, id_transaksi, tipe_transaksi, keterangan)
                  VALUES (:id_barang, 'keluar', :satuan, :jumlah, :tanggal_mutasi, :id_transaksi, :tipe_transaksi, :keterangan)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_barang', $idBarang, PDO::PARAM_INT);
        $stmt->bindValue(':satuan', trim((string)($satuan ?: 'pcs')) ?: 'pcs');
        $stmt->bindValue(':jumlah', $jumlah);
        $stmt->bindValue(':tanggal_mutasi', $this->normalizeDateTime($tanggalMutasi));
        $stmt->bindValue(':id_transaksi', $idTransaksi, $idTransaksi === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':tipe_transaksi', $tipeTransaksi ?: null, $tipeTransaksi === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':keterangan', $keterangan ?: null, $keterangan === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function rollbackByTransaksi(string $tipeTransaksi, int $idTransaksi): void {
        $query = "DELETE FROM stok_mutasi WHERE tipe_transaksi = :tipe_transaksi AND id_transaksi = :id_transaksi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':tipe_transaksi', $tipeTransaksi);
        $stmt->bindValue(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
        $stmt->execute();
    }
}
