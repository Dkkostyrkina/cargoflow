Warning: A partial dump from a server that has GTIDs will by default include the GTIDs of all transactions, even those that changed suppressed parts of the database. If you don't want to restore GTIDs, pass --set-gtid-purged=OFF. To make a complete dump, pass --all-databases --triggers --routines --events. 
Warning: A dump from a server that has GTIDs enabled will by default include the GTIDs of all transactions, even those that were executed during its extraction and might not be represented in the dumped data. This might result in an inconsistent data dump. 
In order to ensure a consistent backup of the database, pass --single-transaction or --lock-all-tables or --source-data. 
-- MySQL dump 10.13  Distrib 9.6.0, for macos26.3 (arm64)
--
-- Host: localhost    Database: cargoflow
-- ------------------------------------------------------
-- Server version	9.6.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '070c11d8-41bf-11f1-9f78-fcf5d44af1dd:1-17';

--
-- Current Database: `cargoflow`
--

/*!40000 DROP DATABASE IF EXISTS `cargoflow`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `cargoflow` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `cargoflow`;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int unsigned NOT NULL,
  `country_from` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_from` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_to` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_to` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transport_type` enum('air','sea','road','rail','multi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sea',
  `cargo_type` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight_kg` decimal(12,2) DEFAULT NULL,
  `volume_cbm` decimal(12,3) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1=Новая, 2=В обработке, 3=Документы проверены, 4=В пути, 5=Завершена',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES (1,'2026-03-15 10:30:00','2026-04-27 01:27:58',1,'Китай','Шанхай','Россия','Москва','sea','Электроника',12500.00,48.000,'Контейнер 40HC, срочная доставка',4),(2,'2026-02-20 14:15:00','2026-04-27 01:27:58',1,'Турция','Стамбул','Россия','Новороссийск','sea','Текстиль',8200.00,32.000,'FCL, 2 контейнера 20ft',5),(3,'2026-04-01 09:00:00','2026-04-27 01:27:58',1,'Германия','Гамбург','Россия','Санкт-Петербург','road','Автозапчасти',3400.00,12.500,'Сборный груз, палетная загрузка',3),(4,'2026-04-10 16:45:00','2026-04-27 01:27:58',1,'Китай','Гуанчжоу','Россия','Владивосток','air','Медицинское оборудование',450.00,2.800,'Хрупкий груз, температурный режим',2),(5,'2026-04-25 11:20:00','2026-04-27 01:27:58',1,'Италия','Милан','Россия','Москва','road','Мебель',6800.00,45.000,'3 еврофуры',1);
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_app_status_change` BEFORE UPDATE ON `applications` FOR EACH ROW BEGIN
  IF OLD.status <> NEW.status THEN
    SET NEW.updated_at = NOW();
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `application_id` int unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_app` (`application_id`),
  CONSTRAINT `fk_doc_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,'2026-03-18 12:00:00',1,'Коносамент_B/L_MSKU123456.pdf','/docs/1.pdf',245000),(2,'2026-03-18 12:05:00',1,'Инвойс_INV-2026-0315.pdf','/docs/2.pdf',128000),(3,'2026-02-22 09:30:00',2,'CMR_TR-2026-0220.pdf','/docs/3.pdf',312000),(4,'2026-04-03 15:20:00',3,'ДТ_10702030_260401.pdf','/docs/4.pdf',198000),(5,'2026-04-03 15:25:00',3,'Сертификат_соответствия.pdf','/docs/5.pdf',87000);
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transport_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_description` text COLLATE utf8mb4_unicode_ci,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'web',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (1,'2026-04-20 10:00:00','Петров Сергей','petrov@mail.ru','Китай → Москва','sea','Электроника, 5 тонн','Нужна консультация по таможне','web'),(2,'2026-04-22 14:30:00','ООО \"ТехноИмпорт\"','info@technoimport.ru','Турция → Новороссийск','road','Строительные материалы, 20 тонн','Регулярные поставки','web'),(3,'2026-04-25 09:15:00','Козлова Анна','kozlova@gmail.com','Германия → СПб','air','Медикаменты, 200 кг','Срочно, температурный режим','web');
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `client_id` int unsigned NOT NULL,
  `lead_id` int unsigned DEFAULT NULL,
  `direction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transport_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  KEY `fk_orders_lead` (`lead_id`),
  CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('client','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `confirm_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirm_token_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  KEY `idx_confirm_token` (`confirm_token`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'2026-04-27 01:27:19','demo@cargoflow.ru','$2y$12$rLUrDPZkJbtVMWjKIW31keIl9V2RwLGJUPmLWH4i3yD9Ws/RTB6Eq','Иванов Алексей Петрович','ООО «ТрансЛогистик»','+7 (495) 123-45-67','client',0,NULL,NULL),(2,'2026-04-27 01:27:19','admin@cargoflow.ru','$2y$12$WId3e/Ei5oNN3r/COFslbOPg0xo8oEniEKdrp3bytEM2OL3pryXzS','Администратор','CargoFlow','+7 (495) 000-00-00','admin',0,NULL,NULL),(3,'2026-04-27 11:44:54','123@mail.ru','$2y$12$haCl8C1uHb/AgBg.D07XoOyAfDokWGKYZX2Lj4yjXv.lT6rqekI8G','123','123','12345','client',0,'71bb057e760ea4f5542deea0aa6395bfbf0bfcfe50d170c2970a28dad5126520','2026-04-27 11:44:54');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'cargoflow'
--
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-05 12:46:05
