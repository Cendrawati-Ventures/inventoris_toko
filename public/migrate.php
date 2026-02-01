<?php
// Migration Script - Jalankan sekali untuk create tables di Railway
require_once __DIR__ . '/../app/config/database.php';

echo "<!DOCTYPE html><html><head><title>Database Migration</title></head><body>";
echo "<h2>🔧 Database Migration untuk Railway</h2>";
echo "<pre>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed!");
    }
    
    echo "✓ Database connection successful\n\n";
    echo "📋 Creating tables...\n";
    echo str_repeat("-", 50) . "\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/../database/skema_postgresql.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Execute SQL
    $conn->exec($sql);
    
    echo "\n✅ SUCCESS! All tables created!\n";
    echo str_repeat("-", 50) . "\n\n";
    
    // Check tables
    echo "📊 Verifying tables...\n";
    $stmt = $conn->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  • $table\n";
        }
    } else {
        echo "⚠️ No tables found!\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    echo "✅ Migration completed successfully!\n";
    echo "\n🎉 You can now access your application at:\n";
    echo "   <a href='/login'>/login</a>\n";
    echo "\n👤 Default credentials:\n";
    echo "   Admin: admin / admin123\n";
    echo "   Kasir: kasir / kasir123\n";
    
} catch (PDOException $e) {
    echo "\n❌ Database Error:\n";
    echo $e->getMessage() . "\n";
    exit;
} catch (Exception $e) {
    echo "\n❌ Error:\n";
    echo $e->getMessage() . "\n";
    exit;
}

echo "</pre></body></html>";
?>
