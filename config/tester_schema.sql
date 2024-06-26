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
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `value` text NOT NULL,
  `question_id` int(11) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finished_exams`
--

CREATE TABLE `finished_exams` (
  `id` int(11) NOT NULL,
  `completed_on` datetime NOT NULL DEFAULT current_timestamp(),
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finished_questions`
--

CREATE TABLE `finished_questions` (
  `question_id` int(11) NOT NULL,
  `marked_answer` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_details`
--

CREATE TABLE `question_details` (
    `id` int(11) NOT NULL,
    `question_id` int(11) NOT NULL,
    `timestamp` datetime NOT NULL,
    `faculty_number` varchar(50),
    `question_number` int(11),
    `purpose` text NOT NULL,
    `type` int(11) NOT NULL,
    `correct_answer` int(11) NOT NULL,
    `difficulty_level` int(11) NOT NULL,
    `feedback_correct` text,
    `feedback_incorrect` text,
    `remarks` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `users` (
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `email` varchar(128) NOT NULL,
  `password` varchar(512) NOT NULL,
  `nickname` varchar(128) NOT NULL,
  `faculty_number` varchar(50) UNIQUE,
  `id` int(11) NOT NULL,
  `can_create_test` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waiting_exams`
--

CREATE TABLE `waiting_exams` (
  `waiting_due` datetime NOT NULL,
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rating` int(11) NOT NULL,
  `difficulty` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL
) ;

DELIMITER //
CREATE PROCEDURE DropConstraintIfExists(IN tableName VARCHAR(64), IN constraintName VARCHAR(64))
BEGIN
    DECLARE CONTINUE HANDLER FOR 1091 BEGIN END;  -- Ignore error if constraint doesn't exist
    SET @s = CONCAT('ALTER TABLE ', tableName, ' DROP FOREIGN KEY ', constraintName);
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END//
DELIMITER ;

CALL DropConstraintIfExists('reviews', 'check_rating');
ALTER TABLE `reviews` ADD CONSTRAINT `check_rating` CHECK (`rating` >= -5 AND `rating` <= 5);

CALL DropConstraintIfExists('reviews', 'check_difficulty');
ALTER TABLE `reviews` ADD CONSTRAINT `check_difficulty` CHECK (`difficulty` >= -5 AND `difficulty` <= 5);


--
-- Indexes for dumped tables
--

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answers_question_id` (`question_id`);

-- Indexes for table `finished_exams`
--
ALTER TABLE `finished_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `finished_exams_test_id` (`test_id`),
  ADD KEY `finished_exams_user_id` (`user_id`);

--
-- Indexes for table `finished_questions`
--
ALTER TABLE `finished_questions`
  ADD KEY `finished_questions_question_id` (`question_id`),
  ADD KEY `finished_questions_marked_answer_id` (`marked_answer`),
  ADD KEY `finished_question_exam_id` (`exam_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_test_id` (`test_id`);

--
-- Indexes for table `question_details`
--

ALTER TABLE `question_details`
    ADD PRIMARY KEY (`id`),
    ADD KEY `question_details_question_id` (`question_id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tests_user_id` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD UNIQUE KEY `nickname_uniq` (`nickname`);

--
-- Indexes for table `waiting_exams`
--
ALTER TABLE `waiting_exams`
  ADD KEY `waiting_exams_test_id` (`test_id`),
  ADD KEY `waiting_exams_user_id` (`user_id`);

--
--  Indexes for table `reviews`
--
ALTER TABLE `reviews`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unique_review` (`user_id`, `question_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `finished_exams`
--
ALTER TABLE `finished_exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `question_details`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `FK_answers_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

--
-- Constraints for table `finished_exams`
--
ALTER TABLE `finished_exams`
  ADD CONSTRAINT `FK_finished_exams_tests` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_finished_exams_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `finished_questions`
--
ALTER TABLE `finished_questions`
  ADD CONSTRAINT `FK_finished_questions_answers` FOREIGN KEY (`marked_answer`) REFERENCES `answers` (`id`),
  ADD CONSTRAINT `FK_finished_questions_exams` FOREIGN KEY (`exam_id`) REFERENCES `finished_exams` (`id`),
  ADD CONSTRAINT `FK_finished_questions_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `FK_questions_tests` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`);

--
-- Constraints for table `question_details`
--

ALTER TABLE `question_details`
  ADD CONSTRAINT `FK_question_details_question` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE;

--
-- Constraints for table `tests`
--
ALTER TABLE `tests`
  ADD CONSTRAINT `FK_tests_users` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `waiting_exams`
--
ALTER TABLE `waiting_exams`
  ADD CONSTRAINT `FK_waiting_exams_tests` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
  ADD CONSTRAINT `FK_waiting_exams_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
ADD CONSTRAINT `FK_reviews_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_reviews_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

-- Predefined data for users
INSERT INTO `users` (created, email, password, nickname, faculty_number, id, can_create_test, avatar)
VALUES
    (current_timestamp(), 'admin@example.com', 'ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 'admin', '9999', 1, 1, NULL),
    (current_timestamp(), 'user@example.com', 'ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 'user', '9998', 2, 0, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
