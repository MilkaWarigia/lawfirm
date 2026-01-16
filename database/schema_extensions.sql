-- =====================================================
-- LAW FIRM MANAGEMENT SYSTEM - DATABASE EXTENSIONS
-- Additional tables for new features
-- =====================================================
-- 
-- INSTRUCTIONS:
-- 1. Run this after the main schema.sql
-- 2. Go to phpMyAdmin → lawfirm_db → SQL tab
-- 3. Copy and paste this entire file
-- 4. Click "Go"
-- =====================================================

USE lawfirm_db;

-- =====================================================
-- TABLE: DOCUMENT
-- Stores case documents with categories and versions
-- =====================================================
CREATE TABLE IF NOT EXISTS DOCUMENT (
    DocumentId INT AUTO_INCREMENT PRIMARY KEY,
    CaseNo INT NOT NULL,
    DocumentName VARCHAR(255) NOT NULL,
    DocumentCategory VARCHAR(100) NOT NULL,
    FilePath VARCHAR(500) NOT NULL,
    FileSize INT NOT NULL,
    FileType VARCHAR(50) NOT NULL,
    UploadedBy INT NOT NULL,
    UploadedByRole ENUM('admin', 'advocate', 'receptionist', 'client') NOT NULL,
    Version INT DEFAULT 1,
    IsCurrentVersion BOOLEAN DEFAULT TRUE,
    Description TEXT,
    UploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE CASCADE,
    INDEX idx_case (CaseNo),
    INDEX idx_category (DocumentCategory),
    INDEX idx_version (Version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: TASK
-- Task management for cases
-- =====================================================
CREATE TABLE IF NOT EXISTS TASK (
    TaskId INT AUTO_INCREMENT PRIMARY KEY,
    CaseNo INT NOT NULL,
    TaskTitle VARCHAR(200) NOT NULL,
    TaskDescription TEXT,
    AssignedTo INT,
    AssignedToRole ENUM('admin', 'advocate', 'receptionist') NOT NULL,
    Priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    Status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    DueDate DATE,
    CreatedBy INT NOT NULL,
    CreatedByRole ENUM('admin', 'advocate', 'receptionist') NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CompletedAt TIMESTAMP NULL,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE CASCADE,
    INDEX idx_case (CaseNo),
    INDEX idx_assigned (AssignedTo),
    INDEX idx_status (Status),
    INDEX idx_priority (Priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: MESSAGE
-- Chat/messaging system between clients and advocates
-- =====================================================
CREATE TABLE IF NOT EXISTS MESSAGE (
    MessageId INT AUTO_INCREMENT PRIMARY KEY,
    CaseNo INT NOT NULL,
    ClientId INT NOT NULL,
    AdvocateId INT NOT NULL,
    SenderRole ENUM('client', 'advocate') NOT NULL,
    Message TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CaseNo) REFERENCES `CASE`(CaseNo) ON DELETE CASCADE,
    FOREIGN KEY (ClientId) REFERENCES CLIENT(ClientId) ON DELETE CASCADE,
    FOREIGN KEY (AdvocateId) REFERENCES ADVOCATE(AdvtId) ON DELETE CASCADE,
    INDEX idx_case (CaseNo),
    INDEX idx_client (ClientId),
    INDEX idx_advocate (AdvocateId),
    INDEX idx_read (IsRead)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: CLIENT_AUTH
-- Client login credentials for portal access
-- =====================================================
CREATE TABLE IF NOT EXISTS CLIENT_AUTH (
    AuthId INT AUTO_INCREMENT PRIMARY KEY,
    ClientId INT NOT NULL UNIQUE,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE,
    LastLogin TIMESTAMP NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ClientId) REFERENCES CLIENT(ClientId) ON DELETE CASCADE,
    INDEX idx_username (Username),
    INDEX idx_client (ClientId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: USER_PROFILE
-- Extended user profile with profile pictures
-- =====================================================
CREATE TABLE IF NOT EXISTS USER_PROFILE (
    ProfileId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    UserRole ENUM('admin', 'advocate', 'receptionist') NOT NULL,
    ProfilePicture VARCHAR(500) NULL,
    Bio TEXT,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (UserId, UserRole),
    INDEX idx_user (UserId, UserRole)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Add profile picture column to existing tables (if needed)
-- =====================================================
-- Note: We'll use USER_PROFILE table instead of modifying existing tables

-- =====================================================
-- Sample data (optional)
-- =====================================================
-- You can insert sample data here if needed
