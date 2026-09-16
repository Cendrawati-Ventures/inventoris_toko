<?php
// Integration regression: temporary PostgreSQL tables only; no live rows or sequences changed.
require_once __DIR__ . '/../app/models/Penjualan.php';
require_once __DIR__ . '/../app/models/Laporan.php';
require_once __DIR__ . '/../app/models/Pembelian.php';
function modelWithConnection(string $class, PDO $conn) {
    $r = new ReflectionClass($class);
    $model = $r->newInstanceWithoutConstructor();
    $r->getProperty('conn')->setValue($model, $conn);
    return $model;
}
function check($condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
$config = new Database();
$r = new ReflectionClass($config);
$v = [];
foreach (['host', 'port', 'db_name', 'username', 'password'] as $key) {
    $v[$key] = $r->getProperty($key)->getValue($config);
}
$conn = new PDO('pgsql:host='.$v['host'].';port='.$v['port'].';dbname='.$v['db_name'], $v['username'], $v['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$conn->exec('SET search_path TO pg_temp');
foreach (['pembelian', 'detail_pembelian', 'barang', 'kategori', 'users', 'penjualan', 'detail_penjualan', 'hutang', 'stok_mutasi', 'inventory_batches', 'penjualan_batch_konsumsi', 'audit_logs'] as $table) {
    // Copy structure, constraints and indexes, but never defaults pointing at live sequences.
    $conn->exec("CREATE TEMP TABLE $table (LIKE public.$table INCLUDING CONSTRAINTS INCLUDING INDEXES)");
    $columns = $conn->query("SELECT column_name, column_default FROM information_schema.columns WHERE table_schema = 'public' AND table_name = '$table'")->fetchAll();
    foreach ($columns as $column) {
        $name = $column['column_name'];
        $default = $column['column_default'];
        if ($default === null) continue;
        if (strpos($default, 'nextval(') !== false) {
            $sequence = 'test_'.$table.'_'.$name;
            $conn->exec("CREATE TEMP SEQUENCE $sequence");
            $default = "nextval('pg_temp.$sequence')";
        }
        $conn->exec("ALTER TABLE pg_temp.$table ALTER COLUMN $name SET DEFAULT $default");
    }
}
$conn->exec("INSERT INTO barang (id_barang, id_kategori, kode_barang, nama_barang, satuan, stok, harga_beli, harga_jual, satuan_detail) VALUES (1, 1, 'TEST', 'Barang uji', 'pcs', 30, 1000, 2000, '[{\"satuan\":\"pcs\",\"nilai\":1,\"harga_beli\":1000,\"harga_jual\":2000},{\"satuan\":\"dus\",\"nilai\":12,\"harga_beli\":12000,\"harga_jual\":24000}]')");
$model = modelWithConnection(Penjualan::class, $conn);
foreach (['barangModel' => Barang::class, 'stokMutasi' => StokMutasi::class, 'batchModel' => InventoryBatch::class, 'auditTrail' => AuditTrail::class] as $property => $class) {
    (new ReflectionClass($model))->getProperty($property)->setValue($model, modelWithConnection($class, $conn));
}
$report = modelWithConnection(Laporan::class, $conn);
$stock = modelWithConnection(StokMutasi::class, $conn);
$day = date('Y-m-d');
check($stock->getSaldoAt(1, $day) === 30.0, 'Opening stock is available without mutation history');
$data = ['tanggal' => $day, 'items' => [['id_barang' => 1, 'satuan' => 'dus', 'jumlah' => 1, 'harga_satuan' => 24000, 'diskon' => 0]], 'uang_diberikan' => 24000, 'nama_pembeli' => 'Uji', 'keterangan' => '', 'id_user' => null];
$result = $model->create($data);
check($result['success'], 'Sale saves successfully: '.($result['message'] ?? ''));
check(count($model->getByDateRange($day, $day)) === 1, 'Sale appears in sales list');
check(count($report->getLaporanPenjualan($day, $day)) === 1, 'Item appears in sales report');
check($report->getLaporanPenjualan($day, $day)[0]['satuan'] === 'dus', 'Report shows sold unit');
check($stock->getSaldoAt(1, $day) === 18.0, 'Stock decreases by converted base quantity');
check((float)$model->getDetailById($result['id'])[0]['harga_beli_item'] === 12000.0, 'Pack sale snapshot uses cost per pack');
$data['items'][] = $data['items'][0];
$result = $model->create($data);
check(!$result['success'], 'Combined quantities exceeding stock are rejected');
check(count($model->getByDateRange($day, $day)) === 1, 'Rejected sale leaves no partial transaction');
check($stock->getSaldoAt(1, $day) === 18.0, 'Rejected sale preserves stock');
$conn->exec("INSERT INTO stok_mutasi (id_barang, jenis_mutasi, jumlah, tanggal_mutasi) VALUES (1, 'masuk', 5, '$day 10:00:00')");
$conn->exec('UPDATE barang SET stok = stok + 5 WHERE id_barang = 1');
check($stock->getSaldoAt(1, $day) === 23.0, 'Date includes incoming stock later than midnight');
$conn->exec("INSERT INTO stok_mutasi (id_barang, jenis_mutasi, jumlah, tanggal_mutasi) VALUES (1, 'masuk', 7, (DATE '$day' + INTERVAL '1 day'))");
$conn->exec('UPDATE barang SET stok = stok + 7 WHERE id_barang = 1');
check($stock->getSaldoAt(1, $day) === 23.0, 'Tomorrow movements excluded from today balance');

$data['items'] = [array_merge($data['items'][0], ['satuan' => 'pcs', 'jumlah' => 1, 'harga_satuan' => 2000])];
$data['tanggal'] = (new DateTimeImmutable($day))->modify('-1 day')->format('Y-m-d');
$result = $model->create($data);
check($result['success'], 'Backdated sale saves with opening stock');
check(count($model->getByDateRange($data['tanggal'], $data['tanggal'])) === 1, 'Backdated sale appears on selected date');
check(count($report->getLaporanPenjualan($data['tanggal'], $data['tanggal'])) === 1, 'Backdated sale appears in matching report period');

$conn->exec("INSERT INTO barang (id_barang, id_kategori, kode_barang, nama_barang, satuan, stok, harga_beli, harga_jual, satuan_detail) VALUES (2, 1, 'PUCUK-TEST', 'Teh pucuk uji', 'pack', 12, 46000, 48000, '[{\"satuan\":\"botol\",\"nilai\":1,\"harga_beli\":4000,\"harga_jual\":5000},{\"satuan\":\"pack\",\"nilai\":12,\"harga_beli\":46000,\"harga_jual\":48000}]')");
$batch = modelWithConnection(InventoryBatch::class, $conn);
$batch->createBatchFromPurchaseDetail(999, 999, 2, 12, 46000 / 12, $day);
$data['tanggal'] = $day;
$data['items'] = [['id_barang' => 2, 'satuan' => 'botol', 'jumlah' => 10, 'harga_satuan' => 5000, 'diskon' => 0]];
$data['uang_diberikan'] = 50000;
$result = $model->create($data);
check($result['success'], 'Ten bottles from a twelve-bottle pack save');
$detail = $model->getDetailById($result['id'])[0];
check($detail['satuan'] === 'botol' && (int)$detail['jumlah'] === 10, 'Detail retains ten bottles, not master unit pack');
check(abs((float)$detail['harga_beli_item'] - 3833.33) < 0.01, 'Bottle cost uses pack purchase price divided by twelve');
check(abs((float)$detail['laba_item'] - 11666.70) < 0.01, 'Bottle profit is 11666.70, not minus 410000');
$updated = $model->update($result['id'], $data);
check($updated['success'], 'Editing bottle sale succeeds');
$detail = $model->getDetailById($result['id'])[0];
check($detail['satuan'] === 'botol' && abs((float)$detail['laba_item'] - 11666.70) < 0.01, 'Editing preserves unit and cost');
check($stock->getSaldoAt(2, $day) === 2.0, 'Editing restores and consumes converted stock correctly');

$packData = $data;
$packData['items'] = [['id_barang' => 1, 'satuan' => 'dus', 'jumlah' => 1, 'harga_satuan' => 24000, 'diskon' => 0]];
$stockBefore = $stock->getSaldoAt(1, $day);
$packResult = $model->create($packData);
check($packResult['success'], 'Pack sale before deletion succeeds');
$deleted = $model->delete($packResult['id']);
check($deleted['success'] && $stock->getSaldoAt(1, $day) === $stockBefore, 'Deleting pack sale restores base units');

$units = [['satuan'=>'pcs','nilai'=>1,'harga_beli'=>1000,'harga_jual'=>2000], ['satuan'=>'pack','nilai'=>12,'harga_beli'=>12000,'harga_jual'=>22000], ['satuan'=>'dus','nilai'=>100,'harga_beli'=>100000,'harga_jual'=>180000], ['satuan'=>'renteng','nilai'=>6,'harga_beli'=>6000,'harga_jual'=>11000]];
$insert = $conn->prepare("INSERT INTO barang (id_barang,id_kategori,kode_barang,nama_barang,satuan,stok,harga_beli,harga_jual,satuan_detail) VALUES (3,1,'MIX','Barang multi satuan','pcs',300,1000,2000,:units)");
$insert->execute(['units'=>json_encode($units)]);
$multi = $data;
$multi['items'] = [
    ['id_barang'=>3,'satuan'=>'dus','jumlah'=>1,'harga_satuan'=>180000,'diskon'=>0],
    ['id_barang'=>3,'satuan'=>'pack','jumlah'=>2,'harga_satuan'=>22000,'diskon'=>0],
    ['id_barang'=>3,'satuan'=>'pcs','jumlah'=>3,'harga_satuan'=>2000,'diskon'=>0],
    ['id_barang'=>3,'satuan'=>'renteng','jumlah'=>1,'harga_satuan'=>11000,'diskon'=>0]
];
$mixedResult=$model->create($multi);
check($mixedResult['success'], 'Mixed dus, pack, pcs and renteng sale saves');
check($stock->getSaldoAt(3,$day)===167.0, '300 minus (100 + 24 + 3 + 6) leaves 167 base units');
check((float)$model->getById($mixedResult['id'])['total_harga']===241000.0, 'Each sold unit uses its own price');
$purchaseModel=modelWithConnection(Pembelian::class,$conn);
foreach (['barangModel'=>Barang::class,'stokMutasi'=>StokMutasi::class,'batchModel'=>InventoryBatch::class,'auditTrail'=>AuditTrail::class] as $prop=>$class) {
    (new ReflectionClass($purchaseModel))->getProperty($prop)->setValue($purchaseModel,modelWithConnection($class,$conn));
}
$incoming=$data;
$incoming['items']=[['id_barang'=>3,'satuan'=>'dus','jumlah'=>2,'harga_satuan'=>100000,'diskon'=>0]];
$purchaseResult=$purchaseModel->create($incoming);
check($purchaseResult['success'], 'Purchase of two dus succeeds');
check($stock->getSaldoAt(3,$day)===367.0, 'Two dus add 200 base units');
$incoming['items'][0]=['id_barang'=>3,'satuan'=>'pack','jumlah'=>1,'harga_satuan'=>12000,'diskon'=>0];
$purchaseUpdated=$purchaseModel->update($purchaseResult['id'],$incoming);
check($purchaseUpdated['success'], 'Purchase changes from two dus to one pack: '.($purchaseUpdated['message'] ?? ''));
check($stock->getSaldoAt(3,$day)===179.0, 'Purchase edit reverses 200 and adds 12');
// Changing future pack contents must not alter the stored purchase reversal.
$units[1]['nilai']=10;
$change=$conn->prepare('UPDATE barang SET satuan_detail=:units WHERE id_barang=3');
$change->execute(['units'=>json_encode($units)]);
$purchaseDeleted=$purchaseModel->delete($purchaseResult['id']);
check($purchaseDeleted['success'] && $stock->getSaldoAt(3,$day)===167.0, 'Purchase deletion uses saved factor 12, not current factor 10');
$newPack=$multi;
$newPack['items']=[['id_barang'=>3,'satuan'=>'pack','jumlah'=>1,'harga_satuan'=>22000,'diskon'=>0]];
$newPackResult=$model->create($newPack);
check($newPackResult['success'] && $stock->getSaldoAt(3,$day)===157.0, 'New sale uses product-specific pack factor 10');
$newPack['items'][0]['satuan']='unregistered';
$invalid=$model->create($newPack);
check(!$invalid['success'] && $stock->getSaldoAt(3,$day)===157.0, 'Unknown unit rejected without stock changes');

// A sale entered today for yesterday belongs only to yesterday and still changes live stock.
$insert=$conn->prepare("INSERT INTO barang (id_barang,id_kategori,kode_barang,nama_barang,satuan,stok,harga_beli,harga_jual,satuan_detail) VALUES (4,1,'BACKDATE','Barang tanggal lampau','pcs',50,1000,2000,:units)");
$insert->execute(['units'=>json_encode([['satuan'=>'pcs','nilai'=>1,'harga_beli'=>1000,'harga_jual'=>2000],['satuan'=>'pack','nilai'=>12,'harga_beli'=>12000,'harga_jual'=>22000]])]);
$yesterday=(new DateTimeImmutable($day))->modify('-1 day')->format('Y-m-d');
$beforeYesterday=(new DateTimeImmutable($day))->modify('-2 days')->format('Y-m-d');
$forgotten=$data;
$forgotten['tanggal']=$yesterday;
$forgotten['items']=[['id_barang'=>4,'satuan'=>'pack','jumlah'=>2,'harga_satuan'=>22000,'diskon'=>0]];
$saved=$model->create($forgotten);
check($saved['success'], 'Forgotten sale dated yesterday saves today');
check(substr($model->getById($saved['id'])['tanggal'],0,10)===$yesterday, 'Header keeps chosen date instead of today');
$todayIds=array_column($model->getByDateRange($day,$day),'id_penjualan');
check(!in_array($saved['id'],$todayIds), 'Yesterday sale absent from today sales list');
$yesterdayIds=array_column($report->getLaporanPenjualan($yesterday,$yesterday),'id_penjualan');
check(in_array($saved['id'],$yesterdayIds), 'Yesterday report includes the forgotten sale');
check($stock->getSaldoAt(4,$day)===26.0 && $stock->getSaldoAt(4,$yesterday)===26.0, 'Two packs reduce current and yesterday stock from 50 to 26');
check($stock->getSaldoAt(4,$beforeYesterday)===50.0, 'Stock before transaction date stays unchanged');
$movement=$conn->query('SELECT tanggal_mutasi,jumlah FROM stok_mutasi WHERE id_barang=4')->fetch();
check(substr($movement['tanggal_mutasi'],0,10)===$yesterday && (float)$movement['jumlah']===24.0, 'Movement uses chosen date and converted quantity');
$forgotten['tanggal']='2026-02-30';
$rejected=$model->create($forgotten);
check(!$rejected['success'] && $stock->getSaldoAt(4,$day)===26.0, 'Invalid calendar date rejected instead of silently changed');
