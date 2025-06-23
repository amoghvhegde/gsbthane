<?php
namespace App\Models;

use App\Database\DatabaseConnection;

class User {
    private $id;
    private $firstName;
    private $middleName;
    private $surname;
    private $email;
    private $mobileNo;
    private $userType;
    private $isAdmin;
    private $createdAt;
    private $updatedAt;
    
    private $db;
    
    public function __construct($data = []) {
        $this->db = DatabaseConnection::getInstance();
        
        if (!empty($data)) {
            $this->id = $data['id'] ?? $this->generateUUID();
            $this->firstName = $data['first_name'] ?? null;
            $this->middleName = $data['middle_name'] ?? null;
            $this->surname = $data['surname'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->mobileNo = $data['mobile_no'] ?? null;
            $this->userType = $data['user_type'] ?? 'NM';
            $this->isAdmin = $data['is_admin'] ?? false;
            $this->createdAt = $data['created_at'] ?? null;
            $this->updatedAt = $data['updated_at'] ?? null;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getFirstName() { return $this->firstName; }
    public function getMiddleName() { return $this->middleName; }
    public function getSurname() { return $this->surname; }
    public function getEmail() { return $this->email; }
    public function getMobileNo() { return $this->mobileNo; }
    public function getUserType() { return $this->userType; }
    public function isAdmin() { return $this->isAdmin; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    
    // Setters
    public function setFirstName($firstName) { $this->firstName = $firstName; }
    public function setMiddleName($middleName) { $this->middleName = $middleName; }
    public function setSurname($surname) { $this->surname = $surname; }
    public function setEmail($email) { $this->email = $email; }
    public function setMobileNo($mobileNo) { $this->mobileNo = $mobileNo; }
    public function setUserType($userType) { $this->userType = $userType; }
    public function setIsAdmin($isAdmin) { $this->isAdmin = $isAdmin; }
    
    public function getFullName() {
        $fullName = $this->firstName;
        if (!empty($this->middleName)) {
            $fullName .= ' ' . $this->middleName;
        }
        $fullName .= ' ' . $this->surname;
        return $fullName;
    }
    
    public function save() {
        if (empty($this->id)) {
            $this->id = $this->generateUUID();
        }
        
        // Check if user exists
        $stmt = $this->db->executeQuery("SELECT id FROM users WHERE id = ?", [$this->id]);
        $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($exists) {
            // Update
            $sql = "UPDATE users SET 
                    first_name = ?, 
                    middle_name = ?, 
                    surname = ?, 
                    email = ?, 
                    mobile_no = ?, 
                    user_type = ?, 
                    is_admin = ? 
                    WHERE id = ?";
                    
            $params = [
                $this->firstName,
                $this->middleName,
                $this->surname,
                $this->email,
                $this->mobileNo,
                $this->userType,
                $this->isAdmin ? 1 : 0,
                $this->id
            ];
            
            $this->db->executeQuery($sql, $params);
        } else {
            // Insert
            $sql = "INSERT INTO users 
                    (id, first_name, middle_name, surname, email, mobile_no, user_type, is_admin) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $params = [
                $this->id,
                $this->firstName,
                $this->middleName,
                $this->surname,
                $this->email,
                $this->mobileNo,
                $this->userType,
                $this->isAdmin ? 1 : 0
            ];
            
            $this->db->executeQuery($sql, $params);
        }
        
        return $this->id;
    }
    
    public function delete() {
        if (empty($this->id)) {
            return false;
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $this->db->executeQuery($sql, [$this->id]);
        return true;
    }
    
    public static function findById($id) {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT * FROM users WHERE id = ?", [$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user ? new User($user) : null;
    }
    
    public static function findByEmail($email) {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user ? new User($user) : null;
    }
    
    public static function findByMobileNo($mobileNo) {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT * FROM users WHERE mobile_no = ?", [$mobileNo]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $user ? new User($user) : null;
    }
    
    public static function findAll($limit = null, $offset = null) {
        $db = DatabaseConnection::getInstance();
        
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $params = [];
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }
        
        $stmt = $db->executeQuery($sql, $params);
        $users = [];
        
        while ($user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = new User($user);
        }
        
        return $users;
    }
    
    public static function count() {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? $result['count'] : 0;
    }
    
    public static function search($term, $limit = null, $offset = null) {
        $db = DatabaseConnection::getInstance();
        
        $sql = "SELECT * FROM users WHERE 
                first_name LIKE ? OR 
                middle_name LIKE ? OR 
                surname LIKE ? OR 
                email LIKE ? OR 
                mobile_no LIKE ? 
                ORDER BY created_at DESC";
                
        $searchTerm = "%{$term}%";
        $params = [
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm
        ];
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }
        
        $stmt = $db->executeQuery($sql, $params);
        $users = [];
        
        while ($user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = new User($user);
        }
        
        return $users;
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}