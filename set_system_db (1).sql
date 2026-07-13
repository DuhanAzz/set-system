-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 13, 2026 at 09:05 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `set_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `roll_clubs`
--

CREATE TABLE `roll_clubs` (
  `id` int(11) NOT NULL,
  `club_name` varchar(255) NOT NULL,
  `city_province` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_entries`
--

CREATE TABLE `roll_entries` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `skater_id` int(11) NOT NULL,
  `race_distance` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_events`
--

CREATE TABLE `roll_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `race_format` enum('DTT','SPRINT','PTP','ELIMINATION','TIME_TRIAL') NOT NULL,
  `status` enum('Draft','Published','Completed') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_date_start` date DEFAULT NULL,
  `event_date_end` date DEFAULT NULL,
  `event_location` varchar(255) DEFAULT NULL,
  `event_city` varchar(255) DEFAULT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `logo_left` varchar(255) DEFAULT NULL,
  `is_result_published` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_event_details`
--

CREATE TABLE `roll_event_details` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `distance` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_event_results`
--

CREATE TABLE `roll_event_results` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `skater_id` int(11) NOT NULL,
  `heat_name` varchar(50) NOT NULL,
  `finish_time_ms` int(11) DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `is_eliminated` tinyint(1) DEFAULT 0,
  `finish_position` int(11) DEFAULT NULL,
  `advancement_status` enum('Lolos','Gugur','Menunggu') DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_hero_images`
--

CREATE TABLE `roll_hero_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_pelotons`
--

CREATE TABLE `roll_pelotons` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `skater_id` int(11) NOT NULL,
  `heat_name` varchar(50) NOT NULL,
  `start_grid` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_site_settings`
--

CREATE TABLE `roll_site_settings` (
  `id` int(11) NOT NULL,
  `app_name` varchar(50) DEFAULT 'SET ROLL SYSTEM',
  `hero_title` varchar(255) DEFAULT 'Kejuaraan Sepatu Roda Nasional',
  `hero_subtitle` text DEFAULT NULL,
  `running_text` text DEFAULT NULL,
  `info_title` varchar(255) DEFAULT 'OPEN REGISTRATION',
  `info_text` text DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT 'info@setroll.id',
  `contact_wa` varchar(20) DEFAULT '628123456789',
  `link_instagram` varchar(255) DEFAULT 'https://instagram.com',
  `link_facebook` varchar(255) DEFAULT '#',
  `site_description` text DEFAULT NULL,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `about_image` varchar(255) DEFAULT NULL,
  `footer_image` varchar(255) DEFAULT NULL,
  `event_fallback_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_skaters`
--

CREATE TABLE `roll_skaters` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `skater_name` varchar(255) NOT NULL,
  `gender` enum('M','F') NOT NULL,
  `birth_date` date NOT NULL,
  `age_group` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roll_users`
--

CREATE TABLE `roll_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('master','admin','user') NOT NULL,
  `account_status` enum('active','pending','suspended') DEFAULT 'active',
  `club_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_athlete_records`
--

CREATE TABLE `swim_athlete_records` (
  `id` int(11) NOT NULL,
  `swimmer_id` int(11) NOT NULL,
  `nomor_lomba` varchar(50) NOT NULL,
  `waktu_terbaik` varchar(20) NOT NULL,
  `tanggal_dicapai` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_clubs`
--

CREATE TABLE `swim_clubs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_klub` varchar(150) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_documents`
--

CREATE TABLE `swim_documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `judul_file` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `kategori` enum('buku_acara','buku_hasil','lainnya','JUKNIS','FORMULIR') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_dq_rules`
--

CREATE TABLE `swim_dq_rules` (
  `id` int(11) NOT NULL,
  `kategori_gaya` varchar(50) NOT NULL,
  `pasal` varchar(50) NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_events`
--

