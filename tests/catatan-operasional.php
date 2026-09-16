<?php
require_once __DIR__ . '/../app/controllers/CatatanController.php';
function verify($condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
$conn = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$conn->exec('PRAGMA foreign_keys = ON');
$conn->exec('CREATE TABLE users (id_user INTEGER PRIMARY KEY)');
$conn->exec('INSERT INTO users VALUES (1), (2)');
$conn->exec('CREATE TABLE catatan_operasional (id_catatan INTEGER PRIMARY KEY AUTOINCREMENT, id_user INTEGER REFERENCES users(id_user) ON DELETE SET NULL, nama_kasir TEXT NOT NULL, catatan TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
$conn->exec("INSERT INTO catatan_operasional(id_user,nama_kasir,catatan) VALUES(1,'Kasir Lama','Catatan sebelum field uang')");
$model = new CatatanOperasional($conn);
verify((float)$model->listPage(null,1)[0]['uang_dikeluarkan'] === 0.0, 'Migration preserves old notes with zero amount');
$conn->exec('DELETE FROM catatan_operasional');
$model = new CatatanOperasional($conn); // Migration is safe to repeat.
$model->create(1, 'Kasir Satu', "  Kertas struk habis.\nPerlu dibeli.  ");
$model->create(2, 'Kasir Dua', '<script>alert(1)</script>', CatatanOperasional::expenseAmount('60.000'));
verify((float)$model->listPage(2,1)[0]['uang_dikeluarkan'] === 60000.0, 'Expense persists as 60000, not 6000000');
verify(CatatanOperasional::expenseAmount('60000.00') === 60000.0, 'Database decimal amount is read correctly');
verify(CatatanOperasional::expenseAmount('60.000,50') === 60000.5, 'Decimal expense accepted');
verify(CatatanOperasional::expenseAmount('') === 0.0, 'Blank expense defaults to zero');
foreach (['-1', 'abc', '10000000000', []] as $invalid) {
    try {
        CatatanOperasional::expenseAmount($invalid);
        throw new RuntimeException('Invalid expense accepted');
    } catch (InvalidArgumentException $e) {}
}
verify($model->count() === 2, 'Admin sees both notes');
verify($model->count(1) === 1, 'Cashier sees only own notes');
verify($model->listPage(1, 1)[0]['catatan'] === "Kertas struk habis.\nPerlu dibeli.", 'Text persists, including line breaks');
verify($model->listPage(null, 1, 1)[0]['nama_kasir'] === 'Kasir Dua', 'Newest note appears first');
verify($model->listPage(null, 2, 1)[0]['nama_kasir'] === 'Kasir Satu', 'Pagination does not skip notes');
verify(CatatanOperasional::validate('   ') !== null, 'Empty input rejected');
verify(CatatanOperasional::validate(str_repeat('a', 4001)) !== null, 'Long input rejected');
verify(CatatanController::canAccess('kasir', true), 'Cashier can write');
verify(CatatanController::canAccess('user', true), 'Legacy cashier role supported');
verify(CatatanController::canAccess('admin'), 'Admin can read');
verify(!CatatanController::canAccess('admin', true), 'Admin reader cannot impersonate cashier');
verify(!CatatanController::canAccess('inspeksi') && !CatatanController::canAccess('manager'), 'Other roles denied');
$_SESSION = ['user_id' => 1, 'role' => 'kasir', 'catatan_csrf' => 'correct'];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['csrf_token' => 'incorrect', 'catatan' => 'test'];
ob_start();
(new CatatanController())->store();
ob_end_clean();
verify(http_response_code() === 403, 'Invalid token rejected before persistence');
$_SESSION['role'] = 'manager';
ob_start();
(new CatatanController())->index();
ob_end_clean();
verify(http_response_code() === 403, 'Direct URL denies unauthorized role');
$conn->exec('DELETE FROM users WHERE id_user = 2');
verify($model->listPage(null, 1)[0]['nama_kasir'] === 'Kasir Dua', 'Author name survives account deletion');
$isAdmin = true;
$notes = $model->listPage(null, 1);
$total = 2; $pages = 1; $page = 1;
$source = file_get_contents(__DIR__ . '/../app/views/catatan/index.php');
$source = str_replace("include __DIR__ . '/../layout/header.php';", 'echo $content;', $source);
ob_start(); eval('?>' . $source); $html = ob_get_clean();
verify(strpos($html, '&lt;script&gt;') !== false && strpos($html, '<script>alert') === false, 'Notes escaped in admin view');
verify(strpos($html, '<textarea') === false, 'Admin view is read-only');
verify(strpos($html, 'Rp 60.000') !== false, 'Admin sees expense directly in note card');
$isAdmin = false; $draft = 'Beli kertas struk'; $expenseDraft = '60.000';
ob_start(); eval('?>' . $source); $cashierHtml = ob_get_clean();
verify(strpos($cashierHtml, 'name="uang_dikeluarkan"') !== false && strpos($cashierHtml, 'value="60.000"') !== false, 'Cashier expense field restores draft');
echo "PASS: persistence, owner isolation, admin listing, pagination, validation, role guards, CSRF, HTML escaping, expense amounts and migration\n";
