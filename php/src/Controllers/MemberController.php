<?php

namespace App\Controllers;

use App\Models\Member;

/**
 * Controller for handling member-related operations
 */
class MemberController
{
    /**
     * Get all members
     */
    public function getAll(): array
    {
        $orderBy = $_GET['order_by'] ?? 'last_name';
        $direction = $_GET['direction'] ?? 'ASC';
        
        $members = Member::getAll($orderBy, $direction);
        $result = [];
        
        foreach ($members as $member) {
            $result[] = $member->toArray();
        }
        
        return $result;
    }
    
    /**
     * Get member by ID
     */
    public function getById(int $id): ?array
    {
        $member = Member::findById($id);
        
        if ($member) {
            return $member->toArray();
        }
        
        return null;
    }
    
    /**
     * Create a new member
     */
    public function create(array $data): array
    {
        $this->validateMemberData($data);
        
        $member = new Member(
            null,
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['membershipLevel'] ?? 'Standard',
            $data['joinDate'] ?? date('Y-m-d'),
            $data['active'] ?? true
        );
        
        if ($member->save()) {
            return [
                'success' => true,
                'message' => 'Member created successfully',
                'id' => $member->getId()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to create member'
        ];
    }
    
    /**
     * Update an existing member
     */
    public function update(int $id, array $data): array
    {
        $member = Member::findById($id);
        
        if (!$member) {
            return [
                'success' => false,
                'message' => 'Member not found'
            ];
        }
        
        $this->validateMemberData($data);
        
        if (isset($data['firstName'])) $member->setFirstName($data['firstName']);
        if (isset($data['lastName'])) $member->setLastName($data['lastName']);
        if (isset($data['email'])) $member->setEmail($data['email']);
        if (array_key_exists('phone', $data)) $member->setPhone($data['phone']);
        if (array_key_exists('address', $data)) $member->setAddress($data['address']);
        if (isset($data['membershipLevel'])) $member->setMembershipLevel($data['membershipLevel']);
        if (isset($data['joinDate'])) $member->setJoinDate($data['joinDate']);
        if (isset($data['active'])) $member->setActive($data['active']);
        
        if ($member->save()) {
            return [
                'success' => true,
                'message' => 'Member updated successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update member'
        ];
    }
    
    /**
     * Delete a member
     */
    public function delete(int $id): array
    {
        if (Member::delete($id)) {
            return [
                'success' => true,
                'message' => 'Member deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete member'
        ];
    }
    
    /**
     * Search for members
     */
    public function search(string $term): array
    {
        $members = Member::search($term);
        $result = [];
        
        foreach ($members as $member) {
            $result[] = $member->toArray();
        }
        
        return $result;
    }
    
    /**
     * Validate member data
     * 
     * @throws \InvalidArgumentException
     */
    private function validateMemberData(array $data): void
    {
        // Check required fields
        if (isset($data['firstName']) && empty($data['firstName'])) {
            throw new \InvalidArgumentException('First name is required');
        }
        
        if (isset($data['lastName']) && empty($data['lastName'])) {
            throw new \InvalidArgumentException('Last name is required');
        }
        
        if (isset($data['email'])) {
            if (empty($data['email'])) {
                throw new \InvalidArgumentException('Email is required');
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }
        }
        
        // Validate join date format if provided
        if (isset($data['joinDate']) && !empty($data['joinDate'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['joinDate']);
            if (!$date || $date->format('Y-m-d') !== $data['joinDate']) {
                throw new \InvalidArgumentException('Invalid date format. Use YYYY-MM-DD');
            }
        }
    }
}