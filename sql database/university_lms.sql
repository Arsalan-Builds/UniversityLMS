-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 06:35 PM
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
-- Database: `university_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_by`, `created_at`) VALUES
(1, 'mid exam', 'mid exam is going to be held next week from monday', 1, '2026-10-03 18:22:31'),
(2, 'Event', 'new event is coming soon !', 1, '2026-10-03 19:33:19');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `credit_hours` int(11) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `department_id`, `teacher_id`, `course_code`, `course_name`, `description`, `credit_hours`, `created_at`) VALUES
(5, 6, 3, '098 m', 'biology', '', 3, '2026-10-04 10:17:55'),
(6, 3, 9, '0989', 'literature', '', 3, '2026-10-04 18:08:05'),
(7, 1, 11, 'cs101', 'fop', '', 3, '2026-10-04 18:08:29'),
(8, 2, 11, 'cs1012', 'ict', '', 3, '2026-10-04 18:08:55'),
(9, 2, 4, 'cs102', 'probability', '', 3, '2026-10-04 18:09:21'),
(10, 1, 11, '003c', 'ict', '', 3, '2026-10-04 18:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'softwarwe engineering', 'cs department', '2026-10-03 09:33:56'),
(2, 'Ai', 'cs department', '2026-10-03 09:34:28'),
(3, 'BS English', 'new english dept', '2026-10-03 19:29:47'),
(4, 'IT', '', '2026-10-03 19:30:07'),
(6, 'Nursing', 'new dep available !', '2026-10-04 10:14:55'),
(7, 'Law dept', '', '2026-10-04 18:27:50'),
(8, 'Data science', '', '2026-10-04 18:28:15');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrolled_at`) VALUES
(6, 7, 5, '2026-10-04 10:18:12'),
(7, 23, 5, '2026-10-04 18:05:05'),
(8, 23, 8, '2026-10-04 18:10:57'),
(9, 7, 6, '2026-10-04 18:11:06'),
(11, 7, 7, '2026-10-04 18:16:38'),
(12, 7, 8, '2026-10-04 18:23:56'),
(13, 10, 7, '2026-10-04 18:29:03');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `marks` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `teacher_id` varchar(50) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `address`, `student_id`, `teacher_id`, `semester`, `department_id`, `password`, `role`, `created_at`) VALUES
(1, 'Arsalan', 'arsal@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$QEKsRyZP0l5XsF8v3mzRJuXZef6biA8.5rVvrgdAFHQU9m4umYqHq', 'admin', '2026-10-03 09:04:15'),
(2, 'System Admin', 'admin@university.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC6aT2k2VY6hF5jV9m', 'admin', '2026-10-03 09:18:05'),
(3, 'ahmad ali', 'ahmad@gmail.com', '090897997', '0', NULL, '0075', NULL, 6, '0', 'teacher', '2026-10-03 09:42:32'),
(4, 'ali khan', 'ali@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$G57V2dMfQprikC5ARh8Uoei0MkdkQTikOboiFfZLDDdZsKDqR0C/K', 'teacher', '2026-10-03 09:55:44'),
(7, 'ayesha 66', 'ayesha@gmail.com', '0892887872', 'town 1 city islamabad', '00003', NULL, 1, 6, '$2y$10$tlitt/gRqZESf8QVxYDdRO3r3zZRiY/2kjm8hBbyxQP/2Cx8MQgAG', 'student', '2026-10-03 19:23:12'),
(8, 'sir touqeer', 'touqeer@gmail.com', '118978612876278', '0', NULL, 'a21', NULL, 5, '0', 'teacher', '2026-10-03 19:31:14'),
(9, 'sir bilal', 'bkhan@gmail.com', '03125965752', '0', NULL, 'a1', NULL, 3, '0', 'teacher', '2026-10-03 19:31:57'),
(10, 'rizwan', 'rizwan@gmail.com', '080979868', 'town A', '0005', NULL, 1, 3, '$2y$10$4DChMSF460WYoW4sgfhid.i.sawwpt50TfL1RhjBk1bp7Mx6m8Tp2', 'student', '2026-10-04 09:25:19'),
(11, 'wahab', 'wahab@gmail.com', '09890897', 'new peshawar city', NULL, '009', NULL, 4, '$2y$10$NejeZRki1lSVEPb9IriIs.g5mxMVaQXNSX1ehR2jxAlluo0xPn76u', 'teacher', '2026-10-04 10:14:19'),
(22, 'ADMIN2', 'admin2@gmail.com', '0898797798', 'peshawar', NULL, NULL, NULL, NULL, '1122', 'admin', '2026-10-04 12:22:28'),
(23, 'maryam', 'maryam@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$fAmdx15CO1DiI10DoTiR3OS9NAfO5C1yac.c5YOe.4LDDEAS.7pvm', 'student', '2026-10-04 12:29:17'),
(24, 'khan', '122@gmail.com', '04664664', '0', NULL, '76', NULL, 8, '0', 'teacher', '2026-10-05 11:38:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_id` (`assignment_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
