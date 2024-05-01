-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 01, 2024 at 01:16 PM
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
-- Database: `tester`
--

-- --------------------------------------------------------

--
-- Table structure for table `Answer`
--

CREATE TABLE `Answer` (
  `value` text NOT NULL,
  `question_id` int(11) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Comment`
--

CREATE TABLE `Comment` (
  `question_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Finished Exam`
--

CREATE TABLE `Finished Exam` (
  `id` int(11) NOT NULL,
  `completed_on` datetime NOT NULL DEFAULT current_timestamp(),
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Finished Question`
--

CREATE TABLE `Finished Question` (
  `question_id` int(11) NOT NULL,
  `marked_answer` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Question`
--

CREATE TABLE `Question` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Test`
--

CREATE TABLE `Test` (
  `id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `email` varchar(128) NOT NULL,
  `password` varchar(512) NOT NULL,
  `nickname` varchar(128) NOT NULL,
  `id` int(11) NOT NULL,
  `can_create_test` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Waiting Exam`
--

CREATE TABLE `Waiting Exam` (
  `waiting_due` datetime NOT NULL,
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Answer`
--
ALTER TABLE `Answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_question_fk` (`question_id`);

--
-- Indexes for table `Comment`
--
ALTER TABLE `Comment`
  ADD KEY `comment_question_id` (`question_id`),
  ADD KEY `comment_creator_id` (`creator_id`);

--
-- Indexes for table `Finished Exam`
--
ALTER TABLE `Finished Exam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `finished_exam_test_fk` (`test_id`),
  ADD KEY `finished_exam_user_fk` (`user_id`);

--
-- Indexes for table `Finished Question`
--
ALTER TABLE `Finished Question`
  ADD KEY `finished_question_fk` (`question_id`),
  ADD KEY `finished_question_answer_fk` (`marked_answer`),
  ADD KEY `finished_question_exam_fk` (`exam_id`);

--
-- Indexes for table `Question`
--
ALTER TABLE `Question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_test_fk` (`test_id`);

--
-- Indexes for table `Test`
--
ALTER TABLE `Test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_user_fkey` (`created_by`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD UNIQUE KEY `nickname_uniq` (`nickname`);

--
-- Indexes for table `Waiting Exam`
--
ALTER TABLE `Waiting Exam`
  ADD KEY `waiting_exam_test_fk` (`test_id`),
  ADD KEY `waiting_exam_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Answer`
--
ALTER TABLE `Answer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Finished Exam`
--
ALTER TABLE `Finished Exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Question`
--
ALTER TABLE `Question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Test`
--
ALTER TABLE `Test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Answer`
--
ALTER TABLE `Answer`
  ADD CONSTRAINT `answer_question_fk` FOREIGN KEY (`question_id`) REFERENCES `Question` (`id`);

--
-- Constraints for table `Comment`
--
ALTER TABLE `Comment`
  ADD CONSTRAINT `comment_creator_id` FOREIGN KEY (`creator_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comment_question_id` FOREIGN KEY (`question_id`) REFERENCES `Question` (`id`);

--
-- Constraints for table `Finished Exam`
--
ALTER TABLE `Finished Exam`
  ADD CONSTRAINT `finished_exam_test_fk` FOREIGN KEY (`test_id`) REFERENCES `Test` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `finished_exam_user_fk` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Finished Question`
--
ALTER TABLE `Finished Question`
  ADD CONSTRAINT `finished_question_answer_fk` FOREIGN KEY (`marked_answer`) REFERENCES `Answer` (`id`),
  ADD CONSTRAINT `finished_question_exam_fk` FOREIGN KEY (`exam_id`) REFERENCES `Finished Exam` (`id`),
  ADD CONSTRAINT `finished_question_fk` FOREIGN KEY (`question_id`) REFERENCES `Question` (`id`);

--
-- Constraints for table `Question`
--
ALTER TABLE `Question`
  ADD CONSTRAINT `question_test_fk` FOREIGN KEY (`test_id`) REFERENCES `Test` (`id`);

--
-- Constraints for table `Test`
--
ALTER TABLE `Test`
  ADD CONSTRAINT `test_user_fkey` FOREIGN KEY (`created_by`) REFERENCES `User` (`id`);

--
-- Constraints for table `Waiting Exam`
--
ALTER TABLE `Waiting Exam`
  ADD CONSTRAINT `waiting_exam_test_fk` FOREIGN KEY (`test_id`) REFERENCES `Test` (`id`),
  ADD CONSTRAINT `waiting_exam_user_id` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
