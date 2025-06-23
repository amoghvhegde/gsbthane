<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Api\ApiClient;

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create API client instance (adjust the base URL as needed)
$apiClient = new ApiClient('http://localhost:8000/api/v1');

// Simple health check example
try {
    // Basic health check (no authentication needed)
    $healthStatus = $apiClient->healthCheck();
    echo "<h2>API Health Check</h2>";
    echo "<pre>" . json_encode($healthStatus, JSON_PRETTY_PRINT) . "</pre>";
    
    // Example: Try to authenticate with the API
    echo "<h2>Authentication Example</h2>";
    if ($apiClient->authenticate('admin@example.com', 'admin123')) {
        echo "<div style='color: green;'>Authentication successful!</div>";
        
        // Example: Get users (requires authentication)
        try {
            echo "<h2>Users Example</h2>";
            $users = $apiClient->getUsers();
            echo "<pre>" . json_encode($users, JSON_PRETTY_PRINT) . "</pre>";
        } catch (Exception $e) {
            echo "<div style='color: red;'>Error fetching users: " . $e->getMessage() . "</div>";
        }
        
        // Example: Get memberships
        try {
            echo "<h2>Memberships Example</h2>";
            $memberships = $apiClient->getMemberships();
            echo "<pre>" . json_encode($memberships, JSON_PRETTY_PRINT) . "</pre>";
        } catch (Exception $e) {
            echo "<div style='color: red;'>Error fetching memberships: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='color: red;'>Authentication failed. Check your credentials.</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>API Error: " . $e->getMessage() . "</div>";
}

// Add some basic styling
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    pre {
        background-color: #f4f4f4;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: auto;
    }
    h2 {
        color: #333;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
</style>
";