<?php

/**
 * Production Readiness Checker
 * Usage: php test-production.php
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║  Toko Inventori - Production Readiness Check          ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";
echo "\n";

$checks = [];
$warnings = [];
$errors = [];

// 1. Check PHP Version
echo "1️⃣  Checking PHP Requirements...\n";
$phpVersion = phpversion();
$checks['PHP Version'] = $phpVersion;

if (version_compare($phpVersion, '7.4.0', '<')) {
    $errors['PHP Version'] = "PHP 7.4+ required, found $phpVersion";
} else {
    echo "   ✓ PHP Version: $phpVersion\n";
}

// 2. Check Required Extensions
echo "\n2️⃣  Checking Required Extensions...\n";
$requiredExtensions = ['pdo', 'pdo_pgsql', 'json', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ $ext\n";
        $checks[$ext] = 'Loaded';
    } else {
        $missingExtensions[] = $ext;
        $errors[$ext] = 'NOT LOADED';
    }
}

if (!empty($missingExtensions)) {
    $errors['Missing Extensions'] = implode(', ', $missingExtensions);
}

// 3. Check File Permissions
echo "\n3️⃣  Checking File Permissions...\n";
$projectRoot = dirname(__DIR__);
$dirsToCheck = [
    'logs' => 'Writable',
    'app/uploads' => 'Writable',
    'public/uploads' => 'Writable',
    'database' => 'Readable'
];

foreach ($dirsToCheck as $dir => $requirement) {
    $path = $projectRoot . '/' . $dir;
    if (is_dir($path)) {
        $isWritable = is_writable($path);
        if ($isWritable && $requirement === 'Writable') {
            echo "   ✓ $dir (writable)\n";
            $checks[$dir] = 'OK';
        } elseif (!$isWritable && $requirement === 'Writable') {
            $warnings[$dir] = 'Not writable (chmod 755 needed)';
            echo "   ⚠️  $dir (NOT writable)\n";
        } else {
            echo "   ✓ $dir\n";
            $checks[$dir] = 'OK';
        }
    } else {
        $warnings[$dir] = 'Directory not found';
        echo "   ⚠️  $dir (NOT FOUND)\n";
    }
}

// 4. Check Environment Variables
echo "\n4️⃣  Checking Environment Variables...\n";
$envFile = $projectRoot . '/.env';
if (file_exists($envFile)) {
    echo "   ✓ .env file exists\n";
    $checks['.env'] = 'Exists';
    
    $env = parse_ini_file($envFile);
    $requiredEnvVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    
    foreach ($requiredEnvVars as $var) {
        if (isset($env[$var]) && !empty($env[$var])) {
            echo "   ✓ $var configured\n";
        } else {
            $warnings[$var] = 'Not configured in .env';
            echo "   ⚠️  $var not configured\n";
        }
    }
} else {
    $errors['.env'] = 'NOT FOUND - Copy from .env.example first!';
    echo "   ✗ .env file not found\n";
}

// 5. Check Database Connection
echo "\n5️⃣  Checking Database Connection...\n";
try {
    require_once dirname(__DIR__) . '/app/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "   ✓ Database connection successful\n";
        $checks['Database'] = 'Connected';
        
        // Test query
        $result = $conn->query("SELECT version()");
        if ($result) {
            $version = $result->fetch();
            echo "   ✓ PostgreSQL Version: " . $version['version'] . "\n";
        }
    } else {
        $errors['Database'] = 'Connection failed';
        echo "   ✗ Database connection failed\n";
    }
} catch (Exception $e) {
    $errors['Database'] = $e->getMessage();
    echo "   ✗ Database Error: " . $e->getMessage() . "\n";
}

// 6. Check Security
echo "\n6️⃣  Checking Security Settings...\n";
if (ini_get('display_errors') == 0) {
    echo "   ✓ display_errors = Off\n";
    $checks['display_errors'] = 'Off';
} else {
    $warnings['display_errors'] = 'Should be Off in production';
    echo "   ⚠️  display_errors is On\n";
}

if (getenv('APP_DEBUG') === 'false' || getenv('APP_DEBUG') === false) {
    echo "   ✓ APP_DEBUG = false\n";
    $checks['APP_DEBUG'] = 'false';
} else {
    $warnings['APP_DEBUG'] = 'Should be false in production';
    echo "   ⚠️  APP_DEBUG = " . getenv('APP_DEBUG') . "\n";
}

// 7. Check .gitignore
echo "\n7️⃣  Checking .gitignore...\n";
$gitignoreFile = $projectRoot . '/.gitignore';
if (file_exists($gitignoreFile)) {
    $gitignoreContent = file_get_contents($gitignoreFile);
    if (strpos($gitignoreContent, '.env') !== false) {
        echo "   ✓ .env in .gitignore\n";
        $checks['.gitignore'] = 'OK';
    } else {
        $warnings['.gitignore'] = '.env should be in .gitignore';
        echo "   ⚠️  .env not in .gitignore\n";
    }
} else {
    $warnings['.gitignore'] = 'File not found';
    echo "   ⚠️  .gitignore not found\n";
}

// Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║  Summary                                               ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ CHECKS PASSED: " . (count($checks)) . "\n";
echo "⚠️  WARNINGS: " . count($warnings) . "\n";
echo "❌ ERRORS: " . count($errors) . "\n";
echo "\n";

if (!empty($errors)) {
    echo "ERRORS TO FIX:\n";
    foreach ($errors as $check => $message) {
        echo "  ❌ $check: $message\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS TO ADDRESS:\n";
    foreach ($warnings as $check => $message) {
        echo "  ⚠️  $check: $message\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "🎉 YOUR APPLICATION IS READY FOR PRODUCTION!\n";
} else {
    echo "🔴 PLEASE FIX THE ERRORS ABOVE BEFORE DEPLOYING\n";
}

echo "\n";
?>
