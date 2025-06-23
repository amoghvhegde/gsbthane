<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Member model class for interacting with members table in database
 */
class Member
{
    private ?int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone;
    private ?string $address;
    private ?string $membershipLevel;
    private ?string $joinDate;
    private ?bool $active;
    
    /**
     * Constructor
     */
    public function __construct(
        ?int $id = null,
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
     * Save member to database (create or update)
     */
    public function save(): bool
    {
        $db = Database::getConnection();
        
        if ($this->id === null) {
            // Insert new member
            $sql = "INSERT INTO members (first_name, last_name, email, phone, address, membership_level, join_date, active) 
                    VALUES (:firstName, :lastName, :email, :phone, :address, :membershipLevel, :joinDate, :active)
                    RETURNING id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':firstName', $this->firstName);
            $stmt->bindParam(':lastName', $this->lastName);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':membershipLevel', $this->membershipLevel);
            $stmt->bindParam(':joinDate', $this->joinDate);
            $stmt->bindParam(':active', $this->active, PDO::PARAM_BOOL);
            
            if ($stmt->execute()) {
                $this->id = (int)$db->lastInsertId();
                return true;
            }
            
            return false;
        } else {
            // Update existing member
            $sql = "UPDATE members 
                    SET first_name = :firstName, 
                        last_name = :lastName, 
                        email = :email, 
                        phone = :phone, 
                        address = :address, 
                        membership_level = :membershipLevel, 
                        join_date = :joinDate, 
                        active = :active 
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':firstName', $this->firstName);
            $stmt->bindParam(':lastName', $this->lastName);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':membershipLevel', $this->membershipLevel);
            $stmt->bindParam(':joinDate', $this->joinDate);
            $stmt->bindParam(':active', $this->active, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
    }
    
    /**
     * Find member by ID
     */
    public static function findById(int $id): ?Member
    {
        $db = Database::getConnection();
        
        $sql = "SELECT * FROM members WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $memberData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$memberData) {
            return null;
        }
        
        return new Member(
            (int)$memberData['id'],
            $memberData['first_name'],
            $memberData['last_name'],
            $memberData['email'],
            $memberData['phone'],
            $memberData['address'],
            $memberData['membership_level'],
            $memberData['join_date'],
            (bool)$memberData['active']
        );
    }
    
    /**
     * Get all members
     */
    public static function getAll(string $orderBy = 'last_name', string $direction = 'ASC'): array
    {
        $allowedColumns = ['id', 'first_name', 'last_name', 'email', 'membership_level', 'join_date'];
        $allowedDirections = ['ASC', 'DESC'];
        
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'last_name';
        }
        
        if (!in_array(strtoupper($direction), $allowedDirections)) {
            $direction = 'ASC';
        }
        
        $db = Database::getConnection();
        
        $sql = "SELECT * FROM members ORDER BY $orderBy $direction";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $members = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $members[] = new Member(
                (int)$row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['phone'],
                $row['address'],
                $row['membership_level'],
                $row['join_date'],
                (bool)$row['active']
            );
        }
        
        return $members;
    }
    
    /**
     * Delete member by ID
     */
    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        
        $sql = "DELETE FROM members WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Search members
     */
    public static function search(string $term): array
    {
        $db = Database::getConnection();
        
        $searchTerm = '%' . $term . '%';
        
        $sql = "SELECT * FROM members 
                WHERE first_name ILIKE :term 
                OR last_name ILIKE :term 
                OR email ILIKE :term 
                ORDER BY last_name ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':term', $searchTerm);
        $stmt->execute();
        
        $members = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $members[] = new Member(
                (int)$row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['email'],
                $row['phone'],
                $row['address'],
                $row['membership_level'],
                $row['join_date'],
                (bool)$row['active']
            );
        }
        
        return $members;
    }
    
    // Getters and setters
    
    public function getId(): ?int
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