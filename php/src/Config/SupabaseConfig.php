<?php

namespace App\Config;

/**
 * Configuration class for Supabase credentials and endpoints
 */
class SupabaseConfig
{
    // Supabase URL from your project settings
    private static string $url;
    
    // Supabase anon key from your project settings
    private static string $key;
    
    // Database connection details
    private static string $dbHost;
    private static string $dbPort = '5432'; // Default PostgreSQL port
    private static string $dbName;
    private static string $dbUser;
    private static string $dbPassword;
    
    /**
     * Initialize Supabase configuration
     */
    public static function init(
        string $url,
        string $key,
        string $dbHost,
        string $dbPort,
        string $dbName,
        string $dbUser,
        string $dbPassword
    ): void {
        self::$url = $url;
        self::$key = $key;
        self::$dbHost = $dbHost;
        self::$dbPort = $dbPort;
        self::$dbName = $dbName;
        self::$dbUser = $dbUser;
        self::$dbPassword = $dbPassword;
        
        // Initialize the database connection settings
        Database::init(self::$dbHost, self::$dbPort, self::$dbName, self::$dbUser, self::$dbPassword);
    }
    
    /**
     * Get Supabase URL
     */
    public static function getUrl(): string
    {
        return self::$url;
    }
    
    /**
     * Get Supabase anonymous key
     */
    public static function getKey(): string
    {
        return self::$key;
    }
    
    /**
     * Get database host
     */
    public static function getDbHost(): string
    {
        return self::$dbHost;
    }
    
    /**
     * Get database port
     */
    public static function getDbPort(): string
    {
        return self::$dbPort;
    }
    
    /**
     * Get database name
     */
    public static function getDbName(): string
    {
        return self::$dbName;
    }
    
    /**
     * Get database user
     */
    public static function getDbUser(): string
    {
        return self::$dbUser;
    }
    
    /**
     * Get database password
     */
    public static function getDbPassword(): string
    {
        return self::$dbPassword;
    }
}