CREATE TABLE `swim_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT 'Standard',
  `participation_type` varchar(50) DEFAULT 'club',
  `event_name` varchar(255) DEFAULT NULL,
  `event_location` varchar(255) DEFAULT NULL,
  `event_city` varchar(100) DEFAULT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `event_date_start` date DEFAULT NULL,
  `event_date_end` date DEFAULT NULL,
  `lane_count` int(11) DEFAULT 8,
  `used_lanes` varchar(100) DEFAULT NULL,
  `competition_system` varchar(50) DEFAULT 'Langsung Final',
  `event_status` varchar(50) DEFAULT 'upcoming',
  `pool_type` varchar(10) DEFAULT '50m',
  `age_calculation_type` varchar(20) DEFAULT 'Dec 31',
  `logo_left` varchar(255) DEFAULT NULL,
  `logo_right` varchar(255) DEFAULT NULL,
  `sponsor_footer` varchar(255) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_account_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pricing_mode` enum('per_item','package') DEFAULT 'per_item',
  `package_price` decimal(15,2) DEFAULT 0.00,
  `package_limit` int(11) DEFAULT 0,
  `extra_price` decimal(15,2) DEFAULT 0.00,
  `is_result_published` tinyint(1) DEFAULT 0,
  `show_records_in_program` tinyint(1) DEFAULT 0,
  `record_package_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_event_age_groups`
--

CREATE TABLE `swim_event_age_groups` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `min_age` int(11) DEFAULT NULL,
  `max_age` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_event_entries`
--

CREATE TABLE `swim_event_entries` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `swimmer_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entry_time` varchar(15) DEFAULT '00:00.00',
  `entry_time_ms` int(11) DEFAULT 999999999,
  `status` enum('Pending','Approved','Rejected','Scratched') DEFAULT 'Pending',
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_event_numbers`
--

