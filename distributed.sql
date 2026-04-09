-- phpMyAdmin SQL Dump
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `user_name`, `password`) VALUES
(1, 'Cal', '$2y$10$HPRlXW73HMLCzIklxdgtr.gd.n3i5g8iwpzEa.rFbQTxEdw1N1IpK'),
(5, 'CallumSealy', '$2y$10$zF1Ss6P/mBK8rGxm9qfBO.t/6vHXwl/YWy1Xlcxb09/ROOCIE63eC'),
(6, 'Josie', '$2y$10$EszNFCPqTuyDUJI1Fcnfde/LIqfmgkXBioMCUdVM1iOR5r2eN6bNG'),
(9, 'test', '$2y$10$jDDO.dOdKFRh.iAAAS.A8uEDPMW8e3XEK/uxVsLXoE9I3wMgk7/L6');

CREATE TABLE `group_chats` (
  `group_id` bigint(20) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `group_chats` (`group_id`, `group_name`, `created_by`, `created_at`) VALUES
(1, 'Course Group', 6, NOW());

CREATE TABLE `group_chat_members` (
  `group_member_id` bigint(20) NOT NULL,
  `group_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `group_chat_members` (`group_member_id`, `group_id`, `user_id`) VALUES
(1, 1, 6),
(2, 1, 9),
(3, 1, 5);

CREATE TABLE `messages` (
  `message_id` bigint(20) NOT NULL,
  `user_from` bigint(20) NOT NULL,
  `user_to` bigint(20) DEFAULT NULL,
  `group_id` bigint(20) DEFAULT NULL,
  `message` text NOT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `messages` (`message_id`, `user_from`, `user_to`, `group_id`, `message`, `date_sent`, `file_path`) VALUES
(1, 6, 9, NULL, 'hello!', '2026-04-07 12:05:14', NULL),
(2, 9, 6, NULL, 'hi josie', '2026-04-07 12:05:20', NULL),
(3, 6, 9, NULL, 'here is my file', '2026-04-09 12:27:14', 'uploads/sample_notes.txt'),
(4, 6, NULL, 1, 'Hello everyone', '2026-04-09 12:33:32', NULL),
(5, 9, NULL, 1, 'group chat works now', '2026-04-09 12:35:02', NULL);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `unique_user_name` (`user_name`);

ALTER TABLE `group_chats`
  ADD PRIMARY KEY (`group_id`);

ALTER TABLE `group_chat_members`
  ADD PRIMARY KEY (`group_member_id`),
  ADD UNIQUE KEY `unique_group_member` (`group_id`,`user_id`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `group_chats`
  MODIFY `group_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `group_chat_members`
  MODIFY `group_member_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `messages`
  MODIFY `message_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;
