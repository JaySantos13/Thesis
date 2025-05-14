-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 06:12 AM
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
-- Database: `mywebsite`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `created_at`, `last_login`, `is_super_admin`, `status`) VALUES
(1, 'admin', 'admin@dslab.com', '$2y$10$MWhDGzFpFo1qHlbZvBcQ9Ols3/VdbdiunYpX6QJNa8HDERF7MxmCa', 'System Administrator', '2025-04-30 21:26:34', '2025-05-12 12:43:41', 1, 'active'),
(5, 'admin 1', 'admin1@dslab.com', 'admin123', 'System Administrator', '2025-05-01 04:54:10', NULL, 1, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `action_type`, `action_details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'Admin login successful', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:138.0) Gecko/20100101 Firefox/138.0', '2025-05-12 12:03:08'),
(2, 1, 'login', 'Admin login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 05:13:47'),
(3, 1, 'login', 'Admin login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 05:19:13'),
(4, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:45:47'),
(5, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:45:49'),
(6, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:48:18'),
(7, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:49:37'),
(8, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:54:21'),
(9, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:57:13'),
(10, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 12:58:51'),
(11, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:00:47'),
(12, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:04:14'),
(13, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:09:47'),
(14, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:09:48'),
(15, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:11:40'),
(16, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:13:16'),
(17, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:14:50'),
(18, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:16:31'),
(19, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:18:24'),
(20, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:20:13'),
(21, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:23:25'),
(22, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:24:56'),
(23, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:30:25'),
(24, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 13:44:17'),
(25, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 14:11:32'),
(26, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 14:11:38'),
(27, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 14:17:32'),
(28, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 14:32:38'),
(29, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 14:47:56'),
(30, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 14:53:48'),
(31, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 16:35:29'),
(32, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 16:35:33'),
(33, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-01 16:35:36'),
(34, 1, 'login', 'Admin login successful', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-02 09:09:30'),
(35, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-02 09:09:47'),
(36, 1, 'view', 'Viewed reports and logs panel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-02 09:56:11'),
(37, 1, 'login', 'Admin login successful', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:138.0) Gecko/20100101 Firefox/138.0', '2025-05-12 12:43:41');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `can_manage_users` tinyint(1) DEFAULT 0,
  `can_manage_equipment` tinyint(1) DEFAULT 0,
  `can_approve_requests` tinyint(1) DEFAULT 0,
  `can_view_reports` tinyint(1) DEFAULT 0,
  `can_manage_admins` tinyint(1) DEFAULT 0,
  `can_manage_inventory` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `can_manage_users`, `can_manage_equipment`, `can_approve_requests`, `can_view_reports`, `can_manage_admins`, `can_manage_inventory`) VALUES
(1, 1, 1, 1, 1, 1, 1, 1),
(2, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `borrowing_items`
--

CREATE TABLE `borrowing_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('Pending','Borrowed','Returned','Damaged') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrowing_requests`
--

CREATE TABLE `borrowing_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `request_type` enum('Lab Day','Direct Request') NOT NULL,
  `status` enum('Pending','Approved','Rejected','Returned','Cancelled') DEFAULT 'Pending',
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `purpose` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `professor_id`, `name`, `course_code`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Fundamentals to Electronics Circuits', 'EE101', 'Introduction to basic electronic circuit design and analysis', '2025-05-02 07:01:28', '2025-05-02 07:01:28'),
(2, 1, 'Digital Logic Design', 'EE201', 'Study of digital circuits and logic design principles', '2025-05-02 07:01:28', '2025-05-02 07:01:28'),
(3, 1, 'Advanced Circuit Theory', 'EE301', 'Advanced topics in circuit analysis and design', '2025-05-02 07:01:28', '2025-05-02 07:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`id`, `class_id`, `schedule_date`, `start_time`, `end_time`, `room`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-05-03', '09:00:00', '12:00:00', 'Lab Room A', '2025-05-02 07:01:28', '2025-05-02 07:01:28'),
(2, 1, '2025-05-10', '09:00:00', '12:00:00', 'Lab Room A', '2025-05-02 07:01:28', '2025-05-02 07:01:28'),
(3, 2, '2025-05-04', '13:00:00', '16:00:00', 'Lab Room B', '2025-05-02 07:01:28', '2025-05-02 07:01:28'),
(4, 3, '2025-05-05', '10:00:00', '13:00:00', 'Lab Room C', '2025-05-02 07:01:28', '2025-05-02 07:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `class_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `code`, `title`, `section`, `class_no`) VALUES
(1, 1, 'BSCOME2207', 'Fundamentals to Electronics Circuits', '', '53104'),
(2, 1, 'BSCOME2207L', 'Software Design', '', '53102');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity_available` int(11) NOT NULL DEFAULT 1,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('Available','Partially Available','Not Available') DEFAULT 'Available',
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `description`, `quantity_available`, `category`, `status`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Arduino Kit', 'Complete Arduino starter kit with sensors and components', 10, 'Electronics', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(2, 'Raspberry Pi 4', '4GB RAM model with case', 5, 'Computing', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(3, 'Oscilloscope', 'Digital oscilloscope for signal analysis', 3, 'Measurement', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(4, 'Multimeter', 'Digital multimeter for electrical measurements', 8, 'Measurement', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(5, 'Soldering Station', 'Temperature-controlled soldering station', 4, 'Tools', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(6, 'Breadboard Kit', 'Breadboard with jumper wires', 15, 'Electronics', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(7, 'Logic Analyzer', '8-channel logic analyzer', 2, 'Measurement', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(8, '3D Printer', 'FDM 3D printer with heated bed', 1, 'Manufacturing', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(9, 'Power Supply', 'Adjustable DC power supply', 6, 'Power', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03'),
(10, 'Robot Kit', 'Educational robot building kit', 5, 'Robotics', 'Available', NULL, '2025-05-01 06:00:03', '2025-05-01 06:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_borrowing`
--

CREATE TABLE `equipment_borrowing` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `borrow_date` datetime DEFAULT current_timestamp(),
  `expected_return_date` datetime DEFAULT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Borrowed','Returned','Overdue') DEFAULT 'Pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_maintenance`
--

CREATE TABLE `equipment_maintenance` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `maintenance_date` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `status` enum('Scheduled','In Progress','Completed') DEFAULT 'Scheduled',
  `next_maintenance_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `subject`, `message`, `type`, `date`) VALUES
(1, 'Returned AC Meter', 'AC Meter was returned by John Doe.', 'return', '2025-04-19 18:00:00'),
(2, 'Borrowed Bread Board', 'Bread Board borrowed by Jane Smith.', 'borrow', '2025-04-18 15:30:00'),
(3, 'Reminder: Return Item', 'Reminder sent to John Doe for overdue item.', 'reminder', '2025-04-17 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `icon` varchar(32) DEFAULT 'info',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `is_unread` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `icon`, `subject`, `message`, `date`, `is_unread`) VALUES
(1, 1, 'info', 'Rodillo Gole Scheduled Lab Day', '', '2025-03-11 00:00:00', 1),
(2, 1, 'reminder', 'Return Reminder: AC Meter is due on March 20.', 'Admin has set March 20, 2025 for your overdue. Please return it by then to avoid penalties.', '2025-03-11 00:00:00', 1),
(3, 1, 'alert', 'Overdue Alert. Bread Board was due on March 10, 2025. Return ASAP!', '', '2025-03-11 00:00:00', 1),
(4, 1, 'success', 'Return Successful! ITEMS has been marked as returned on March 11, 2025. Thank you!', '', '2025-03-11 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pending_returns`
--

CREATE TABLE `pending_returns` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `course` varchar(150) DEFAULT NULL,
  `request_type` varchar(100) DEFAULT NULL,
  `request_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_returns`
--

INSERT INTO `pending_returns` (`id`, `subject`, `course`, `request_type`, `request_date`) VALUES
(1, 'Diode & Circuits', 'Fundametals to Electronics Circuits', NULL, '2025-03-11'),
(2, NULL, NULL, 'Direct Request', '2025-03-11');

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`id`, `name`, `email`, `password`, `department`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Dr. John Smith', 'john.smith@dslab.edu', '$2y$12$9bJU0cczBSVg/ZF.amTPD.wiFidlxBJP5uaQ7T/bQOvh824VH7Fz.', 'Computer Science', '123-456-7890', '2025-05-01 16:40:40', '2025-05-01 16:40:40'),
(2, 'Dr. Emily Johnson', 'emily.johnson@dslab.edu', '$2y$12$9bJU0cczBSVg/ZF.amTPD.wiFidlxBJP5uaQ7T/bQOvh824VH7Fz.', 'Electrical Engineering', '123-456-7891', '2025-05-01 16:40:40', '2025-05-01 16:40:40'),
(3, 'Dr. Michael Brown', 'michael.brown@dslab.edu', '$2y$12$9bJU0cczBSVg/ZF.amTPD.wiFidlxBJP5uaQ7T/bQOvh824VH7Fz.', 'Mechanical Engineering', '123-456-7892', '2025-05-01 16:40:40', '2025-05-01 16:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `role` enum('Borrower','Member') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `subject`, `course`, `schedule_date`, `start_time`, `end_time`) VALUES
(1, 'Diode & Circuits', 'Fundametals to Electronics Circuits', '2025-03-11', '16:30:00', '19:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `system_notifications`
--

CREATE TABLE `system_notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `target` varchar(50) NOT NULL DEFAULT 'all',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `student_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `password`, `reset_token`, `reset_expires`, `program`, `student_no`) VALUES
(1, 'Jay Michael', 'Santos', 'Jay', 'jaymichaelsantos.personal@gmail.com', '$2y$10$9VD9HgVQwjAl0OlQeYVNjehOMkN/r2Y2LubHdlyVHiMazG/x4JLfu', 'e4a165c384c3331bc231b3c93b0c05d74fbd17b28405546456f4d1286112f9a42f3d48fe4e8f956dd255d17b20f4c58ddfc6', '2025-04-15 20:51:04', NULL, NULL),
(2, 'Dave', 'Beatingo', 'dave', 'dave123@gmail.com', '$2y$10$dWhibYu9yO1VXto7Ax8LVeSILjx1o3Bh2xmOeR2bdY.avrFaJXoCS', NULL, NULL, NULL, NULL),
(3, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `borrowing_items`
--
ALTER TABLE `borrowing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `borrowing_requests`
--
ALTER TABLE `borrowing_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_borrowing`
--
ALTER TABLE `equipment_borrowing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_returns`
--
ALTER TABLE `pending_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pending_returns`
--
ALTER TABLE `pending_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
