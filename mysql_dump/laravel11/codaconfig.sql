/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `codaconfig` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `engine` varchar(255) DEFAULT NULL,
  `engine_version` varchar(255) DEFAULT NULL,
  `api` varchar(255) DEFAULT NULL,
  `api_status` varchar(255) DEFAULT NULL,
  `api_config` varchar(255) DEFAULT NULL,
  `api_service` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_description` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `options` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `codaconfig` (`id`, `created_at`, `updated_at`, `uuid`, `type`, `engine`, `engine_version`, `api`, `api_status`, `api_config`, `api_service`, `status`, `status_description`, `description`, `options`) VALUES
	(1, '2024-09-12 13:53:40', '2024-09-12 13:53:41', '1', 'folder', 'root_folder', NULL, 'NER_DATA', 'NER_DATA', NULL, NULL, NULL, NULL, NULL, NULL),
	(44, '2024-09-12 13:56:17', '2024-09-12 13:56:17', '2', 'converter', 'tika', '1', 'http://10.10.6.25:9998/tika', 'http://10.10.6.25:9998', 'http://10.10.6.25:9998', 'http://127.0.0.1:8080/status', NULL, NULL, NULL, '{"method":"PUT","payload":"body"}'),
	(45, '2024-09-12 13:57:55', '2024-09-12 13:57:59', NULL, 'analyzer', 'spacy_ner', NULL, 'http://10.10.6.25:7861/ner', 'http://10.10.6.25:7861/info', 'https://jsonplaceholder.typicode.com/albums/2', NULL, NULL, NULL, NULL, '{"method":"POST","payload":"body"}'),
	(46, '2024-09-12 13:57:56', '2024-09-12 13:58:00', NULL, 'analyzer', 'spacy_regexp', NULL, 'http://10.10.6.25:7861/regexp', 'http://10.10.6.25:7861/info', 'https://jsonplaceholder.typicode.com/albums/3', NULL, NULL, NULL, NULL, '{"method":"POST","payload":"body"}'),
	(47, '2024-09-12 13:57:56', '2024-09-12 13:58:00', NULL, 'analyzer', 'hf01', NULL, 'http://10.10.6.25:7862/run', 'http://10.10.6.25:7862/status', 'http://127.0.0.1:8080/status', NULL, NULL, NULL, 'Hugging Face Libray', '{"method":"PUT","payload":"body"}'),
	(48, '2024-09-28 08:45:57', '2024-09-28 08:45:58', '3', 'folder', 'upload_folder', NULL, 'NER_UPLOAD', 'NER_UPLOAD', NULL, NULL, NULL, NULL, NULL, NULL),
	(50, '2024-10-12 07:15:32', '2024-10-12 07:15:33', '9', 'analyzer', 'ollama', 'gemma2', 'http://10.10.6.25:7880/api/generate', 'http://10.10.6.25:7880', NULL, NULL, NULL, NULL, NULL, '{"method":"POST", "model":"llama3.2:1b","prompt":"Elenca le città nella seguente frase: \'Ciao Mario vai a Roma\'", "stream":false}'),
	(51, '2024-10-29 11:09:59', '2024-10-29 11:10:00', '20', 'converter', 'python', 'pymudf', 'http://10.10.6.25:7860/pymupdf', 'http://10.10.6.25:7860/status', 'http://10.10.6.25:9998', 'http://127.0.0.1:8080/status', NULL, NULL, NULL, '{"method":"POST_FORM","payload":"body"}'),
	(53, '2024-10-29 14:29:50', '2024-10-29 14:29:49', '5', 'cleaner', 'python_clean_local', 'locla', 'http://127.0.0.1:8080/preprocess', 'http://127.0.0.1:8080/status', NULL, NULL, NULL, NULL, NULL, '{"method":"PUT","payload":"body"}'),
	(54, '2024-11-02 08:28:33', '2024-11-02 08:28:34', '8', 'cleaner', 'python_clean', 'remote', 'http://10.10.6.25:7860/preprocess', 'http://10.10.6.25:7860/status', NULL, NULL, NULL, NULL, NULL, '{"method":"PUT","payload":"body"}'),
	(55, '2024-11-02 08:32:00', '2024-11-02 08:32:01', NULL, 'analyzer', 'ollama', 'llama32', 'http://10.10.6.25:7881/api/generate', 'http://10.10.6.25:7881', NULL, NULL, NULL, NULL, NULL, '{"method":"POST", "model":"llama3.2:1b","prompt":"Elenca le città nella seguente frase: \'Ciao Mario vai a Roma\'", "stream":false}'),
	(56, '2024-11-02 08:32:22', '2024-11-02 08:32:21', NULL, 'analyzer', 'ollama', 'phi3', 'http://10.10.6.25:7882/api/generate', 'http://10.10.6.25:7882', NULL, NULL, NULL, NULL, NULL, '{"method":"POST", "model":"llama3.2:1b","prompt":"Elenca le città nella seguente frase: \'Ciao Mario vai a Roma\'", "stream":false}'),
	(57, '2024-11-02 10:38:42', '2024-11-02 10:38:42', NULL, 'converter', 'python', 'ocr', 'http://10.10.6.25:7860/pymupdf_ocr', 'http://10.10.6.25:7860/status', NULL, NULL, NULL, NULL, NULL, '{"method":"POST_FORM","payload":"body"}');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
