<?php
// CLI only. Inspect the configured database without migrations, seeds or data changes.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../app/config/database.php';

$code = $argv[1] ?? '';
if ($code === '') { fwrite(STDERR, "Usage: php scripts/check-unit-cost.php BRG-003\n"); exit(1); }
try {
    $config = new Database();
    $reflection = new ReflectionClass($config);
    $values = [];
    foreach (['host', 'port', 'db_name', 'username', 'password'] as $key) {
        $values[$key] = $reflection->getProperty($key)->getValue($config);
    }
    $db = new PDO('pgsql:host='.$values['host'].';port='.$values['port'].';dbname='.$values['db_name'],
        $values['username'], $values['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('BEGIN TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
    $query = function (string $sql, array $params) use ($db): array {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    };
    $items = $query('SELECT id_barang, kode_barang, nama_barang, satuan, stok, harga_beli, harga_jual, satuan_detail FROM barang WHERE kode_barang = :code', ['code' => $code]);
    if (count($items) !== 1) throw new RuntimeException('Kode barang tidak ditemukan atau tidak unik.');
    $id = $items[0]['id_barang'];
    $items[0]['satuan_detail'] = json_decode($items[0]['satuan_detail'] ?? '[]', true);
    $batches = $query('SELECT b.id_batch, b.id_detail_pembelian, b.tanggal_batch, b.qty_awal, b.qty_sisa, b.harga_modal,
        d.satuan AS satuan_beli, d.nilai_satuan AS isi_saat_beli, d.jumlah AS jumlah_beli, d.harga_satuan AS harga_beli_per_satuan,
        d.harga_satuan / NULLIF(d.nilai_satuan, 0) AS modal_dasar_dari_pembelian
        FROM inventory_batches b JOIN detail_pembelian d ON d.id_detail = b.id_detail_pembelian
        WHERE b.id_barang = :id ORDER BY b.tanggal_batch, b.id_batch', ['id' => $id]);
    $sales = $query('SELECT d.id_detail, d.id_penjualan, p.tanggal, d.satuan, d.nilai_satuan, d.jumlah,
        d.harga_satuan, d.harga_beli_saat_transaksi, d.subtotal,
        d.subtotal - d.jumlah * d.harga_beli_saat_transaksi AS laba
        FROM detail_penjualan d JOIN penjualan p ON p.id_penjualan = d.id_penjualan
        WHERE d.id_barang = :id ORDER BY d.id_detail DESC LIMIT 10', ['id' => $id]);
    foreach ($sales as &$sale) {
        $sale['sumber_modal'] = $query('SELECT id_batch, qty, harga_modal, total_modal FROM penjualan_batch_konsumsi
            WHERE id_detail_penjualan = :id ORDER BY id_konsumsi', ['id' => $sale['id_detail']]);
    }
    unset($sale);
    $db->rollBack();
    echo json_encode(['barang' => $items[0], 'batch_modal' => $batches, 'penjualan_terakhir' => $sales], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "Pemeriksaan gagal. Pastikan koneksi database tersedia dan kode barang benar. Tidak ada data yang diubah.\n");
    exit(1);
}
