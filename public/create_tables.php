<?php
/**
 * FORCE CREATE TABLES - Bypass existence check
 * URL: https://your-app.railway.app/create_tables.php
 */

echo '<pre>';
echo "=== FORCE CREATE TABLES ===\n\n";

try {
    // Connect directly
    $dsn = "pgsql:host=" . (getenv('DB_HOST') ?? 'localhost') . 
           ";port=" . (getenv('DB_PORT') ?? '5432') . 
           ";dbname=" . (getenv('DB_NAME') ?? 'toko_inventori');
    
    $conn = new PDO(
        $dsn,
        getenv('DB_USER') ?? 'postgres',
        getenv('DB_PASS') ?? 'password'
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "[1] Connected\n\n";

    // Load embedded schema
    require_once __DIR__ . '/../app/config/schema_embedded.php';
    $schema = getEmbeddedSchema();
    
    echo "[2] Schema loaded (" . strlen($schema) . " bytes)\n\n";
    
    // Split into statements
    $statements = array_filter(array_map('trim', preg_split('/;/', $schema)));
    echo "[3] Total statements: " . count($statements) . "\n\n";
    
    // Execute each
    echo "[4] Executing statements:\n";
    $successCount = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|\\\\c)/i', $statement)) {
            try {
                $conn->exec($statement . ';');
                $successCount++;
                // Show first 60 chars of each successful statement
                echo "  ✓ " . substr(trim($statement), 0, 60) . "...\n";
            } catch (Exception $e) {
                // Only log real errors, not "already exists"
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $errors[] = [
                        'statement' => substr($statement, 0, 100),
                        'error' => $e->getMessage()
                    ];
                    echo "  ✗ ERROR: " . $e->getMessage() . "\n";
                } else {
                    $successCount++;
                    echo "  ↻ Already exists (skipped)\n";
                }
            }
        }
    }
    
    echo "\n[5] Results: $successCount / " . count($statements) . " executed\n";
    
    if (!empty($errors)) {
        echo "\nErrors encountered:\n";
        foreach ($errors as $err) {
            echo "  - " . $err['error'] . "\n";
            echo "    Statement: " . $err['statement'] . "...\n";
        }
    }
    
    // Verify tables now
    echo "\n[6] Verification:\n";
    $tables = ['users', 'kategori', 'satuan', 'barang', 'pembelian', 'penjualan'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as cnt FROM $table");
            $count = $stmt->fetch()['cnt'];
            echo "  ✓ $table: exists ($count records)\n";
        } catch (Exception $e) {
            echo "  ✗ $table: NOT FOUND\n";
        }
    }
    
    echo "\n[7] Now run seeding:\n";
    require_once __DIR__ . '/../app/config/seed.php';
    seedIfNeeded($conn);
    echo "  ✓ Seeding completed\n";
    
    // Final count
    echo "\n[8] Final counts:\n";
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as cnt FROM $table");
            $count = $stmt->fetch()['cnt'];
            echo "  - $table: $count records\n";
        } catch (Exception $e) {
            echo "  - $table: ERROR\n";
        }
    }

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n</pre>";
?>
