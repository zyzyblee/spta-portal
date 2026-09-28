-- =====================================================================
-- SPTA Payment Monitoring System
-- Database schema and starter data
-- Sta. Catalina National High School
-- =====================================================================
-- HOW TO USE:
--   1. Open phpMyAdmin (http://localhost/phpmyadmin)
--   2. Click "Import" and choose this file, OR
--   3. Click "New" to create a database named exactly as below, then
--      open the "SQL" tab and paste/run this whole file.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS spta_payment_monitoring
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE spta_payment_monitoring;

-- ---------------------------------------------------------------------
-- Table: admin_users
-- Stores administrator accounts. Passwords are always stored hashed
-- (see includes/functions.php / password_hash()) -- never in plain text.
-- ---------------------------------------------------------------------
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: strands
-- The three Senior High School strands. Not hard-coded in PHP -- the
-- admin can rename or add to these through the admin panel.
-- ---------------------------------------------------------------------
CREATE TABLE strands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'book',
    color_theme VARCHAR(20) NOT NULL DEFAULT 'navy',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: sections
-- Sections belong to a strand. Fully editable by the admin so section
-- names are never hard-coded in the PHP files.
-- ---------------------------------------------------------------------
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strand_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sections_strand FOREIGN KEY (strand_id)
        REFERENCES strands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: students
-- ---------------------------------------------------------------------
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    strand_id INT NOT NULL,
    section_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_strand FOREIGN KEY (strand_id)
        REFERENCES strands(id) ON DELETE CASCADE,
    CONSTRAINT fk_students_section FOREIGN KEY (section_id)
        REFERENCES sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: payment_requirements
-- The fee items every student is monitored against. The admin can add
-- more of these later (e.g. a new fee type) without any code changes --
-- every student automatically picks up the new requirement as "unpaid".
-- ---------------------------------------------------------------------
CREATE TABLE payment_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: student_payments
-- One row per (student, requirement) once a status/date has been set.
-- If no row exists yet for a pair, the app treats it as "unpaid" --
-- see getStudentPayments() in includes/functions.php.
-- ---------------------------------------------------------------------
CREATE TABLE student_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    requirement_id INT NOT NULL,
    status ENUM('paid','unpaid') NOT NULL DEFAULT 'unpaid',
    date_paid DATE DEFAULT NULL,
    UNIQUE KEY unique_student_requirement (student_id, requirement_id),
    CONSTRAINT fk_payments_student FOREIGN KEY (student_id)
        REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_requirement FOREIGN KEY (requirement_id)
        REFERENCES payment_requirements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Starter data
-- =====================================================================

INSERT INTO strands (code, name, description, icon, color_theme) VALUES
('ABM',   'ABM',   'Accountancy, Business, and Management',        'calculator', 'navy'),
('STEM',  'STEM',  'Science, Technology, Engineering, and Mathematics', 'flask',  'maroon'),
('HUMSS', 'HUMSS', 'Humanities and Social Sciences',                'book',       'navy');

INSERT INTO sections (strand_id, name) VALUES
(1, 'ABM Section 1'),
(1, 'ABM Section 2'),
(2, 'STEM Section 1'),
(2, 'STEM Section 2'),
(3, 'HUMSS Section 1'),
(3, 'HUMSS Section 2'),
(3, 'HUMSS Section 3');

INSERT INTO payment_requirements (name, amount, sort_order) VALUES
('School Paper',  90.00, 1),
('PTA',           80.00, 2),
('Athletic Fee',  70.00, 3),
('SSLG',          60.00, 4),
('Academic',      60.00, 5),
('Test Paper',    40.00, 6);

-- NOTE ON THE DEFAULT ADMIN ACCOUNT:
-- We deliberately do NOT insert a hard-coded password hash here.
-- Bcrypt hashes are generated differently by different PHP builds, and
-- an incorrect hand-copied hash would silently lock you out with no
-- clear error. Instead, after importing this file, open setup.php in
-- your browser ONCE (see README/SETUP_GUIDE.md, Step 3b). It calls
-- PHP's own password_hash() on your machine and creates:
--     Username: admin
--     Password: Admin@123
-- setup.php safely refuses to run again once an admin account exists.
