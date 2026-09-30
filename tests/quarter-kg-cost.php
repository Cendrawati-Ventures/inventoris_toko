<?php
require_once __DIR__ . '/../app/models/Barang.php';
require_once __DIR__ . '/../app/models/InventoryBatch.php';
$barang = (new ReflectionClass(Barang::class))->newInstanceWithoutConstructor();
$units = [
    ['satuan' => '1/4 kg', 'nilai' => 1, 'harga_beli' => 4300, 'harga_jual' => 5000],
    ['satuan' => '1 kg', 'nilai' => 4, 'harga_beli' => 17200, 'harga_jual' => 19000],
];
if ($barang->validateUnitDefinitions($units) !== null) throw new RuntimeException('Valid quarter-kg configuration rejected');
$item = ['satuan_detail' => $units];
$purchase = $barang->resolveSatuanUnit($item, '1 kg');
$sale = $barang->resolveSatuanUnit($item, '1/4 kg');
foreach ([4300, 17200, 4500] as $batchCost) {
    $db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('CREATE TABLE inventory_batches (id_batch INTEGER PRIMARY KEY, id_barang INTEGER, qty_sisa NUMERIC, harga_modal NUMERIC, tanggal_batch TEXT)');
    $db->exec('CREATE TABLE penjualan_batch_konsumsi (id_penjualan INTEGER, id_detail_penjualan INTEGER, id_batch INTEGER, id_barang INTEGER, qty NUMERIC, harga_modal NUMERIC, total_modal NUMERIC)');
    $stmt = $db->prepare("INSERT INTO inventory_batches VALUES (1, 3, :qty, :cost, '2026-09-30')");
    $stmt->execute(['qty' => $purchase['nilai'], 'cost' => $batchCost]);
    $ref = new ReflectionClass(InventoryBatch::class);
    $model = $ref->newInstanceWithoutConstructor();
    $ref->getProperty('conn')->setValue($model, $db);
    $result = $model->consumeForSaleDetail(1, 1, 3, $sale['nilai'], $sale['harga_beli']);
    if ($result['total_modal'] !== (float)$batchCost) throw new RuntimeException('FIFO source changed');
    if ((float)$db->query('SELECT qty_sisa FROM inventory_batches')->fetchColumn() !== 3.0) throw new RuntimeException('Quarter-kg sale deducted incorrect quantity');
    if ($batchCost === 4300 && $purchase['harga_beli'] / $purchase['nilai'] !== 4300.0) throw new RuntimeException('Purchase cost conversion failed');
    echo 'PASS: batch modal ', $batchCost, ', laba ', 5000 - $result['total_modal'], ', sisa 3 x 1/4 kg', "\n";
}
