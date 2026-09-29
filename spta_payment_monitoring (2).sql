-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2026 at 02:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spta_payment_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `created_at`) VALUES
(1, 'admin', '$2y$10$1GYS6TCOcc16QpRBgRsng.Ppbs6jNkwsrRLJet.c0smhRbIIuTcDW', 'System Administrator', '2026-08-13 23:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `payment_requirements`
--

CREATE TABLE `payment_requirements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_requirements`
--

INSERT INTO `payment_requirements` (`id`, `name`, `amount`, `sort_order`) VALUES
(1, 'School Paper', 90.00, 1),
(2, 'PTA', 80.00, 2),
(3, 'Athletic Fee', 70.00, 3),
(4, 'SSLG', 60.00, 4),
(5, 'Academic', 60.00, 5),
(6, 'Test Paper', 40.00, 6);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `strand_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `strand_id`, `name`, `created_at`) VALUES
(1, 1, 'Magnates', '2026-08-13 23:19:18'),
(2, 1, 'Executives', '2026-08-13 23:19:18'),
(3, 2, 'STEM Section 1', '2026-08-13 23:19:18'),
(4, 2, 'STEM Section 2', '2026-08-13 23:19:18'),
(5, 3, 'Visionaries', '2026-08-13 23:19:18'),
(6, 3, 'HUMSS Section 2', '2026-08-13 23:19:18'),
(7, 3, 'HUMSS Section 3', '2026-08-13 23:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `strands`
--

CREATE TABLE `strands` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'book',
  `color_theme` varchar(20) NOT NULL DEFAULT 'navy',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `strands`
--

INSERT INTO `strands` (`id`, `code`, `name`, `description`, `icon`, `color_theme`, `created_at`) VALUES
(1, 'ABM', 'ABM', 'Accountancy, Business, and Management', 'calculator', 'navy', '2026-08-13 23:19:18'),
(2, 'STEM', 'STEM', 'Science, Technology, Engineering, and Mathematics', 'flask', 'maroon', '2026-08-13 23:19:18'),
(3, 'HUMSS', 'HUMSS', 'Humanities and Social Sciences', 'book', 'navy', '2026-08-13 23:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `strand_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `full_name`, `strand_id`, `section_id`, `created_at`) VALUES
(1, '2543536', 'Marieza Krystal S. Arevalo', 1, 1, '2026-08-13 23:32:04'),
(2, '134019', 'Renz Balahadia', 3, 5, '2026-09-17 12:15:16'),
(3, '4261', 'wsjuqj', 1, 2, '2026-09-29 07:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `requirement_id` int(11) NOT NULL,
  `status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `date_paid` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_payments`
--

INSERT INTO `student_payments` (`id`, `student_id`, `requirement_id`, `status`, `date_paid`) VALUES
(1, 1, 1, 'paid', '2026-08-13'),
(2, 2, 1, 'paid', '2026-09-02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `payment_requirements`
--
ALTER TABLE `payment_requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sections_strand` (`strand_id`);

--
-- Indexes for table `strands`
--
ALTER TABLE `strands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `fk_students_strand` (`strand_id`),
  ADD KEY `fk_students_section` (`section_id`);

--
-- Indexes for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_requirement` (`student_id`,`requirement_id`),
  ADD KEY `fk_payments_requirement` (`requirement_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_requirements`
--
ALTER TABLE `payment_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `strands`
--
ALTER TABLE `strands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_payments`
--
ALTER TABLE `student_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_strand` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_students_strand` FOREIGN KEY (`strand_id`) REFERENCES `strands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD CONSTRAINT `fk_payments_requirement` FOREIGN KEY (`requirement_id`) REFERENCES `payment_requirements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payments_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
