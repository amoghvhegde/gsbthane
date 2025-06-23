<?php

namespace App\Config;

/**
 * Configuration for API connection
 */
class ApiConfig
{
    /**
     * Base URL for the FastAPI backend
     */
    private static string $baseUrl = 'http://localhost:8000/api/v1';
    
    /**
     * Default admin credentials for automated tests and initial setup
     */
    private static string $defaultAdminEmail = 'admin@example.com';
    private static string $defaultAdminPassword = 'admin123';
    
    /**
     * Get the base URL for API
     */
    public static function getBaseUrl(): string
    {
        // Allow override from environment variable if set
        return $_ENV['API_BASE_URL'] ?? self::$baseUrl;
    }
    
    /**
     * Set the base URL for API
     */
    public static function setBaseUrl(string $url): void
    {
        self::$baseUrl = $url;
    }
    
    /**
     * Get default admin email
     */
    public static function getDefaultAdminEmail(): string
    {
        return self::$defaultAdminEmail;
    }
    
    /**
     * Get default admin password
     */
    public static function getDefaultAdminPassword(): string
    {
        return self::$defaultAdminPassword;
    }
}