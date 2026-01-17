-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 17, 2026 at 11:03 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teacher_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
CREATE TABLE IF NOT EXISTS `activities` (
  `activity_id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `quarter` tinyint NOT NULL,
  `component_type` enum('ww','pt','qa') NOT NULL,
  `item_number` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `activity_type` enum('quiz','file') NOT NULL DEFAULT 'file',
  `max_score` decimal(5,2) NOT NULL DEFAULT '100.00',
  `due_date` datetime DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`activity_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `component_type` (`component_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `assignment_id`, `quarter`, `component_type`, `item_number`, `title`, `description`, `activity_type`, `max_score`, `due_date`, `is_published`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 41, 1, 'ww', 1, 'quiz A', 'dajkd', 'file', 5.00, '2026-02-20 23:59:00', 1, NULL, '2026-01-18 07:02:06', '2026-01-18 07:02:06'),
(2, 41, 1, 'pt', 1, 'QUIZ B', 'DAJDSKA', 'quiz', 8.00, '2026-01-20 23:49:00', 1, NULL, '2026-01-18 07:03:13', '2026-01-18 07:03:13');

-- --------------------------------------------------------

--
-- Table structure for table `activity_submissions`
--

DROP TABLE IF EXISTS `activity_submissions`;
CREATE TABLE IF NOT EXISTS `activity_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `activity_id` int NOT NULL,
  `student_id` int NOT NULL,
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `submission_type` enum('quiz','file') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('submitted','graded','late','missing') DEFAULT 'submitted',
  `score` decimal(5,2) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `graded_by` int DEFAULT NULL,
  `feedback` text,
  PRIMARY KEY (`submission_id`),
  UNIQUE KEY `uniq_submission` (`activity_id`,`student_id`),
  KEY `activity_id` (`activity_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `computed_grades`
--

DROP TABLE IF EXISTS `computed_grades`;
CREATE TABLE IF NOT EXISTS `computed_grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject_enrollment_id` int NOT NULL,
  `quarter` tinyint NOT NULL,
  `ww_percentage` decimal(5,2) DEFAULT NULL,
  `ww_weighted` decimal(5,2) DEFAULT NULL,
  `pt_percentage` decimal(5,2) DEFAULT NULL,
  `pt_weighted` decimal(5,2) DEFAULT NULL,
  `qa_percentage` decimal(5,2) DEFAULT NULL,
  `qa_weighted` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `is_released` tinyint(1) DEFAULT '0',
  `release_type` enum('final','full') DEFAULT NULL,
  `released_at` datetime DEFAULT NULL,
  `computed_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_computed_grade` (`subject_enrollment_id`,`quarter`),
  KEY `subject_enrollment_id` (`subject_enrollment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `computed_grades`
--

INSERT INTO `computed_grades` (`id`, `subject_enrollment_id`, `quarter`, `ww_percentage`, `ww_weighted`, `pt_percentage`, `pt_weighted`, `qa_percentage`, `qa_weighted`, `final_grade`, `is_released`, `release_type`, `released_at`, `computed_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0, NULL, NULL, '2026-01-18 06:54:45');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `section_id` int NOT NULL,
  `school_year_id` int NOT NULL,
  `enrollment_date` date DEFAULT NULL,
  `status` enum('active','dropped','transferred') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_enrollment` (`student_id`,`section_id`,`school_year_id`),
  KEY `student_id` (`student_id`),
  KEY `section_id` (`section_id`),
  KEY `school_year_id` (`school_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `section_id`, `school_year_id`, `enrollment_date`, `status`) VALUES
(1, 1, 4, 1, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
CREATE TABLE IF NOT EXISTS `grades` (
  `grade_id` int NOT NULL AUTO_INCREMENT,
  `subject_enrollment_id` int NOT NULL,
  `component_id` int NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `remarks` text,
  PRIMARY KEY (`grade_id`),
  UNIQUE KEY `uniq_grade` (`subject_enrollment_id`,`component_id`),
  KEY `subject_enrollment_id` (`subject_enrollment_id`),
  KEY `component_id` (`component_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `subject_enrollment_id`, `component_id`, `score`, `remarks`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grading_components`
--

DROP TABLE IF EXISTS `grading_components`;
CREATE TABLE IF NOT EXISTS `grading_components` (
  `component_id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `quarter` tinyint NOT NULL,
  `component_type` enum('ww','pt','qa') NOT NULL,
  `item_number` int NOT NULL,
  `max_score` decimal(5,2) NOT NULL DEFAULT '100.00',
  `description` varchar(255) DEFAULT NULL,
  `date_given` date DEFAULT NULL,
  PRIMARY KEY (`component_id`),
  UNIQUE KEY `uniq_component` (`assignment_id`,`quarter`,`component_type`,`item_number`),
  KEY `assignment_id` (`assignment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `grading_components`
--

INSERT INTO `grading_components` (`component_id`, `assignment_id`, `quarter`, `component_type`, `item_number`, `max_score`, `description`, `date_given`) VALUES
(1, 41, 1, 'ww', 1, 5.00, 'quiz A', '2026-01-18'),
(2, 41, 1, 'pt', 1, 8.00, 'QUIZ B', '2026-01-18');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `activity_id` int NOT NULL,
  `question_number` int NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') NOT NULL,
  `points` decimal(5,2) NOT NULL DEFAULT '1.00',
  `correct_answer` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  KEY `activity_id` (`activity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`question_id`, `activity_id`, `question_number`, `question_text`, `question_type`, `points`, `correct_answer`, `created_at`) VALUES
(1, 2, 1, 'CHOOSE 1', 'multiple_choice', 5.00, '0', '2026-01-18 07:03:13'),
(2, 2, 2, 'choose true', 'true_false', 3.00, 'true', '2026-01-18 07:03:13');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question_options`
--

DROP TABLE IF EXISTS `quiz_question_options`;
CREATE TABLE IF NOT EXISTS `quiz_question_options` (
  `option_id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `option_order` int NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_question_options`
--

INSERT INTO `quiz_question_options` (`option_id`, `question_id`, `option_text`, `option_order`, `is_correct`) VALUES
(1, 1, '1', 1, 1),
(2, 1, '2', 2, 0),
(3, 1, '3', 3, 0),
(4, 1, '4', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

DROP TABLE IF EXISTS `school_years`;
CREATE TABLE IF NOT EXISTS `school_years` (
  `id` int NOT NULL AUTO_INCREMENT,
  `school_year` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_year` (`school_year`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `school_year`, `is_active`) VALUES
(1, '2025-2026', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
CREATE TABLE IF NOT EXISTS `sections` (
  `section_id` int NOT NULL AUTO_INCREMENT,
  `grade_level` varchar(10) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `grade_level` (`grade_level`,`section_name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `grade_level`, `section_name`) VALUES
(4, '8', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lrn` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `sex` enum('M','F') NOT NULL,
  `age` int NOT NULL,
  `section_id` int NOT NULL,
  `status` enum('Enrolled','Transferred','Dropped') DEFAULT 'Enrolled',
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `lrn` (`lrn`),
  KEY `user_id` (`user_id`),
  KEY `section_id` (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `lrn`, `first_name`, `last_name`, `sex`, `age`, `section_id`, `status`) VALUES
(1, 20, '1001', 'testijng', 'testing', 'M', 20, 4, 'Enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `student_quiz_answers`
--

DROP TABLE IF EXISTS `student_quiz_answers`;
CREATE TABLE IF NOT EXISTS `student_quiz_answers` (
  `answer_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `question_id` int NOT NULL,
  `answer_text` text,
  `selected_option_id` int DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`answer_id`),
  UNIQUE KEY `uniq_answer` (`submission_id`,`question_id`),
  KEY `submission_id` (`submission_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_id` int NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(50) NOT NULL,
  `teacher_id` int NOT NULL,
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_code` (`subject_code`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `teacher_id`) VALUES
(4, 'test', 'testing for triggers convertion', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subject_assignments`
--

DROP TABLE IF EXISTS `subject_assignments`;
CREATE TABLE IF NOT EXISTS `subject_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `teacher_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `section_id` int NOT NULL,
  `school_year_id` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `uniq_assignment` (`teacher_id`,`subject_id`,`section_id`,`school_year_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  KEY `section_id` (`section_id`),
  KEY `school_year_id` (`school_year_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subject_assignments`
--

INSERT INTO `subject_assignments` (`assignment_id`, `teacher_id`, `subject_id`, `section_id`, `school_year_id`, `is_active`) VALUES
(41, 1, 4, 4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `subject_enrollments`
--

DROP TABLE IF EXISTS `subject_enrollments`;
CREATE TABLE IF NOT EXISTS `subject_enrollments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `enrollment_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `is_enrolled` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_subject_enrollment` (`enrollment_id`,`subject_id`),
  KEY `enrollment_id` (`enrollment_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subject_enrollments`
--

INSERT INTO `subject_enrollments` (`id`, `enrollment_id`, `subject_id`, `is_enrolled`) VALUES
(1, 1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE IF NOT EXISTS `teachers` (
  `teacher_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`teacher_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `first_name`, `last_name`) VALUES
(1, 2, 'Bryan', 'Napiza');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(150) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','student','admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin@school.edu', 'admin', '$2y$10$WCsQ45/ptZMfY5ABUC7X1uxOnvqzHPUuJl8QZzbWKQ3eyZmd7TYdq', 'admin', 1, '2026-01-16 22:06:40'),
(2, 'teacher@school.edu', 'teacher', '$2y$10$h0vko3G18wtBs3tZsCLvieaxF7Cn0LpV7OsFGNgGPfRaFDC1xyT2q', 'teacher', 1, '2026-01-16 22:06:40'),
(20, 'test@schoo.edu', '1001', '$2y$10$OTAspKoIhr6AVlKiTWMwn.4BAxeao7VX2.8fje8GS6S//sQ/8ci..', 'student', 1, '2026-01-18 06:54:45');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_admin_grades`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_admin_grades`;
CREATE TABLE IF NOT EXISTS `view_admin_grades` (
`student_id` int
,`lrn` varchar(20)
,`student_name` varchar(102)
,`sex` enum('M','F')
,`school_year` varchar(20)
,`section` varchar(63)
,`subject_name` varchar(50)
,`quarter` tinyint
,`written_work_score` decimal(5,2)
,`perf_task_score` decimal(5,2)
,`quarterly_assessment_score` decimal(5,2)
,`final_grade` decimal(5,2)
,`remarks` varchar(8)
,`is_released` tinyint(1)
,`released_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_student_masterlist`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_student_masterlist`;
CREATE TABLE IF NOT EXISTS `view_student_masterlist` (
`student_id` int
,`lrn` varchar(20)
,`student_name` varchar(102)
,`sex` enum('M','F')
,`section_id` int
,`section` varchar(63)
,`subject_id` int
,`subject_name` varchar(50)
,`teacher_id` int
,`teacher_name` varchar(102)
,`enrollment_id` int
,`subject_enrollment_id` int
,`assignment_id` int
,`school_year` varchar(20)
);

-- --------------------------------------------------------

--
-- Structure for view `view_admin_grades`
--
DROP TABLE IF EXISTS `view_admin_grades`;

DROP VIEW IF EXISTS `view_admin_grades`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_admin_grades`  AS SELECT `s`.`student_id` AS `student_id`, `s`.`lrn` AS `lrn`, concat(`s`.`last_name`,', ',`s`.`first_name`) AS `student_name`, `s`.`sex` AS `sex`, `sy`.`school_year` AS `school_year`, concat(`sec`.`grade_level`,' - ',`sec`.`section_name`) AS `section`, `sub`.`subject_name` AS `subject_name`, `cg`.`quarter` AS `quarter`, `cg`.`ww_weighted` AS `written_work_score`, `cg`.`pt_weighted` AS `perf_task_score`, `cg`.`qa_weighted` AS `quarterly_assessment_score`, `cg`.`final_grade` AS `final_grade`, (case when (`cg`.`final_grade` is null) then 'No Grade' when (`cg`.`final_grade` >= 75) then 'Passed' else 'Failed' end) AS `remarks`, `cg`.`is_released` AS `is_released`, `cg`.`released_at` AS `released_at` FROM ((((((`students` `s` join `enrollments` `e` on((`s`.`student_id` = `e`.`student_id`))) join `sections` `sec` on((`e`.`section_id` = `sec`.`section_id`))) join `school_years` `sy` on((`e`.`school_year_id` = `sy`.`id`))) join `subject_enrollments` `se` on((`e`.`id` = `se`.`enrollment_id`))) join `subjects` `sub` on((`se`.`subject_id` = `sub`.`subject_id`))) left join `computed_grades` `cg` on((`se`.`id` = `cg`.`subject_enrollment_id`))) WHERE (`e`.`status` = 'active') ORDER BY `sy`.`id` DESC, `sec`.`grade_level` ASC, `sec`.`section_name` ASC, `sub`.`subject_name` ASC, `s`.`last_name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_student_masterlist`
--
DROP TABLE IF EXISTS `view_student_masterlist`;

DROP VIEW IF EXISTS `view_student_masterlist`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_student_masterlist`  AS SELECT `s`.`student_id` AS `student_id`, `s`.`lrn` AS `lrn`, concat(`s`.`last_name`,', ',`s`.`first_name`) AS `student_name`, `s`.`sex` AS `sex`, `sec`.`section_id` AS `section_id`, concat(`sec`.`grade_level`,' - ',`sec`.`section_name`) AS `section`, `sub`.`subject_id` AS `subject_id`, `sub`.`subject_name` AS `subject_name`, `t`.`teacher_id` AS `teacher_id`, concat(`t`.`last_name`,', ',`t`.`first_name`) AS `teacher_name`, `e`.`id` AS `enrollment_id`, `se`.`id` AS `subject_enrollment_id`, `sa`.`assignment_id` AS `assignment_id`, `sy`.`school_year` AS `school_year` FROM (((((((`students` `s` join `enrollments` `e` on((`s`.`student_id` = `e`.`student_id`))) join `sections` `sec` on((`e`.`section_id` = `sec`.`section_id`))) join `school_years` `sy` on((`e`.`school_year_id` = `sy`.`id`))) join `subject_enrollments` `se` on((`e`.`id` = `se`.`enrollment_id`))) join `subjects` `sub` on((`se`.`subject_id` = `sub`.`subject_id`))) left join `subject_assignments` `sa` on(((`sa`.`subject_id` = `sub`.`subject_id`) and (`sa`.`section_id` = `sec`.`section_id`) and (`sa`.`school_year_id` = `sy`.`id`)))) left join `teachers` `t` on((`sa`.`teacher_id` = `t`.`teacher_id`))) WHERE ((`e`.`status` = 'active') AND (`se`.`is_enrolled` = 1)) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `subject_assignments` (`assignment_id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_submissions`
--
ALTER TABLE `activity_submissions`
  ADD CONSTRAINT `activity_submissions_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `computed_grades`
--
ALTER TABLE `computed_grades`
  ADD CONSTRAINT `computed_grades_ibfk_1` FOREIGN KEY (`subject_enrollment_id`) REFERENCES `subject_enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`subject_enrollment_id`) REFERENCES `subject_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `grading_components` (`component_id`) ON DELETE CASCADE;

--
-- Constraints for table `grading_components`
--
ALTER TABLE `grading_components`
  ADD CONSTRAINT `grading_components_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `subject_assignments` (`assignment_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_question_options`
--
ALTER TABLE `quiz_question_options`
  ADD CONSTRAINT `quiz_question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_quiz_answers`
--
ALTER TABLE `student_quiz_answers`
  ADD CONSTRAINT `student_quiz_answers_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `activity_submissions` (`submission_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_quiz_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_enrollments`
--
ALTER TABLE `subject_enrollments`
  ADD CONSTRAINT `subject_enrollments_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
