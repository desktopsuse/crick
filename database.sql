-- =====================================================
-- DATABASE: vital_events_db
-- =====================================================

CREATE DATABASE IF NOT EXISTS vital_events_db;
USE vital_events_db;

-- TABLE: users
CREATE TABLE IF NOT EXISTS users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    national_id VARCHAR(50) UNIQUE,
    address TEXT,
    profile_image VARCHAR(255) DEFAULT 'profile-placeholder.jpg',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- TABLE: otp_verification
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100),
    phone VARCHAR(20),
    otp_code VARCHAR(10) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- birth_registrations table
-- =====================================================
CREATE TABLE IF NOT EXISTS birth_registrations (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    child_name VARCHAR(100) NOT NULL,
    child_father_name VARCHAR(100),
    child_grandfather_name VARCHAR(100),
    sn VARCHAR(50),
    birth_month VARCHAR(20),
    birth_day INT,
    birth_year INT,
    birth_date DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    birth_place VARCHAR(100),
    phone VARCHAR(20),
    nationality VARCHAR(50) DEFAULT 'Ethiopian',
    zonal_admin VARCHAR(100),
    region_admin VARCHAR(100),
    woreda VARCHAR(100),
    birth_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    mother_name VARCHAR(100) NOT NULL,
    mother_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    father_name VARCHAR(100) NOT NULL,
    father_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    child_photo VARCHAR(255) DEFAULT NULL,
    registration_number VARCHAR(50) UNIQUE,
    certificate_number VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT(11) DEFAULT NULL,
    registrar_signature TEXT DEFAULT NULL,
    registrar_full_name VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- marriage_registrations table
-- =====================================================
CREATE TABLE IF NOT EXISTS marriage_registrations (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    husband_birth_registration_unique_id VARCHAR(50),
    wife_birth_registration_unique_id VARCHAR(50),
    wife_name VARCHAR(100) NOT NULL,
    wife_father_name VARCHAR(100),
    wife_grandfather_name VARCHAR(100),
    wife_birth_month VARCHAR(20),
    wife_birth_day INT,
    wife_birth_year INT,
    wife_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    husband_name VARCHAR(100) NOT NULL,
    husband_father_name VARCHAR(100),
    husband_grandfather_name VARCHAR(100),
    husband_birth_month VARCHAR(20),
    husband_birth_day INT,
    husband_birth_year INT,
    husband_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    marriage_month VARCHAR(20),
    marriage_day INT,
    marriage_year INT,
    marriage_date DATE NOT NULL,
    marriage_region VARCHAR(100),
    marriage_zone VARCHAR(100),
    marriage_city VARCHAR(100),
    registration_month VARCHAR(20),
    registration_day INT,
    registration_year INT,
    husband_photo VARCHAR(255) DEFAULT NULL,
    wife_photo VARCHAR(255) DEFAULT NULL,
    registration_number VARCHAR(50) UNIQUE,
    certificate_number VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT(11) DEFAULT NULL,
    registrar_signature TEXT DEFAULT NULL,
    registrar_full_name VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- divorce_registrations table
-- =====================================================
CREATE TABLE IF NOT EXISTS divorce_registrations (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    divorcer_birth_registration_unique_id VARCHAR(50),
    divorcee_birth_registration_unique_id VARCHAR(50),
    divorcee_name VARCHAR(100) NOT NULL,
    divorcee_father_name VARCHAR(100),
    divorcee_grandfather_name VARCHAR(100),
    divorcee_birth_month VARCHAR(20),
    divorcee_birth_day INT,
    divorcee_birth_year INT,
    divorcee_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    divorcer_name VARCHAR(100) NOT NULL,
    divorcer_father_name VARCHAR(100),
    divorcer_grandfather_name VARCHAR(100),
    divorcer_birth_month VARCHAR(20),
    divorcer_birth_day INT,
    divorcer_birth_year INT,
    divorcer_nationality VARCHAR(50) DEFAULT 'Ethiopian',
    divorce_month VARCHAR(20),
    divorce_day INT,
    divorce_year INT,
    divorce_date DATE NOT NULL,
    divorce_region VARCHAR(100),
    divorce_zone VARCHAR(100),
    divorce_city VARCHAR(100),
    registration_month VARCHAR(20),
    registration_day INT,
    registration_year INT,
    court_name VARCHAR(100),
    decree_number VARCHAR(50) NOT NULL,
    husband_photo VARCHAR(255) DEFAULT NULL,
    wife_photo VARCHAR(255) DEFAULT NULL,
    registration_number VARCHAR(50) UNIQUE,
    certificate_number VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT(11) DEFAULT NULL,
    registrar_signature TEXT DEFAULT NULL,
    registrar_full_name VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- death_registrations table
-- =====================================================
CREATE TABLE IF NOT EXISTS death_registrations (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    death_form_number VARCHAR(50),
    death_unique_id VARCHAR(50),
    deceased_name VARCHAR(100) NOT NULL,
    deceased_father_name VARCHAR(100),
    deceased_grandfather_name VARCHAR(100),
    title VARCHAR(50),
    sex ENUM('Male', 'Female'),
    dob_month VARCHAR(20),
    dob_day INT,
    dob_year INT,
    date_of_birth DATE,
    nationality VARCHAR(50) DEFAULT 'Ethiopian',
    place_of_death VARCHAR(200),
    death_month VARCHAR(20),
    death_day INT,
    death_year INT,
    date_of_death DATE NOT NULL,
    cause_of_death VARCHAR(100),
    death_place VARCHAR(100),
    death_registration_month VARCHAR(20),
    death_registration_day INT,
    death_registration_year INT,
    death_registration_date DATE,
    certificate_issue_date DATE,
    registrar_name VARCHAR(100),
    registrar_father_name VARCHAR(100),
    registrar_grandfather_name VARCHAR(100),
    deceased_photo VARCHAR(255) DEFAULT NULL,
    certificate_number VARCHAR(50) NOT NULL,
    registration_number VARCHAR(50) UNIQUE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT(11) DEFAULT NULL,
    registrar_signature TEXT DEFAULT NULL,
    registrar_full_name VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- TABLE: contact_messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- UPDATED: admin_signatures table (for permanent admin signature storage)
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_signatures (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    admin_id INT(11) NOT NULL UNIQUE,
    signature_image TEXT NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    title VARCHAR(100) DEFAULT 'Registrar',
    position VARCHAR(100) DEFAULT 'Director General',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- INSERT DEFAULT ADMIN USER (password: admin123)
-- =====================================================
INSERT INTO users (full_name, email, phone, password, national_id, role) 
VALUES ('Administrator', 'admin@vitalevents.gov.et', '+251-11-123-4567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN-001', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- =====================================================
-- INSERT SAMPLE USER (password: user123)
-- =====================================================
INSERT INTO users (full_name, email, phone, password, national_id, role) 
VALUES ('Alemu Bekele', 'alemu@example.com', '+251-911-123456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ETH-1234-56789', 'user')
ON DUPLICATE KEY UPDATE id=id;

-- =====================================================
-- INSERT DEFAULT ADMIN SIGNATURE (placeholder)
-- =====================================================
INSERT INTO admin_signatures (admin_id, signature_image, full_name, title, position) 
VALUES (1, 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'Dr. Tadesse Mekonnen', 'Registrar', 'Director General')
ON DUPLICATE KEY UPDATE id=id;

-- Add position column to admin_signatures table
ALTER TABLE admin_signatures 
ADD COLUMN IF NOT EXISTS position VARCHAR(100) DEFAULT 'Director General';