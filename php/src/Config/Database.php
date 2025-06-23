<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database class for handling connections to Supabase PostgreSQL database
 */
class Database
{
    private static ?PDO $connection = null;
    private static string $host;
    private static string $port;
    private static string $database;
    private static string $user;
    private static string $password;
    
    /**
     * Initialize database connection parameters
     */
    public static function init(string $host, string $port, string $database, string $user, string $password): void
    {
        self::$host = $host;
        self::$port = $port;
        self::$database = $database;
        self::$user = $user;
        self::$password = $password;
    }
    
    /**
     * Get PDO database connection
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = "pgsql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$database;
                self::$connection = new PDO(
                    $dsn, 
                    self::$user, 
                    self::$password, 
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                // Log error and rethrow
                error_log('Connection error: ' . $e->getMessage());
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Close the database connection
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}