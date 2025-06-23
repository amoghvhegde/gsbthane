<?php

namespace App\Controllers;

use App\Api\ApiClient;
use App\Config\ApiConfig;
use Exception;

/**
 * Base controller for API operations
 */
class ApiController
{
    protected ApiClient $apiClient;
    protected bool $authenticated = false;
    protected array $errors = [];
    protected ?array $userData = null;

    /**
     * Constructor - initialize API client and try to authenticate
     */
    public function __construct(string $baseUrl = null)
    {
        // Initialize API client
        $this->apiClient = new ApiClient($baseUrl ?? ApiConfig::getBaseUrl());
        
        // Try to authenticate if session has token
        $this->trySessionAuthentication();
    }

    /**
     * Try to authenticate using session data
     */
    protected function trySessionAuthentication(): bool
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if we have a valid token in session
        if (isset($_SESSION['api_token'])) {
            $this->apiClient->setAccessToken($_SESSION['api_token']);
            
            // Verify token by making a simple API call
            try {
                // Try a simple health check or user profile call
                $this->userData = $this->apiClient->request('GET', '/users/me');
                $this->authenticated = true;
                return true;
            } catch (Exception $e) {
                // Token invalid or expired - clear it
                $this->logout();
            }
        }
        
        return false;
    }

    /**
     * Log in user and store token in session
     */
    public function login(string $username, string $password): bool
    {
        try {
            if ($this->apiClient->authenticate($username, $password)) {
                // Start session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Store token in session
                $_SESSION['api_token'] = $this->apiClient->getAccessToken();
                $this->authenticated = true;
                
                // Get user data
                try {
                    $this->userData = $this->apiClient->request('GET', '/users/me');
                } catch (Exception $e) {
                    // Failed to get user data, but authentication succeeded
                }
                
                return true;
            }
        } catch (Exception $e) {
            $this->errors[] = 'Authentication failed: ' . $e->getMessage();
        }
        
        return false;
    }

    /**
     * Log out current user
     */
    public function logout(): void
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear token from session
        if (isset($_SESSION['api_token'])) {
            unset($_SESSION['api_token']);
        }
        
        // Reset authentication status
        $this->apiClient->setAccessToken(null);
        $this->authenticated = false;
        $this->userData = null;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    /**
     * Get current authenticated user data
     */
    public function getUserData(): ?array
    {
        return $this->userData;
    }

    /**
     * Get last errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Clear errors
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Add error message
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Execute API request and handle common errors
     */
    protected function executeRequest(string $method, string $endpoint, ?array $data = null): ?array
    {
        try {
            return $this->apiClient->request($method, $endpoint, $data);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            
            // Check if error is due to authentication
            if ($e->getCode() === 401) {
                $this->logout();
            }
            
            return null;
        }
    }

    /**
     * Get API client instance
     */
    public function getApiClient(): ApiClient
    {
        return $this->apiClient;
    }
}