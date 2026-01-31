<?php
/**
 * Manual SQL execution - bypass all helpers
 * URL: https://your-app.railway.app/manual_sql.php
 */

echo '<pre>';
echo "=== MANUAL SQL EXECUTION ===\n\n";

try {
    // Direct connection
    $dsn = "pgsql:host=" . getenv('DB_HOST') . 
           ";port=" . getenv('DB_PORT') . 
           ";dbname=" . getenv('DB_NAME');
    
    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "[1] Connected to " . getenv('DB_NAME') . "\n\n";

    // Check if users table exists
    echo "[2] Checking if users table exists:\n";
    try {
        $stmt = $conn->query("SELECT 1 FROM users LIMIT 1");
        echo "  Result: TABLE EXISTS\n\n";
    } catch (Exception $e) {
        echo "  Result: TABLE DOES NOT EXIST\n";
        echo "  Error: " . $e->getMessage() . "\n\n";
    }

    // List all tables
    echo "[3] All tables in database:\n";
    $stmt = $conn->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
    $tables = $stmt->fetchAll();
    if (empty($tables)) {
        echo "  (no tables found)\n\n";
    } else {
        foreach ($tables as $table) {
            echo "  - " . $table['tablename'] . "\n";
        }
        echo "\n";
    }

    // Try to create users table manually
    echo "[4] Creating users table manually:\n";
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id_user SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    try {
        $conn->exec($sql);
        echo "  ✓ Created successfully\n\n";
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }

    // Verify
    echo "[5] Verify users table now exists:\n";
    try {
        $stmt = $conn->query("SELECT 1 FROM users LIMIT 1");
        echo "  ✓ YES, table exists\n\n";
    } catch (Exception $e) {
        echo "  ✗ NO, still doesn't exist\n";
        echo "  Error: " . $e->getMessage() . "\n\n";
    }

    // Try to insert a test user
    echo "[6] Insert test user:\n";
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?) ON CONFLICT (username) DO NOTHING");
        $stmt->execute(['test', password_hash('test123', PASSWORD_BCRYPT), 'admin']);
        echo "  ✓ Inserted\n\n";
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }

    // Count users
    echo "[7] Count users:\n";
    try {
        $stmt = $conn->query("SELECT COUNT(*) as cnt FROM users");
        $count = $stmt->fetch()['cnt'];
        echo "  Total: $count\n";
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "FATAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n</pre>";
?>
