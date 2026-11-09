-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 17, 2026 at 05:33 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci */;

--
-- Database: `labflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int UNSIGNED NOT NULL,
  `type` enum('pc_issue','pc_update','stock_in','lab_status','login','user_mgmt','stock_out','ticket','asset','system','user_report') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laboratory_id` int UNSIGNED DEFAULT NULL,
  `pc_id` int UNSIGNED DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `type`, `title`, `description`, `laboratory_id`, `pc_id`, `user_id`, `created_at`) VALUES
(1, 'pc_issue', 'Lab RPL • PC 7', 'VGA Missing', 1, 7, 2, '2026-10-06 13:36:15'),
(2, 'stock_in', 'Stock Opname Received', '15 New CPU Received in inventory', NULL, NULL, 2, '2026-10-06 13:36:15'),
(3, 'lab_status', 'Lab TIK • Maintenance', 'Lab TIK Under Maintenance', 3, NULL, 2, '2026-10-06 13:36:15'),
(4, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-06 13:37:15'),
(5, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-06 13:37:32'),
(6, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-06 14:10:42'),
(7, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-06 14:13:01'),
(8, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-06 14:14:28'),
(9, 'user_mgmt', 'User dibuat: admin_gaza', 'Role Admin', NULL, NULL, 1, '2026-10-06 14:15:25'),
(10, 'login', 'Login', 'Gaza Faiz Moebarrak masuk ke sistem', NULL, NULL, NULL, '2026-10-06 14:15:43'),
(11, 'login', 'Login', 'Gaza Faiz Moebarrak masuk ke sistem', NULL, NULL, NULL, '2026-10-06 14:16:22'),
(12, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-06 14:16:50'),
(13, 'user_mgmt', 'User dihapus: admin_gaza', NULL, NULL, NULL, 1, '2026-10-06 14:16:57'),
(14, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-06 14:33:58'),
(15, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-06 15:07:04'),
(16, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-06 15:07:29'),
(17, 'user_mgmt', 'User dibuat: user', 'Role User (View only)', NULL, NULL, 1, '2026-10-06 15:13:43'),
(18, 'login', 'Login', '- masuk ke sistem', NULL, NULL, NULL, '2026-10-06 15:13:53'),
(19, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-06 15:14:15'),
(20, 'user_mgmt', 'User dihapus: user', NULL, NULL, NULL, 1, '2026-10-06 15:14:33'),
(21, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-08 10:58:01'),
(22, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-08 10:58:46'),
(23, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-10 11:26:00'),
(24, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-10 12:26:11'),
(25, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-13 22:15:42'),
(26, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-14 19:20:04'),
(27, 'login', 'Login', 'Ms. sapdal masuk ke sistem', NULL, NULL, 2, '2026-10-14 19:21:49'),
(28, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-14 19:24:45'),
(29, 'system', 'Backup database', 'Backup manual: labflow_20261014_192453.sql', NULL, NULL, 1, '2026-10-14 19:24:53'),
(30, 'system', 'Backup dihapus', 'labflow_20261014_192453.sql', NULL, NULL, 1, '2026-10-14 19:25:08'),
(31, 'system', 'Backup database', 'Backup manual: labflow_20261014_192510.sql', NULL, NULL, 1, '2026-10-14 19:25:10'),
(32, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-15 15:20:21'),
(33, 'login', 'Login', 'Administrator masuk ke sistem', NULL, NULL, 1, '2026-10-15 17:28:06');

-- --------------------------------------------------------

--
-- Table structure for table `calendar_notes`
--

CREATE TABLE `calendar_notes` (
  `id` int UNSIGNED NOT NULL,
  `note_date` date NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` enum('blue','yellow','red','green') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'blue',
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `component_history`
--

CREATE TABLE `component_history` (
  `id` int UNSIGNED NOT NULL,
  `pc_id` int UNSIGNED NOT NULL,
  `component_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('add','replace','remove') COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_spec` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_spec` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laboratories`
--

CREATE TABLE `laboratories` (
  `id` int UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_file` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `floor` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lab.jpg',
  `total_pcs` smallint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laboratories`
--

INSERT INTO `laboratories` (`id`, `code`, `name`, `folder`, `page_file`, `floor`, `status`, `image`, `total_pcs`, `created_at`, `updated_at`) VALUES
(1, 'rpl', 'Lab RPL', 'Lab_rpl', 'lab_rpl.php', '3rd Floor', 'active', 'lab.jpg', 30, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(2, 'tkj', 'Lab TKJ', 'Lab_tkj', 'lab_tkj.php', '3rd Floor', 'active', 'lab.jpg', 30, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(3, 'tik', 'Lab TIK', 'Lab_tik', 'lab_tik.php', '3rd Floor', 'maintenance', 'lab.jpg', 7, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(4, 'animasi', 'Lab ANM', 'Lab_animasi', 'lab_animasi.php', '1st Floor', 'active', 'lab.jpg', 6, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(5, 'dkv', 'Lab DKV', 'Lab_dkv', 'lab_dkv.php', '2nd Floor', 'active', 'lab.jpg', 30, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(6, 'bc', 'Lab Multimedia', 'Lab_bc', 'lab_bc.php', '2nd Floor', 'active', 'lab.jpg', 30, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(7, '1', 'Lab 1', 'Lab_1', 'lab_1.php', '1st Floor', 'maintenance', 'lab.jpg', 30, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(8, '2', 'Lab 2', 'Lab_2', 'lab_2.php', '1st Floor', 'active', 'lab.jpg', 30, '2026-10-05 23:36:15', '2026-10-05 23:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `lab_reports`
--

CREATE TABLE `lab_reports` (
  `id` int UNSIGNED NOT NULL,
  `pc_id` int UNSIGNED NOT NULL,
  `component_id` int UNSIGNED DEFAULT NULL,
  `component_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','waiting_part','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `technician` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` enum('staff','public') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `reporter_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolution_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reported_by` int UNSIGNED DEFAULT NULL,
  `reported_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lab_reports`
--

INSERT INTO `lab_reports` (`id`, `pc_id`, `component_id`, `component_type`, `description`, `status`, `priority`, `technician`, `source`, `reporter_name`, `resolution_note`, `reported_by`, `reported_at`, `updated_at`, `resolved_at`) VALUES
(1, 7, 27, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(2, 29, 115, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(3, 37, 147, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(4, 59, 235, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(5, 80, 319, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(6, 102, 407, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(7, 110, 439, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(8, 132, 527, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(9, 140, 559, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(10, 162, 647, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(11, 170, 679, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL),
(12, 192, 767, 'gpu', 'VGA Missing', 'open', 'medium', NULL, 'staff', NULL, NULL, 2, '2026-10-06 13:36:15', '2026-10-14 19:19:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pcs`
--

CREATE TABLE `pcs` (
  `id` int UNSIGNED NOT NULL,
  `laboratory_id` int UNSIGNED NOT NULL,
  `pc_number` smallint UNSIGNED NOT NULL,
  `asset_tag` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_until` date DEFAULT NULL,
  `supplier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pcs`
--

INSERT INTO `pcs` (`id`, `laboratory_id`, `pc_number`, `asset_tag`, `purchase_date`, `warranty_until`, `supplier`, `asset_notes`, `created_at`) VALUES
(1, 1, 1, 'INV-RPL-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(2, 1, 2, 'INV-RPL-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(3, 1, 3, 'INV-RPL-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(4, 1, 4, 'INV-RPL-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(5, 1, 5, 'INV-RPL-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(6, 1, 6, 'INV-RPL-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(7, 1, 7, 'INV-RPL-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(8, 1, 8, 'INV-RPL-08', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(9, 1, 9, 'INV-RPL-09', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(10, 1, 10, 'INV-RPL-10', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(11, 1, 11, 'INV-RPL-11', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(12, 1, 12, 'INV-RPL-12', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(13, 1, 13, 'INV-RPL-13', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(14, 1, 14, 'INV-RPL-14', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(15, 1, 15, 'INV-RPL-15', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(16, 1, 16, 'INV-RPL-16', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(17, 1, 17, 'INV-RPL-17', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(18, 1, 18, 'INV-RPL-18', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(19, 1, 19, 'INV-RPL-19', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(20, 1, 20, 'INV-RPL-20', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(21, 1, 21, 'INV-RPL-21', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(22, 1, 22, 'INV-RPL-22', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(23, 1, 23, 'INV-RPL-23', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(24, 1, 24, 'INV-RPL-24', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(25, 1, 25, 'INV-RPL-25', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(26, 1, 26, 'INV-RPL-26', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(27, 1, 27, 'INV-RPL-27', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(28, 1, 28, 'INV-RPL-28', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(29, 1, 29, 'INV-RPL-29', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(30, 1, 30, 'INV-RPL-30', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(31, 2, 1, 'INV-TKJ-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(32, 2, 2, 'INV-TKJ-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(33, 2, 3, 'INV-TKJ-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(34, 2, 4, 'INV-TKJ-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(35, 2, 5, 'INV-TKJ-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(36, 2, 6, 'INV-TKJ-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(37, 2, 7, 'INV-TKJ-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(38, 2, 8, 'INV-TKJ-08', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(39, 2, 9, 'INV-TKJ-09', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(40, 2, 10, 'INV-TKJ-10', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(41, 2, 11, 'INV-TKJ-11', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(42, 2, 12, 'INV-TKJ-12', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(43, 2, 13, 'INV-TKJ-13', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(44, 2, 14, 'INV-TKJ-14', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(45, 2, 15, 'INV-TKJ-15', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(46, 2, 16, 'INV-TKJ-16', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(47, 2, 17, 'INV-TKJ-17', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(48, 2, 18, 'INV-TKJ-18', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(49, 2, 19, 'INV-TKJ-19', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(50, 2, 20, 'INV-TKJ-20', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(51, 2, 21, 'INV-TKJ-21', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(52, 2, 22, 'INV-TKJ-22', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(53, 2, 23, 'INV-TKJ-23', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(54, 2, 24, 'INV-TKJ-24', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(55, 2, 25, 'INV-TKJ-25', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(56, 2, 26, 'INV-TKJ-26', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(57, 2, 27, 'INV-TKJ-27', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(58, 2, 28, 'INV-TKJ-28', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(59, 2, 29, 'INV-TKJ-29', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(60, 2, 30, 'INV-TKJ-30', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(61, 3, 1, 'INV-TIK-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(62, 3, 2, 'INV-TIK-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(63, 3, 3, 'INV-TIK-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(64, 3, 4, 'INV-TIK-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(65, 3, 5, 'INV-TIK-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(66, 3, 6, 'INV-TIK-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(67, 3, 7, 'INV-TIK-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(68, 4, 1, 'INV-ANIMASI-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(69, 4, 2, 'INV-ANIMASI-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(70, 4, 3, 'INV-ANIMASI-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(71, 4, 4, 'INV-ANIMASI-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(72, 4, 5, 'INV-ANIMASI-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(73, 4, 6, 'INV-ANIMASI-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(74, 5, 1, 'INV-DKV-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(75, 5, 2, 'INV-DKV-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(76, 5, 3, 'INV-DKV-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(77, 5, 4, 'INV-DKV-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(78, 5, 5, 'INV-DKV-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(79, 5, 6, 'INV-DKV-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(80, 5, 7, 'INV-DKV-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(81, 5, 8, 'INV-DKV-08', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(82, 5, 9, 'INV-DKV-09', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(83, 5, 10, 'INV-DKV-10', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(84, 5, 11, 'INV-DKV-11', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(85, 5, 12, 'INV-DKV-12', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(86, 5, 13, 'INV-DKV-13', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(87, 5, 14, 'INV-DKV-14', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(88, 5, 15, 'INV-DKV-15', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(89, 5, 16, 'INV-DKV-16', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(90, 5, 17, 'INV-DKV-17', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(91, 5, 18, 'INV-DKV-18', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(92, 5, 19, 'INV-DKV-19', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(93, 5, 20, 'INV-DKV-20', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(94, 5, 21, 'INV-DKV-21', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(95, 5, 22, 'INV-DKV-22', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(96, 5, 23, 'INV-DKV-23', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(97, 5, 24, 'INV-DKV-24', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(98, 5, 25, 'INV-DKV-25', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(99, 5, 26, 'INV-DKV-26', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(100, 5, 27, 'INV-DKV-27', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(101, 5, 28, 'INV-DKV-28', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(102, 5, 29, 'INV-DKV-29', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(103, 5, 30, 'INV-DKV-30', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(104, 6, 1, 'INV-BC-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(105, 6, 2, 'INV-BC-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(106, 6, 3, 'INV-BC-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(107, 6, 4, 'INV-BC-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(108, 6, 5, 'INV-BC-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(109, 6, 6, 'INV-BC-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(110, 6, 7, 'INV-BC-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(111, 6, 8, 'INV-BC-08', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(112, 6, 9, 'INV-BC-09', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(113, 6, 10, 'INV-BC-10', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(114, 6, 11, 'INV-BC-11', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(115, 6, 12, 'INV-BC-12', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(116, 6, 13, 'INV-BC-13', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(117, 6, 14, 'INV-BC-14', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(118, 6, 15, 'INV-BC-15', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(119, 6, 16, 'INV-BC-16', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(120, 6, 17, 'INV-BC-17', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(121, 6, 18, 'INV-BC-18', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(122, 6, 19, 'INV-BC-19', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(123, 6, 20, 'INV-BC-20', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(124, 6, 21, 'INV-BC-21', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(125, 6, 22, 'INV-BC-22', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(126, 6, 23, 'INV-BC-23', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(127, 6, 24, 'INV-BC-24', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(128, 6, 25, 'INV-BC-25', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(129, 6, 26, 'INV-BC-26', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(130, 6, 27, 'INV-BC-27', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(131, 6, 28, 'INV-BC-28', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(132, 6, 29, 'INV-BC-29', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(133, 6, 30, 'INV-BC-30', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(134, 7, 1, 'INV-1-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(135, 7, 2, 'INV-1-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(136, 7, 3, 'INV-1-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(137, 7, 4, 'INV-1-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(138, 7, 5, 'INV-1-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(139, 7, 6, 'INV-1-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(140, 7, 7, 'INV-1-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(141, 7, 8, 'INV-1-08', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(142, 7, 9, 'INV-1-09', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(143, 7, 10, 'INV-1-10', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(144, 7, 11, 'INV-1-11', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(145, 7, 12, 'INV-1-12', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(146, 7, 13, 'INV-1-13', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(147, 7, 14, 'INV-1-14', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(148, 7, 15, 'INV-1-15', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(149, 7, 16, 'INV-1-16', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(150, 7, 17, 'INV-1-17', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(151, 7, 18, 'INV-1-18', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(152, 7, 19, 'INV-1-19', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(153, 7, 20, 'INV-1-20', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(154, 7, 21, 'INV-1-21', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(155, 7, 22, 'INV-1-22', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(156, 7, 23, 'INV-1-23', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(157, 7, 24, 'INV-1-24', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(158, 7, 25, 'INV-1-25', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(159, 7, 26, 'INV-1-26', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(160, 7, 27, 'INV-1-27', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(161, 7, 28, 'INV-1-28', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(162, 7, 29, 'INV-1-29', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(163, 7, 30, 'INV-1-30', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(164, 8, 1, 'INV-2-01', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(165, 8, 2, 'INV-2-02', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(166, 8, 3, 'INV-2-03', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(167, 8, 4, 'INV-2-04', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(168, 8, 5, 'INV-2-05', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(169, 8, 6, 'INV-2-06', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(170, 8, 7, 'INV-2-07', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(171, 8, 8, 'INV-2-08', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(172, 8, 9, 'INV-2-09', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(173, 8, 10, 'INV-2-10', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(174, 8, 11, 'INV-2-11', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(175, 8, 12, 'INV-2-12', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(176, 8, 13, 'INV-2-13', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(177, 8, 14, 'INV-2-14', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(178, 8, 15, 'INV-2-15', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(179, 8, 16, 'INV-2-16', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(180, 8, 17, 'INV-2-17', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(181, 8, 18, 'INV-2-18', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(182, 8, 19, 'INV-2-19', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(183, 8, 20, 'INV-2-20', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(184, 8, 21, 'INV-2-21', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(185, 8, 22, 'INV-2-22', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(186, 8, 23, 'INV-2-23', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(187, 8, 24, 'INV-2-24', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(188, 8, 25, 'INV-2-25', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(189, 8, 26, 'INV-2-26', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(190, 8, 27, 'INV-2-27', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(191, 8, 28, 'INV-2-28', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(192, 8, 29, 'INV-2-29', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15'),
(193, 8, 30, 'INV-2-30', NULL, NULL, NULL, NULL, '2026-10-05 23:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `pc_components`
--

CREATE TABLE `pc_components` (
  `id` int UNSIGNED NOT NULL,
  `pc_id` int UNSIGNED NOT NULL,
  `component_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('online','issue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online',
  `report` text COLLATE utf8mb4_unicode_ci,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pc_components`
--

INSERT INTO `pc_components` (`id`, `pc_id`, `component_type`, `specification`, `status`, `report`, `updated_by`, `updated_at`) VALUES
(1, 1, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(2, 1, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(3, 1, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(4, 1, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(5, 2, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(6, 2, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(7, 2, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(8, 2, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(9, 3, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(10, 3, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(11, 3, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(12, 3, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(13, 4, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(14, 4, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(15, 4, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(16, 4, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(17, 5, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(18, 5, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(19, 5, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(20, 5, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(21, 6, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(22, 6, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(23, 6, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(24, 6, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(25, 7, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(26, 7, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(27, 7, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(28, 7, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(29, 8, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(30, 8, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(31, 8, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(32, 8, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(33, 9, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(34, 9, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(35, 9, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(36, 9, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(37, 10, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(38, 10, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(39, 10, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(40, 10, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(41, 11, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(42, 11, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(43, 11, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(44, 11, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(45, 12, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(46, 12, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(47, 12, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(48, 12, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(49, 13, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(50, 13, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(51, 13, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(52, 13, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(53, 14, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(54, 14, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(55, 14, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(56, 14, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(57, 15, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(58, 15, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(59, 15, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(60, 15, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(61, 16, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(62, 16, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(63, 16, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(64, 16, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(65, 17, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(66, 17, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(67, 17, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(68, 17, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(69, 18, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(70, 18, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(71, 18, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(72, 18, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(73, 19, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(74, 19, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(75, 19, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(76, 19, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(77, 20, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(78, 20, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(79, 20, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(80, 20, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(81, 21, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(82, 21, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(83, 21, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(84, 21, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(85, 22, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(86, 22, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(87, 22, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(88, 22, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(89, 23, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(90, 23, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(91, 23, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(92, 23, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(93, 24, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(94, 24, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(95, 24, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(96, 24, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(97, 25, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(98, 25, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(99, 25, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(100, 25, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(101, 26, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(102, 26, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(103, 26, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(104, 26, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(105, 27, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(106, 27, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(107, 27, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(108, 27, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(109, 28, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(110, 28, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(111, 28, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(112, 28, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(113, 29, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(114, 29, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(115, 29, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(116, 29, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(117, 30, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(118, 30, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(119, 30, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(120, 30, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(121, 31, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(122, 31, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(123, 31, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(124, 31, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(125, 32, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(126, 32, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(127, 32, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(128, 32, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(129, 33, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(130, 33, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(131, 33, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(132, 33, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(133, 34, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(134, 34, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(135, 34, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(136, 34, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(137, 35, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(138, 35, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(139, 35, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(140, 35, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(141, 36, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(142, 36, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(143, 36, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(144, 36, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(145, 37, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(146, 37, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(147, 37, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(148, 37, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(149, 38, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(150, 38, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(151, 38, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(152, 38, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(153, 39, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(154, 39, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(155, 39, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(156, 39, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(157, 40, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(158, 40, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(159, 40, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(160, 40, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(161, 41, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(162, 41, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(163, 41, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(164, 41, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(165, 42, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(166, 42, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(167, 42, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(168, 42, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(169, 43, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(170, 43, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(171, 43, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(172, 43, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(173, 44, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(174, 44, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(175, 44, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(176, 44, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(177, 45, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(178, 45, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(179, 45, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(180, 45, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(181, 46, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(182, 46, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(183, 46, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(184, 46, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(185, 47, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(186, 47, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(187, 47, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(188, 47, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(189, 48, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(190, 48, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(191, 48, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(192, 48, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(193, 49, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(194, 49, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(195, 49, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(196, 49, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(197, 50, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(198, 50, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(199, 50, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(200, 50, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(201, 51, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(202, 51, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(203, 51, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(204, 51, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(205, 52, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(206, 52, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(207, 52, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(208, 52, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(209, 53, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(210, 53, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(211, 53, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(212, 53, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(213, 54, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(214, 54, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(215, 54, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(216, 54, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(217, 55, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(218, 55, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(219, 55, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(220, 55, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(221, 56, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(222, 56, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(223, 56, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(224, 56, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(225, 57, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(226, 57, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(227, 57, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(228, 57, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(229, 58, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(230, 58, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(231, 58, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(232, 58, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(233, 59, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(234, 59, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(235, 59, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(236, 59, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(237, 60, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(238, 60, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(239, 60, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(240, 60, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(241, 61, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(242, 61, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(243, 61, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(244, 61, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(245, 62, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(246, 62, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(247, 62, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(248, 62, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(249, 63, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(250, 63, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(251, 63, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(252, 63, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(253, 64, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(254, 64, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(255, 64, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(256, 64, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(257, 65, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(258, 65, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(259, 65, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(260, 65, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(261, 66, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(262, 66, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(263, 66, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(264, 66, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(265, 67, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(266, 67, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(267, 67, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(268, 67, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(269, 68, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(270, 68, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(271, 68, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(272, 68, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(273, 69, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(274, 69, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(275, 69, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(276, 69, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(277, 70, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(278, 70, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(279, 70, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(280, 70, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(281, 71, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(282, 71, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(283, 71, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(284, 71, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(285, 72, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(286, 72, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(287, 72, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(288, 72, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(289, 73, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(290, 73, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(291, 73, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(292, 73, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(293, 74, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(294, 74, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(295, 74, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(296, 74, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(297, 75, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(298, 75, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(299, 75, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(300, 75, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(301, 76, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(302, 76, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(303, 76, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(304, 76, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(305, 77, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(306, 77, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(307, 77, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(308, 77, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(309, 78, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(310, 78, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(311, 78, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(312, 78, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(313, 79, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(314, 79, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(315, 79, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(316, 79, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(317, 80, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(318, 80, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(319, 80, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(320, 80, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(321, 81, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(322, 81, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(323, 81, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(324, 81, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(325, 82, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(326, 82, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(327, 82, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(328, 82, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(329, 83, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(330, 83, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(331, 83, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(332, 83, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(333, 84, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(334, 84, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(335, 84, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(336, 84, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(337, 85, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(338, 85, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(339, 85, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(340, 85, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(341, 86, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(342, 86, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(343, 86, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(344, 86, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(345, 87, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(346, 87, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(347, 87, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(348, 87, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(349, 88, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(350, 88, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(351, 88, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(352, 88, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(353, 89, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(354, 89, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(355, 89, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(356, 89, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(357, 90, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(358, 90, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(359, 90, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(360, 90, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(361, 91, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(362, 91, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(363, 91, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(364, 91, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(365, 92, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(366, 92, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(367, 92, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(368, 92, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(369, 93, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(370, 93, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(371, 93, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(372, 93, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(373, 94, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(374, 94, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(375, 94, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(376, 94, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(377, 95, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(378, 95, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(379, 95, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(380, 95, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(381, 96, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(382, 96, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(383, 96, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(384, 96, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(385, 97, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(386, 97, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(387, 97, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(388, 97, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(389, 98, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(390, 98, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(391, 98, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(392, 98, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(393, 99, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(394, 99, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(395, 99, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(396, 99, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(397, 100, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(398, 100, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(399, 100, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(400, 100, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(401, 101, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(402, 101, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(403, 101, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(404, 101, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(405, 102, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(406, 102, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(407, 102, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(408, 102, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(409, 103, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(410, 103, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(411, 103, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(412, 103, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(413, 104, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(414, 104, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(415, 104, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(416, 104, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(417, 105, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(418, 105, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(419, 105, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(420, 105, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(421, 106, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(422, 106, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(423, 106, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(424, 106, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(425, 107, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(426, 107, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(427, 107, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(428, 107, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(429, 108, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(430, 108, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(431, 108, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(432, 108, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(433, 109, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(434, 109, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(435, 109, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(436, 109, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(437, 110, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(438, 110, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(439, 110, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(440, 110, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(441, 111, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(442, 111, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(443, 111, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(444, 111, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(445, 112, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(446, 112, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(447, 112, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(448, 112, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(449, 113, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(450, 113, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(451, 113, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(452, 113, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(453, 114, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(454, 114, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(455, 114, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(456, 114, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(457, 115, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(458, 115, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(459, 115, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(460, 115, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(461, 116, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(462, 116, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(463, 116, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(464, 116, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(465, 117, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(466, 117, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(467, 117, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(468, 117, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(469, 118, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(470, 118, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(471, 118, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(472, 118, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(473, 119, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(474, 119, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(475, 119, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(476, 119, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(477, 120, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(478, 120, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(479, 120, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(480, 120, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(481, 121, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(482, 121, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(483, 121, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(484, 121, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(485, 122, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(486, 122, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(487, 122, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(488, 122, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(489, 123, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(490, 123, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(491, 123, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(492, 123, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(493, 124, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(494, 124, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(495, 124, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(496, 124, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(497, 125, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(498, 125, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(499, 125, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(500, 125, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(501, 126, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(502, 126, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(503, 126, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(504, 126, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(505, 127, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(506, 127, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(507, 127, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(508, 127, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(509, 128, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(510, 128, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(511, 128, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(512, 128, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(513, 129, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(514, 129, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(515, 129, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(516, 129, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(517, 130, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(518, 130, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(519, 130, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(520, 130, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(521, 131, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(522, 131, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(523, 131, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15');
INSERT INTO `pc_components` (`id`, `pc_id`, `component_type`, `specification`, `status`, `report`, `updated_by`, `updated_at`) VALUES
(524, 131, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(525, 132, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(526, 132, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(527, 132, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(528, 132, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(529, 133, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(530, 133, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(531, 133, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(532, 133, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(533, 134, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(534, 134, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(535, 134, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(536, 134, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(537, 135, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(538, 135, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(539, 135, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(540, 135, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(541, 136, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(542, 136, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(543, 136, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(544, 136, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(545, 137, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(546, 137, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(547, 137, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(548, 137, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(549, 138, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(550, 138, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(551, 138, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(552, 138, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(553, 139, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(554, 139, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(555, 139, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(556, 139, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(557, 140, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(558, 140, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(559, 140, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(560, 140, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(561, 141, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(562, 141, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(563, 141, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(564, 141, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(565, 142, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(566, 142, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(567, 142, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(568, 142, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(569, 143, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(570, 143, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(571, 143, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(572, 143, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(573, 144, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(574, 144, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(575, 144, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(576, 144, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(577, 145, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(578, 145, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(579, 145, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(580, 145, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(581, 146, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(582, 146, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(583, 146, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(584, 146, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(585, 147, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(586, 147, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(587, 147, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(588, 147, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(589, 148, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(590, 148, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(591, 148, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(592, 148, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(593, 149, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(594, 149, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(595, 149, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(596, 149, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(597, 150, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(598, 150, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(599, 150, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(600, 150, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(601, 151, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(602, 151, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(603, 151, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(604, 151, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(605, 152, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(606, 152, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(607, 152, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(608, 152, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(609, 153, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(610, 153, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(611, 153, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(612, 153, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(613, 154, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(614, 154, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(615, 154, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(616, 154, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(617, 155, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(618, 155, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(619, 155, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(620, 155, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(621, 156, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(622, 156, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(623, 156, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(624, 156, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(625, 157, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(626, 157, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(627, 157, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(628, 157, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(629, 158, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(630, 158, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(631, 158, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(632, 158, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(633, 159, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(634, 159, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(635, 159, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(636, 159, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(637, 160, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(638, 160, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(639, 160, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(640, 160, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(641, 161, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(642, 161, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(643, 161, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(644, 161, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(645, 162, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(646, 162, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(647, 162, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(648, 162, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(649, 163, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(650, 163, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(651, 163, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(652, 163, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(653, 164, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(654, 164, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(655, 164, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(656, 164, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(657, 165, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(658, 165, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(659, 165, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(660, 165, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(661, 166, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(662, 166, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(663, 166, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(664, 166, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(665, 167, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(666, 167, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(667, 167, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(668, 167, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(669, 168, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(670, 168, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(671, 168, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(672, 168, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(673, 169, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(674, 169, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(675, 169, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(676, 169, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(677, 170, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(678, 170, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(679, 170, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(680, 170, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(681, 171, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(682, 171, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(683, 171, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(684, 171, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(685, 172, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(686, 172, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(687, 172, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(688, 172, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(689, 173, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(690, 173, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(691, 173, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(692, 173, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(693, 174, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(694, 174, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(695, 174, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(696, 174, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(697, 175, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(698, 175, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(699, 175, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(700, 175, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(701, 176, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(702, 176, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(703, 176, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(704, 176, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(705, 177, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(706, 177, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(707, 177, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(708, 177, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(709, 178, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(710, 178, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(711, 178, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(712, 178, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(713, 179, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(714, 179, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(715, 179, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(716, 179, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(717, 180, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(718, 180, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(719, 180, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(720, 180, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(721, 181, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(722, 181, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(723, 181, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(724, 181, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(725, 182, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(726, 182, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(727, 182, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(728, 182, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(729, 183, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(730, 183, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(731, 183, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(732, 183, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(733, 184, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(734, 184, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(735, 184, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(736, 184, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(737, 185, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(738, 185, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(739, 185, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(740, 185, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(741, 186, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(742, 186, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(743, 186, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(744, 186, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(745, 187, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(746, 187, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(747, 187, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(748, 187, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(749, 188, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(750, 188, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(751, 188, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(752, 188, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(753, 189, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(754, 189, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(755, 189, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(756, 189, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(757, 190, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(758, 190, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(759, 190, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(760, 190, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(761, 191, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(762, 191, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(763, 191, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(764, 191, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(765, 192, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(766, 192, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(767, 192, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'issue', 'VGA Missing', NULL, '2026-10-05 23:36:15'),
(768, 192, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(769, 193, 'cpu', 'Intel Core i5-12400F, 6 Cores / 12 Threads', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(770, 193, 'ram', '16GB DDR4 3200MHz', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(771, 193, 'gpu', 'NVIDIA RTX 4090 / Integrated', 'online', 'Normal', NULL, '2026-10-05 23:36:15'),
(772, 193, 'storage', '512GB NVMe SSD', 'online', 'Normal', NULL, '2026-10-05 23:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` bigint UNSIGNED NOT NULL,
  `bucket` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `k` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_events`
--

CREATE TABLE `report_events` (
  `id` int UNSIGNED NOT NULL,
  `report_id` int UNSIGNED NOT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `actor_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_events`
--

INSERT INTO `report_events` (`id`, `report_id`, `action`, `detail`, `user_id`, `actor_name`, `created_at`) VALUES
(1, 1, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(2, 2, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(3, 3, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(4, 4, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(5, 5, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(6, 6, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(7, 7, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(8, 8, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(9, 9, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(10, 10, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(11, 11, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15'),
(12, 12, 'created', 'Tiket dibuat: VGA Missing', 2, NULL, '2026-10-06 13:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `stock_categories`
--

CREATE TABLE `stock_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_categories`
--

INSERT INTO `stock_categories` (`id`, `name`) VALUES
(2, 'CPU & Komputer'),
(5, 'Display & Proyektor'),
(3, 'Jaringan'),
(4, 'Penyimpanan'),
(1, 'Periferal & Input Device');

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `min_quantity` int UNSIGNED NOT NULL DEFAULT '5',
  `laboratory_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_items`
--

INSERT INTO `stock_items` (`id`, `name`, `category_id`, `description`, `quantity`, `min_quantity`, `laboratory_id`, `created_at`, `updated_at`) VALUES
(1, 'Logitech G102 Lightsync Optical Mouse', 1, 'Kondisi baru segel box, PO #PO-2023-109, Alokasi Lab Multimedia 1.', 15, 5, 6, '2026-10-05 23:36:15', '2026-10-05 23:36:15'),
(2, 'CPU Intel Core i5-12400F', 2, 'Unit CPU baru untuk penggantian komputer lab.', 15, 5, NULL, '2026-10-05 23:36:15', '2026-10-05 23:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` int UNSIGNED NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `type` enum('in','out','adjust') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `quantity` int UNSIGNED NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `item_id`, `type`, `quantity`, `notes`, `user_id`, `created_at`) VALUES
(1, 1, 'in', 15, 'Barang masuk manual - PO #PO-2023-109', 2, '2026-10-06 13:36:15'),
(2, 2, 'in', 15, '15 New CPU Received in inventory', 2, '2026-10-06 13:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','coordinator','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `is_active`, `avatar`, `created_by`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$B6/LftUFl1..udpXMjHUwOQSieyr6Nsz93kzz1D1vL3idX7tbBhyq', 'Administrator', 'admin', 1, 'u1_757a831b5a84.jpg', NULL, '2026-10-15 17:28:06', '2026-10-05 23:36:15'),
(2, 'sapdal', '$2y$10$B6/LftUFl1..udpXMjHUwOQSieyr6Nsz93kzz1D1vL3idX7tbBhyq', 'Ms. sapdal', 'coordinator', 1, NULL, NULL, '2026-10-14 19:21:49', '2026-10-05 23:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
-- Laporan dari role User (akses lab) untuk Koordinator & Admin
--

CREATE TABLE `user_reports` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_id` int UNSIGNED NOT NULL,
  `pc_id` int UNSIGNED DEFAULT NULL,
  `category` enum('kerusakan','fasilitas','jaringan','kebersihan','keamanan','saran','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kerusakan',
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','read','processing','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `reported_by` int UNSIGNED DEFAULT NULL,
  `reporter_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `handled_by` int UNSIGNED DEFAULT NULL,
  `handled_at` datetime DEFAULT NULL,
  `ticket_id` int UNSIGNED DEFAULT NULL,
  `reporter_seen` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ur_status` (`status`,`created_at`),
  KEY `idx_ur_user` (`reported_by`,`reporter_seen`),
  KEY `fk_ur_lab` (`lab_id`),
  KEY `fk_ur_pc` (`pc_id`),
  KEY `fk_ur_handler` (`handled_by`),
  KEY `fk_ur_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_report_events`
--

CREATE TABLE `user_report_events` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int UNSIGNED NOT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ure_report` (`report_id`,`created_at`),
  KEY `fk_ure_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_dashboard_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_dashboard_stats` (
`pc_active` bigint
,`pc_issue` bigint
,`pc_total` bigint
,`stock_in_month` decimal(32,0)
,`stock_items` bigint
,`stock_low` bigint
,`stock_total` decimal(32,0)
,`tickets_open` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_lab_overview`
-- (See below for the actual view)
--
CREATE TABLE `v_lab_overview` (
`code` varchar(20)
,`floor` varchar(30)
,`folder` varchar(50)
,`id` int unsigned
,`image` varchar(255)
,`name` varchar(100)
,`page_file` varchar(80)
,`pcs_issue` decimal(23,0)
,`pcs_online` decimal(23,0)
,`status` enum('active','maintenance')
,`total_pcs` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_monthly_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_monthly_summary` (
`lab_report` decimal(42,0)
,`stock_in` decimal(54,0)
,`ym` varchar(7)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_pc_status`
-- (See below for the actual view)
--
CREATE TABLE `v_pc_status` (
`issue_count` decimal(23,0)
,`laboratory_id` int unsigned
,`pc_id` int unsigned
,`pc_number` smallint unsigned
,`status` varchar(6)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_act_date` (`created_at`),
  ADD KEY `idx_act_type` (`type`),
  ADD KEY `fk_act_lab` (`laboratory_id`),
  ADD KEY `fk_act_pc` (`pc_id`),
  ADD KEY `fk_act_user` (`user_id`);

--
-- Indexes for table `calendar_notes`
--
ALTER TABLE `calendar_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_note_date` (`note_date`),
  ADD KEY `fk_note_user` (`user_id`);

--
-- Indexes for table `component_history`
--
ALTER TABLE `component_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ch_pc` (`pc_id`,`created_at`),
  ADD KEY `fk_ch_user` (`user_id`);

--
-- Indexes for table `laboratories`
--
ALTER TABLE `laboratories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_lab_status` (`status`);

--
-- Indexes for table `lab_reports`
--
ALTER TABLE `lab_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rep_date` (`reported_at`),
  ADD KEY `idx_rep_status` (`status`),
  ADD KEY `fk_rep_pc` (`pc_id`),
  ADD KEY `fk_rep_comp` (`component_id`),
  ADD KEY `fk_rep_user` (`reported_by`),
  ADD KEY `idx_rep_pc` (`pc_id`);

--
-- Indexes for table `pcs`
--
ALTER TABLE `pcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lab_pc` (`laboratory_id`,`pc_number`),
  ADD UNIQUE KEY `uq_asset_tag` (`asset_tag`);

--
-- Indexes for table `pc_components`
--
ALTER TABLE `pc_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pc_component` (`pc_id`,`component_type`),
  ADD KEY `idx_comp_status` (`status`),
  ADD KEY `fk_comp_user` (`updated_by`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rl` (`bucket`,`k`,`created_at`);

--
-- Indexes for table `report_events`
--
ALTER TABLE `report_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ev_report` (`report_id`,`created_at`),
  ADD KEY `fk_ev_user` (`user_id`);

--
-- Indexes for table `stock_categories`
--
ALTER TABLE `stock_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_item_name` (`name`),
  ADD KEY `fk_item_cat` (`category_id`),
  ADD KEY `fk_item_lab` (`laboratory_id`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tx_date` (`created_at`),
  ADD KEY `fk_tx_item` (`item_id`),
  ADD KEY `fk_tx_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `calendar_notes`
--
ALTER TABLE `calendar_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `component_history`
--
ALTER TABLE `component_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laboratories`
--
ALTER TABLE `laboratories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lab_reports`
--
ALTER TABLE `lab_reports`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pcs`
--
ALTER TABLE `pcs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `pc_components`
--
ALTER TABLE `pc_components`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=773;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_events`
--
ALTER TABLE `report_events`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_categories`
--
ALTER TABLE `stock_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Structure for view `v_dashboard_stats`
--
DROP TABLE IF EXISTS `v_dashboard_stats`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_dashboard_stats`  AS SELECT (select coalesce(sum(`stock_items`.`quantity`),0) from `stock_items`) AS `stock_total`, (select coalesce(sum(`stock_transactions`.`quantity`),0) from `stock_transactions` where ((`stock_transactions`.`type` = 'in') and (year(`stock_transactions`.`created_at`) = year(curdate())) and (month(`stock_transactions`.`created_at`) = month(curdate())))) AS `stock_in_month`, (select count(0) from `stock_items`) AS `stock_items`, (select count(0) from `v_pc_status` where (`v_pc_status`.`status` = 'online')) AS `pc_active`, (select count(0) from `v_pc_status` where (`v_pc_status`.`status` = 'issue')) AS `pc_issue`, (select count(0) from `pcs`) AS `pc_total`, (select count(0) from `stock_items` where (`stock_items`.`quantity` <= `stock_items`.`min_quantity`)) AS `stock_low`, (select count(0) from `lab_reports` where (`lab_reports`.`status` <> 'resolved')) AS `tickets_open` ;

-- --------------------------------------------------------

--
-- Structure for view `v_lab_overview`
--
DROP TABLE IF EXISTS `v_lab_overview`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_lab_overview`  AS SELECT `l`.`id` AS `id`, `l`.`code` AS `code`, `l`.`name` AS `name`, `l`.`folder` AS `folder`, `l`.`page_file` AS `page_file`, `l`.`floor` AS `floor`, `l`.`status` AS `status`, `l`.`image` AS `image`, count(`v`.`pc_id`) AS `total_pcs`, coalesce(sum((`v`.`status` = 'online')),0) AS `pcs_online`, coalesce(sum((`v`.`status` = 'issue')),0) AS `pcs_issue` FROM (`laboratories` `l` left join `v_pc_status` `v` on((`v`.`laboratory_id` = `l`.`id`))) GROUP BY `l`.`id` ;

-- --------------------------------------------------------

--
-- Structure for view `v_monthly_summary`
--
DROP TABLE IF EXISTS `v_monthly_summary`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_monthly_summary`  AS SELECT `t`.`ym` AS `ym`, sum(`t`.`lab_report`) AS `lab_report`, sum(`t`.`stock_in`) AS `stock_in` FROM (select convert(date_format(`lab_reports`.`reported_at`,'%Y-%m') using utf8mb4) collate utf8mb4_unicode_ci AS `ym`, count(0) AS `lab_report`, 0 AS `stock_in` from `lab_reports` group by `ym` union all select convert(date_format(`stock_transactions`.`created_at`,'%Y-%m') using utf8mb4) collate utf8mb4_unicode_ci AS `ym`, 0 AS `lab_report`, sum(`stock_transactions`.`quantity`) AS `stock_in` from `stock_transactions` where (`stock_transactions`.`type` = 'in') group by `ym`) AS `t` GROUP BY `t`.`ym` ;

-- --------------------------------------------------------

--
-- Structure for view `v_pc_status`
--
DROP TABLE IF EXISTS `v_pc_status`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_pc_status`  AS SELECT `p`.`id` AS `pc_id`, `p`.`laboratory_id` AS `laboratory_id`, `p`.`pc_number` AS `pc_number`, (case when (sum((`c`.`status` = 'issue')) > 0) then _utf8mb4'issue' collate utf8mb4_unicode_ci else _utf8mb4'online' collate utf8mb4_unicode_ci end) AS `status`, sum((`c`.`status` = 'issue')) AS `issue_count` FROM (`pcs` `p` left join `pc_components` `c` on((`c`.`pc_id` = `p`.`id`))) GROUP BY `p`.`id`, `p`.`laboratory_id`, `p`.`pc_number` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_act_lab` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_act_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_act_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `calendar_notes`
--
ALTER TABLE `calendar_notes`
  ADD CONSTRAINT `fk_note_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `component_history`
--
ALTER TABLE `component_history`
  ADD CONSTRAINT `fk_ch_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ch_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lab_reports`
--
ALTER TABLE `lab_reports`
  ADD CONSTRAINT `fk_rep_comp` FOREIGN KEY (`component_id`) REFERENCES `pc_components` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rep_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rep_user` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pcs`
--
ALTER TABLE `pcs`
  ADD CONSTRAINT `fk_pc_lab` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pc_components`
--
ALTER TABLE `pc_components`
  ADD CONSTRAINT `fk_comp_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comp_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `fk_ur_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ur_handler` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ur_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `lab_reports` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_report_events`
--
ALTER TABLE `user_report_events`
  ADD CONSTRAINT `fk_ure_report` FOREIGN KEY (`report_id`) REFERENCES `user_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ure_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_events`
--
ALTER TABLE `report_events`
  ADD CONSTRAINT `fk_ev_report` FOREIGN KEY (`report_id`) REFERENCES `lab_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD CONSTRAINT `fk_item_cat` FOREIGN KEY (`category_id`) REFERENCES `stock_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_lab` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `fk_tx_item` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
