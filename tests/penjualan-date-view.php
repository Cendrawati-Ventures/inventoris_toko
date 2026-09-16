<?php
require_once __DIR__ . '/../app/helpers/transaction_date.php';
$barang = [];
$notaConfig = [];
$_SESSION = ['role' => 'admin'];
$_GET = ['tanggal' => '2024-02-29'];
$tanggal_default = transactionDate($_GET['tanggal']);
$source = file_get_contents(__DIR__ . '/../app/views/penjualan/create.php');
// Render the actual form without navigation/layout or database access.
$source = str_replace("include __DIR__ . '/../layout/header.php';", 'echo $content;', $source);
ob_start();
eval('?>' . $source);
$html = ob_get_clean();
if (!preg_match('/name="tanggal" required value="2024-02-29"/', $html)) {
    throw new RuntimeException('Form lost the selected date');
}
if (strpos($html, 'id="confirm_tanggal"') === false) {
    throw new RuntimeException('Confirmation omits transaction date');
}
try {
    transactionDate('2025-02-29');
    throw new RuntimeException('Invalid date was accepted');
} catch (InvalidArgumentException $e) {
    echo "PASS: selected date rendered, confirmation date present, invalid date rejected\n";
}
