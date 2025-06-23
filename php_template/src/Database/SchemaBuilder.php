<?php
namespace App\Database;

class SchemaBuilder {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    public function createTables() {
        // Start transaction for database setup
        $this->db->beginTransaction();
        
        try {
            // Create users table
            $this->createUsersTable();
            
            // Create memberships table
            $this->createMembershipsTable();
            
            // Create pages table
            $this->createPagesTable();
            
            // Create sevas table
            $this->createSevasTable();
            
            // Create bookings table
            $this->createBookingsTable();
            
            // Create booking_items table
            $this->createBookingItemsTable();
            
            // Commit transaction if all tables are created successfully
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            $this->db->rollBack();
            throw new \Exception("Failed to create database schema: " . $e->getMessage());
        }
    }
    
    private function createUsersTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` CHAR(36) PRIMARY KEY,
            `first_name` VARCHAR(100) NOT NULL,
            `middle_name` VARCHAR(100) DEFAULT NULL,
            `surname` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) DEFAULT NULL UNIQUE,
            `password_hash` VARCHAR(255) DEFAULT NULL,
            `mobile_no` VARCHAR(20) NOT NULL,
            `user_type` ENUM('M', 'NM') NOT NULL DEFAULT 'NM',
            `is_admin` BOOLEAN NOT NULL DEFAULT false,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
    
    private function createMembershipsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `memberships` (
            `id` CHAR(36) PRIMARY KEY,
            `user_id` CHAR(36) NOT NULL UNIQUE,
            `gender` ENUM('MALE', 'FEMALE') NOT NULL,
            `postal_address` TEXT,
            `pin_code` VARCHAR(10),
            `date_of_birth` DATE,
            `occupation` VARCHAR(100),
            `qualification` VARCHAR(100),
            `marital_status` ENUM('MARRIED', 'UNMARRIED'),
            `number_of_kids` INT DEFAULT NULL,
            `gotra` VARCHAR(100),
            `kuladevata` VARCHAR(100),
            `math` ENUM('KASHI', 'GOKARNA', 'KAVALE'),
            `native_place` VARCHAR(100),
            `other_gsb_memberships` TEXT DEFAULT NULL,
            `introducer_name` VARCHAR(100) DEFAULT NULL,
            `membership_type` ENUM('PATRON', 'LIFE', 'ORDINARY') NOT NULL DEFAULT 'PATRON',
            `status` ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
            `application_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `approval_date` TIMESTAMP NULL DEFAULT NULL,
            `aadhar_number` VARCHAR(16) DEFAULT NULL,
            `pan_number` VARCHAR(10) DEFAULT NULL,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
    
    private function createPagesTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `pages` (
            `id` CHAR(36) PRIMARY KEY,
            `title` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `content` TEXT NOT NULL,
            `created_by` CHAR(36) NOT NULL,
            `is_published` BOOLEAN NOT NULL DEFAULT false,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
    
    private function createSevasTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `sevas` (
            `id` CHAR(36) PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `description` TEXT DEFAULT NULL,
            `price` DECIMAL(10, 2) NOT NULL CHECK (`price` >= 0),
            `is_active` BOOLEAN NOT NULL DEFAULT true
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
    
    private function createBookingsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `bookings` (
            `id` CHAR(36) PRIMARY KEY,
            `user_id` CHAR(36) NOT NULL,
            `booking_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `total_amount` DECIMAL(10, 2) NOT NULL,
            `donation_amount` DECIMAL(10, 2) DEFAULT 0,
            `pan_number` VARCHAR(10) DEFAULT NULL,
            `payment_status` ENUM('PENDING', 'COMPLETED', 'FAILED') NOT NULL DEFAULT 'PENDING',
            `receipt_id` VARCHAR(50) UNIQUE,
            `payment_gateway_ref` VARCHAR(100) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
    
    private function createBookingItemsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `booking_items` (
            `id` CHAR(36) PRIMARY KEY,
            `booking_id` CHAR(36) NOT NULL,
            `seva_id` CHAR(36) NOT NULL,
            `quantity` INT NOT NULL DEFAULT 1,
            `price_at_booking` DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`seva_id`) REFERENCES `sevas`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
}