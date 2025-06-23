<?php

namespace App\Controllers;

use App\Models\ApiMember;
use Exception;

/**
 * Controller for member-related API operations
 */
class MemberApiController extends ApiController
{
    /**
     * Get all members
     */
    public function getAllMembers(int $page = 1, int $limit = 20): array
    {
        $skip = ($page - 1) * $limit;
        
        $result = $this->executeRequest('GET', '/users?skip=' . $skip . '&limit=' . $limit);
        
        if (!$result) {
            return [];
        }
        
        // Convert API response to ApiMember objects
        $members = [];
        foreach ($result as $memberData) {
            $members[] = $this->mapApiResponseToMember($memberData);
        }
        
        return $members;
    }
    
    /**
     * Get member by ID
     */
    public function getMemberById(string $id): ?ApiMember
    {
        $memberData = $this->executeRequest('GET', '/users/' . $id);
        
        if (!$memberData) {
            return null;
        }
        
        return $this->mapApiResponseToMember($memberData);
    }
    
    /**
     * Create a new member
     */
    public function createMember(array $memberData): ?ApiMember
    {
        $data = $this->prepareMemberData($memberData);
        $result = $this->executeRequest('POST', '/users', $data);
        
        if (!$result) {
            return null;
        }
        
        return $this->mapApiResponseToMember($result);
    }
    
    /**
     * Update an existing member
     */
    public function updateMember(string $id, array $memberData): bool
    {
        $data = $this->prepareMemberData($memberData);
        $result = $this->executeRequest('PUT', '/users/' . $id, $data);
        
        return $result !== null;
    }
    
    /**
     * Delete a member
     */
    public function deleteMember(string $id): bool
    {
        $result = $this->executeRequest('DELETE', '/users/' . $id);
        
        return $result !== null && isset($result['success']) && $result['success'];
    }
    
    /**
     * Search members by term
     */
    public function searchMembers(string $searchTerm): array
    {
        $result = $this->executeRequest('GET', '/users?search=' . urlencode($searchTerm));
        
        if (!$result) {
            return [];
        }
        
        // Convert API response to ApiMember objects
        $members = [];
        foreach ($result as $memberData) {
            $members[] = $this->mapApiResponseToMember($memberData);
        }
        
        return $members;
    }

    /**
     * Map API response to ApiMember object
     */
    private function mapApiResponseToMember(array $data): ApiMember
    {
        return new ApiMember(
            $data['id'] ?? null,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['email'] ?? '',
            $data['phone_number'] ?? null,
            $data['address'] ?? null,
            $data['membership_level'] ?? 'Standard',
            $data['join_date'] ?? date('Y-m-d'),
            $data['is_active'] ?? true
        );
    }
    
    /**
     * Prepare member data for API submission
     */
    private function prepareMemberData(array $data): array
    {
        return [
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone_number' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'membership_level' => $data['membership_level'] ?? 'Standard',
            'join_date' => $data['join_date'] ?? date('Y-m-d'),
            'is_active' => $data['active'] ?? true,
        ];
    }
    
    /**
     * Get member statistics
     */
    public function getMemberStats(): ?array
    {
        return $this->executeRequest('GET', '/users/stats');
    }
    
    /**
     * Import members from CSV file
     */
    public function importMembers(string $csvFilePath): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        
        // Read CSV file
        if (($handle = fopen($csvFilePath, "r")) !== false) {
            // Skip header row
            $header = fgetcsv($handle, 1000, ",");
            
            // Process each row
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Map CSV columns to member data
                $memberData = [
                    'first_name' => $data[0] ?? '',
                    'last_name' => $data[1] ?? '',
                    'email' => $data[2] ?? '',
                    'phone' => $data[3] ?? null,
                    'address' => $data[4] ?? null,
                    'membership_level' => $data[5] ?? 'Standard',
                    'join_date' => $data[6] ?? date('Y-m-d'),
                    'active' => $data[7] ?? true,
                ];
                
                // Create member via API
                $result = $this->createMember($memberData);
                
                if ($result) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = "Failed to import member: " . $memberData['first_name'] . ' ' . $memberData['last_name'];
                }
            }
            fclose($handle);
        }
        
        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }
}