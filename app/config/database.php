<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        // Load dari .env jika ada, atau gunakan default
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $this->host = $env['DB_HOST'] ?? 'localhost';
            $this->db_name = $env['DB_NAME'] ?? 'toko_inventori';
            $this->username = $env['DB_USER'] ?? 'postgres';
            $this->password = $env['DB_PASS'] ?? 'password';
            $this->port = $env['DB_PORT'] ?? '5432';
        } else {
            // Fallback untuk development
            $this->host = 'localhost';
            $this->db_name = 'toko_inventori';
            $this->username = 'postgres';
            $this->password = 'password';
            $this->port = '5432';
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // PostgreSQL connection string
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES 'UTF8'");
        } catch(PDOException $exception) {
            // Jangan tampilkan detail error di production
            if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
                echo "Connection error: " . $exception->getMessage();
            } else {
                error_log("Database connection error: " . $exception->getMessage());
                die("Database connection failed. Please contact administrator.");
            }
        }

        return $this->conn;
    }
}
