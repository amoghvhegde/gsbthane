<?php
namespace App\Models;

use App\Database\DatabaseConnection;

class Membership {
    private $id;
    private $userId;
    private $gender;
    private $postalAddress;
    private $pinCode;
    private $dateOfBirth;
    private $occupation;
    private $qualification;
    private $maritalStatus;
    private $numberOfKids;
    private $gotra;
    private $kuladevata;
    private $math;
    private $nativePlace;
    private $otherGsbMemberships;
    private $introducerName;
    private $membershipType;
    private $status;
    private $applicationDate;
    private $approvalDate;
    private $aadharNumber;
    private $panNumber;
    
    private $db;
    
    public function __construct($data = []) {
        $this->db = DatabaseConnection::getInstance();
        
        if (!empty($data)) {
            $this->id = $data['id'] ?? $this->generateUUID();
            $this->userId = $data['user_id'] ?? null;
            $this->gender = $data['gender'] ?? null;
            $this->postalAddress = $data['postal_address'] ?? null;
            $this->pinCode = $data['pin_code'] ?? null;
            $this->dateOfBirth = $data['date_of_birth'] ?? null;
            $this->occupation = $data['occupation'] ?? null;
            $this->qualification = $data['qualification'] ?? null;
            $this->maritalStatus = $data['marital_status'] ?? null;
            $this->numberOfKids = $data['number_of_kids'] ?? null;
            $this->gotra = $data['gotra'] ?? null;
            $this->kuladevata = $data['kuladevata'] ?? null;
            $this->math = $data['math'] ?? null;
            $this->nativePlace = $data['native_place'] ?? null;
            $this->otherGsbMemberships = $data['other_gsb_memberships'] ?? null;
            $this->introducerName = $data['introducer_name'] ?? null;
            $this->membershipType = $data['membership_type'] ?? 'PATRON';
            $this->status = $data['status'] ?? 'PENDING';
            $this->applicationDate = $data['application_date'] ?? null;
            $this->approvalDate = $data['approval_date'] ?? null;
            $this->aadharNumber = $data['aadhar_number'] ?? null;
            $this->panNumber = $data['pan_number'] ?? null;
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getGender() { return $this->gender; }
    public function getPostalAddress() { return $this->postalAddress; }
    public function getPinCode() { return $this->pinCode; }
    public function getDateOfBirth() { return $this->dateOfBirth; }
    public function getOccupation() { return $this->occupation; }
    public function getQualification() { return $this->qualification; }
    public function getMaritalStatus() { return $this->maritalStatus; }
    public function getNumberOfKids() { return $this->numberOfKids; }
    public function getGotra() { return $this->gotra; }
    public function getKuladevata() { return $this->kuladevata; }
    public function getMath() { return $this->math; }
    public function getNativePlace() { return $this->nativePlace; }
    public function getOtherGsbMemberships() { return $this->otherGsbMemberships; }
    public function getIntroducerName() { return $this->introducerName; }
    public function getMembershipType() { return $this->membershipType; }
    public function getStatus() { return $this->status; }
    public function getApplicationDate() { return $this->applicationDate; }
    public function getApprovalDate() { return $this->approvalDate; }
    public function getAadharNumber() { return $this->aadharNumber; }
    public function getPanNumber() { return $this->panNumber; }
    
    // Setters
    public function setUserId($userId) { $this->userId = $userId; }
    public function setGender($gender) { $this->gender = $gender; }
    public function setPostalAddress($postalAddress) { $this->postalAddress = $postalAddress; }
    public function setPinCode($pinCode) { $this->pinCode = $pinCode; }
    public function setDateOfBirth($dateOfBirth) { $this->dateOfBirth = $dateOfBirth; }
    public function setOccupation($occupation) { $this->occupation = $occupation; }
    public function setQualification($qualification) { $this->qualification = $qualification; }
    public function setMaritalStatus($maritalStatus) { $this->maritalStatus = $maritalStatus; }
    public function setNumberOfKids($numberOfKids) { $this->numberOfKids = $numberOfKids; }
    public function setGotra($gotra) { $this->gotra = $gotra; }
    public function setKuladevata($kuladevata) { $this->kuladevata = $kuladevata; }
    public function setMath($math) { $this->math = $math; }
    public function setNativePlace($nativePlace) { $this->nativePlace = $nativePlace; }
    public function setOtherGsbMemberships($otherGsbMemberships) { $this->otherGsbMemberships = $otherGsbMemberships; }
    public function setIntroducerName($introducerName) { $this->introducerName = $introducerName; }
    public function setMembershipType($membershipType) { $this->membershipType = $membershipType; }
    public function setStatus($status) { $this->status = $status; }
    public function setApprovalDate($approvalDate) { $this->approvalDate = $approvalDate; }
    public function setAadharNumber($aadharNumber) { $this->aadharNumber = $aadharNumber; }
    public function setPanNumber($panNumber) { $this->panNumber = $panNumber; }
    
    public function save() {
        if (empty($this->id)) {
            $this->id = $this->generateUUID();
        }
        
        // Check if membership exists
        $stmt = $this->db->executeQuery("SELECT id FROM memberships WHERE id = ?", [$this->id]);
        $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($exists) {
            // Update
            $sql = "UPDATE memberships SET 
                    user_id = ?, 
                    gender = ?, 
                    postal_address = ?, 
                    pin_code = ?, 
                    date_of_birth = ?, 
                    occupation = ?, 
                    qualification = ?, 
                    marital_status = ?, 
                    number_of_kids = ?, 
                    gotra = ?, 
                    kuladevata = ?, 
                    math = ?, 
                    native_place = ?, 
                    other_gsb_memberships = ?, 
                    introducer_name = ?, 
                    membership_type = ?, 
                    status = ?, 
                    approval_date = ?, 
                    aadhar_number = ?, 
                    pan_number = ? 
                    WHERE id = ?";
                    
            $params = [
                $this->userId,
                $this->gender,
                $this->postalAddress,
                $this->pinCode,
                $this->dateOfBirth,
                $this->occupation,
                $this->qualification,
                $this->maritalStatus,
                $this->numberOfKids,
                $this->gotra,
                $this->kuladevata,
                $this->math,
                $this->nativePlace,
                $this->otherGsbMemberships,
                $this->introducerName,
                $this->membershipType,
                $this->status,
                $this->approvalDate,
                $this->aadharNumber,
                $this->panNumber,
                $this->id
            ];
            
            $this->db->executeQuery($sql, $params);
        } else {
            // Insert
            $sql = "INSERT INTO memberships 
                    (id, user_id, gender, postal_address, pin_code, date_of_birth, occupation, 
                     qualification, marital_status, number_of_kids, gotra, kuladevata, math, 
                     native_place, other_gsb_memberships, introducer_name, membership_type, 
                     status, approval_date, aadhar_number, pan_number) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $params = [
                $this->id,
                $this->userId,
                $this->gender,
                $this->postalAddress,
                $this->pinCode,
                $this->dateOfBirth,
                $this->occupation,
                $this->qualification,
                $this->maritalStatus,
                $this->numberOfKids,
                $this->gotra,
                $this->kuladevata,
                $this->math,
                $this->nativePlace,
                $this->otherGsbMemberships,
                $this->introducerName,
                $this->membershipType,
                $this->status,
                $this->approvalDate,
                $this->aadharNumber,
                $this->panNumber
            ];
            
            $this->db->executeQuery($sql, $params);
        }
        
        return $this->id;
    }
    
    public function delete() {
        if (empty($this->id)) {
            return false;
        }
        
        $sql = "DELETE FROM memberships WHERE id = ?";
        $this->db->executeQuery($sql, [$this->id]);
        return true;
    }
    
    public static function findById($id) {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT * FROM memberships WHERE id = ?", [$id]);
        $membership = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $membership ? new Membership($membership) : null;
    }
    
    public static function findByUserId($userId) {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT * FROM memberships WHERE user_id = ?", [$userId]);
        $membership = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $membership ? new Membership($membership) : null;
    }
    
    public static function findAll($limit = null, $offset = null) {
        $db = DatabaseConnection::getInstance();
        
        $sql = "SELECT m.*, u.first_name, u.middle_name, u.surname, u.email, u.mobile_no 
                FROM memberships m 
                JOIN users u ON m.user_id = u.id 
                ORDER BY m.application_date DESC";
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
        $memberships = [];
        
        while ($membership = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $memberships[] = new Membership($membership);
        }
        
        return $memberships;
    }
    
    public static function count() {
        $db = DatabaseConnection::getInstance();
        $stmt = $db->executeQuery("SELECT COUNT(*) as count FROM memberships");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? $result['count'] : 0;
    }
    
    public static function search($term, $limit = null, $offset = null) {
        $db = DatabaseConnection::getInstance();
        
        $sql = "SELECT m.*, u.first_name, u.middle_name, u.surname, u.email, u.mobile_no 
                FROM memberships m 
                JOIN users u ON m.user_id = u.id 
                WHERE 
                u.first_name LIKE ? OR 
                u.middle_name LIKE ? OR 
                u.surname LIKE ? OR 
                u.email LIKE ? OR 
                u.mobile_no LIKE ? OR 
                m.gotra LIKE ? OR 
                m.kuladevata LIKE ? OR 
                m.native_place LIKE ? OR 
                m.occupation LIKE ? 
                ORDER BY m.application_date DESC";
                
        $searchTerm = "%{$term}%";
        $params = [
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
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
        $memberships = [];
        
        while ($membership = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $memberships[] = new Membership($membership);
        }
        
        return $memberships;
    }
    
    public function getUser() {
        if (empty($this->userId)) {
            return null;
        }
        
        return User::findById($this->userId);
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