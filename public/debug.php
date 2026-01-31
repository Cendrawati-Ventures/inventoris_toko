<?php
/**
 * Debug endpoint untuk test database & seed
 * URL: https://your-app.railway.app/debug.php
 * 
 * NOTES:
 * - Hanya untuk debugging, JANGAN deploy ke production!
 * - Delete setelah done
 */

header('Content-Type: text/html; charset=utf-8');

echo '<pre style="font-family: monospace; background: #f5f5f5; padding: 15px;">' . PHP_EOL;

try {
    echo "=== DATABASE & SEED DEBUG ===" . PHP_EOL . PHP_EOL;

    // Connect ke database
    echo "[1] Connecting to database..." . PHP_EOL;
    require_once __DIR__ . '/../app/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "✓ Connected" . PHP_EOL . PHP_EOL;

    // Check tables
    echo "[2] Checking tables..." . PHP_EOL;
    $tables = ['users', 'kategori', 'satuan', 'barang'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "  ✓ $table: $count records" . PHP_EOL;
        } catch (Exception $e) {
            echo "  ✗ $table: NOT FOUND" . PHP_EOL;
        }
    }
    echo PHP_EOL;

    // Check users
    echo "[3] Checking users..." . PHP_EOL;
    $stmt = $conn->query("SELECT id_user, username, role FROM users");
    $users = $stmt->fetchAll();
    foreach ($users as $user) {
        echo "  - ID: {$user['id_user']}, Username: {$user['username']}, Role: {$user['role']}" . PHP_EOL;
    }
    echo PHP_EOL;

    // Test login
    echo "[4] Testing login..." . PHP_EOL;
    require_once __DIR__ . '/../app/models/User.php';
    $userModel = new User();
    
    $loginTest = $userModel->login('admin', 'admin123');
    if ($loginTest) {
        echo "  ✓ admin/admin123: LOGIN SUCCESS" . PHP_EOL;
    } else {
        echo "  ✗ admin/admin123: LOGIN FAILED" . PHP_EOL;
    }
    
    $loginTest2 = $userModel->login('kasir', 'kasir123');
    if ($loginTest2) {
        echo "  ✓ kasir/kasir123: LOGIN SUCCESS" . PHP_EOL;
    } else {
        echo "  ✗ kasir/kasir123: LOGIN FAILED" . PHP_EOL;
    }
    echo PHP_EOL;

    echo "=== ALL CHECKS COMPLETE ===" . PHP_EOL;
    echo "✓ If all tests passed, login should work!" . PHP_EOL;

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo '</pre>';
?>
