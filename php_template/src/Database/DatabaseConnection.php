<?php
namespace App\Database;

class DatabaseConnection {
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct() {
        $this->loadConfig();
        $this->connect();
    }

    private function loadConfig() {
        $configPath = __DIR__ . '/../../config/database.php';
        if (!file_exists($configPath)) {
            throw new \Exception("Database configuration file not found");
        }
        $this->config = require $configPath;
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']}";            
            if (!empty($this->config['port'])) {
                $dsn .= ";port={$this->config['port']}";
            }
            
            $this->connection = new \PDO($dsn, $this->config['username'], $this->config['password']);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $this->connection->exec("CREATE DATABASE IF NOT EXISTS `{$this->config['database']}` 
                                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Use the database
            $this->connection->exec("USE `{$this->config['database']}`");
            
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollBack() {
        return $this->connection->rollBack();
    }
}