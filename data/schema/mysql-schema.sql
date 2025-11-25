/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `api_docs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_docs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `data` json DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_docs_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eniro_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eniro_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `data` json NOT NULL,
  `import_id` bigint unsigned NOT NULL,
  `validation_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_alla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_alla` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `type` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_bolag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_bolag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `org_nr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bolagsform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sni_branch` json DEFAULT NULL,
  `juridiskt_namn` text COLLATE utf8mb4_unicode_ci,
  `registreringsdatum` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_hus` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_foretag_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_foretag_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `foretagsnamn` text COLLATE utf8mb4_unicode_ci,
  `orgnummer` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_foretag_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_foretag_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_phone` int NOT NULL DEFAULT '0',
  `foretag_house` int NOT NULL DEFAULT '0',
  `foretag_saved` int NOT NULL DEFAULT '0',
  `foretag_total` int NOT NULL DEFAULT '0',
  `foretag_page` int unsigned NOT NULL DEFAULT '0',
  `foretag_pages` int unsigned NOT NULL DEFAULT '0',
  `foretag_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `foretag_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_personer_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_personer_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_personer_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_personer_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personer_phone` int NOT NULL DEFAULT '0',
  `personer_house` int NOT NULL DEFAULT '0',
  `personer_saved` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `personer_page` int unsigned NOT NULL DEFAULT '0',
  `personer_pages` int unsigned NOT NULL DEFAULT '0',
  `personer_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `personer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_total` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `foretag_phone` int NOT NULL DEFAULT '0',
  `personer_phone` int NOT NULL DEFAULT '0',
  `personer_house` int NOT NULL DEFAULT '0',
  `foretag_saved` int NOT NULL DEFAULT '0',
  `personer_saved` int NOT NULL DEFAULT '0',
  `personer_pages` int unsigned NOT NULL DEFAULT '0',
  `personer_page` int unsigned NOT NULL DEFAULT '0',
  `personer_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `foretag_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `personer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hitta_se`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hitta_se` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` json DEFAULT NULL,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_hus` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `merinfo_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merinfo_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` json DEFAULT NULL,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_total` int DEFAULT NULL,
  `merinfo_foretag_total` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `merinfo_foretag_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merinfo_foretag_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `foretagsnamn` text COLLATE utf8mb4_unicode_ci,
  `orgnummer` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` json DEFAULT NULL,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_total` int DEFAULT NULL,
  `merinfo_foretag_total` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `merinfo_foretag_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merinfo_foretag_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_phone` int NOT NULL DEFAULT '0',
  `foretag_house` int NOT NULL DEFAULT '0',
  `foretag_saved` int NOT NULL DEFAULT '0',
  `foretag_total` int NOT NULL DEFAULT '0',
  `foretag_page` int unsigned NOT NULL DEFAULT '0',
  `foretag_pages` int unsigned NOT NULL DEFAULT '0',
  `foretag_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `foretag_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `merinfo_personer_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merinfo_personer_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` json DEFAULT NULL,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_total` int DEFAULT NULL,
  `merinfo_foretag_total` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `merinfo_personer_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merinfo_personer_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personer_phone` int NOT NULL DEFAULT '0',
  `personer_house` int NOT NULL DEFAULT '0',
  `personer_saved` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `personer_page` int unsigned NOT NULL DEFAULT '0',
  `personer_pages` int unsigned NOT NULL DEFAULT '0',
  `personer_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `personer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `merinfo_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merinfo_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_total` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `foretag_phone` int NOT NULL DEFAULT '0',
  `personer_phone` int NOT NULL DEFAULT '0',
  `personer_house` int NOT NULL DEFAULT '0',
  `foretag_saved` int NOT NULL DEFAULT '0',
  `personer_saved` int NOT NULL DEFAULT '0',
  `personer_pages` int unsigned NOT NULL DEFAULT '0',
  `personer_page` int unsigned NOT NULL DEFAULT '0',
  `personer_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `foretag_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `personer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personer_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personer_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gatuadress` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `hitta_data_id` int DEFAULT NULL,
  `hitta_personnamn` text COLLATE utf8mb4_unicode_ci,
  `hitta_gatuadress` text COLLATE utf8mb4_unicode_ci,
  `hitta_postnummer` text COLLATE utf8mb4_unicode_ci,
  `hitta_postort` text COLLATE utf8mb4_unicode_ci,
  `hitta_alder` text COLLATE utf8mb4_unicode_ci,
  `hitta_kon` text COLLATE utf8mb4_unicode_ci,
  `hitta_telefon` text COLLATE utf8mb4_unicode_ci,
  `hitta_telefonnummer` text COLLATE utf8mb4_unicode_ci,
  `hitta_karta` text COLLATE utf8mb4_unicode_ci,
  `hitta_link` text COLLATE utf8mb4_unicode_ci,
  `hitta_bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `hitta_bostadspris` text COLLATE utf8mb4_unicode_ci,
  `hitta_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_data_id` int DEFAULT NULL,
  `merinfo_personnamn` text COLLATE utf8mb4_unicode_ci,
  `merinfo_alder` text COLLATE utf8mb4_unicode_ci,
  `merinfo_kon` text COLLATE utf8mb4_unicode_ci,
  `merinfo_gatuadress` text COLLATE utf8mb4_unicode_ci,
  `merinfo_postnummer` text COLLATE utf8mb4_unicode_ci,
  `merinfo_postort` text COLLATE utf8mb4_unicode_ci,
  `merinfo_telefon` json DEFAULT NULL,
  `merinfo_karta` text COLLATE utf8mb4_unicode_ci,
  `merinfo_link` text COLLATE utf8mb4_unicode_ci,
  `merinfo_bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `merinfo_bostadspris` text COLLATE utf8mb4_unicode_ci,
  `merinfo_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_data_id` int DEFAULT NULL,
  `ratsit_gatuadress` text COLLATE utf8mb4_unicode_ci,
  `ratsit_postnummer` text COLLATE utf8mb4_unicode_ci,
  `ratsit_postort` text COLLATE utf8mb4_unicode_ci,
  `ratsit_forsamling` text COLLATE utf8mb4_unicode_ci,
  `ratsit_kommun` text COLLATE utf8mb4_unicode_ci,
  `ratsit_lan` text COLLATE utf8mb4_unicode_ci,
  `ratsit_adressandring` text COLLATE utf8mb4_unicode_ci,
  `ratsit_kommun_ratsit` text COLLATE utf8mb4_unicode_ci,
  `ratsit_stjarntacken` text COLLATE utf8mb4_unicode_ci,
  `ratsit_fodelsedag` text COLLATE utf8mb4_unicode_ci,
  `ratsit_personnummer` text COLLATE utf8mb4_unicode_ci,
  `ratsit_alder` text COLLATE utf8mb4_unicode_ci,
  `ratsit_kon` text COLLATE utf8mb4_unicode_ci,
  `ratsit_civilstand` text COLLATE utf8mb4_unicode_ci,
  `ratsit_fornamn` text COLLATE utf8mb4_unicode_ci,
  `ratsit_efternamn` text COLLATE utf8mb4_unicode_ci,
  `ratsit_personnamn` text COLLATE utf8mb4_unicode_ci,
  `ratsit_agandeform` text COLLATE utf8mb4_unicode_ci,
  `ratsit_bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `ratsit_boarea` text COLLATE utf8mb4_unicode_ci,
  `ratsit_byggar` text COLLATE utf8mb4_unicode_ci,
  `ratsit_fastighet` text COLLATE utf8mb4_unicode_ci,
  `ratsit_telfonnummer` json DEFAULT NULL,
  `ratsit_epost_adress` json DEFAULT NULL,
  `ratsit_personer` json DEFAULT NULL,
  `ratsit_foretag` json DEFAULT NULL,
  `ratsit_grannar` json DEFAULT NULL,
  `ratsit_fordon` json DEFAULT NULL,
  `ratsit_hundar` json DEFAULT NULL,
  `ratsit_bolagsengagemang` json DEFAULT NULL,
  `ratsit_longitude` text COLLATE utf8mb4_unicode_ci,
  `ratsit_latitud` text COLLATE utf8mb4_unicode_ci,
  `ratsit_google_maps` text COLLATE utf8mb4_unicode_ci,
  `ratsit_google_streetview` text COLLATE utf8mb4_unicode_ci,
  `ratsit_ratsit_se` text COLLATE utf8mb4_unicode_ci,
  `ratsit_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hitta_created_at` timestamp NULL DEFAULT NULL,
  `hitta_updated_at` timestamp NULL DEFAULT NULL,
  `merinfo_created_at` timestamp NULL DEFAULT NULL,
  `merinfo_updated_at` timestamp NULL DEFAULT NULL,
  `ratsit_created_at` timestamp NULL DEFAULT NULL,
  `ratsit_updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_gatuadress_personnamn` (`gatuadress`,`personnamn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_count` int NOT NULL DEFAULT '0',
  `count` int NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress` tinyint unsigned NOT NULL DEFAULT '0',
  `last_processed_page` int unsigned DEFAULT NULL,
  `processed_count` int unsigned DEFAULT NULL,
  `phone` int NOT NULL DEFAULT '0',
  `house` int NOT NULL DEFAULT '0',
  `bolag` int NOT NULL DEFAULT '0',
  `foretag` int NOT NULL DEFAULT '0',
  `personer` int NOT NULL DEFAULT '0',
  `personer_house` int DEFAULT NULL,
  `platser` int NOT NULL DEFAULT '0',
  `merinfo_personer` int DEFAULT NULL,
  `merinfo_foretag` int DEFAULT NULL,
  `merinfo_personer_total` int DEFAULT NULL,
  `merinfo_foretag_total` int DEFAULT NULL,
  `last_livewire_update` timestamp NULL DEFAULT NULL,
  `ratsit_personer_total` int DEFAULT NULL,
  `ratsit_foretag_total` int DEFAULT NULL,
  `is_pending` tinyint(1) NOT NULL DEFAULT '1',
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_nummer_post_nummer_unique` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_apis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_apis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_checks` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hitta_personer_total` int NOT NULL DEFAULT '0',
  `hitta_foretag_total` int NOT NULL DEFAULT '0',
  `merinfo_personer_total` int NOT NULL DEFAULT '0',
  `merinfo_foretag_total` int NOT NULL DEFAULT '0',
  `ratsit_personer_total` int NOT NULL DEFAULT '0',
  `ratsit_foretag_total` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_foretag_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_foretag_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_foretag_saved` int DEFAULT NULL,
  `merinfo_foretag_total` int DEFAULT NULL,
  `merinfo_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_foretag_saved` int DEFAULT NULL,
  `ratsit_foretag_total` int DEFAULT NULL,
  `ratsit_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hitta_foretag_saved` int DEFAULT NULL,
  `hitta_foretag_total` int DEFAULT NULL,
  `hitta_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_nummer_foretag_saved` int DEFAULT NULL,
  `post_nummer_foretag_total` int DEFAULT NULL,
  `post_nummer_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_nummer_foretag_queue_post_nummer_unique` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_personer_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_personer_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_personer_saved` int DEFAULT NULL,
  `merinfo_personer_total` int DEFAULT NULL,
  `merinfo_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_personer_saved` int DEFAULT NULL,
  `ratsit_personer_total` int DEFAULT NULL,
  `ratsit_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hitta_personer_saved` int DEFAULT NULL,
  `hitta_personer_total` int DEFAULT NULL,
  `hitta_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_nummer_personer_saved` int DEFAULT NULL,
  `post_nummer_personer_total` int DEFAULT NULL,
  `post_nummer_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_nummer_personer_queue_post_nummer_unique` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_que`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_que` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_count` int NOT NULL DEFAULT '0',
  `count` int NOT NULL DEFAULT '0',
  `phone` int NOT NULL DEFAULT '0',
  `house` int NOT NULL DEFAULT '0',
  `bolag` int NOT NULL DEFAULT '0',
  `foretag` int NOT NULL DEFAULT '0',
  `personer` int NOT NULL DEFAULT '0',
  `platser` int NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress` int NOT NULL DEFAULT '0',
  `is_pending` tinyint(1) NOT NULL DEFAULT '1',
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_processed_page` int DEFAULT NULL,
  `processed_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_nummer_que_post_nummer_unique` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_personer_saved` int DEFAULT NULL,
  `merinfo_foretag_saved` int DEFAULT NULL,
  `merinfo_personer_total` int DEFAULT NULL,
  `merinfo_foretag_total` int DEFAULT NULL,
  `merinfo_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_checked` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_queued` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_complete` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_personer_saved` int DEFAULT NULL,
  `ratsit_foretag_saved` int DEFAULT NULL,
  `ratsit_personer_total` int DEFAULT NULL,
  `ratsit_foretag_total` int DEFAULT NULL,
  `ratsit_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_checked` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_queued` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_complete` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_personer_saved` int DEFAULT NULL,
  `hitta_foretag_saved` int DEFAULT NULL,
  `hitta_personer_total` int DEFAULT NULL,
  `hitta_foretag_total` int DEFAULT NULL,
  `hitta_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hitta_checked` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_queued` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_complete` tinyint(1) NOT NULL DEFAULT '0',
  `post_nummer_personer_saved` int DEFAULT NULL,
  `post_nummer_foretag_saved` int DEFAULT NULL,
  `post_nummer_personer_total` int DEFAULT NULL,
  `post_nummer_foretag_total` int DEFAULT NULL,
  `post_nummer_status` enum('pending','running','complete','empty','resume','idle','failed','checked','queued','scraped') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_nummer_checked` tinyint(1) NOT NULL DEFAULT '0',
  `post_nummer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `post_nummer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `post_nummer_complete` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_nummer_queue_post_nummer_unique` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_nummer_sverige`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_nummer_sverige` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hitta_personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_personer_checked` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_foretag_checked` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_personer_saved` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_foretag_saved` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_personer_phone` int NOT NULL DEFAULT '0',
  `hitta_foretag_phone` int NOT NULL DEFAULT '0',
  `hitta_personer_house` int NOT NULL DEFAULT '0',
  `hitta_foretag_house` int NOT NULL DEFAULT '0',
  `hitta_personer_count` int NOT NULL DEFAULT '0',
  `hitta_foretag_count` int NOT NULL DEFAULT '0',
  `hitta_personer_total` int NOT NULL DEFAULT '0',
  `hitta_foretag_total` int NOT NULL DEFAULT '0',
  `hitta_personer_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hitta_foretag_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hitta_personer_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_foretag_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `hitta_personer_updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hitta_foretag_updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_checked` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_foretag_checked` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_saved` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_foretag_saved` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_phone` int NOT NULL DEFAULT '0',
  `merinfo_foretag_phone` int NOT NULL DEFAULT '0',
  `merinfo_personer_house` int NOT NULL DEFAULT '0',
  `merinfo_foretag_house` int NOT NULL DEFAULT '0',
  `merinfo_personer_count` int NOT NULL DEFAULT '0',
  `merinfo_foretag_count` int NOT NULL DEFAULT '0',
  `merinfo_personer_total` int NOT NULL DEFAULT '0',
  `merinfo_foretag_total` int NOT NULL DEFAULT '0',
  `merinfo_personer_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_foretag_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_personer_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_foretag_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `merinfo_personer_updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `merinfo_foretag_updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_personer_checked` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_foretag_checked` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_personer_saved` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_foretag_saved` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_personer_phone` int NOT NULL DEFAULT '0',
  `ratsit_foretag_phone` int NOT NULL DEFAULT '0',
  `ratsit_personer_house` int NOT NULL DEFAULT '0',
  `ratsit_foretag_house` int NOT NULL DEFAULT '0',
  `ratsit_personer_count` int NOT NULL DEFAULT '0',
  `ratsit_foretag_count` int NOT NULL DEFAULT '0',
  `ratsit_personer_total` int NOT NULL DEFAULT '0',
  `ratsit_foretag_total` int NOT NULL DEFAULT '0',
  `ratsit_personer_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_foretag_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_personer_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_foretag_is_active` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_personer_updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratsit_foretag_updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personer_pending` tinyint(1) NOT NULL DEFAULT '1',
  `foretag_pending` tinyint(1) NOT NULL DEFAULT '1',
  `personer_complete` tinyint(1) NOT NULL DEFAULT '1',
  `foretag_complete` tinyint(1) NOT NULL DEFAULT '1',
  `personer_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `private_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `private_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `forsamling` text COLLATE utf8mb4_unicode_ci,
  `kommun` text COLLATE utf8mb4_unicode_ci,
  `lan` text COLLATE utf8mb4_unicode_ci,
  `adressandring` text COLLATE utf8mb4_unicode_ci,
  `bo_gatuadress` text COLLATE utf8mb4_unicode_ci,
  `bo_postnummer` text COLLATE utf8mb4_unicode_ci,
  `bo_postort` text COLLATE utf8mb4_unicode_ci,
  `bo_forsamling` text COLLATE utf8mb4_unicode_ci,
  `bo_kommun` text COLLATE utf8mb4_unicode_ci,
  `bo_lan` text COLLATE utf8mb4_unicode_ci,
  `telfonnummer` json DEFAULT NULL,
  `telefon` json DEFAULT NULL,
  `stjarntacken` text COLLATE utf8mb4_unicode_ci,
  `fodelsedag` text COLLATE utf8mb4_unicode_ci,
  `personnummer` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `civilstand` text COLLATE utf8mb4_unicode_ci,
  `fornamn` text COLLATE utf8mb4_unicode_ci,
  `efternamn` text COLLATE utf8mb4_unicode_ci,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `ps_fodelsedag` text COLLATE utf8mb4_unicode_ci,
  `ps_personnummer` text COLLATE utf8mb4_unicode_ci,
  `ps_alder` text COLLATE utf8mb4_unicode_ci,
  `ps_kon` text COLLATE utf8mb4_unicode_ci,
  `ps_civilstand` text COLLATE utf8mb4_unicode_ci,
  `ps_fornamn` text COLLATE utf8mb4_unicode_ci,
  `ps_efternamn` text COLLATE utf8mb4_unicode_ci,
  `ps_personnamn` text COLLATE utf8mb4_unicode_ci,
  `ps_telefon` json DEFAULT NULL,
  `ps_epost_adress` json DEFAULT NULL,
  `ps_bolagsengagemang` json DEFAULT NULL,
  `agandeform` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `boarea` text COLLATE utf8mb4_unicode_ci,
  `byggar` text COLLATE utf8mb4_unicode_ci,
  `bo_agandeform` text COLLATE utf8mb4_unicode_ci,
  `bo_bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bo_boarea` text COLLATE utf8mb4_unicode_ci,
  `bo_byggar` text COLLATE utf8mb4_unicode_ci,
  `bo_fastighet` text COLLATE utf8mb4_unicode_ci,
  `fastighet` text COLLATE utf8mb4_unicode_ci,
  `personer` json DEFAULT NULL,
  `foretag` json DEFAULT NULL,
  `grannar` json DEFAULT NULL,
  `fordon` json DEFAULT NULL,
  `hundar` json DEFAULT NULL,
  `bolagsengagemang` json DEFAULT NULL,
  `epost_adress` json DEFAULT NULL,
  `bo_personer` int DEFAULT NULL,
  `bo_foretag` int DEFAULT NULL,
  `bo_grannar` json DEFAULT NULL,
  `bo_fordon` json DEFAULT NULL,
  `bo_hundar` json DEFAULT NULL,
  `longitude` text COLLATE utf8mb4_unicode_ci,
  `latitud` text COLLATE utf8mb4_unicode_ci,
  `google_maps` text COLLATE utf8mb4_unicode_ci,
  `google_streetview` text COLLATE utf8mb4_unicode_ci,
  `ratsit_link` text COLLATE utf8mb4_unicode_ci,
  `bo_longitude` text COLLATE utf8mb4_unicode_ci,
  `bo_latitud` text COLLATE utf8mb4_unicode_ci,
  `hitta_link` text COLLATE utf8mb4_unicode_ci,
  `hitta_karta` text COLLATE utf8mb4_unicode_ci,
  `bostad_typ` text COLLATE utf8mb4_unicode_ci,
  `bostad_pris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_update` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_adresser_sverige`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_adresser_sverige` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gatuadress_namn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gatuadress_count` int NOT NULL,
  `gatuadress_nummer_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `forsamling` text COLLATE utf8mb4_unicode_ci,
  `kommun` text COLLATE utf8mb4_unicode_ci,
  `lan` text COLLATE utf8mb4_unicode_ci,
  `adressandring` text COLLATE utf8mb4_unicode_ci,
  `telfonnummer` json DEFAULT NULL,
  `stjarntacken` text COLLATE utf8mb4_unicode_ci,
  `fodelsedag` text COLLATE utf8mb4_unicode_ci,
  `personnummer` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `civilstand` text COLLATE utf8mb4_unicode_ci,
  `fornamn` text COLLATE utf8mb4_unicode_ci,
  `efternamn` text COLLATE utf8mb4_unicode_ci,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `epost_adress` json DEFAULT NULL,
  `agandeform` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `boarea` text COLLATE utf8mb4_unicode_ci,
  `byggar` text COLLATE utf8mb4_unicode_ci,
  `fastighet` text COLLATE utf8mb4_unicode_ci,
  `personer` json DEFAULT NULL,
  `foretag` json DEFAULT NULL,
  `grannar` json DEFAULT NULL,
  `fordon` json DEFAULT NULL,
  `hundar` json DEFAULT NULL,
  `bolagsengagemang` json DEFAULT NULL,
  `longitude` text COLLATE utf8mb4_unicode_ci,
  `latitud` text COLLATE utf8mb4_unicode_ci,
  `google_maps` text COLLATE utf8mb4_unicode_ci,
  `google_streetview` text COLLATE utf8mb4_unicode_ci,
  `ratsit_se` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `kommun_ratsit` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_foretag_adresser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_foretag_adresser` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gatuadress_namn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_count` int NOT NULL,
  `ratsit_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratsit_foretag_adresser_post_ort_index` (`post_ort`),
  KEY `ratsit_foretag_adresser_post_nummer_index` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_foretag_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_foretag_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telfonnummer` json DEFAULT NULL,
  `telefon` json DEFAULT NULL,
  `epost_adress` json DEFAULT NULL,
  `longitude` text COLLATE utf8mb4_unicode_ci,
  `latitud` text COLLATE utf8mb4_unicode_ci,
  `google_maps` text COLLATE utf8mb4_unicode_ci,
  `google_streetview` text COLLATE utf8mb4_unicode_ci,
  `ratsit_se` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_foretag_kommuner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_foretag_kommuner` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kommun` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_count` int NOT NULL,
  `ratsit_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `foretag_postort_saved` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ratsit_foretag_kommuner_kommun_index` (`kommun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_foretag_postorter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_foretag_postorter` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer_count` int NOT NULL,
  `post_nummer_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratsit_foretag_postorter_post_ort_index` (`post_ort`),
  KEY `ratsit_foretag_postorter_post_nummer_index` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_foretag_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_foretag_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_phone` int NOT NULL DEFAULT '0',
  `foretag_house` int NOT NULL DEFAULT '0',
  `foretag_saved` int NOT NULL DEFAULT '0',
  `foretag_total` int NOT NULL DEFAULT '0',
  `foretag_page` int unsigned NOT NULL DEFAULT '0',
  `foretag_pages` int unsigned NOT NULL DEFAULT '0',
  `foretag_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `foretag_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_kommuner_sverige`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_kommuner_sverige` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kommun` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort_saved` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `ratsit_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratsit_kommuner_sverige_kommun_unique` (`kommun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_person_adresser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_person_adresser` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gatuadress_namn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_count` int NOT NULL,
  `ratsit_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratsit_person_adresser_post_ort_index` (`post_ort`),
  KEY `ratsit_person_adresser_post_nummer_index` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_person_kommuner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_person_kommuner` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kommun` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personer_count` int NOT NULL,
  `ratsit_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `post_ort_saved` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ratsit_person_kommuner_kommun_index` (`kommun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_person_postorter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_person_postorter` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer_count` int NOT NULL,
  `post_nummer_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratsit_person_postorter_post_ort_index` (`post_ort`),
  KEY `ratsit_person_postorter_post_nummer_index` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_personer_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_personer_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `forsamling` text COLLATE utf8mb4_unicode_ci,
  `kommun` text COLLATE utf8mb4_unicode_ci,
  `lan` text COLLATE utf8mb4_unicode_ci,
  `adressandring` text COLLATE utf8mb4_unicode_ci,
  `telfonnummer` json DEFAULT NULL,
  `stjarntacken` text COLLATE utf8mb4_unicode_ci,
  `fodelsedag` text COLLATE utf8mb4_unicode_ci,
  `personnummer` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `civilstand` text COLLATE utf8mb4_unicode_ci,
  `fornamn` text COLLATE utf8mb4_unicode_ci,
  `efternamn` text COLLATE utf8mb4_unicode_ci,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `telefon` json DEFAULT NULL,
  `epost_adress` json DEFAULT NULL,
  `agandeform` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `boarea` text COLLATE utf8mb4_unicode_ci,
  `byggar` text COLLATE utf8mb4_unicode_ci,
  `fastighet` text COLLATE utf8mb4_unicode_ci,
  `personer` json DEFAULT NULL,
  `foretag` json DEFAULT NULL,
  `grannar` json DEFAULT NULL,
  `fordon` json DEFAULT NULL,
  `hundar` json DEFAULT NULL,
  `bolagsengagemang` json DEFAULT NULL,
  `longitude` text COLLATE utf8mb4_unicode_ci,
  `latitud` text COLLATE utf8mb4_unicode_ci,
  `google_maps` text COLLATE utf8mb4_unicode_ci,
  `google_streetview` text COLLATE utf8mb4_unicode_ci,
  `ratsit_se` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `is_hus` tinyint(1) NOT NULL DEFAULT '0',
  `ratsit_personer_total` int NOT NULL DEFAULT '0',
  `ratsit_foretag_total` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_personer_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_personer_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personer_phone` int NOT NULL DEFAULT '0',
  `personer_house` int NOT NULL DEFAULT '0',
  `personer_saved` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `personer_page` int unsigned NOT NULL DEFAULT '0',
  `personer_pages` int unsigned NOT NULL DEFAULT '0',
  `personer_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `personer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_postorter_sverige`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_postorter_sverige` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_nummer_count` int NOT NULL DEFAULT '0',
  `post_nummer_link` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratsit_postorter_sverige_post_ort_index` (`post_ort`),
  KEY `ratsit_postorter_sverige_post_nummer_index` (`post_nummer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratsit_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratsit_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_nummer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_ort` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_lan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foretag_total` int NOT NULL DEFAULT '0',
  `personer_total` int NOT NULL DEFAULT '0',
  `foretag_phone` int NOT NULL DEFAULT '0',
  `personer_phone` int NOT NULL DEFAULT '0',
  `personer_house` int NOT NULL DEFAULT '0',
  `foretag_saved` int NOT NULL DEFAULT '0',
  `personer_saved` int NOT NULL DEFAULT '0',
  `personer_pages` int unsigned NOT NULL DEFAULT '0',
  `personer_page` int unsigned NOT NULL DEFAULT '0',
  `personer_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_status` enum('pending','running','complete','empty','resume','idle','failed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foretag_queued` tinyint(1) NOT NULL DEFAULT '0',
  `personer_queued` tinyint(1) NOT NULL DEFAULT '0',
  `foretag_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `personer_scraped` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `payload` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `taggables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taggables` (
  `tag_id` bigint unsigned NOT NULL,
  `taggable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taggable_id` bigint unsigned NOT NULL,
  UNIQUE KEY `taggables_tag_id_taggable_id_taggable_type_unique` (`tag_id`,`taggable_id`,`taggable_type`),
  KEY `taggables_taggable_type_taggable_id_index` (`taggable_type`,`taggable_id`),
  CONSTRAINT `taggables_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` json NOT NULL,
  `slug` json NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_column` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upplysning_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `upplysning_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnamn` text COLLATE utf8mb4_unicode_ci,
  `alder` text COLLATE utf8mb4_unicode_ci,
  `kon` text COLLATE utf8mb4_unicode_ci,
  `gatuadress` text COLLATE utf8mb4_unicode_ci,
  `postnummer` text COLLATE utf8mb4_unicode_ci,
  `postort` text COLLATE utf8mb4_unicode_ci,
  `telefon` text COLLATE utf8mb4_unicode_ci,
  `karta` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `bostadstyp` text COLLATE utf8mb4_unicode_ci,
  `bostadspris` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_telefon` tinyint(1) NOT NULL DEFAULT '0',
  `is_ratsit` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2025_11_15_200001_create_post_nummer_foretag_queue_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_11_15_200001_create_post_nummer_personer_queue_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_11_15_200001_create_post_nummer_queue_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_11_15_200001_create_post_nummer_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_11_15_200002_create_hitta_se_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_11_15_200003_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_11_15_200004_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_11_15_200005_create_private_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_11_15_200006_create_hitta_foretag_queue',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_11_15_200006_create_hitta_personer_queue',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_11_15_200006_create_hitta_queue_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_11_15_200006_create_merinfo_foretag_queue',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_11_15_200006_create_merinfo_personer_queue',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_11_15_200006_create_merinfo_queue_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_11_15_200006_create_ratsit_foretag_queue',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_11_15_200006_create_ratsit_personer_queue',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_11_15_200006_create_ratsit_queue_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_11_15_200007_create_merinfo_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_11_15_200007_create_merinfo_foretag_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_11_15_200007_create_merinfo_personer_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_11_15_200009_create_hitta_alla_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_11_15_200010_create_hitta_bolag_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_11_15_200011_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_11_15_200012_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_11_15_200013_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_11_15_200014_create_tag_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_11_15_200015_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_11_15_200030_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_11_15_200032_create_job_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_11_15_200033_create_imports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_11_15_200034_create_failed_import_rows_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_11_15_200035_create_exports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_11_15_200036_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_11_15_200038_create_ratsit_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_11_15_200038_create_ratsit_foretag_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_11_15_200038_create_ratsit_personer_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_11_15_200041_create_hitta_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_11_15_200041_create_hitta_foretag_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_11_15_200041_create_hitta_personer_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_11_15_200043_create_post_nummer_apis_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_11_15_200044_create_post_nummer_que_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_11_15_200046_create_eniro_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_11_15_200047_create_upplysning_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_11_15_200048_create_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_11_16_000339_create_api_docs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_11_16_013337_add_missing_columns_to_ratsit_personer_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_11_16_023003_modify_post_nummer_status_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_11_16_192901_create_post_nummer_sverige',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_11_16_212132_create_post_nummer_checks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_11_17_010137_add_last_livewire_update_to_post_nummer_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_11_17_014500_add_status_to_post_nummer_checks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_11_17_043920_add_name_to_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_11_17_054437_change_telefon_column_to_text_in_ratsit_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_11_17_062223_add_kommun_ratsit_column_to_ratsit_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_11_17_065607_add_is_hus_column_to_hitta_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_11_17_065614_add_is_hus_column_to_hitta_se_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_11_17_065738_update_jobs_name_default_value',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_11_17_074233_create_ratsit_kommuner_sverige_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_11_17_090421_create_personer_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_11_17_091515_create_ratsit_postorter_sverige_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_11_17_103218_modify_personer_data_timestamps',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_11_17_114428_create_ratsit_adresser_sverige_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_11_17_130030_create_ratsit_person_kommuner_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_11_17_130039_create_ratsit_person_postorter_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_11_17_130044_create_ratsit_person_adresser_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_11_17_130052_create_ratsit_foretag_kommuner_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_11_17_130106_create_ratsit_foretag_postorter_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_11_17_130125_create_ratsit_foretag_adresser_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_11_17_134640_add_person_postort_saved_to_ratsit_person_kommuner_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_11_17_134653_add_foretag_postort_saved_to_ratsit_foretag_kommuner_table',1);
