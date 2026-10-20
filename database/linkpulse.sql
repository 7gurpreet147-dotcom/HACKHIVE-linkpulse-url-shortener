-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2026 at 02:43 PM
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
-- Database: `linkpulse`
--

-- --------------------------------------------------------

--
-- Table structure for table `clicks`
--

CREATE TABLE `clicks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `link_id` bigint(20) UNSIGNED NOT NULL,
  `clicked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `referrer` text DEFAULT NULL,
  `device_type` varchar(30) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clicks`
--

INSERT INTO `clicks` (`id`, `link_id`, `clicked_at`, `referrer`, `device_type`, `browser`, `operating_system`, `country`) VALUES
(1, 1, '2026-10-20 06:02:02', NULL, NULL, NULL, NULL, NULL),
(2, 1, '2026-10-20 06:03:29', NULL, NULL, NULL, NULL, NULL),
(3, 2, '2026-10-20 06:22:07', NULL, NULL, NULL, NULL, NULL),
(4, 2, '2026-10-20 07:26:02', NULL, NULL, NULL, NULL, NULL),
(5, 2, '2026-10-20 07:30:15', NULL, NULL, NULL, NULL, NULL),
(6, 2, '2026-10-20 07:30:37', NULL, NULL, NULL, NULL, NULL),
(7, 2, '2026-10-20 07:32:45', NULL, NULL, NULL, NULL, NULL),
(8, 2, '2026-10-20 07:33:13', NULL, NULL, NULL, NULL, NULL),
(9, 2, '2026-10-20 07:34:33', NULL, NULL, NULL, NULL, NULL),
(10, 2, '2026-10-20 07:35:56', NULL, NULL, NULL, NULL, NULL),
(11, 2, '2026-10-20 07:45:29', NULL, NULL, NULL, NULL, NULL),
(12, 2, '2026-10-20 07:45:39', NULL, NULL, NULL, NULL, NULL),
(13, 2, '2026-10-20 07:47:18', NULL, NULL, NULL, NULL, NULL),
(14, 2, '2026-10-20 07:48:47', NULL, NULL, NULL, NULL, NULL),
(15, 2, '2026-10-20 07:50:48', NULL, NULL, NULL, NULL, NULL),
(16, 2, '2026-10-20 07:51:56', NULL, NULL, NULL, NULL, NULL),
(17, 2, '2026-10-20 09:49:14', NULL, NULL, NULL, NULL, NULL),
(18, 3, '2026-10-20 09:53:50', NULL, NULL, NULL, NULL, NULL),
(19, 3, '2026-10-20 09:54:05', NULL, NULL, NULL, NULL, NULL),
(20, 3, '2026-10-20 09:55:16', NULL, NULL, NULL, NULL, NULL),
(21, 6, '2026-10-20 10:45:17', NULL, NULL, NULL, NULL, NULL),
(22, 6, '2026-10-20 11:00:05', NULL, NULL, NULL, NULL, NULL),
(23, 6, '2026-10-20 11:00:17', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_url` text NOT NULL,
  `short_code` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `max_clicks` bigint(20) UNSIGNED DEFAULT NULL,
  `click_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `anonymous_token_hash` char(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`id`, `user_id`, `original_url`, `short_code`, `title`, `password_hash`, `expires_at`, `max_clicks`, `click_count`, `status`, `created_at`, `updated_at`, `anonymous_token_hash`) VALUES
(1, NULL, 'https://www.example.com/products', '90602853', NULL, NULL, NULL, NULL, 2, 'active', '2026-10-20 05:19:32', '2026-10-20 07:25:50', NULL),
(2, NULL, 'https://www.wikipedia.org/', '40d334c3', NULL, NULL, NULL, NULL, 15, 'active', '2026-10-20 06:21:46', '2026-10-20 09:49:14', NULL),
(3, 2, 'https://www.youtube.com/', '6a5d4462', NULL, NULL, NULL, NULL, 3, 'active', '2026-10-20 09:50:04', '2026-10-20 09:55:16', NULL),
(4, 2, 'https://www.youtube.com/', 'a85755a2', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 10:04:15', '2026-10-20 10:04:15', NULL),
(5, NULL, 'https://www.facebook.com/', '79363784', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 10:05:40', '2026-10-20 10:05:40', NULL),
(6, NULL, 'https://x.com/', '6353bb37', NULL, NULL, NULL, NULL, 3, 'active', '2026-10-20 10:24:46', '2026-10-20 11:00:17', '2c879e68272d0e558f422fd1da326145df432df2cb8fa0989911b99236914e9d'),
(7, NULL, 'https://en.wikipedia.org/wiki/Special:Random', '3006fbd7', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 11:14:37', '2026-10-20 11:14:37', 'c0938c4db250c0610fde761506ce3d1652437eb18a3afadd31cc1452668d9852'),
(8, NULL, 'https://news.ycombinator.com/', '7fee1b65', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 11:14:48', '2026-10-20 11:14:48', 'c0938c4db250c0610fde761506ce3d1652437eb18a3afadd31cc1452668d9852'),
(9, NULL, 'https://github.com/torvalds/linux', 'fde00afd', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 11:14:53', '2026-10-20 11:14:53', 'c0938c4db250c0610fde761506ce3d1652437eb18a3afadd31cc1452668d9852'),
(10, NULL, 'https://archive.org/details/texts', 'de6a0383', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 11:14:58', '2026-10-20 11:14:58', 'c0938c4db250c0610fde761506ce3d1652437eb18a3afadd31cc1452668d9852'),
(11, NULL, 'https://httpbin.org/get?test=1&source=shortener&format=json', '009b3da3', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 11:15:03', '2026-10-20 11:15:03', 'c0938c4db250c0610fde761506ce3d1652437eb18a3afadd31cc1452668d9852'),
(12, 2, 'https://stackoverflow.com/questions', '1d4ed547', NULL, NULL, NULL, NULL, 0, 'active', '2026-10-20 11:16:54', '2026-10-20 11:16:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `link_creation_rate_limits`
--

CREATE TABLE `link_creation_rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `link_creation_rate_limits`
--

