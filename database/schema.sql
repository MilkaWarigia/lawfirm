-- =====================================================
-- LAW FIRM MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Database: lawfirm_db
-- =====================================================
-- 
-- INSTRUCTIONS:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Click "New" to create a new database
-- 3. Name it: lawfirm_db
-- 4. Select "utf8mb4_general_ci" as collation
-- 5. Click "Create"
-- 6. Go to "SQL" tab
-- 7. Copy and paste this entire file
-- 8. Click "Go"
-- =====================================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS lawfirm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE lawfirm_db;

-- =====================================================
-- TABLE 1: CLIENT
-- Stores information about clients
-- =====================================================
CREATE TABLE IF NOT EXISTS CLIENT (
    ClientId INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    PhoneNo VARCHAR(20) NOT NULL,
    Email VARCHAR(100),
    Address TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (PhoneNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 2: CASE
-- Stores information about legal cases
-- =====================================================
CREATE TABLE IF NOT EXISTS `CASE` (
    CaseNo INT AUTO_INCREMENT PRIMARY KEY,
    CaseName VARCHAR(200) NOT NULL,
    CaseType VARCHAR(100) NOT NULL,
    Court VARCHAR(200),
    ClientId INT NOT NULL,
    Status VARCHAR(50) DEFAULT 'Active',
    Description TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ClientId) REFERENCES CLIENT(ClientId) ON DELETE CASCADE,
    INDEX idx_client (ClientId),
    INDEX idx_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 3: EVENT
-- Stores events/appointments related to cases
-- =====================================================
CREATE TABLE IF NOT EXISTS EVENT (
    EventId INT AUTO_INCREMENT PRIMARY KEY,
    EventName VARCHAR(200) NOT NULL,
    EventType VARCHAR(100) NOT NULL,
    Date DATETIME NOT NULL,
    CaseNo INT NOT NULL,
    Description TEXT,
    Location VARCHAR(200),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE CASCADE,
    INDEX idx_date (Date),
    INDEX idx_case (CaseNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 4: CONTACT
-- Stores contact information (phone numbers)
-- =====================================================
CREATE TABLE IF NOT EXISTS CONTACT (
    PhoneNo VARCHAR(20) PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Location VARCHAR(200),
    CaseNo INT NOT NULL,
    Notes TEXT,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE CASCADE,
    INDEX idx_case (CaseNo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 5: BILLING
-- Stores billing and payment information
-- =====================================================
CREATE TABLE IF NOT EXISTS BILLING (
    BillId INT AUTO_INCREMENT PRIMARY KEY,
    ClientId INT NOT NULL,
    CaseNo INT,
    Date DATE NOT NULL,
    Amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    Deposit DECIMAL(10,2) DEFAULT 0.00,
    Installments DECIMAL(10,2) DEFAULT 0.00,
    Status VARCHAR(50) DEFAULT 'Pending',
    Description TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ClientId) REFERENCES CLIENT(ClientId) ON DELETE CASCADE,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE SET NULL,
    INDEX idx_client (ClientId),
    INDEX idx_date (Date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 6: ADMIN
-- Stores administrator information
-- =====================================================
CREATE TABLE IF NOT EXISTS ADMIN (
    AdminId INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    PhoneNo VARCHAR(20) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 7: ADVOCATE
-- Stores advocate/lawyer information
-- =====================================================
CREATE TABLE IF NOT EXISTS ADVOCATE (
    AdvtId INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    PhoneNo VARCHAR(20) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Address TEXT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Status VARCHAR(20) DEFAULT 'Active',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 8: RECEPTIONIST
-- Stores receptionist information
-- =====================================================
CREATE TABLE IF NOT EXISTS RECEPTIONIST (
    RecId INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    PhoneNo VARCHAR(20) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 9: CASE_ASSIGNMENT
-- Links cases to advocates (many-to-many relationship)
-- =====================================================
CREATE TABLE IF NOT EXISTS CASE_ASSIGNMENT (
    AssId INT AUTO_INCREMENT PRIMARY KEY,
    CaseNo INT NOT NULL,
    AdvtId INT NOT NULL,
    AssignedDate DATE NOT NULL,
    Status VARCHAR(50) DEFAULT 'Active',
    Notes TEXT,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE CASCADE,
    FOREIGN KEY (AdvtId) REFERENCES ADVOCATE(AdvtId) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (CaseNo, AdvtId),
    INDEX idx_case (CaseNo),
    INDEX idx_advocate (AdvtId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERT SAMPLE DATA FOR TESTING
-- =====================================================

-- =====================================================
-- IMPORTANT: Default users will be created by setup_users.php
-- Run setup_users.php after importing this schema to create users with correct password hashes
-- 
-- Default credentials:
-- Admin: admin / admin123
-- Advocate: advocate1 / advocate123  
-- Receptionist: receptionist1 / receptionist123
-- =====================================================

-- Note: User inserts are handled by setup_users.php script
-- This ensures proper password hashing using PHP's password_hash() function

-- =====================================================
-- SAMPLE DATA
-- =====================================================
-- Sample data (client, case, assignments, billing, events) will be automatically
-- inserted by setup_users.php after users are created.
-- 
-- To get sample data:
-- 1. Import this schema.sql file
-- 2. Run setup_users.php (http://localhost/lawfirm/database/setup_users.php)
-- 3. Sample data will be inserted automatically
-- =====================================================

-- =====================================================
-- NOTES:
-- Default passwords are hashed using PHP password_hash()
-- All default passwords are: (username)123
-- Example: admin123, advocate1123, receptionist1123
-- 
-- To create new users, use password_hash('yourpassword', PASSWORD_DEFAULT)
-- =====================================================
