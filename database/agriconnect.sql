-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 27, 2026 at 01:02 PM
-- Server version: 10.11.16-MariaDB-cll-lve
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mubeetec_portfolio_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agri_activity_log`
--

CREATE TABLE `agri_activity_log` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_activity_log`
--

INSERT INTO `agri_activity_log` (`id`, `phone`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, '+2349034394409', 'ussd_verify', 'listing_id=1 source=ussd ok=1', '3.8.44.1', '2026-09-27 09:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `agri_admins`
--

CREATE TABLE `agri_admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_admins`
--

INSERT INTO `agri_admins` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2a$12$/tp3aFohEWf6x3JJW.tZGeyiRZQSLifu.//9qh6ZvcS2iZtlnqj4i', 'Mubarak Bashir', 'admin@agriconnect.com.ng', 'admin', 1, '2026-09-27 11:22:30', '2026-09-27 04:54:00', '2026-09-27 11:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `agri_contacts`
--

CREATE TABLE `agri_contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agri_dealers`
--

CREATE TABLE `agri_dealers` (
  `id` int(11) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `lga` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT 'Kano',
  `products` text DEFAULT NULL COMMENT 'Comma-separated: NPK,Urea,Seeds,Pesticides',
  `address` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `subscription_active` tinyint(1) DEFAULT 1,
  `subscription_expires` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_dealers`
--

INSERT INTO `agri_dealers` (`id`, `business_name`, `phone`, `email`, `lga`, `state`, `products`, `address`, `is_verified`, `subscription_active`, `subscription_expires`, `created_at`, `updated_at`) VALUES
(1, 'Alhaji Farms', '08030000001', NULL, 'Kano Municipal', 'Kano', 'NPK,Urea,Seeds', NULL, 1, 1, NULL, '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(2, 'GreenGrow Ltd', '08060000002', NULL, 'Tarauni', 'Kano', 'Hybrid Seeds,Pesticides', NULL, 1, 1, NULL, '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(3, 'Sahel Agro', '08070000003', NULL, 'Fagge', 'Kano', 'Pesticides,Herbicides', NULL, 0, 1, NULL, '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(4, 'Kaduna Agri-Supply', '08090000004', NULL, 'Kaduna North', 'Kaduna', 'Fertilizer,Seeds', NULL, 1, 1, NULL, '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(5, 'Abuja Farm Hub', '08050000005', NULL, 'Gwagwalada', 'Abuja', 'All Inputs', NULL, 1, 1, NULL, '2026-09-26 08:03:01', '2026-09-26 08:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `agri_farmers`
--

CREATE TABLE `agri_farmers` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `lga` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT 'Kano',
  `crops_interest` varchar(255) DEFAULT NULL COMMENT 'Comma-separated crops',
  `sms_alerts_enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_farmers`
--

INSERT INTO `agri_farmers` (`id`, `phone`, `name`, `lga`, `state`, `crops_interest`, `sms_alerts_enabled`, `created_at`, `updated_at`) VALUES
(1, '+2349034394409', NULL, NULL, 'Kaduna', NULL, 1, '2026-09-27 09:15:04', '2026-09-27 11:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `agri_listings`
--

CREATE TABLE `agri_listings` (
  `id` int(11) NOT NULL,
  `crop` varchar(50) NOT NULL COMMENT 'Maize, Cowpea, Tomato',
  `market` varchar(50) NOT NULL COMMENT 'Kano, Kaduna, Abuja',
  `price` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'NGN',
  `unit` varchar(20) DEFAULT 'ton',
  `verification_count` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0 COMMENT 'Set to 1 when count >= 3',
  `submitted_by` varchar(20) DEFAULT NULL COMMENT 'Phone number of submitter',
  `source` varchar(20) DEFAULT 'admin' COMMENT 'admin, ussd, web',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_listings`
--

INSERT INTO `agri_listings` (`id`, `crop`, `market`, `price`, `currency`, `unit`, `verification_count`, `is_verified`, `submitted_by`, `source`, `created_at`, `updated_at`) VALUES
(1, 'Maize', 'Kano', 45000.00, 'NGN', 'ton', 13, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-27 09:23:23'),
(2, 'Maize', 'Kaduna', 43500.00, 'NGN', 'ton', 8, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(3, 'Maize', 'Abuja', 47000.00, 'NGN', 'ton', 5, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(4, 'Cowpea', 'Kano', 62000.00, 'NGN', 'ton', 15, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(5, 'Cowpea', 'Kaduna', 60000.00, 'NGN', 'ton', 9, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(6, 'Cowpea', 'Abuja', 65000.00, 'NGN', 'ton', 2, 0, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(7, 'Tomato', 'Kano', 28000.00, 'NGN', 'ton', 20, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(8, 'Tomato', 'Kaduna', 26500.00, 'NGN', 'ton', 14, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01'),
(9, 'Tomato', 'Abuja', 30000.00, 'NGN', 'ton', 4, 1, NULL, 'admin', '2026-09-26 08:03:01', '2026-09-26 08:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `agri_settings`
--

CREATE TABLE `agri_settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_settings`
--

INSERT INTO `agri_settings` (`key`, `value`, `updated_at`) VALUES
('alert_email_enabled', '1', '2026-09-27 04:56:16'),
('alert_sms_enabled', '1', '2026-09-27 04:56:16'),
('at_sender_id', 'AgriConnect', '2026-09-27 04:56:16'),
('at_username', 'sandbox', '2026-09-27 04:56:16'),
('at_ussd_code', '*384*0032#', '2026-09-27 04:56:16'),
('site_email', 'admin@agriconnect.com.ng', '2026-09-27 04:56:16'),
('site_name', 'AgriConnect', '2026-09-27 04:56:16'),
('site_phone', '', '2026-09-27 04:56:16');

-- --------------------------------------------------------

--
-- Table structure for table `agri_sms_logs`
--

CREATE TABLE `agri_sms_logs` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `direction` enum('outbound','inbound') DEFAULT 'outbound',
  `status` varchar(50) DEFAULT 'pending',
  `at_message_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_sms_logs`
--

INSERT INTO `agri_sms_logs` (`id`, `phone`, `message`, `direction`, `status`, `at_message_id`, `created_at`) VALUES
(1, '+2349034394409', 'AgriConnect: SMS alerts activated. You will receive daily price updates for your region. Reply STOP to unsubscribe.', 'outbound', 'failed', NULL, '2026-09-27 09:15:29'),
(2, '08090000004', 'AgriConnect Inquiry: Farmer at +2349034394409 is interested in your products. Please call them back.', 'outbound', 'failed', NULL, '2026-09-27 09:18:19'),
(3, '+2349034394409', 'Your inquiry has been sent to Kaduna Agri-Supply. They will contact you shortly.', 'outbound', 'failed', NULL, '2026-09-27 09:18:19'),
(4, '08090000004', 'AgriConnect: Farmer +2349034394409 requested a callback. Please call them back.', 'outbound', 'failed', NULL, '2026-09-27 09:39:53'),
(5, '08030000001', 'AgriConnect Inquiry: Farmer at +2349034394409 is interested in your products. Please call them back.', 'outbound', 'failed', NULL, '2026-09-27 11:22:17'),
(6, '+2349034394409', 'Your inquiry has been sent to Alhaji Farms. They will contact you shortly.', 'outbound', 'failed', NULL, '2026-09-27 11:22:17'),
(7, '+2349034394409', 'AgriConnect: SMS alerts activated. You will receive daily price updates for your region. Reply STOP to unsubscribe.', 'outbound', 'failed', NULL, '2026-09-27 11:24:47'),
(8, '08050000005', 'AgriConnect Inquiry: Farmer at +2349034394409 is interested in your products. Please call them back.', 'outbound', 'failed', NULL, '2026-09-27 11:26:05'),
(9, '+2349034394409', 'Your inquiry has been sent to Abuja Farm Hub. They will contact you shortly.', 'outbound', 'failed', NULL, '2026-09-27 11:26:05'),
(10, '+2349034394409', 'AgriConnect: SMS alerts activated. You will receive daily price updates for your region. Reply STOP to unsubscribe.', 'outbound', 'sent', 'ATXid_38b23941550359c4651efd06f257d6ea', '2026-09-27 11:42:15'),
(11, '08090000004', 'AgriConnect: Farmer +2349034394409 requested a callback. Please call them back.', 'outbound', 'sent', 'None', '2026-09-27 12:28:59'),
(12, '08050000005', 'AgriConnect Inquiry: Farmer at +2349034394409 is interested in your products. Please call them back.', 'outbound', 'sent', 'None', '2026-09-27 12:30:14'),
(13, '+2349034394409', 'Your inquiry has been sent to Abuja Farm Hub. They will contact you shortly.', 'outbound', 'sent', 'ATXid_421ae8c4c8f1e1a8aa04cc2f2838789c', '2026-09-27 12:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `agri_verifications`
--

CREATE TABLE `agri_verifications` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `verified_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_verifications`
--

INSERT INTO `agri_verifications` (`id`, `listing_id`, `phone`, `verified_at`) VALUES
(1, 1, '09034394409', '2026-09-27 09:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `agri_voice_queue`
--

CREATE TABLE `agri_voice_queue` (
  `id` int(11) NOT NULL,
  `farmer_phone` varchar(20) NOT NULL,
  `dealer_phone` varchar(20) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `status` enum('pending','connected','completed','failed') DEFAULT 'pending',
  `at_call_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agri_voice_queue`
--

INSERT INTO `agri_voice_queue` (`id`, `farmer_phone`, `dealer_phone`, `listing_id`, `status`, `at_call_id`, `created_at`, `completed_at`) VALUES
(1, '+2349034394409', '08090000004', NULL, 'failed', NULL, '2026-09-27 09:39:53', NULL),
(2, '+2349034394409', '08090000004', NULL, 'connected', NULL, '2026-09-27 12:28:59', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agri_activity_log`
--
ALTER TABLE `agri_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `agri_admins`
--
ALTER TABLE `agri_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `agri_contacts`
--
ALTER TABLE `agri_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `agri_dealers`
--
ALTER TABLE `agri_dealers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lga` (`lga`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `idx_verified` (`is_verified`);

--
-- Indexes for table `agri_farmers`
--
ALTER TABLE `agri_farmers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_state` (`state`);

--
-- Indexes for table `agri_listings`
--
ALTER TABLE `agri_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_crop_market` (`crop`,`market`),
  ADD KEY `idx_updated` (`updated_at`),
  ADD KEY `idx_verified` (`is_verified`);

--
-- Indexes for table `agri_settings`
--
ALTER TABLE `agri_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `agri_sms_logs`
--
ALTER TABLE `agri_sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `agri_verifications`
--
ALTER TABLE `agri_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_verify` (`listing_id`,`phone`),
  ADD KEY `idx_listing` (`listing_id`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indexes for table `agri_voice_queue`
--
ALTER TABLE `agri_voice_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agri_activity_log`
--
ALTER TABLE `agri_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agri_admins`
--
ALTER TABLE `agri_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agri_contacts`
--
ALTER TABLE `agri_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agri_dealers`
--
ALTER TABLE `agri_dealers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `agri_farmers`
--
ALTER TABLE `agri_farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agri_listings`
--
ALTER TABLE `agri_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `agri_sms_logs`
--
ALTER TABLE `agri_sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `agri_verifications`
--
ALTER TABLE `agri_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agri_voice_queue`
--
ALTER TABLE `agri_voice_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agri_verifications`
--
ALTER TABLE `agri_verifications`
  ADD CONSTRAINT `agri_verifications_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `agri_listings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
