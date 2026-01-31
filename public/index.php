<?php
// Start session
session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load configuration
require_once BASE_PATH . '/app/config/config.php';

// Load database
require_once BASE_PATH . '/app/config/database.php';

// Check authentication (except for login route)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /public from the path if it exists
if (strpos($requestUri, '/public') === 0) {
    $requestUri = substr($requestUri, 7);
}

// Clean up the URI
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri)) {
    $requestUri = '/';
}

// Routes yang tidak perlu login
$publicRoutes = ['/login', '/api/search-barang'];

if (!in_array($requestUri, $publicRoutes) && !isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Load routes
require_once BASE_PATH . '/routes/web.php';
