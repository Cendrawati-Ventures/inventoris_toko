<?php
/**
 * FORCE Migration endpoint
 * URL: https://your-app.railway.app/force_migrate.php
 * 
 * USE ONLY FOR TESTING
 */

echo '<pre>';
echo "=== FORCE MIGRATION ===\n\n";

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
    
    echo "[1] Connected to database\n";
    print_r([
        'Host' => getenv('DB_HOST'),
        'DB' => getenv('DB_NAME'),
        'User' => getenv('DB_USER'),
    ]);
    echo "\n";

    // Check current directory structure
    echo "[2] File system check:\n";
    $dirs = [
        getcwd(),
        dirname(__FILE__),
        dirname(__FILE__, 2),
        dirname(__FILE__, 2) . '/database',
    ];
    foreach ($dirs as $dir) {
        echo "  " . $dir . " => " . (is_dir($dir) ? "✓ exists" : "✗ missing") . "\n";
    }
    echo "\n";

    // List database directory
    echo "[3] Files in database/ directory:\n";
    $dbPath = dirname(__FILE__, 2) . '/database';
    if (is_dir($dbPath)) {
        foreach (scandir($dbPath) as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $dbPath . '/' . $file;
                $size = filesize($filePath);
                echo "  - $file (" . number_format($size) . " bytes)\n";
            }
        }
    }
    echo "\n";

    // Check schema file
    echo "[4] Schema file check:\n";
    $schemaFile = dirname(__FILE__, 2) . '/database/skema_postgresql.sql';
    echo "  Path: $schemaFile\n";
    echo "  Exists: " . (file_exists($schemaFile) ? "✓ YES" : "✗ NO") . "\n";
    if (file_exists($schemaFile)) {
        $content = file_get_contents($schemaFile);
        echo "  Size: " . number_format(strlen($content)) . " bytes\n";
        echo "  First 200 chars:\n";
        echo "    " . substr($content, 0, 200) . "...\n";
    }
    echo "\n";

    // Run migration
    echo "[5] Running migration:\n";
    require_once dirname(__FILE__, 2) . '/app/config/migrate.php';
    runMigration($conn);
    echo "  Migration completed!\n\n";

    // Check tables
    echo "[6] Checking tables:\n";
    $tables = ['users', 'kategori', 'satuan', 'barang'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as cnt FROM $table");
            $count = $stmt->fetch()['cnt'];
            echo "  ✓ $table: $count records\n";
        } catch (Exception $e) {
            echo "  ✗ $table: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "\n</pre>";
?>