CREATE TABLE `swim_event_numbers` (
  `id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `event_number` varchar(10) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `distance` int(11) NOT NULL,
  `stroke` varchar(50) NOT NULL,
  `jenis_kelamin` enum('L','P','Campuran') NOT NULL,
  `age_group` varchar(50) NOT NULL,
  `age_min` int(11) NOT NULL,
  `age_max` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `schedule_date` date DEFAULT NULL,
  `schedule_time` time DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `selected_ku_ids` text DEFAULT NULL,
  `rank_mode` enum('split','overall') DEFAULT 'split',
  `is_relay` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_event_seeding`
--

CREATE TABLE `swim_event_seeding` (
  `id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `heat_prelim` int(3) DEFAULT NULL,
  `lane_prelim` int(3) DEFAULT NULL,
  `time_prelim` varchar(15) DEFAULT NULL,
  `time_prelim_ms` int(11) DEFAULT NULL,
  `rank_prelim` int(5) DEFAULT NULL,
  `is_dq_prelim` tinyint(1) DEFAULT 0,
  `dq_reason_prelim` varchar(255) DEFAULT NULL,
  `heat_final` int(3) DEFAULT NULL,
  `lane_final` int(3) DEFAULT NULL,
  `time_final` varchar(15) DEFAULT NULL,
  `dq_rule_id` int(11) DEFAULT NULL,
  `time_final_ms` int(11) DEFAULT NULL,
  `rank_final` int(5) DEFAULT NULL,
  `is_dq_final` tinyint(1) DEFAULT 0,
  `dq_reason_final` varchar(255) DEFAULT NULL,
  `points` int(5) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_hero_images`
--

CREATE TABLE `swim_hero_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_master_records`
--

CREATE TABLE `swim_master_records` (
  `id` int(11) NOT NULL,
  `distance` int(5) NOT NULL,
  `stroke` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `age_group` varchar(50) NOT NULL,
  `record_type` enum('rekornas','rekor_event') NOT NULL,
  `holder_name` varchar(255) NOT NULL,
  `record_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `record_year` varchar(10) DEFAULT NULL,
  `record_time` varchar(15) NOT NULL,
  `record_time_ms` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_payments`
--

CREATE TABLE `swim_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `file_path` varchar(255) DEFAULT NULL COMMENT 'File Bukti Transfer',
  `admin_file_path` varchar(255) DEFAULT NULL COMMENT 'File Berkas Administrasi',
  `status` enum('Unpaid','Pending','Paid','Rejected') DEFAULT 'Unpaid',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_site_settings`
--

CREATE TABLE `swim_site_settings` (
  `id` int(11) NOT NULL,
  `hero_title` varchar(255) DEFAULT 'Sistem Manajemen Lomba Renang',
  `hero_subtitle` text DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT 'https://images.unsplash.com/photo-1530549387789-4c1017266635',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `running_text` text DEFAULT NULL,
  `hero_title_2` varchar(255) DEFAULT 'Kompetisi Renang Nasional',
  `hero_subtitle_2` varchar(255) DEFAULT 'Daftarkan atlet terbaikmu sekarang juga.',
  `hero_image_2` varchar(255) DEFAULT '',
  `hero_title_3` varchar(255) DEFAULT 'Live Result Realtime',
  `hero_subtitle_3` varchar(255) DEFAULT 'Pantau perolehan waktu dan medali secara langsung.',
  `hero_image_3` varchar(255) DEFAULT '',
  `info_title` varchar(255) DEFAULT 'OPEN REGISTRATION',
  `info_text` text DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT 'info@swimmeet.id',
  `contact_wa` varchar(20) DEFAULT '628123456789',
  `link_instagram` varchar(255) DEFAULT 'https://instagram.com',
  `link_facebook` varchar(255) DEFAULT '#',
  `site_description` text DEFAULT NULL,
  `app_name` varchar(50) DEFAULT 'SwimMeet App',
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `bank_name` varchar(50) DEFAULT 'BCA',
  `bank_account` varchar(50) DEFAULT '1234567890',
  `bank_holder` varchar(100) DEFAULT 'Yayasan Renang Indonesia',
  `allow_register` tinyint(1) DEFAULT 1,
  `show_announcement` tinyint(1) DEFAULT 0,
  `announcement_text` text DEFAULT NULL,
  `support_wa` varchar(50) DEFAULT NULL,
  `support_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_swimmers`
--

CREATE TABLE `swim_swimmers` (
  `id` int(11) NOT NULL,
  `uid` varchar(12) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `nama_atlet` varchar(255) NOT NULL,
  `asal_sekolah` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `tanggal_lahir` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','verified') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_system_logs`
--

CREATE TABLE `swim_system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swim_users`
--

CREATE TABLE `swim_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('master','admin','user') DEFAULT 'user',
  `account_status` enum('pending','active','suspended') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `universal_admins`
--

CREATE TABLE `universal_admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `universal_hero_images`
--

CREATE TABLE `universal_hero_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `universal_settings`
--

CREATE TABLE `universal_settings` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) DEFAULT 'Universal SET System',
  `hero_title` varchar(255) DEFAULT 'UNIVERSAL SET SYSTEM',
  `site_description` text DEFAULT NULL,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `contact_email` varchar(255) DEFAULT 'sportsentrytechsystem@gmail.com',
  `contact_wa` varchar(255) DEFAULT '6281993189787',
  `link_instagram` varchar(255) DEFAULT 'https://www.instagram.com/set_system.id/',
  `swim_system_image` varchar(255) DEFAULT NULL,
  `roll_system_image` varchar(255) DEFAULT NULL,
  `swim_event_logo` varchar(255) DEFAULT NULL,
  `roll_event_logo` varchar(255) DEFAULT NULL,
  `feature_1_title` varchar(255) DEFAULT NULL,
  `feature_1_desc` text DEFAULT NULL,
  `feature_2_title` varchar(255) DEFAULT NULL,
  `feature_2_desc` text DEFAULT NULL,
  `feature_3_title` varchar(255) DEFAULT NULL,
  `feature_3_desc` text DEFAULT NULL,
  `feature_4_title` varchar(255) DEFAULT NULL,
  `feature_4_desc` text DEFAULT NULL,
  `promo_title` varchar(255) DEFAULT NULL,
  `feature_1_icon` varchar(255) DEFAULT NULL,
  `feature_2_icon` varchar(255) DEFAULT NULL,
  `feature_3_icon` varchar(255) DEFAULT NULL,
  `feature_4_icon` varchar(255) DEFAULT NULL,
  `promo_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `roll_clubs`
--
ALTER TABLE `roll_clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_entries`
--
ALTER TABLE `roll_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_events`
--
ALTER TABLE `roll_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_event_details`
--
ALTER TABLE `roll_event_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_event_results`
--
ALTER TABLE `roll_event_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_hero_images`
--
ALTER TABLE `roll_hero_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_pelotons`
--
ALTER TABLE `roll_pelotons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_site_settings`
--
ALTER TABLE `roll_site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_skaters`
--
ALTER TABLE `roll_skaters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roll_users`
--
ALTER TABLE `roll_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `swim_athlete_records`
--
ALTER TABLE `swim_athlete_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_clubs`
--
ALTER TABLE `swim_clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_documents`
--
ALTER TABLE `swim_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_dq_rules`
--
ALTER TABLE `swim_dq_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_events`
--
ALTER TABLE `swim_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_event_age_groups`
--
ALTER TABLE `swim_event_age_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_event_entries`
--
ALTER TABLE `swim_event_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_event_numbers`
--
ALTER TABLE `swim_event_numbers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_event_seeding`
--
ALTER TABLE `swim_event_seeding`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_hero_images`
--
ALTER TABLE `swim_hero_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_master_records`
--
ALTER TABLE `swim_master_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_payments`
--
ALTER TABLE `swim_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_site_settings`
--
ALTER TABLE `swim_site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_swimmers`
--
ALTER TABLE `swim_swimmers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_system_logs`
--
ALTER TABLE `swim_system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swim_users`
--
ALTER TABLE `swim_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `universal_admins`
--
ALTER TABLE `universal_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `universal_hero_images`
--
ALTER TABLE `universal_hero_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `universal_settings`
--
ALTER TABLE `universal_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `roll_clubs`
--
ALTER TABLE `roll_clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_entries`
--
ALTER TABLE `roll_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_events`
--
ALTER TABLE `roll_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_event_details`
--
ALTER TABLE `roll_event_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_event_results`
--
ALTER TABLE `roll_event_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_hero_images`
--
ALTER TABLE `roll_hero_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_pelotons`
--
ALTER TABLE `roll_pelotons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_site_settings`
--
ALTER TABLE `roll_site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_skaters`
--
ALTER TABLE `roll_skaters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roll_users`
--
ALTER TABLE `roll_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_athlete_records`
--
ALTER TABLE `swim_athlete_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_clubs`
--
ALTER TABLE `swim_clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_documents`
--
ALTER TABLE `swim_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_dq_rules`
--
ALTER TABLE `swim_dq_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_events`
--
ALTER TABLE `swim_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_event_age_groups`
--
ALTER TABLE `swim_event_age_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_event_entries`
--
ALTER TABLE `swim_event_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_event_numbers`
--
ALTER TABLE `swim_event_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_event_seeding`
--
ALTER TABLE `swim_event_seeding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_hero_images`
--
ALTER TABLE `swim_hero_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_master_records`
--
ALTER TABLE `swim_master_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_payments`
--
ALTER TABLE `swim_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_site_settings`
--
ALTER TABLE `swim_site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_swimmers`
--
ALTER TABLE `swim_swimmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_system_logs`
--
ALTER TABLE `swim_system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swim_users`
--
ALTER TABLE `swim_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `universal_admins`
--
ALTER TABLE `universal_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `universal_hero_images`
--
ALTER TABLE `universal_hero_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `universal_settings`
--
ALTER TABLE `universal_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
