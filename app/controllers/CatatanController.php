<?php
require_once __DIR__ . '/../models/CatatanOperasional.php';
require_once __DIR__ . '/../helpers/format.php';

class CatatanController {
    // This feature is explicitly restricted to cashier authors and admin readers.
    public static function canAccess(string $role, bool $write = false): bool {
        $role = strtolower(trim($role));
        if ($role === 'user') $role = 'kasir';
        return $write ? $role === 'kasir' : in_array($role, ['kasir', 'admin'], true);
    }

    private function authorize(bool $write = false): bool {
        if (empty($_SESSION['user_id']) || !self::canAccess((string)($_SESSION['role'] ?? ''), $write)) {
            http_response_code(403);
            echo 'Anda tidak memiliki akses ke catatan operasional ini.';
            return false;
        }
        return true;
    }

    public function index(): void {
        if (!$this->authorize()) return;
        $isAdmin = strtolower(trim((string)$_SESSION['role'])) === 'admin';
        $userId = $isAdmin ? null : (int)$_SESSION['user_id'];
        $model = new CatatanOperasional();
        $total = $model->count($userId);
        $pages = max(1, (int)ceil($total / 25));
        $page = min($pages, max(1, (int)($_GET['page'] ?? 1)));
        $notes = $model->listPage($userId, $page);
        if (empty($_SESSION['catatan_csrf'])) $_SESSION['catatan_csrf'] = bin2hex(random_bytes(32));
        $draft = $_SESSION['catatan_draft'] ?? '';
        $cashDraft = $_SESSION['catatan_cash_draft'] ?? '';
        $expenseDraft = $_SESSION['catatan_expense_draft'] ?? '0';
        unset($_SESSION['catatan_draft'], $_SESSION['catatan_expense_draft'], $_SESSION['catatan_cash_draft']);
        require __DIR__ . '/../views/catatan/index.php';
    }

    public function store(): void {
        if (!$this->authorize(true)) return;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || empty($_SESSION['catatan_csrf']) || !hash_equals($_SESSION['catatan_csrf'], $token)) {
            http_response_code(403);
            echo 'Formulir sudah tidak berlaku. Muat ulang halaman catatan operasional.';
            return;
        }
        $catatan = is_string($_POST['catatan'] ?? null) ? trim($_POST['catatan']) : '';
        $_SESSION['catatan_draft'] = $catatan;
        $cashInput = $_POST['uang_di_kasir'] ?? '';
        $_SESSION['catatan_cash_draft'] = is_scalar($cashInput) ? (string)$cashInput : '';
        $expenseInput = $_POST['uang_dikeluarkan'] ?? '';
        $_SESSION['catatan_expense_draft'] = is_scalar($expenseInput) ? (string)$expenseInput : '';
        try {
            $expense = CatatanOperasional::expenseAmount($expenseInput);
            $cash = CatatanOperasional::cashAmount($cashInput);
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect('/catatan-operasional');
        }
        if ($error = CatatanOperasional::validate($catatan)) {
            $_SESSION['error'] = $error;
            redirect('/catatan-operasional');
        }
        try {
            (new CatatanOperasional())->create((int)$_SESSION['user_id'], (string)($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Kasir'), $catatan, $expense, $cash);
        } catch (Exception $e) {
            error_log('Simpan catatan operasional gagal: ' . $e->getMessage());
            $_SESSION['error'] = 'Catatan belum tersimpan. Silakan coba lagi.';
            redirect('/catatan-operasional');
        }
        unset($_SESSION['catatan_draft'], $_SESSION['catatan_expense_draft'], $_SESSION['catatan_cash_draft']);
        $_SESSION['catatan_csrf'] = bin2hex(random_bytes(32));
        $_SESSION['success'] = 'Catatan operasional tersimpan dan dapat dilihat admin.';
        redirect('/catatan-operasional');
    }
}
