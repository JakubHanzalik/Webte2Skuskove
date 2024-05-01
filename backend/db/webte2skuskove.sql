-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 01, 2024 at 04:55 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webte2skuskove`
--

-- --------------------------------------------------------

--
-- Table structure for table `Answers`
--

CREATE TABLE `Answers` (
  `id` int NOT NULL,
  `question_code` varchar(5) NOT NULL,
  `answer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `correct` bit(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Answers`
--

INSERT INTO `Answers` (`id`, `question_code`, `answer`, `correct`) VALUES
(17, 'EKSP6', 'Bratislava', b'0'),
(18, 'EKSP6', 'Salzburg', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `Questions`
--

CREATE TABLE `Questions` (
  `id` int NOT NULL,
  `question_code` varchar(5) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'0',
  `question` varchar(512) NOT NULL,
  `response_type` int NOT NULL,
  `subject_id` int NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Questions`
--

INSERT INTO `Questions` (`id`, `question_code`, `active`, `question`, `response_type`, `subject_id`, `creation_date`, `author_id`) VALUES
(6, 'EKSP6', b'1', 'What is the capital of Slovakiafsdfd?', 1, 5, '2024-04-30 21:40:08', '1');

-- --------------------------------------------------------

--
-- Table structure for table `Subject`
--

CREATE TABLE `Subject` (
  `id` int NOT NULL,
  `text` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Subject`
--

INSERT INTO `Subject` (`id`, `text`, `value`) VALUES
(1, 'TEST', 1),
(5, 'Angličtina', 2),
(6, 'Matematika', 3);

-- --------------------------------------------------------

--
-- Table structure for table `Token`
--

CREATE TABLE `Token` (
  `id` int NOT NULL,
  `token` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `validity` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Token`
--

INSERT INTO `Token` (`id`, `token`, `username`, `validity`) VALUES
(4, 'HTS4N0Fo9kTZ8hPWCWsI4DjOV1ve09', 'jano', '1714589754'),
(5, '7qCtUG4fui7ZPP1Php4LTCv7xCuXMg', 'jano', '1714590048'),
(6, 'tGAW4IyofacJyCsH0Dwb8DDHS2uJdE', 'jano', '2024-05-02 20:23:25'),
(7, 'ihZ0kIXIN5QKJgPvOxwLUhcBfN8Gz2', 'jano', '2024-05-07 20:29:31'),
(8, 'R5Z1jMwZ5WBGEtEkdA29AZZIetJHwO', 'jano', '2024-05-07 21:08:16'),
(9, 'eqrBaLbhgfPa1LRUdtvwur2fDC5lta', 'jano', '2024-05-07 21:09:27'),
(10, 'c08hOoJTJny35UQrqa0zgWYH2rOVr2', 'jano', '2024-05-07 21:11:34'),
(11, '9bwin5twjPLtZYZLsESttBVfcWvyJ1', 'jano', '2024-05-07 21:21:48'),
(12, 'YkZmjW3ZospVrnfYzICkad7DUUavWr', 'jano', '2024-05-07 22:09:02'),
(13, '4WQqFcnCAP7boBJESKtFO0opvTBTaV', 'jano', '2024-05-07 22:09:59'),
(14, '1cSnJKyxCFILY6ZOu6CJsOvbPdqORP', 'jano', '2024-05-07 22:30:16'),
(15, 'zw3p8X3XYDL7nWB6UNJqmzrEXdYRjG', 'jano', '2024-05-08 13:52:20');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'USER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `username`, `password`, `name`, `surname`, `role`) VALUES
(1, 'jano', '$2y$10$19hjLxk0awv8R7hOzBVPfe4Qg6i0VSEyiKDdVmVJs20rbY1ZayYze', 'jano', 'adamik', 'USER');

-- --------------------------------------------------------

--
-- Table structure for table `Vote`
--

CREATE TABLE `Vote` (
  `id` int NOT NULL,
  `answer` varchar(512) NOT NULL,
  `voting_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Voting`
--

CREATE TABLE `Voting` (
  `id` int NOT NULL,
  `question_code` int NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `note` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Answers`
--
ALTER TABLE `Answers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Questions`
--
ALTER TABLE `Questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `question_code` (`question_code`),
  ADD UNIQUE KEY `question_code_2` (`question_code`);

--
-- Indexes for table `Subject`
--
ALTER TABLE `Subject`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Token`
--
ALTER TABLE `Token`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Vote`
--
ALTER TABLE `Vote`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Voting`
--
ALTER TABLE `Voting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `question_code` (`question_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Answers`
--
ALTER TABLE `Answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Questions`
--
ALTER TABLE `Questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Subject`
--
ALTER TABLE `Subject`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Token`
--
ALTER TABLE `Token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Vote`
--
ALTER TABLE `Vote`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Voting`
--
ALTER TABLE `Voting`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
