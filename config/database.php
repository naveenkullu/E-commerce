<?php
// Database Configuration
// Supports local MySQL defaults and Supabase/PostgreSQL via environment variables.
class Database {
    private $driver;
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $databaseUrl = getenv('DATABASE_URL');

        if ($databaseUrl) {
            $parts = parse_url($databaseUrl);
            $this->driver = (isset($parts['scheme']) && strpos($parts['scheme'], 'postgres') === 0) ? 'pgsql' : 'mysql';
            $this->host = $parts['host'] ?? 'localhost';
            $this->port = $parts['port'] ?? ($this->driver === 'pgsql' ? 5432 : 3306);
            $this->db_name = isset($parts['path']) ? ltrim($parts['path'], '/') : 'postgres';
            $this->username = isset($parts['user']) ? urldecode($parts['user']) : '';
            $this->password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
        } else {
            $this->driver = getenv('DB_DRIVER') ?: (getenv('SUPABASE_DB_HOST') ? 'pgsql' : 'mysql');
            $this->host = getenv('SUPABASE_DB_HOST') ?: getenv('DB_HOST') ?: "localhost";
            $this->port = getenv('DB_PORT') ?: ($this->driver === 'pgsql' ? 5432 : 3306);
            $this->db_name = getenv('DB_NAME') ?: ($this->driver === 'pgsql' ? 'postgres' : 'digital_marketplace');
            $this->username = getenv('DB_USER') ?: "root";
            $this->password = getenv('DB_PASS') ?: "";
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            if ($this->driver === 'pgsql') {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};sslmode=require";
                $options = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                );
            } else {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                $options = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                );
            }

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                $options
            );
        } catch(PDOException $e) {
            if ((getenv('SITE_ENV') ?: 'development') === 'production') {
                echo "Database connection failed.";
            } else {
                echo "Connection Error: " . $e->getMessage();
            }
        }
        
        return $this->conn;
    }
}
?>
