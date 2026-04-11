-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 07:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `distributed`
--

-- --------------------------------------------------------

--
-- Table structure for table `group_chats`
--

CREATE TABLE `group_chats` (
  `group_id` bigint(20) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_chats`
--

INSERT INTO `group_chats` (`group_id`, `group_name`, `created_by`, `created_at`) VALUES
(1, 'Course Group', 6, '2026-04-09 21:52:12'),
(2, 'Group', 10, '2026-04-11 17:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `group_chat_members`
--

CREATE TABLE `group_chat_members` (
  `group_member_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_chat_members`
--

INSERT INTO `group_chat_members` (`group_member_id`, `group_id`, `user_id`) VALUES
(3, 1, 5),
(1, 1, 6),
(2, 1, 9),
(4, 2, 1),
(5, 2, 9),
(6, 2, 10);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` bigint(20) NOT NULL,
  `user_from` bigint(20) NOT NULL,
  `user_to` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `message` text NOT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `user_from`, `user_to`, `group_id`, `message`, `date_sent`, `file_path`) VALUES
(1, 6, 9, NULL, 'hello!', '2026-04-07 12:05:14', NULL),
(2, 9, 6, NULL, 'hi josie', '2026-04-07 12:05:20', NULL),
(3, 6, 9, NULL, 'here is my file', '2026-04-09 12:27:14', 'uploads/sample_notes.txt'),
(4, 6, NULL, 1, 'Hello everyone', '2026-04-09 12:33:32', NULL),
(5, 9, NULL, 1, 'group chat works now', '2026-04-09 12:35:02', NULL),
(6, 6, 9, NULL, '', '2026-04-09 22:53:12', 'uploads/1775771592_Untitleddesign20.png'),
(7, 6, NULL, 1, 'hi', '2026-04-09 23:20:52', NULL),
(8, 6, 9, NULL, 'hi', '2026-04-09 23:20:57', NULL),
(9, 9, 6, NULL, 'hi', '2026-04-11 15:25:25', NULL),
(10, 9, 6, NULL, 'hi', '2026-04-11 15:47:17', NULL),
(11, 9, 5, NULL, 'hiii', '2026-04-11 15:47:34', NULL),
(12, 10, 9, NULL, 'hiii', '2026-04-11 17:32:39', NULL),
(13, 10, NULL, 2, 'hii', '2026-04-11 17:38:43', NULL),
(14, 10, 9, NULL, 'how are you?', '2026-04-11 17:59:56', NULL),
(15, 9, 10, NULL, 'Hiyaaaa', '2026-04-11 18:04:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `password`, `profile_pic`) VALUES
(1, 'Cal', '$2y$10$HPRlXW73HMLCzIklxdgtr.gd.n3i5g8iwpzEa.rFbQTxEdw1N1IpK', NULL),
(5, 'CallumSealy', '$2y$10$zF1Ss6P/mBK8rGxm9qfBO.t/6vHXwl/YWy1Xlcxb09/ROOCIE63eC', NULL),
(9, 'test', '$2y$10$jDDO.dOdKFRh.iAAAS.A8uEDPMW8e3XEK/uxVsLXoE9I3wMgk7/L6', NULL),
(10, 'JosiePollard', '$2y$10$9URR212ZTdcXJOBPTzB3buOWAvwGAtIS92Dk6.aBldTS7NemL5.8y', 'uploads/1775927307_Untitled design (10).png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `group_chats`
--
ALTER TABLE `group_chats`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `group_chat_members`
--
ALTER TABLE `group_chat_members`
  ADD PRIMARY KEY (`group_member_id`),
  ADD UNIQUE KEY `unique_group_member` (`group_id`,`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `unique_user_name` (`user_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `group_chats`
--
ALTER TABLE `group_chats`
  MODIFY `group_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_chat_members`
--
ALTER TABLE `group_chat_members`
  MODIFY `group_member_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
