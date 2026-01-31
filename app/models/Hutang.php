<?php

require_once __DIR__ . '/../config/database.php';

class Hutang {
    private $conn;
    private $table = 'hutang';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->bootstrap();
    }

    private function bootstrap() {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        // Create hutang table if it doesn't exist
        $createSql = "CREATE TABLE IF NOT EXISTS hutang (
            id_hutang INT AUTO_INCREMENT PRIMARY KEY,
            id_penjualan INT NOT NULL,
            nama_penghutang VARCHAR(100) NOT NULL,
            jumlah_hutang DECIMAL(12,2) NOT NULL,
            jatuh_tempo DATE NOT NULL,
            status ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan) ON DELETE CASCADE
        )";
        
        try {
            $this->conn->exec($createSql);
        } catch (Exception $e) {
            // Table already exists, silently continue
        }
    }

    public function getAll() {
        $query = "SELECT h.*, p.tanggal as tanggal_transaksi, p.total_harga
                  FROM " . $this->table . " h
                  JOIN penjualan p ON h.id_penjualan = p.id_penjualan
                  ORDER BY h.jatuh_tempo ASC, h.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBelumBayar() {
        $query = "SELECT h.*, p.tanggal as tanggal_transaksi, p.total_harga
                  FROM " . $this->table . " h
                  JOIN penjualan p ON h.id_penjualan = p.id_penjualan
                  WHERE h.status = 'belum_bayar'
                  ORDER BY h.jatuh_tempo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLunas() {
        $query = "SELECT h.*, p.tanggal as tanggal_transaksi, p.total_harga
                  FROM " . $this->table . " h
                  JOIN penjualan p ON h.id_penjualan = p.id_penjualan
                  WHERE h.status = 'lunas'
                  ORDER BY h.updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT h.*, p.tanggal as tanggal_transaksi, p.total_harga, p.nama_pembeli
                  FROM " . $this->table . " h
                  JOIN penjualan p ON h.id_penjualan = p.id_penjualan
                  WHERE h.id_hutang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (id_penjualan, nama_penghutang, jumlah_hutang, jatuh_tempo, status)
                  VALUES (:id_penjualan, :nama_penghutang, :jumlah_hutang, :jatuh_tempo, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_penjualan', $data['id_penjualan']);
        $stmt->bindParam(':nama_penghutang', $data['nama_penghutang']);
        $stmt->bindParam(':jumlah_hutang', $data['jumlah_hutang']);
        $stmt->bindParam(':jatuh_tempo', $data['jatuh_tempo']);
        $status = $data['status'] ?? 'belum_bayar';
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " 
                  SET status = :status 
                  WHERE id_hutang = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_hutang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTotalHutang() {
        $query = "SELECT 
                    SUM(CASE WHEN status = 'belum_bayar' THEN jumlah_hutang ELSE 0 END) as total_belum_lunas,
                    SUM(CASE WHEN status = 'lunas' THEN jumlah_hutang ELSE 0 END) as total_lunas,
                    SUM(jumlah_hutang) as total_semua
                  FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getHutangJatuhTempo() {
        $query = "SELECT h.*, p.tanggal as tanggal_transaksi, p.total_harga
                  FROM " . $this->table . " h
                  JOIN penjualan p ON h.id_penjualan = p.id_penjualan
                  WHERE h.status = 'belum_bayar'
                  AND h.jatuh_tempo <= CURRENT_DATE
                  ORDER BY h.jatuh_tempo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
