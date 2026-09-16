<?php
require_once __DIR__ . '/../app/models/Barang.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';
// Test the real quick-create API without a database connection.
$barang = new class extends Barang {
    public $saved;
    public function __construct() {}
    public function existsByKode($kodeBarang, $excludeId = null) { return $kodeBarang === 'DUPLICATE'; }
    public function createAndReturn($data) {
        $this->saved = $data;
        return ['success' => true, 'data' => array_merge($data, ['id_barang' => 1])];
    }
};
$r = new ReflectionClass(ApiController::class);
$controller = $r->newInstanceWithoutConstructor();
$r->getProperty('barang')->setValue($controller, $barang);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = [];
$units = [['satuan'=>'pcs','nilai'=>1,'harga_beli'=>1000,'harga_jual'=>2000],['satuan'=>'pack','nilai'=>12,'harga_beli'=>12000,'harga_jual'=>22000]];
$_POST = ['kode_barang'=>'NEW','nama_barang'=>'Uji','id_kategori'=>1,'satuan'=>'pcs','harga_beli'=>'1000','harga_jual'=>'2000','stok'=>'0','satuan_detail'=>json_encode($units)];
function callCreate($controller): array {
    ob_start();
    $controller->createBarang();
    return json_decode(ob_get_clean(), true);
}
$result = callCreate($controller);
if (!$result['success'] || $barang->saved['stok'] !== 0 || count($barang->saved['satuan_detail']) !== 2) throw new RuntimeException('Zero stock or multi-unit creation failed');
$_POST['kode_barang'] = 'DUPLICATE';
if (callCreate($controller)['success']) throw new RuntimeException('Duplicate code accepted');
$_POST['kode_barang'] = 'NEW';
$units[1]['harga_jual'] = 10000;
$_POST['satuan_detail'] = json_encode($units);
if (callCreate($controller)['success']) throw new RuntimeException('Invalid unit price accepted');
$kategori=[];
ob_start();
include __DIR__.'/../app/views/barang/_create-fields.php';
$html=ob_get_clean();
foreach (['name="kode_barang" required','name="satuan_detail"','name="stok" required min="0"','name="tanggal_expired"','id="btnAddSatuanDetail"'] as $field) {
    if (strpos($html,$field)===false) throw new RuntimeException('Missing shared field: '.$field);
}
echo "PASS: shared fields, zero opening stock, multiple units, duplicate codes and unit-price validation\n";
