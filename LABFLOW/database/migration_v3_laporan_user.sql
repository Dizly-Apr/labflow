-- ============================================================================
-- LabFlow - MIGRASI v3  (jalankan SEKALI pada database yang SUDAH berjalan)
--
-- Isi:
--  1. PERBAIKAN ERROR  "Illegal mix of collations ... for operation '='" (error 1267)
--     Penyebab: kolom teks hasil hitung di view v_pc_status / v_monthly_summary memakai
--     collation lain (general_ci / 0900_ai_ci) daripada tabel (unicode_ci). Akibatnya
--     halaman Denah Lab (lab.php) dan Laporan & Export (laporan.php) error.
--     View dibuat ulang dengan collation yang dikunci. DEFINER=root juga dihapus
--     (view memakai SQL SECURITY INVOKER) supaya tidak error di hosting / user DB lain.
--  2. Tipe aktivitas baru 'user_report' pada activity_logs.
--  3. Fitur baru: Laporan dari User -> Koordinator & Admin
--     (tabel user_reports + user_report_events).
--
-- Cara pakai: phpMyAdmin -> pilih database labflow -> Import file ini.
-- Aman dijalankan ulang (CREATE OR REPLACE / IF NOT EXISTS / MODIFY).
-- Disarankan backup dulu (menu Backup Database).
-- ============================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---- 1. view (urutan penting: v_pc_status dipakai view lain) -----------------
CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_pc_status`  AS SELECT `p`.`id` AS `pc_id`, `p`.`laboratory_id` AS `laboratory_id`, `p`.`pc_number` AS `pc_number`, (case when (sum((`c`.`status` = 'issue')) > 0) then _utf8mb4'issue' collate utf8mb4_unicode_ci else _utf8mb4'online' collate utf8mb4_unicode_ci end) AS `status`, sum((`c`.`status` = 'issue')) AS `issue_count` FROM (`pcs` `p` left join `pc_components` `c` on((`c`.`pc_id` = `p`.`id`))) GROUP BY `p`.`id`, `p`.`laboratory_id`, `p`.`pc_number` ;

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_lab_overview`  AS SELECT `l`.`id` AS `id`, `l`.`code` AS `code`, `l`.`name` AS `name`, `l`.`folder` AS `folder`, `l`.`page_file` AS `page_file`, `l`.`floor` AS `floor`, `l`.`status` AS `status`, `l`.`image` AS `image`, count(`v`.`pc_id`) AS `total_pcs`, coalesce(sum((`v`.`status` = 'online')),0) AS `pcs_online`, coalesce(sum((`v`.`status` = 'issue')),0) AS `pcs_issue` FROM (`laboratories` `l` left join `v_pc_status` `v` on((`v`.`laboratory_id` = `l`.`id`))) GROUP BY `l`.`id` ;

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_dashboard_stats`  AS SELECT (select coalesce(sum(`stock_items`.`quantity`),0) from `stock_items`) AS `stock_total`, (select coalesce(sum(`stock_transactions`.`quantity`),0) from `stock_transactions` where ((`stock_transactions`.`type` = 'in') and (year(`stock_transactions`.`created_at`) = year(curdate())) and (month(`stock_transactions`.`created_at`) = month(curdate())))) AS `stock_in_month`, (select count(0) from `stock_items`) AS `stock_items`, (select count(0) from `v_pc_status` where (`v_pc_status`.`status` = 'online')) AS `pc_active`, (select count(0) from `v_pc_status` where (`v_pc_status`.`status` = 'issue')) AS `pc_issue`, (select count(0) from `pcs`) AS `pc_total`, (select count(0) from `stock_items` where (`stock_items`.`quantity` <= `stock_items`.`min_quantity`)) AS `stock_low`, (select count(0) from `lab_reports` where (`lab_reports`.`status` <> 'resolved')) AS `tickets_open` ;

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_monthly_summary`  AS SELECT `t`.`ym` AS `ym`, sum(`t`.`lab_report`) AS `lab_report`, sum(`t`.`stock_in`) AS `stock_in` FROM (select convert(date_format(`lab_reports`.`reported_at`,'%Y-%m') using utf8mb4) collate utf8mb4_unicode_ci AS `ym`, count(0) AS `lab_report`, 0 AS `stock_in` from `lab_reports` group by `ym` union all select convert(date_format(`stock_transactions`.`created_at`,'%Y-%m') using utf8mb4) collate utf8mb4_unicode_ci AS `ym`, 0 AS `lab_report`, sum(`stock_transactions`.`quantity`) AS `stock_in` from `stock_transactions` where (`stock_transactions`.`type` = 'in') group by `ym`) AS `t` GROUP BY `t`.`ym` ;

-- ---- 2. tipe aktivitas baru --------------------------------------------------
ALTER TABLE `activity_logs`
  MODIFY `type` enum('pc_issue','pc_update','stock_in','lab_status','login','user_mgmt','stock_out','ticket','asset','system','user_report') COLLATE utf8mb4_unicode_ci NOT NULL;

-- ---- 3. laporan dari user -> koordinator & admin ----------------------------
CREATE TABLE IF NOT EXISTS `user_reports` (
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
  KEY `fk_ur_ticket` (`ticket_id`),
  CONSTRAINT `fk_ur_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_pc` FOREIGN KEY (`pc_id`) REFERENCES `pcs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ur_handler` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ur_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `lab_reports` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_report_events` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int UNSIGNED NOT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ure_report` (`report_id`,`created_at`),
  KEY `fk_ure_user` (`user_id`),
  CONSTRAINT `fk_ure_report` FOREIGN KEY (`report_id`) REFERENCES `user_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ure_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
