<?php

namespace App\Models;

use App\Api\ApiClient;
use App\Config\ApiConfig;
use Exception;

/**
 * Member model class for interacting with members through API
 */
class ApiMember
{
    private ?string $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone;
    private ?string $address;
    private ?string $membershipLevel;
    private ?string $joinDate;
    private ?bool $active;
    
    private static ?ApiClient $apiClient = null;
    
    /**
     * Constructor
     */
    public function __construct(
        ?string $id = null,
        string $firstName = '',
        string $lastName = '',
        string $email = '',
        ?string $phone = null,
        ?string $address = null,
        ?string $membershipLevel = 'Standard',
        ?string $joinDate = null,
        ?bool $active = true
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->membershipLevel = $membershipLevel;
        $this->joinDate = $joinDate ?? date('Y-m-d');
        $this->active = $active;
    }
    
    /**
     * Initialize API client if not already initialized
     */
    private static function initApiClient(): ApiClient
    {
        if (self::$apiClient === null) {
            self::$apiClient = new ApiClient(ApiConfig::getBaseUrl());
            
            // Try to authenticate with default credentials
            try {
                self::$apiClient->authenticate(
                    ApiConfig::getDefaultAdminEmail(),
                    ApiConfig::getDefaultAdminPassword()
                );
            } catch (Exception $e) {
                // Silent fail - we'll handle authentication errors when making API calls
            }
        }
        
        return self::$apiClient;
    }
    
    /**
     * Save member to backend via API (create or update)
     */
    public function save(): bool
    {
        $apiClient = self::initApiClient();
        
        $memberData = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone_number' => $this->phone, 
            'address' => $this->address,
            'membership_level' => $this->membershipLevel,
            'join_date' => $this->joinDate,
            'is_active' => $this->active
        ];
        
        try {
            if ($this->id === null) {
                // Create new member
                $response = $apiClient->createUser($memberData);
                if (isset($response['id'])) {
                    $this->id = $response['id'];
                    return true;
                }
            } else {
                // Update existing member
                $response = $apiClient->updateUser($this->id, $memberData);
                return isset($response['success']) && $response['success'];
            }
        } catch (Exception $e) {
            // Log error or handle accordingly
            error_log('API Error: ' . $e->getMessage());
            return false;
        }
        
        return false;
    }
    
    /**
     * Find member by ID
     */
    public static function findById(string $id): ?ApiMember
    {
        $apiClient = self::initApiClient();
        
        try {
            $memberData = $apiClient->getUserById($id);
            
            if ($memberData) {
                return new ApiMember(
                    $memberData['id'],
                    $memberData['first_name'] ?? '',
                    $memberData['last_name'] ?? '',
                    $memberData['email'] ?? '',
                    $memberData['phone_number'] ?? null,
                    $memberData['address'] ?? null,
                    $memberData['membership_level'] ?? 'Standard',
                    $memberData['join_date'] ?? date('Y-m-d'),
                    $memberData['is_active'] ?? true
                );
            }
        } catch (Exception $e) {
            error_log('API Error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get all members
     */
    public static function getAll(string $orderBy = 'last_name', string $direction = 'ASC'): array
    {
        $apiClient = self::initApiClient();
        
        try {
            // Note: API might have different sorting parameters
            $membersData = $apiClient->getUsers();
            $members = [];
            
            foreach ($membersData as $memberData) {
                $members[] = new ApiMember(
                    $memberData['id'],
                    $memberData['first_name'] ?? '',
                    $memberData['last_name'] ?? '',
                    $memberData['email'] ?? '',
                    $memberData['phone_number'] ?? null,
                    $memberData['address'] ?? null,
                    $memberData['membership_level'] ?? 'Standard',
                    $memberData['join_date'] ?? date('Y-m-d'),
                    $memberData['is_active'] ?? true
                );
            }
            
            // Sort locally if needed (if API doesn't support sorting)
            if ($orderBy && $direction) {
                usort($members, function($a, $b) use ($orderBy, $direction) {
                    $getter = 'get' . ucfirst($orderBy);
                    $valueA = $a->$getter();
                    $valueB = $b->$getter();
                    
                    $comparison = $valueA <=> $valueB;
                    return $direction === 'DESC' ? -$comparison : $comparison;
                });
            }
            
            return $members;
        } catch (Exception $e) {
            error_log('API Error: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Delete member by ID
     */
    public static function delete(string $id): bool
    {
        $apiClient = self::initApiClient();
        
        try {
            // Assuming there's a delete method in your FastAPI
            // (You may need to implement this in ApiClient)
            $response = $apiClient->request('DELETE', '/users/' . $id);
            return isset($response['success']) && $response['success'];
        } catch (Exception $e) {
            error_log('API Error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Search members
     */
    public static function search(string $term): array
    {
        $apiClient = self::initApiClient();
        
        try {
            // Assuming there's a search endpoint in your FastAPI
            // (You may need to implement this in ApiClient)
            $response = $apiClient->request('GET', '/users?search=' . urlencode($term));
            
            $members = [];
            foreach ($response as $memberData) {
                $members[] = new ApiMember(
                    $memberData['id'],
                    $memberData['first_name'] ?? '',
                    $memberData['last_name'] ?? '',
                    $memberData['email'] ?? '',
                    $memberData['phone_number'] ?? null,
                    $memberData['address'] ?? null,
                    $memberData['membership_level'] ?? 'Standard',
                    $memberData['join_date'] ?? date('Y-m-d'),
                    $memberData['is_active'] ?? true
                );
            }
            
            return $members;
        } catch (Exception $e) {
            error_log('API Error: ' . $e->getMessage());
        }
        
        return [];
    }
    
    // Getters and setters
    
    public function getId(): ?string
    {
        return $this->id;
    }
    
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }
    
    public function getLastName(): string
    {
        return $this->lastName;
    }
    
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }
    
    public function getAddress(): ?string
    {
        return $this->address;
    }
    
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }
    
    public function getMembershipLevel(): ?string
    {
        return $this->membershipLevel;
    }
    
    public function setMembershipLevel(string $membershipLevel): void
    {
        $this->membershipLevel = $membershipLevel;
    }
    
    public function getJoinDate(): ?string
    {
        return $this->joinDate;
    }
    
    public function setJoinDate(string $joinDate): void
    {
        $this->joinDate = $joinDate;
    }
    
    public function isActive(): ?bool
    {
        return $this->active;
    }
    
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
    
    /**
     * Convert member to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'membershipLevel' => $this->membershipLevel,
            'joinDate' => $this->joinDate,
            'active' => $this->active,
        ];
    }
}