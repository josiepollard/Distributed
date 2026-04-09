-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 01:44 PM
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
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` bigint(20) NOT NULL,
  `user_from` bigint(20) NOT NULL,
  `user_to` bigint(20) NOT NULL,
  `message` text NOT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `user_from`, `user_to`, `message`, `date_sent`, `file_path`) VALUES
(1, 1, 2, 'Test', '2026-02-16 11:16:17', NULL),
(2, 1, 2, 'yo', '2026-03-16 09:46:04', NULL),
(3, 1, 2, 'vvvvvvvv', '2026-03-16 09:46:15', NULL),
(4, 1, 2, 'v', '2026-03-16 09:46:19', NULL),
(5, 1, 2, 'v', '2026-03-16 09:46:22', NULL),
(6, 1, 2, 'jhello', '2026-03-16 10:02:22', NULL),
(7, 1, 2, 'v', '2026-03-16 10:23:49', NULL),
(8, 5, 1, 'hey ', '2026-03-29 22:07:46', NULL),
(9, 5, 1, 'www', '2026-03-29 22:09:32', NULL),
(10, 5, 1, 'WlooooooWOoooo', '2026-03-29 22:14:17', NULL),
(11, 5, 2, 'Cal is here ', '2026-04-03 12:50:52', NULL),
(12, 6, 9, 'hello!', '2026-04-07 12:05:14', NULL),
(13, 9, 6, 'hi josie', '2026-04-07 12:05:20', NULL),
(14, 6, 9, 'hiyaaa', '2026-04-07 12:11:05', NULL),
(15, 6, 9, 'hello!', '2026-04-07 12:25:58', NULL),
(16, 9, 6, 'hey!!!!', '2026-04-07 12:26:19', NULL),
(17, 6, 9, '', '2026-04-07 12:27:14', NULL),
(18, 6, 9, '6666', '2026-04-07 12:33:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `password`) VALUES
(1, 'Cal', '$2y$10$HPRlXW73HMLCzIklxdgtr.gd.n3i5g8iwpzEa.rFbQTxEdw1N1IpK'),
(2, 'Cal', '$2y$10$pn.7i9zOiq/OcHvbKDbDcudIQWFIWov0lwhwMfyAS0M7zBPg6Ifee'),
(3, 'Cal', '$2y$10$vhSpu6ipNN3AvcyuixMGPOsGxH8Ozd3O1Mfdq0HsZr8b7CL2J4Azq'),
(4, 'Cal', '$2y$10$vDUngbLQkiVQkXbbr9YdQejZzM/tihJCdvztDPqNH3qulMgwslAN6'),
(5, 'CallumSealy', '$2y$10$zF1Ss6P/mBK8rGxm9qfBO.t/6vHXwl/YWy1Xlcxb09/ROOCIE63eC'),
(6, 'Josie', '$2y$10$EszNFCPqTuyDUJI1Fcnfde/LIqfmgkXBioMCUdVM1iOR5r2eN6bNG'),
(7, 'jo', '$2y$10$OFGQjUFaHmTwPpDX4ombkuj8VoryC4hVlX4.RjVvtvR7ieCG9C.Ey'),
(8, 'jo', '$2y$10$ocE.lHCH0ZWBf4VfvduHj.HtpB2h9gi.yyR1ASzN7DL51MosSMCUe'),
(9, 'test', '$2y$10$jDDO.dOdKFRh.iAAAS.A8uEDPMW8e3XEK/uxVsLXoE9I3wMgk7/L6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
