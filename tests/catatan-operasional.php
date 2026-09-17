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
verify($model->listPage(null,1)[0]['uang_di_kasir'] === null, 'Old notes retain unknown cash rather than a fabricated zero');
$conn->exec('DELETE FROM catatan_operasional');
$model = new CatatanOperasional($conn); // Migration is safe to repeat.
$model->create(1, 'Kasir Satu', "  Kertas struk habis.\nPerlu dibeli.  ");
$model->create(2, 'Kasir Dua', '<script>alert(1)</script>', CatatanOperasional::expenseAmount('60.000'), CatatanOperasional::cashAmount('500.000'));
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
verify((float)$model->listPage(2,1)[0]['uang_di_kasir'] === 500000.0, 'Cash balance persists separately from expense');
verify(CatatanOperasional::cashAmount('0') === 0.0, 'Explicit zero cash is valid');
verify(CatatanOperasional::cashAmount('500000.00') === 500000.0, 'Cash database decimal is parsed correctly');
foreach (['', null, '-1', 'invalid', [], '10000000000'] as $invalidCash) {
    try {
        CatatanOperasional::cashAmount($invalidCash);
        throw new RuntimeException('Missing or invalid cash accepted');
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
verify(strpos($html, 'Rp 500.000') !== false, 'Admin sees recorded cash without subtracting expense again');
verify(strpos($html, 'Belum dicatat') !== false, 'Unknown historical cash shown explicitly');
$dateDraft = '2026-09-16';
$isAdmin = false; $draft = 'Beli kertas struk'; $expenseDraft = '60.000'; $cashDraft = '500.000';
ob_start(); eval('?>' . $source); $cashierHtml = ob_get_clean();
verify(strpos($cashierHtml, 'name="uang_dikeluarkan"') !== false && strpos($cashierHtml, 'value="60.000"') !== false, 'Cashier expense field restores draft');
verify(strpos($cashierHtml, 'name="uang_di_kasir" required') !== false && strpos($cashierHtml, 'value="500.000"') !== false, 'Required cashier balance restores draft');
verify(strpos($cashierHtml, 'name="tanggal" required value="2026-09-16"') !== false, 'Selected note date survives validation errors');
verify(strpos($cashierHtml, 'dilihat admin') === false, 'Cashier description does not mention admin visibility');
$model->create(1, 'Kasir Satu', 'Catatan kemarin', 60000, 500000, '2026-09-16');
$backdated = $model->listPage(1, 1)[0];
verify($backdated['tanggal'] === '2026-09-16', 'Backdated note keeps selected date');
verify(substr($backdated['created_at'], 0, 10) === gmdate('Y-m-d'), 'Recording timestamp remains independent of note date');
$notes = [$backdated];
ob_start(); eval('?>' . $source); $datedHtml = ob_get_clean();
verify(strpos($datedHtml, 'Tanggal: 16 September 2026') !== false, 'History displays selected date');
foreach (['', null, [], '2026-02-30', '16-09-2026'] as $invalidDate) {
    try {
        CatatanOperasional::noteDate($invalidDate);
        throw new RuntimeException('Invalid note date accepted');
    } catch (InvalidArgumentException $e) {}
}
echo "PASS: persistence, owner isolation, admin listing, pagination, validation, role guards, CSRF, HTML escaping, expense amounts, cash balance and migration\n";
