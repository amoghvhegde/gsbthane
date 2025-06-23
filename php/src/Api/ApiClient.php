<?php

namespace App\Api;

use Exception;

/**
 * API client for connecting to FastAPI backend
 */
class ApiClient
{
    private string $baseUrl;
    private ?string $accessToken = null;
    private array $headers = [];

    /**
     * ApiClient constructor
     */
    public function __construct(string $baseUrl = 'http://localhost:8000/api/v1')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    /**
     * Set base URL for API calls
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get the current access token
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Set access token for authenticated requests
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Authenticate with the API using username and password
     * @return bool True if authentication successful, false otherwise
     */
    public function authenticate(string $username, string $password): bool
    {
        $formData = [
            'username' => $username, 
            'password' => $password
        ];

        try {
            $response = $this->request('POST', '/auth/login', $formData, true);
            
            if (isset($response['access_token'])) {
                $this->accessToken = $response['access_token'];
                return true;
            }
        } catch (Exception $e) {
            // Authentication failed
        }
        
        return false;
    }

    /**
     * Make HTTP request to the API
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint path
     * @param array|null $data Request data (for POST/PUT requests)
     * @param bool $isFormData Whether the data should be sent as form data
     * @return array Parsed JSON response as associative array
     * @throws Exception If API call fails
     */
    private function request(string $method, string $endpoint, ?array $data = null, bool $isFormData = false): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        
        $headers = $this->headers;
        
        // Add authorization header if token exists
        if ($this->accessToken) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        if ($data !== null) {
            if ($isFormData) {
                // Form data for authentication
                $options[CURLOPT_POSTFIELDS] = http_build_query($data);
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            } else {
                // JSON data for regular requests
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('API request failed: ' . curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        // Handle error responses
        if ($httpCode >= 400) {
            $errorMessage = isset($responseData['detail']) ? $responseData['detail'] : 'Unknown API error';
            throw new Exception('API error: ' . $errorMessage, $httpCode);
        }
        
        return $responseData;
    }

    /**
     * Health check endpoint
     */
    public function healthCheck(): array
    {
        return $this->request('GET', '/health');
    }

    /**
     * Get all users (requires admin access)
     */
    public function getUsers(int $skip = 0, int $limit = 100): array
    {
        return $this->request('GET', '/users?skip=' . $skip . '&limit=' . $limit);
    }

    /**
     * Get user by ID
     */
    public function getUserById(string $userId): array
    {
        return $this->request('GET', '/users/' . $userId);
    }

    /**
     * Create a new user
     */
    public function createUser(array $userData): array
    {
        return $this->request('POST', '/users', $userData);
    }

    /**
     * Update an existing user
     */
    public function updateUser(string $userId, array $userData): array
    {
        return $this->request('PUT', '/users/' . $userId, $userData);
    }

    /**
     * Get all memberships
     */
    public function getMemberships(int $skip = 0, int $limit = 100): array
    {
        return $this->request('GET', '/memberships?skip=' . $skip . '&limit=' . $limit);
    }

    /**
     * Get membership by ID
     */
    public function getMembershipById(string $membershipId): array
    {
        return $this->request('GET', '/memberships/' . $membershipId);
    }

    /**
     * Create a new membership
     */
    public function createMembership(array $membershipData): array
    {
        return $this->request('POST', '/memberships', $membershipData);
    }

    /**
     * Update an existing membership
     */
    public function updateMembership(string $membershipId, array $membershipData): array
    {
        return $this->request('PUT', '/memberships/' . $membershipId, $membershipData);
    }

    /**
     * Get all sevas
     */
    public function getSevas(int $skip = 0, int $limit = 100): array
    {
        return $this->request('GET', '/sevas?skip=' . $skip . '&limit=' . $limit);
    }

    /**
     * Get seva by ID
     */
    public function getSevaById(string $sevaId): array
    {
        return $this->request('GET', '/sevas/' . $sevaId);
    }

    /**
     * Create a new seva
     */
    public function createSeva(array $sevaData): array
    {
        return $this->request('POST', '/sevas', $sevaData);
    }

    /**
     * Update an existing seva
     */
    public function updateSeva(string $sevaId, array $sevaData): array
    {
        return $this->request('PUT', '/sevas/' . $sevaId, $sevaData);
    }

    /**
     * Get all bookings
     */
    public function getBookings(int $skip = 0, int $limit = 100): array
    {
        return $this->request('GET', '/bookings?skip=' . $skip . '&limit=' . $limit);
    }

    /**
     * Get booking by ID
     */
    public function getBookingById(string $bookingId): array
    {
        return $this->request('GET', '/bookings/' . $bookingId);
    }

    /**
     * Create a new booking
     */
    public function createBooking(array $bookingData): array
    {
        return $this->request('POST', '/bookings', $bookingData);
    }

    /**
     * Update an existing booking
     */
    public function updateBooking(string $bookingId, array $bookingData): array
    {
        return $this->request('PUT', '/bookings/' . $bookingId, $bookingData);
    }

    /**
     * Get all pages content
     */
    public function getPages(int $skip = 0, int $limit = 100): array
    {
        return $this->request('GET', '/pages?skip=' . $skip . '&limit=' . $limit);
    }

    /**
     * Get page by slug
     */
    public function getPageBySlug(string $slug): array
    {
        return $this->request('GET', '/pages/' . $slug);
    }

    /**
     * Create a new page
     */
    public function createPage(array $pageData): array
    {
        return $this->request('POST', '/pages', $pageData);
    }

    /**
     * Update an existing page
     */
    public function updatePage(string $slug, array $pageData): array
    {
        return $this->request('PUT', '/pages/' . $slug, $pageData);
    }
}