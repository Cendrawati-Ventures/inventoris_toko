<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuditTrail.php';

class Barang {
    private $conn;
    private $table = 'barang';
    private $auditTrail;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->auditTrail = new AuditTrail($this->conn);
        $this->ensureStockAuditStructure();
        $this->ensureExpiryColumn();
        $this->ensureSatuanDetailColumn();
    }

    private function ensureSatuanDetailColumn() {
        try {
            $checkColumnQuery = "SELECT 1
                                 FROM information_schema.columns
                                 WHERE table_schema = 'public'
                                   AND table_name = 'barang'
                                   AND column_name = 'satuan_detail'
                                 LIMIT 1";
            $stmt = $this->conn->query($checkColumnQuery);
            $exists = $stmt && $stmt->fetch();

            if (!$exists) {
                $this->conn->exec("ALTER TABLE barang ADD COLUMN satuan_detail JSONB NOT NULL DEFAULT '[]'::jsonb");
            }
        } catch (Exception $e) {
            error_log('ensureSatuanDetailColumn error: ' . $e->getMessage());
        }
    }

    private function ensureStockAuditStructure() {
        try {
            $checkColumnQuery = "SELECT 1
                                 FROM information_schema.columns
                                 WHERE table_schema = 'public'
                                   AND table_name = 'barang'
                                   AND column_name = 'stok_updated_by'
                                 LIMIT 1";
            $stmt = $this->conn->query($checkColumnQuery);
            $exists = $stmt && $stmt->fetch();

            if (!$exists) {
                $this->conn->exec("ALTER TABLE barang ADD COLUMN stok_updated_by INT NULL");
            }

            $checkConstraintQuery = "SELECT 1
                                     FROM information_schema.table_constraints
                                     WHERE table_schema = 'public'
                                       AND table_name = 'barang'
                                       AND constraint_name = 'fk_barang_stok_updated_by'
                                     LIMIT 1";
            $stmtConstraint = $this->conn->query($checkConstraintQuery);
            $constraintExists = $stmtConstraint && $stmtConstraint->fetch();

            if (!$constraintExists) {
                $this->conn->exec("ALTER TABLE barang
                                   ADD CONSTRAINT fk_barang_stok_updated_by
                                   FOREIGN KEY (stok_updated_by) REFERENCES users(id_user) ON DELETE SET NULL");
            }
        } catch (Exception $e) {
            error_log('ensureStockAuditStructure error: ' . $e->getMessage());
        }
    }

    private function ensureExpiryColumn() {
        try {
            $this->conn->exec("ALTER TABLE barang ADD COLUMN IF NOT EXISTS tanggal_expired DATE NULL");
        } catch (Exception $e) {
            error_log('ensureExpiryColumn error: ' . $e->getMessage());
        }
    }

    public function normalizeSatuanDetail($payload): array {
        $items = is_array($payload) ? $payload : (is_string($payload) ? json_decode($payload, true) : []);
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $namaSatuan = trim((string)($item['satuan'] ?? ''));
            if ($namaSatuan === '') {
                continue;
            }

            $nilaiSatuan = $item['nilai'] ?? $item['nilai_satuan'] ?? $item['konversi'] ?? 1;
            $nilaiNumeric = is_numeric($nilaiSatuan) ? (float)$nilaiSatuan : 1;
            if (!is_finite($nilaiNumeric) || $nilaiNumeric <= 0) {
                $nilaiNumeric = 1;
            }

            $hargaBeli = isset($item['harga_beli']) ? (float)$item['harga_beli'] : 0;
            $hargaJual = isset($item['harga_jual']) ? (float)$item['harga_jual'] : 0;

            if ($hargaBeli < 0) {
                $hargaBeli = 0;
            }
            if ($hargaJual < 0) {
                $hargaJual = 0;
            }

            $normalized[] = [
                'satuan' => $namaSatuan,
                'nilai' => $nilaiNumeric,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'persentase_keuntungan' => $this->calculateProfitPercent($hargaBeli, $hargaJual),
            ];
        }

        return $normalized;
    }

    public function calculateProfitPercent($hargaBeli, $hargaJual): float {
        $beli = (float)$hargaBeli;
        $jual = (float)$hargaJual;

        if ($beli <= 0) {
            return 0.0;
        }

        return round((($jual - $beli) / $beli) * 100, 2);
    }

    public function decodeSatuanDetail($value): array {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                return [];
            }
            return $this->normalizeSatuanDetail($decoded);
        }

        if (is_array($value)) {
            return $this->normalizeSatuanDetail($value);
        }

        return [];
    }

    public function getBaseUnitLabel(array $barang): string {
        foreach ($this->decodeSatuanDetail($barang['satuan_detail'] ?? []) as $unit) {
            if ((float)$unit['nilai'] === 1.0) return $unit['satuan'];
        }
        return trim((string)($barang['satuan'] ?? 'pcs')) ?: 'pcs';
    }

    public function validateUnitDefinitions(array $units): ?string {
        if (!$units) return null; // Existing single-unit products remain supported.
        $seen = [];
        $hasBase = false;
        foreach ($units as $unit) {
            $label = strtolower(trim((string)($unit['satuan'] ?? '')));
            $factor = (float)($unit['nilai'] ?? 1);
            if ($label === '' || isset($seen[$label])) return 'Nama satuan wajib diisi dan tidak boleh berulang.';
            if (!is_finite($factor) || $factor < 1 || floor($factor) !== $factor) return 'Isi satuan harus bilangan bulat minimal 1.';
            $seen[$label] = true;
            if ($factor === 1.0) $hasBase = true;
        }
        return $hasBase ? null : 'Tambahkan satuan dasar bernilai 1 (contoh: pcs atau botol).';
    }

    public function resolveSatuanUnit(array $barang, string $selectedSatuan = ''): array {
        $detail = $this->decodeSatuanDetail($barang['satuan_detail'] ?? []);
        if (empty($detail)) {
            $fallback = trim((string)($barang['satuan'] ?? 'pcs')) ?: 'pcs';
            if ($selectedSatuan !== '' && strcasecmp(trim($selectedSatuan), $fallback) !== 0) {
                throw new InvalidArgumentException('Satuan tidak terdaftar untuk barang ini: ' . $selectedSatuan);
            }
            return [
                'satuan' => $fallback,
                'nilai' => 1.0,
                'harga_beli' => (float)($barang['harga_beli'] ?? 0),
                'harga_jual' => (float)($barang['harga_jual'] ?? 0),
            ];
        }

        $normalizeLabel = function ($value) {
            return strtolower(trim((string)$value));
        };

        $target = $normalizeLabel($selectedSatuan);
        foreach ($detail as $unit) {
            if ($normalizeLabel($unit['satuan'] ?? '') === $target) {
                return [
                    'satuan' => (string)($unit['satuan'] ?? ''),
                    'nilai' => (float)($unit['nilai'] ?? 1),
                    'harga_beli' => (float)($unit['harga_beli'] ?? 0),
                    'harga_jual' => (float)($unit['harga_jual'] ?? 0),
                ];
            }
        }

        if ($target !== '') {
            throw new InvalidArgumentException('Satuan tidak terdaftar untuk barang ini: ' . $selectedSatuan);
        }
        $first = $detail[0] ?? [];
        return [
            'satuan' => (string)($first['satuan'] ?? (trim((string)($barang['satuan'] ?? 'pcs')) ?: 'pcs')),
            'nilai' => (float)($first['nilai'] ?? 1),
            'harga_beli' => (float)($first['harga_beli'] ?? ($barang['harga_beli'] ?? 0)),
            'harga_jual' => (float)($first['harga_jual'] ?? ($barang['harga_jual'] ?? 0)),
        ];
    }

    public function convertQtyToBaseById(int $idBarang, string $selectedSatuan, float $qty): float {
        $barang = $this->getById($idBarang);
        if (!$barang) {
            return (float)$qty;
        }

        $unit = $this->resolveSatuanUnit($barang, $selectedSatuan);
        $nilai = (float)($unit['nilai'] ?? 1);
        if (!is_finite($nilai) || $nilai <= 0) {
            $nilai = 1;
        }

        return (float)$qty * $nilai;
    }

    // Generate incremental kode barang (BRG-001, BRG-002, ...)
    public function generateKodeBarang() {
        $query = "SELECT COALESCE(MAX(id_barang) + 1, 1) as next_id FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        $nextId = isset($row['next_id']) ? (int)$row['next_id'] : 1;
        return 'BRG-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    public function getAll($kategoriId = null) {
        $where = '';
        if (!empty($kategoriId)) {
            $where = 'WHERE b.id_kategori = :id_kategori';
        }

        $query = "SELECT b.*, k.nama_kategori, u.username AS stok_updated_by_username, u.nama AS stok_updated_by_nama, u.role AS stok_updated_by_role
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  LEFT JOIN users u ON b.stok_updated_by = u.id_user
                  $where
                  ORDER BY b.nama_barang ASC";
        $stmt = $this->conn->prepare($query);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Trim spasi di nama_barang dan kode_barang
        foreach ($results as &$row) {
            $row['nama_barang'] = trim($row['nama_barang']);
            $row['kode_barang'] = trim($row['kode_barang']);
            $row['satuan_detail'] = $this->decodeSatuanDetail($row['satuan_detail'] ?? []);
            if (empty($row['satuan_detail']) && !empty($row['satuan'])) {
                $row['satuan_detail'] = [[
                    'satuan' => $row['satuan'],
                    'nilai' => 1,
                    'harga_beli' => (float)($row['harga_beli'] ?? 0),
                    'harga_jual' => (float)($row['harga_jual'] ?? 0),
                ]];
            }
        }
        return $results;
    }

    public function getAllWithPagination($offset, $limit, $kategoriId = null) {
        $where = '';
        if (!empty($kategoriId)) {
            $where = 'WHERE b.id_kategori = :id_kategori';
        }

        $query = "SELECT b.*, k.nama_kategori, u.username AS stok_updated_by_username, u.nama AS stok_updated_by_nama, u.role AS stok_updated_by_role
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  LEFT JOIN users u ON b.stok_updated_by = u.id_user
                  $where
                  ORDER BY b.nama_barang ASC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach ($results as &$row) {
            $row['nama_barang'] = trim($row['nama_barang']);
            $row['kode_barang'] = trim($row['kode_barang']);
            $row['satuan_detail'] = $this->decodeSatuanDetail($row['satuan_detail'] ?? []);
        }
        return $results;
    }

    public function countAll($kategoriId = null) {
        $where = '';
        if (!empty($kategoriId)) {
            $where = 'WHERE id_kategori = :id_kategori';
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table . " $where";
        $stmt = $this->conn->prepare($query);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function getTotals() {
                $query = "SELECT
                                        COALESCE(SUM(harga_beli * stok), 0) as total_harga_beli,
                                        COALESCE(SUM(harga_jual * stok), 0) as total_harga_jual,
                                        COALESCE(SUM(stok), 0) as total_stok
                                    FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getTotalsByKategori() {
        $query = "SELECT
                    k.id_kategori,
                    k.nama_kategori,
                    COALESCE(SUM(b.harga_beli * b.stok), 0) as total_harga_beli,
                    COALESCE(SUM(b.harga_jual * b.stok), 0) as total_harga_jual,
                    COALESCE(SUM(b.stok), 0) as total_stok
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  GROUP BY k.id_kategori, k.nama_kategori
                  ORDER BY k.nama_kategori ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT b.*, k.nama_kategori, u.username AS stok_updated_by_username, u.nama AS stok_updated_by_nama, u.role AS stok_updated_by_role
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  LEFT JOIN users u ON b.stok_updated_by = u.id_user
                  WHERE b.id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $row['satuan_detail'] = $this->decodeSatuanDetail($row['satuan_detail'] ?? []);
        return $row;
    }

    public function existsByKode($kodeBarang, $excludeId = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE kode_barang = :kode_barang";
        if (!empty($excludeId)) {
            $query .= " AND id_barang <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        if (!empty($excludeId)) {
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return ((int)($row['total'] ?? 0)) > 0;
    }

    public function create($data) {
        $kodeBarang = !empty($data['kode_barang']) ? $data['kode_barang'] : $this->generateKodeBarang();
        $satuanDetail = $this->normalizeSatuanDetail($data['satuan_detail'] ?? []);
        $defaultSatuan = trim((string)($data['satuan'] ?? ''));
        if (empty($defaultSatuan) && !empty($satuanDetail)) {
            $defaultSatuan = (string)$satuanDetail[0]['satuan'];
        }
        if ($defaultSatuan === '') {
            $defaultSatuan = 'pcs';
        }

        $query = "INSERT INTO " . $this->table . " 
                  (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok, tanggal_expired, stok_updated_by, satuan_detail) 
                  VALUES (:kode_barang, :nama_barang, :id_kategori, :satuan, :harga_beli, :harga_jual, :stok, :tanggal_expired, :stok_updated_by, :satuan_detail)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        $stmt->bindParam(':nama_barang', $data['nama_barang']);
        $stmt->bindParam(':id_kategori', $data['id_kategori']);
        $stmt->bindParam(':satuan', $defaultSatuan);
        $stmt->bindParam(':harga_beli', $data['harga_beli']);
        $stmt->bindParam(':harga_jual', $data['harga_jual']);
        $stmt->bindParam(':stok', $data['stok']);
        $tanggalExpired = !empty($data['tanggal_expired']) ? $data['tanggal_expired'] : null;
        $stmt->bindValue(':tanggal_expired', $tanggalExpired, $tanggalExpired === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stokUpdatedBy = isset($data['stok_updated_by']) && $data['stok_updated_by'] !== '' ? (int)$data['stok_updated_by'] : null;
        $stmt->bindValue(':stok_updated_by', $stokUpdatedBy, $stokUpdatedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $jsonSatuanDetail = json_encode($satuanDetail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt->bindValue(':satuan_detail', $jsonSatuanDetail === false ? '[]' : $jsonSatuanDetail, PDO::PARAM_STR);
        
        $success = $stmt->execute();
        if ($success) {
            $idBaru = (int)$this->conn->lastInsertId();
            $this->auditTrail->log([
                'modul' => 'barang',
                'aksi' => 'create',
                'entitas' => 'barang',
                'id_entitas' => (string)$idBaru,
                'deskripsi' => 'Menambahkan barang baru',
                'data_baru' => $data,
                'id_user' => $data['stok_updated_by'] ?? null
            ]);
        }
        return $success;
    }

    public function createAndReturn($data) {
        $kodeBarang = !empty($data['kode_barang']) ? $data['kode_barang'] : $this->generateKodeBarang();
        $satuanDetail = $this->normalizeSatuanDetail($data['satuan_detail'] ?? []);
        $defaultSatuan = trim((string)($data['satuan'] ?? ''));
        if (empty($defaultSatuan) && !empty($satuanDetail)) {
            $defaultSatuan = (string)$satuanDetail[0]['satuan'];
        }
        if ($defaultSatuan === '') {
            $defaultSatuan = 'pcs';
        }

        $query = "INSERT INTO " . $this->table . " 
                  (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok, tanggal_expired, stok_updated_by, satuan_detail) 
                  VALUES (:kode_barang, :nama_barang, :id_kategori, :satuan, :harga_beli, :harga_jual, :stok, :tanggal_expired, :stok_updated_by, :satuan_detail)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        $stmt->bindParam(':nama_barang', $data['nama_barang']);
        $stmt->bindParam(':id_kategori', $data['id_kategori']);
        $stmt->bindParam(':satuan', $defaultSatuan);
        $stmt->bindParam(':harga_beli', $data['harga_beli']);
        $stmt->bindParam(':harga_jual', $data['harga_jual']);
        $stmt->bindParam(':stok', $data['stok']);
        $tanggalExpired = !empty($data['tanggal_expired']) ? $data['tanggal_expired'] : null;
        $stmt->bindValue(':tanggal_expired', $tanggalExpired, $tanggalExpired === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stokUpdatedBy = isset($data['stok_updated_by']) && $data['stok_updated_by'] !== '' ? (int)$data['stok_updated_by'] : null;
        $stmt->bindValue(':stok_updated_by', $stokUpdatedBy, $stokUpdatedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $jsonSatuanDetail = json_encode($satuanDetail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt->bindValue(':satuan_detail', $jsonSatuanDetail === false ? '[]' : $jsonSatuanDetail, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $newId = $this->conn->lastInsertId();
            $row = $this->getById($newId);
            return ['success' => true, 'data' => $row];
        }

        return ['success' => false];
    }

    public function update($id, $data) {
        $lama = $this->getById($id);
        $kodeBarang = !empty($data['kode_barang']) ? $data['kode_barang'] : $this->generateKodeBarang();
        $satuanDetail = $this->normalizeSatuanDetail($data['satuan_detail'] ?? []);
        $defaultSatuan = trim((string)($data['satuan'] ?? ''));
        if (empty($defaultSatuan) && !empty($satuanDetail)) {
            $defaultSatuan = (string)$satuanDetail[0]['satuan'];
        }
        if ($defaultSatuan === '') {
            $defaultSatuan = 'pcs';
        }

        $query = "UPDATE " . $this->table . " 
                  SET kode_barang = :kode_barang,
                      nama_barang = :nama_barang,
                      id_kategori = :id_kategori,
                      satuan = :satuan, 
                      harga_beli = :harga_beli, 
                      harga_jual = :harga_jual, 
                      stok = :stok,
                      tanggal_expired = :tanggal_expired,
                      stok_updated_by = :stok_updated_by,
                      satuan_detail = :satuan_detail,
                      updated_at = NOW()
                  WHERE id_barang = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        $stmt->bindParam(':nama_barang', $data['nama_barang']);
        $stmt->bindParam(':id_kategori', $data['id_kategori']);
        $stmt->bindParam(':satuan', $defaultSatuan);
        $stmt->bindParam(':harga_beli', $data['harga_beli']);
        $stmt->bindParam(':harga_jual', $data['harga_jual']);
        $stmt->bindParam(':stok', $data['stok']);
        $tanggalExpired = !empty($data['tanggal_expired']) ? $data['tanggal_expired'] : null;
        $stmt->bindValue(':tanggal_expired', $tanggalExpired, $tanggalExpired === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stokUpdatedBy = isset($data['stok_updated_by']) && $data['stok_updated_by'] !== '' ? (int)$data['stok_updated_by'] : null;
        $stmt->bindValue(':stok_updated_by', $stokUpdatedBy, $stokUpdatedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $jsonSatuanDetail = json_encode($satuanDetail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt->bindValue(':satuan_detail', $jsonSatuanDetail === false ? '[]' : $jsonSatuanDetail, PDO::PARAM_STR);
        
        $success = $stmt->execute();
        if ($success) {
            $this->auditTrail->log([
                'modul' => 'barang',
                'aksi' => 'update',
                'entitas' => 'barang',
                'id_entitas' => (string)$id,
                'deskripsi' => 'Mengubah data barang',
                'data_lama' => $lama,
                'data_baru' => $data,
                'id_user' => $data['stok_updated_by'] ?? null
            ]);
        }
        return $success;
    }

    public function updateSatuanBarang($id, $satuan) {
        $query = "UPDATE " . $this->table . " SET satuan = :satuan, updated_at = NOW() WHERE id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':satuan', $satuan);
        return $stmt->execute();
    }

    public function updateAtributDariPembelian($id, $satuan, $hargaBeli, $hargaJual, $tanggalExpired = null) {
        $query = "UPDATE " . $this->table . "
                  SET satuan = COALESCE(NULLIF(:satuan, ''), satuan),
                      harga_beli = COALESCE(:harga_beli, harga_beli),
                      harga_jual = COALESCE(:harga_jual, harga_jual),
                      tanggal_expired = COALESCE(:tanggal_expired, tanggal_expired),
                      updated_at = NOW()
                  WHERE id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':satuan', $satuan);
        $hargaBeliValue = $hargaBeli !== null && $hargaBeli !== '' ? (float)$hargaBeli : null;
        $stmt->bindValue(':harga_beli', $hargaBeliValue, $hargaBeliValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $hargaJualValue = $hargaJual !== null && $hargaJual !== '' ? (float)$hargaJual : null;
        $stmt->bindValue(':harga_jual', $hargaJualValue, $hargaJualValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $expiredValue = !empty($tanggalExpired) ? $tanggalExpired : null;
        $stmt->bindValue(':tanggal_expired', $expiredValue, $expiredValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function delete($id) {
        $lama = $this->getById($id);
        $query = "DELETE FROM " . $this->table . " WHERE id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $success = $stmt->execute();
        if ($success) {
            $this->auditTrail->log([
                'modul' => 'barang',
                'aksi' => 'delete',
                'entitas' => 'barang',
                'id_entitas' => (string)$id,
                'deskripsi' => 'Menghapus barang',
                'data_lama' => $lama
            ]);
        }
        return $success;
    }

    public function updateStok($id, $jumlah, $updatedBy = null) {
        $lama = $this->getById($id);
        $query = "UPDATE " . $this->table . " 
                  SET stok = stok + :jumlah,
                      stok_updated_by = :stok_updated_by,
                      updated_at = NOW() 
                  WHERE id_barang = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':jumlah', $jumlah);
        $updatedByValue = $updatedBy !== null && $updatedBy !== '' ? (int)$updatedBy : null;
        $stmt->bindValue(':stok_updated_by', $updatedByValue, $updatedByValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        
        $success = $stmt->execute();
        if ($success) {
            $baru = $this->getById($id);
            $this->auditTrail->log([
                'modul' => 'stok',
                'aksi' => 'adjust',
                'entitas' => 'barang',
                'id_entitas' => (string)$id,
                'deskripsi' => 'Penyesuaian stok manual',
                'data_lama' => ['stok' => $lama['stok'] ?? null],
                'data_baru' => ['stok' => $baru['stok'] ?? null, 'delta' => $jumlah],
                'id_user' => $updatedBy
            ]);
        }
        return $success;
    }

    public function getStokRendah($batas = 10) {
        $query = "SELECT * FROM " . $this->table . " WHERE stok <= :batas ORDER BY stok ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':batas', $batas);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchBarang($keyword, $kategoriId = null) {
        $where = "WHERE (nama_barang ILIKE :keyword OR kode_barang ILIKE :keyword)";
        if (!empty($kategoriId)) {
            $where .= " AND id_kategori = :id_kategori";
        }
        
        $query = "SELECT b.*, k.nama_kategori, u.username AS stok_updated_by_username, u.nama AS stok_updated_by_nama, u.role AS stok_updated_by_role
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  LEFT JOIN users u ON b.stok_updated_by = u.id_user
                  " . $where . " 
                  ORDER BY b.nama_barang ASC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = '%' . $keyword . '%';
        $stmt->bindParam(':keyword', $keyword);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Trim spasi
        foreach ($results as &$row) {
            $row['nama_barang'] = trim($row['nama_barang']);
            $row['kode_barang'] = trim($row['kode_barang']);
        }
        return $results;
    }

    public function getAllKategori() {
        $query = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllSatuan() {
        $query = "SELECT id_satuan, nama_satuan FROM satuan ORDER BY nama_satuan ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addKategori($nama_kategori) {
        $query = "INSERT INTO kategori (nama_kategori) VALUES (:nama_kategori)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        return $stmt->execute();
    }

    public function updateKategori($id, $nama_kategori) {
        $query = "UPDATE kategori SET nama_kategori = :nama_kategori WHERE id_kategori = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        return $stmt->execute();
    }

    public function deleteKategori($id) {
        $query = "DELETE FROM kategori WHERE id_kategori = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // likely blocked by FK usage
        }
    }

    public function addSatuan($nama_satuan) {
        $query = "INSERT INTO satuan (nama_satuan) VALUES (:nama_satuan)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_satuan', $nama_satuan);
        return $stmt->execute();
    }

    public function updateSatuan($id, $nama_satuan) {
        $query = "UPDATE satuan SET nama_satuan = :nama_satuan WHERE id_satuan = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_satuan', $nama_satuan);
        return $stmt->execute();
    }

    public function deleteSatuan($id) {
        $query = "DELETE FROM satuan WHERE id_satuan = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // likely blocked by FK usage
        }
    }
}
