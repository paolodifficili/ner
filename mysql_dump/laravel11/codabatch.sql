/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `codabatch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `batch_uuid` varchar(255) DEFAULT NULL,
  `batch_description` varchar(255) DEFAULT NULL,
  `batch_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`batch_options`)),
  `batch_action` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `codabatch` (`id`, `created_at`, `updated_at`, `batch_uuid`, `batch_description`, `batch_options`, `batch_action`, `file`, `info`, `status`, `last_run_at`) VALUES
	(16, '2024-10-05 05:46:14', '2024-10-05 05:46:14', 'BATCH_2024_10_05_09_46_04', 'BATCH_2024_10_05_09_46_04', '{"action_selected":"RUN_ENGINE","engines_selected":[1],"files_selected":[6]}', 'RUN_ENGINE', NULL, NULL, NULL, NULL),
	(17, '2024-10-05 05:50:59', '2024-10-05 05:50:59', 'BATCH_2024_10_05_09_50_48', 'BATCH_2024_10_05_09_50_48', '{"action_selected":"RUN_ENGINE","engines_selected":[44,45],"files_selected":[6]}', 'RUN_ENGINE', NULL, NULL, NULL, NULL),
	(18, '2024-10-05 05:52:42', '2024-10-05 05:52:42', 'BATCH_2024_10_05_09_52_28', 'BATCH_2024_10_05_09_52_28', '{"action_selected":"CHECK_CONFIG","engines_selected":[44],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(19, '2024-10-05 07:17:14', '2024-10-05 07:17:14', 'BATCH_TEST_149', 'BATCH_TEST_149', '{"action_selected":0,"engines_selected":[1,44],"files_selected":[5,7]}', 'CHECH_CONFIG', NULL, NULL, NULL, NULL),
	(20, '2024-10-05 07:19:51', '2024-10-05 07:19:56', 'BATCH_TEST_1659', 'BATCH_TEST_1659', '{"action_selected":0,"engines_selected":[1,44],"files_selected":[5,7]}', 'CHECK_CONFIG', NULL, NULL, NULL, '2024-10-05 07:19:56'),
	(21, '2024-10-05 07:24:41', '2024-10-05 07:24:46', 'BATCH_TEST_1731', 'BATCH_TEST_1731', '{"action_selected":0,"engines_selected":[1,44],"files_selected":[5,7]}', 'CHECK_CONFIG', NULL, NULL, NULL, '2024-10-05 07:24:46'),
	(22, '2024-10-05 07:33:45', '2024-10-10 07:50:31', 'BATCH_2024_10_05_11_33_32', 'BATCH_2024_10_05_11_33_32', '{"action_selected":"RUN_ENGINE","engines_selected":[44,47],"files_selected":[12]}', 'RUN_ENGINE', NULL, NULL, NULL, '2024-10-10 07:50:31'),
	(23, '2024-10-10 07:03:46', '2024-10-10 07:41:00', 'BATCH_2024_10_10_11_03_34', 'BATCH_2024_10_10_11_03_34', '{"action_selected":"CHECK_CONFIG","engines_selected":[1,44,45,46,47,48],"files_selected":[10]}', 'CHECK_CONFIG', NULL, NULL, NULL, '2024-10-10 07:41:00'),
	(24, '2024-10-10 10:40:04', '2024-10-10 10:40:04', 'BATCH_2024_10_10_14_39_51', 'BATCH_2024_10_10_14_39_51', '{"action_selected":"RUN_ENGINE","engines_selected":[1,44,45,47],"files_selected":[15]}', 'RUN_ENGINE', NULL, NULL, NULL, NULL),
	(25, '2024-10-11 13:23:57', '2024-10-11 13:23:57', 'BATCH_2024_10_11_17_23_46', 'BATCH_2024_10_11_17_23_46', '{"action_selected":"RUN_ENGINE","engines_selected":[45],"files_selected":[7]}', 'RUN_ENGINE', NULL, NULL, NULL, NULL),
	(26, '2024-10-12 05:24:26', '2024-10-12 05:33:24', 'BATCH_2024_10_12_09_24_08', 'BATCH_2024_10_12_09_24_08', '{"action_selected":"CHECK_CONFIG","engines_selected":[50],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, '2024-10-12 05:33:24'),
	(27, '2024-10-12 05:38:05', '2024-10-12 05:38:05', 'BATCH_2024_10_12_09_37_48', 'BATCH_2024_10_12_09_37_48', '{"action_selected":"RUN_ENGINE","engines_selected":[50,45],"files_selected":[6]}', 'RUN_ENGINE', NULL, NULL, NULL, NULL),
	(28, '2024-10-12 05:44:57', '2024-11-02 07:52:31', 'BATCH_2024_10_12_09_42_09', 'BATCH_2024_10_12_09_42_09', '{"action_selected":"RUN_ENGINE","engines_selected":[45,50],"files_selected":[6]}', 'RUN_ENGINE', NULL, NULL, NULL, '2024-11-02 07:52:31'),
	(29, '2024-10-29 08:59:01', '2024-10-29 08:59:01', 'BATCH_2024_10_29_10_58_48', 'BATCH_2024_10_29_10_58_48', '{"action_selected":"CHECK_CONFIG","engines_selected":[],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(30, '2024-10-31 08:04:21', '2024-10-31 08:06:43', 'BATCH_2024_10_31_10_04_11', 'BATCH_2024_10_31_10_04_11', '{"action_selected":"CHECK_CONFIG","engines_selected":[1],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, '2024-10-31 08:06:43'),
	(31, '2024-10-31 09:15:18', '2024-10-31 09:15:18', 'BATCH_2024_10_31_11_15_04', 'BATCH_2024_10_31_11_15_04', '{"action_selected":"RUN_ENGINE","engines_selected":[51,50],"files_selected":[6]}', 'RUN_ENGINE', NULL, NULL, NULL, NULL),
	(32, '2024-10-31 09:23:05', '2024-10-31 09:23:05', 'BATCH_2024_10_31_11_22_52', 'BATCH_2024_10_31_11_22_52', '{"action_selected":"CHECK_CONFIG","engines_selected":[44],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(33, '2024-10-31 09:23:34', '2024-10-31 09:23:34', 'BATCH_2024_10_31_11_22_52', 'BATCH_2024_10_31_11_22_52', '{"action_selected":"CHECK_CONFIG","engines_selected":[44],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(34, '2024-10-31 09:34:57', '2024-10-31 09:34:57', 'BATCH_2024_10_31_11_34_09', 'BATCH_2024_10_31_11_34_09', '{"action_selected":"CHECK_CONFIG","engines_selected":[44],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(35, '2024-10-31 09:35:25', '2024-10-31 09:35:25', 'BATCH_2024_10_31_11_34_09', 'BATCH_2024_10_31_11_34_09', '{"action_selected":"CHECK_CONFIG","engines_selected":[44],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(36, '2024-10-31 09:35:43', '2024-10-31 09:35:43', 'BATCH_2024_10_31_11_35_34', 'BATCH_2024_10_31_11_35_34', '{"action_selected":"CHECK_CONFIG","engines_selected":[1],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(37, '2024-10-31 09:36:09', '2024-10-31 09:36:09', 'BATCH_2024_10_31_11_36_01', 'BATCH_2024_10_31_11_36_01', '{"action_selected":"CHECK_CONFIG","engines_selected":[1],"files_selected":[6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(38, '2024-10-31 09:37:19', '2024-10-31 09:37:19', 'BATCH_2024_10_31_11_37_12', 'BATCH_2024_10_31_11_37_12', '{"action_selected":"CHECK_CONFIG","engines_selected":[1],"files_selected":[5]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(39, '2024-10-31 09:37:58', '2024-10-31 09:37:58', 'BATCH_2024_10_31_11_37_49', 'BATCH_2024_10_31_11_37_49', '{"action_selected":"CHECK_CONFIG","engines_selected":[1],"files_selected":[5,6]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(41, '2024-10-31 16:03:24', '2024-10-31 16:03:24', 'BATCH_2024_10_31_18_03_12', 'BATCH_2024_10_31_18_03_12', '{"action_selected":"CHECK_CONFIG","engines_selected":[44],"files_selected":[7]}', 'CHECK_CONFIG', NULL, NULL, NULL, NULL),
	(42, '2024-11-02 07:08:20', '2024-11-02 07:34:26', 'BATCH_2024_11_02_09_07_35', 'BATCH_2024_11_02_09_07_35', '{"action_selected":"CHECK_CONFIG","engines_selected":[44,46,47],"files_selected":[]}', 'CHECK_CONFIG', NULL, NULL, NULL, '2024-11-02 07:34:26'),
	(43, '2024-11-02 07:36:10', '2024-11-02 07:36:25', 'BATCH_2024_11_02_09_35_58', 'BATCH_2024_11_02_09_35_58', '{"action_selected":"RUN_ENGINE","engines_selected":[1,45,46],"files_selected":[6]}', 'RUN_ENGINE', NULL, NULL, NULL, '2024-11-02 07:36:25');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