INSERT INTO `link_creation_rate_limits` (`id`, `ip_hash`, `created_at`) VALUES
(1, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:44:37'),
(2, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:44:48'),
(3, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:44:53'),
(4, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:44:58'),
(5, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:45:03'),
(6, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:45:17'),
(7, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 16:46:54'),
(8, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-10-20 17:27:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'linkpulse.test@example.com', '$2y$10$YKVlFEJ2V0Jyo9RZfDgRferSdAq0Qm/bc8.XHWM8UEVryuF6Z5rRG', 'user', '2026-10-20 08:10:01', '2026-10-20 08:10:01'),
(2, 'Gurpreet Singh', '7gurpreet147@gmail.com', '$2y$10$IUrp.qROvAo7ZmqxH.xKneGwK3/gykDjaLY3bb2RvnEpKlm0ZNcBe', 'user', '2026-10-20 08:15:29', '2026-10-20 08:15:29'),
(3, 'Kamal deep Singh', '9690056544k@gmail.com', '$2y$10$oaVMkCMXwaREZMMBOV2DEOkrdpY9.wX82NIyqR73Wx6Ul13WKe4mm', 'user', '2026-10-20 10:06:58', '2026-10-20 10:06:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clicks`
--
ALTER TABLE `clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clicks_link_time` (`link_id`,`clicked_at`),
  ADD KEY `idx_clicks_time` (`clicked_at`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `idx_links_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_links_status_expiry` (`status`,`expires_at`);

--
-- Indexes for table `link_creation_rate_limits`
--
ALTER TABLE `link_creation_rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_created` (`ip_hash`,`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clicks`
--
ALTER TABLE `clicks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `link_creation_rate_limits`
--
ALTER TABLE `link_creation_rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clicks`
--
ALTER TABLE `clicks`
  ADD CONSTRAINT `fk_clicks_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `fk_links_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
