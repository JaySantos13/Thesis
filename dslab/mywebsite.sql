-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 05:18 PM
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
(2, 1, 'BSCOME2207L', 'Software Design', '', '53102'),
(3, 2, 'BSCOME2207', 'Fundamentals to Electronics Circuits', '', '53104'),
(4, 2, 'BSCOME2207L', 'Software Design', '', '53102'),
(5, 4, 'BSCOME2207', 'Fundamentals to Electronics Circuits', '', '53104'),
(6, 4, 'BSCOME2207L', 'Software Design', '', '53102');

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
(1, 'Jay Michael', 'Santos', 'Jay', 'jaymichaelsantos.personal@gmail.com', '$2y$10$9VD9HgVQwjAl0OlQeYVNjehOMkN/r2Y2LubHdlyVHiMazG/x4JLfu', '5be87fe268b5a1c9838568ed44b38ced2e79ef1e46f5a882dec3b8d77642927f43664ec34e703a7c8f0166404eed07ffe017', '2025-04-28 13:28:53', NULL, NULL),
(2, 'Dave', 'Beatingo', 'dave', 'dave123@gmail.com', '$2y$10$dWhibYu9yO1VXto7Ax8LVeSILjx1o3Bh2xmOeR2bdY.avrFaJXoCS', NULL, NULL, 'BSCOME', '11-1111-111'),
(3, 'Karl Paolo', 'Cabantugan', 'Karl', 'Cabantugan.kpc@gmail.com', '$2y$10$vGvvz5bWU0qbTfyaa5t/b.OrPNiT/jM2l0Dcd7HOkrhAqZbQWzUze', '7e72a45d6844db6872cf7db4b4e3bdc6a3c1f25ad5352925bc250965f771cabe84f2c98c832bc76bd05098a2eadddc0a057d', '2025-04-22 20:06:22', NULL, NULL),
(4, 'Ray ', 'Reynaldo', 'Ray', 'ray123@gmail.com', '$2y$10$jcjVjL2L4LJVSOzyz4AAp.qmk7LHyB.NZwuee5xc6s/1.Lkpxlqay', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
