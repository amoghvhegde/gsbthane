<?php
namespace App\Database;

use App\Utils\CSVParser;

class DataImporter {
    private $db;
    private $csvParser;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
        $this->csvParser = new CSVParser();
    }
    
    public function importMemberData($membersFilePath, $addressesFilePath, $consolidatedFilePath) {
        // Start transaction for data import
        $this->db->beginTransaction();
        
        try {
            // Import Users and Memberships from FINAL-MEMBER.csv
            $this->importUsers($membersFilePath);
            
            // Update addresses from FINAL-ADDRESS.csv
            $this->updateAddresses($addressesFilePath);
            
            // Import additional data from CONSOLIDATED.csv if needed
            $this->importAdditionalData($consolidatedFilePath);
            
            // Commit transaction if all imports are successful
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            $this->db->rollBack();
            throw new \Exception("Failed to import data: " . $e->getMessage());
        }
    }
    
    private function importUsers($filePath) {
        if (!file_exists($filePath)) {
            throw new \Exception("Member data file not found: {$filePath}");
        }
        
        $memberData = $this->csvParser->parse($filePath);
        
        if (empty($memberData)) {
            throw new \Exception("No data found in member file");
        }
        
        foreach ($memberData as $row) {
            // Generate UUID for user
            $userId = $this->generateUUID();
            
            // Prepare user data
            $firstName = $row['NAME'] ?? '';
            $middleName = $row['MIDDLE_NAME'] ?? null;
            $surname = $row['SURNAME'] ?? '';
            $email = $row['EMAIL'] ?? null;
            $mobileNo = $row['MOBILE'] ?? '';
            $userType = 'M'; // Member by default
            
            // Insert user
            $userSql = "INSERT INTO users (id, first_name, middle_name, surname, email, mobile_no, user_type) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $this->db->executeQuery($userSql, [
                $userId, 
                $firstName, 
                $middleName, 
                $surname, 
                $email, 
                $mobileNo, 
                $userType
            ]);
            
            // Generate UUID for membership
            $membershipId = $this->generateUUID();
            
            // Prepare membership data
            $gender = strtoupper($row['GENDER'] ?? 'MALE');
            $dateOfBirth = !empty($row['DATE_OF_BIRTH']) ? date('Y-m-d', strtotime($row['DATE_OF_BIRTH'])) : null;
            $membershipType = strtoupper($row['MEMBERSHIP_TYPE'] ?? 'ORDINARY');
            $math = strtoupper($row['MATH'] ?? null);
            $maritalStatus = strtoupper($row['MARITAL_STATUS'] ?? null);
            $occupation = $row['OCCUPATION'] ?? null;
            $qualification = $row['QUALIFICATION'] ?? null;
            $gotra = $row['GOTRA'] ?? null;
            $kuladevata = $row['KULADEVATA'] ?? null;
            $nativePlace = $row['NATIVE_PLACE'] ?? null;
            $aadharNumber = $row['AADHAR'] ?? null;
            $panNumber = $row['PAN'] ?? null;
            
            // Insert membership
            $membershipSql = "INSERT INTO memberships 
                              (id, user_id, gender, date_of_birth, membership_type, math, 
                               marital_status, occupation, qualification, gotra, kuladevata, 
                               native_place, aadhar_number, pan_number) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";            
            
            $this->db->executeQuery($membershipSql, [
                $membershipId,
                $userId,
                $gender,
                $dateOfBirth,
                $membershipType,
                $math,
                $maritalStatus,
                $occupation,
                $qualification,
                $gotra,
                $kuladevata,
                $nativePlace,
                $aadharNumber,
                $panNumber
            ]);
        }
    }
    
    private function updateAddresses($filePath) {
        if (!file_exists($filePath)) {
            throw new \Exception("Address data file not found: {$filePath}");
        }
        
        $addressData = $this->csvParser->parse($filePath);
        
        if (empty($addressData)) {
            return; // No addresses to update
        }
        
        foreach ($addressData as $row) {
            // Find user by email, name, or other identifiers
            $identifiers = [];
            $params = [];
            
            if (!empty($row['EMAIL'])) {
                $identifiers[] = "email = ?";
                $params[] = $row['EMAIL'];
            } else {
                if (!empty($row['NAME']) && !empty($row['SURNAME'])) {
                    $identifiers[] = "(first_name = ? AND surname = ?)";
                    $params[] = $row['NAME'];
                    $params[] = $row['SURNAME'];
                }
                
                if (!empty($row['MOBILE'])) {
                    $identifiers[] = "mobile_no = ?";
                    $params[] = $row['MOBILE'];
                }
            }
            
            if (empty($identifiers)) {
                continue; // No identifiers to match
            }
            
            // Find the user
            $userSql = "SELECT id FROM users WHERE " . implode(" OR ", $identifiers) . " LIMIT 1";
            $stmt = $this->db->executeQuery($userSql, $params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                continue; // User not found
            }
            
            // Update the address in membership
            $postalAddress = $row['ADDRESS'] ?? null;
            $pinCode = $row['PIN_CODE'] ?? null;
            
            // Update membership record
            $updateSql = "UPDATE memberships SET postal_address = ?, pin_code = ? WHERE user_id = ?";
            $this->db->executeQuery($updateSql, [$postalAddress, $pinCode, $user['id']]);
        }
    }
    
    private function importAdditionalData($filePath) {
        if (!file_exists($filePath)) {
            return; // Additional data is optional
        }
        
        $consolidatedData = $this->csvParser->parse($filePath);
        
        if (empty($consolidatedData)) {
            return; // No additional data to import
        }
        
        foreach ($consolidatedData as $row) {
            // Find user by email, name, or other identifiers
            $identifiers = [];
            $params = [];
            
            if (!empty($row['EMAIL'])) {
                $identifiers[] = "email = ?";
                $params[] = $row['EMAIL'];
            } else {
                if (!empty($row['NAME']) && !empty($row['SURNAME'])) {
                    $identifiers[] = "(first_name = ? AND surname = ?)";
                    $params[] = $row['NAME'];
                    $params[] = $row['SURNAME'];
                }
                
                if (!empty($row['MOBILE'])) {
                    $identifiers[] = "mobile_no = ?";
                    $params[] = $row['MOBILE'];
                }
            }
            
            if (empty($identifiers)) {
                continue; // No identifiers to match
            }
            
            // Find the user
            $userSql = "SELECT id FROM users WHERE " . implode(" OR ", $identifiers) . " LIMIT 1";
            $stmt = $this->db->executeQuery($userSql, $params);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                continue; // User not found
            }
            
            // Extract additional data
            $additionalData = [];
            $additionalParams = [];
            
            // Collect fields that might be in the consolidated file but not in other files
            if (isset($row['NUMBER_OF_KIDS'])) {
                $additionalData[] = "number_of_kids = ?";
                $additionalParams[] = $row['NUMBER_OF_KIDS'];
            }
            
            if (isset($row['OTHER_GSB_MEMBERSHIPS'])) {
                $additionalData[] = "other_gsb_memberships = ?";
                $additionalParams[] = $row['OTHER_GSB_MEMBERSHIPS'];
            }
            
            if (isset($row['INTRODUCER_NAME'])) {
                $additionalData[] = "introducer_name = ?";
                $additionalParams[] = $row['INTRODUCER_NAME'];
            }
            
            if (!empty($additionalData)) {
                $additionalParams[] = $user['id'];
                $updateSql = "UPDATE memberships SET " . implode(", ", $additionalData) . " WHERE user_id = ?";
                $this->db->executeQuery($updateSql, $additionalParams);
            }
        }
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