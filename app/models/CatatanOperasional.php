<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/money.php';

class CatatanOperasional {
    private PDO $conn;

    public function __construct(?PDO $conn = null) {
        $this->conn = $conn ?? (new Database())->getConnection();
        $this->conn->exec("CREATE TABLE IF NOT EXISTS catatan_operasional (
            id_catatan SERIAL PRIMARY KEY,
            id_user INT NULL REFERENCES users(id_user) ON DELETE SET NULL,
            nama_kasir VARCHAR(200) NOT NULL,
            catatan TEXT NOT NULL,
            uang_dikeluarkan NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (uang_dikeluarkan >= 0),
            uang_di_kasir NUMERIC(12,2) NULL CHECK (uang_di_kasir >= 0),
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        // Upgrade existing notes without discarding their content.
        if ($this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $columns = $this->conn->query('PRAGMA table_info(catatan_operasional)')->fetchAll(PDO::FETCH_ASSOC);
            if (!in_array('uang_di_kasir', array_column($columns, 'name'), true)) {
                $this->conn->exec('ALTER TABLE catatan_operasional ADD COLUMN uang_di_kasir NUMERIC(12,2) NULL CHECK (uang_di_kasir >= 0)');
            }
            if (!in_array('uang_dikeluarkan', array_column($columns, 'name'), true)) {
                $this->conn->exec('ALTER TABLE catatan_operasional ADD COLUMN uang_dikeluarkan NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (uang_dikeluarkan >= 0)');
            }
        } else {
            $this->conn->exec('ALTER TABLE catatan_operasional ADD COLUMN IF NOT EXISTS uang_di_kasir NUMERIC(12,2) NULL CHECK (uang_di_kasir >= 0)');
            $this->conn->exec('ALTER TABLE catatan_operasional ADD COLUMN IF NOT EXISTS uang_dikeluarkan NUMERIC(12,2) NOT NULL DEFAULT 0 CHECK (uang_dikeluarkan >= 0)');
        }
        $this->conn->exec('CREATE INDEX IF NOT EXISTS idx_catatan_operasional_user ON catatan_operasional(id_user, created_at)');
    }

    public static function validate(string $catatan): ?string {
        if (trim($catatan) === '') return 'Catatan wajib diisi.';
        if (mb_strlen($catatan) > 4000) return 'Catatan maksimal 4.000 karakter.';
        return null;
    }

    public static function expenseAmount($value, string $label = 'Uang dikeluarkan'): float {
        if (!is_scalar($value) && $value !== null) throw new InvalidArgumentException($label . ' tidak valid.');
        $amount = trim((string)$value) === '' ? 0.0 : parseMoneyInput($value);
        if ($amount === null || $amount < 0 || $amount > 9999999999.99) {
            throw new InvalidArgumentException($label . ' harus antara Rp0 dan Rp9.999.999.999,99.');
        }
        return round($amount, 2);
    }

    public static function cashAmount($value): float {
        if ($value === null || (is_scalar($value) && trim((string)$value) === '')) {
            throw new InvalidArgumentException('Uang di kasir wajib diisi. Isi 0 jika tidak ada uang tunai.');
        }
        return self::expenseAmount($value, 'Uang di kasir');
    }

    public function create(int $userId, string $nama, string $catatan, float $uangDikeluarkan = 0, ?float $uangDiKasir = null): void {
        if ($error = self::validate($catatan)) throw new InvalidArgumentException($error);
        $uangDikeluarkan = self::expenseAmount($uangDikeluarkan);
        if ($uangDiKasir !== null) $uangDiKasir = self::cashAmount($uangDiKasir);
        $stmt = $this->conn->prepare('INSERT INTO catatan_operasional (id_user, nama_kasir, catatan, uang_dikeluarkan, uang_di_kasir) VALUES (:id_user, :nama, :catatan, :uang, :kas)');
        $stmt->execute(['id_user' => $userId, 'nama' => $nama, 'catatan' => trim($catatan), 'uang' => number_format($uangDikeluarkan, 2, '.', ''), 'kas' => $uangDiKasir === null ? null : number_format($uangDiKasir, 2, '.', '')]);
    }

    public function count(?int $userId = null): int {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM catatan_operasional' . ($userId === null ? '' : ' WHERE id_user = :id_user'));
        $stmt->execute($userId === null ? [] : ['id_user' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function listPage(?int $userId, int $page, int $limit = 25): array {
        $stmt = $this->conn->prepare('SELECT * FROM catatan_operasional' . ($userId === null ? '' : ' WHERE id_user = :id_user') . ' ORDER BY created_at DESC, id_catatan DESC LIMIT :limit OFFSET :offset');
        if ($userId !== null) $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (max(1, $page) - 1) * $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
