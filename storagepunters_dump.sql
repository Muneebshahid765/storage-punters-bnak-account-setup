-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: storagepunters
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tbl_auction_categories`
--

DROP TABLE IF EXISTS `tbl_auction_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_categories`
--

LOCK TABLES `tbl_auction_categories` WRITE;
/*!40000 ALTER TABLE `tbl_auction_categories` DISABLE KEYS */;
INSERT INTO `tbl_auction_categories` VALUES (1,'General','2025-02-25 06:35:21','2025-02-25 06:35:21');
/*!40000 ALTER TABLE `tbl_auction_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_category_relationship`
--

DROP TABLE IF EXISTS `tbl_auction_category_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_category_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `auction_category_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `auction_category_id` (`auction_category_id`),
  CONSTRAINT `tbl_auction_category_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_category_relationship_ibfk_2` FOREIGN KEY (`auction_category_id`) REFERENCES `tbl_auction_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_category_relationship`
--

LOCK TABLES `tbl_auction_category_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_auction_category_relationship` DISABLE KEYS */;
INSERT INTO `tbl_auction_category_relationship` VALUES (1,4,1,'2025-12-22 14:36:19','2025-12-22 14:36:19'),(2,5,1,'2025-12-22 14:53:35','2025-12-22 14:53:35'),(3,6,1,'2025-12-22 23:39:08','2025-12-22 23:39:08');
/*!40000 ALTER TABLE `tbl_auction_category_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_confirmation_types`
--

DROP TABLE IF EXISTS `tbl_auction_confirmation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_confirmation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_confirmation_types`
--

LOCK TABLES `tbl_auction_confirmation_types` WRITE;
/*!40000 ALTER TABLE `tbl_auction_confirmation_types` DISABLE KEYS */;
INSERT INTO `tbl_auction_confirmation_types` VALUES (1,'Confirmed','2025-04-17 06:08:02','2025-04-17 06:08:02'),(2,'Declined','2025-04-17 06:08:02','2025-04-17 06:08:02');
/*!40000 ALTER TABLE `tbl_auction_confirmation_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_confirmations`
--

DROP TABLE IF EXISTS `tbl_auction_confirmations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_confirmations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `winner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `confirmation_type_id` int(11) NOT NULL,
  `confirmed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `winner_id` (`winner_id`),
  KEY `user_id` (`user_id`),
  KEY `tbl_auction_confirmations_ibfk_3` (`confirmation_type_id`),
  CONSTRAINT `tbl_auction_confirmations_ibfk_1` FOREIGN KEY (`winner_id`) REFERENCES `tbl_auction_won` (`id`),
  CONSTRAINT `tbl_auction_confirmations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_auction_confirmations_ibfk_3` FOREIGN KEY (`confirmation_type_id`) REFERENCES `tbl_auction_confirmation_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_confirmations`
--

LOCK TABLES `tbl_auction_confirmations` WRITE;
/*!40000 ALTER TABLE `tbl_auction_confirmations` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_confirmations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_favorites`
--

DROP TABLE IF EXISTS `tbl_auction_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_auction_favorites_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_favorites_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_favorites`
--

LOCK TABLES `tbl_auction_favorites` WRITE;
/*!40000 ALTER TABLE `tbl_auction_favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_locations`
--

DROP TABLE IF EXISTS `tbl_auction_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_locations` (
  `auction_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`auction_id`,`location_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `tbl_auction_locations_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_auction_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_locations`
--

LOCK TABLES `tbl_auction_locations` WRITE;
/*!40000 ALTER TABLE `tbl_auction_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_master_profiles`
--

DROP TABLE IF EXISTS `tbl_auction_master_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_master_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(255) NOT NULL,
  `tab1_data` json NOT NULL,
  `created_by_admin_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_master_profiles`
--

LOCK TABLES `tbl_auction_master_profiles` WRITE;
/*!40000 ALTER TABLE `tbl_auction_master_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_master_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_release_payment_schedule`
--

DROP TABLE IF EXISTS `tbl_auction_release_payment_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_release_payment_schedule` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auction_schedule_type_id` int(11) NOT NULL,
  `release_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_a` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `auction_id` (`auction_id`),
  KEY `user_id` (`user_id`),
  KEY `auction_schedule_type_id` (`auction_schedule_type_id`),
  CONSTRAINT `tbl_auction_release_payment_schedule_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_release_payment_schedule_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_auction_release_payment_schedule_ibfk_3` FOREIGN KEY (`auction_schedule_type_id`) REFERENCES `tbl_auction_schedule_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_release_payment_schedule`
--

LOCK TABLES `tbl_auction_release_payment_schedule` WRITE;
/*!40000 ALTER TABLE `tbl_auction_release_payment_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_release_payment_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_schedule_types`
--

DROP TABLE IF EXISTS `tbl_auction_schedule_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_schedule_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_schedule_types`
--

LOCK TABLES `tbl_auction_schedule_types` WRITE;
/*!40000 ALTER TABLE `tbl_auction_schedule_types` DISABLE KEYS */;
INSERT INTO `tbl_auction_schedule_types` VALUES (1,'Scheduled','2025-04-17 08:20:11','2025-04-17 08:20:11'),(2,'Released','2025-04-17 08:20:11','2025-04-17 08:20:11'),(3,'Failed','2025-04-17 08:20:24','2025-04-17 08:20:24'),(4,'Cancelled','2025-04-17 08:20:24','2025-04-17 08:20:24');
/*!40000 ALTER TABLE `tbl_auction_schedule_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_status_relationship`
--

DROP TABLE IF EXISTS `tbl_auction_status_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_status_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `auction_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `tbl_auction_status_relationship_ibfk_2` (`auction_type_id`),
  CONSTRAINT `tbl_auction_status_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_status_relationship_ibfk_2` FOREIGN KEY (`auction_type_id`) REFERENCES `tbl_auction_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_status_relationship`
--

LOCK TABLES `tbl_auction_status_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_auction_status_relationship` DISABLE KEYS */;
INSERT INTO `tbl_auction_status_relationship` VALUES (1,3,1,'2025-12-22 14:35:13','2025-12-22 14:35:13'),(2,4,1,'2025-12-22 14:36:19','2025-12-22 14:36:19'),(3,5,1,'2025-12-22 14:53:35','2025-12-22 14:53:35'),(4,1,1,'2025-12-22 15:21:50','2025-12-22 15:21:50'),(5,2,1,'2025-12-22 15:21:50','2025-12-22 15:21:50'),(6,6,1,'2025-12-22 23:39:08','2025-12-22 23:39:08');
/*!40000 ALTER TABLE `tbl_auction_status_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_storage_facility_relationship`
--

DROP TABLE IF EXISTS `tbl_auction_storage_facility_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_storage_facility_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `storage_facility_id` (`storage_facility_id`),
  CONSTRAINT `tbl_auction_storage_facility_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_storage_facility_relationship_ibfk_2` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_storage_facility_relationship`
--

LOCK TABLES `tbl_auction_storage_facility_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_auction_storage_facility_relationship` DISABLE KEYS */;
INSERT INTO `tbl_auction_storage_facility_relationship` VALUES (1,1,1,'2025-12-22 14:27:38','2025-12-22 14:27:38'),(2,2,1,'2025-12-22 14:28:12','2025-12-22 14:28:12'),(3,3,1,'2025-12-22 14:35:13','2025-12-22 14:35:13'),(4,4,1,'2025-12-22 14:36:19','2025-12-22 14:36:19'),(5,5,1,'2025-12-22 14:53:35','2025-12-22 14:53:35'),(6,6,1,'2025-12-22 23:39:08','2025-12-22 23:39:08');
/*!40000 ALTER TABLE `tbl_auction_storage_facility_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_types`
--

DROP TABLE IF EXISTS `tbl_auction_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_types`
--

LOCK TABLES `tbl_auction_types` WRITE;
/*!40000 ALTER TABLE `tbl_auction_types` DISABLE KEYS */;
INSERT INTO `tbl_auction_types` VALUES (1,'Live','2025-02-09 22:25:43','2025-02-09 22:25:43'),(2,'Paused','2025-02-09 22:25:43','2025-02-09 22:25:43'),(3,'Scheduled','2025-02-09 22:25:52','2025-02-09 22:25:52'),(4,'Closed','2025-02-09 22:25:52','2025-02-09 22:25:52'),(5,'Cancelled','2025-02-09 22:25:58','2025-02-09 22:25:58'),(6,'Draft','2025-02-09 22:26:12','2025-02-09 22:26:12'),(7,'Under Review','2025-02-09 22:26:12','2025-02-09 22:26:12'),(8,'Sold','2025-02-09 22:26:17','2025-02-09 22:26:17'),(9,'Won','2025-04-28 06:49:58','2025-04-28 06:49:58'),(10,'Expired','2025-05-01 08:13:53','2025-05-01 08:13:53');
/*!40000 ALTER TABLE `tbl_auction_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_upload_relationship`
--

DROP TABLE IF EXISTS `tbl_auction_upload_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_upload_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `upload_id` (`upload_id`),
  CONSTRAINT `tbl_auction_upload_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_upload_relationship_ibfk_2` FOREIGN KEY (`upload_id`) REFERENCES `tbl_uploads` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_upload_relationship`
--

LOCK TABLES `tbl_auction_upload_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_auction_upload_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_upload_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_views`
--

DROP TABLE IF EXISTS `tbl_auction_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_auction_views_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_views`
--

LOCK TABLES `tbl_auction_views` WRITE;
/*!40000 ALTER TABLE `tbl_auction_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auction_won`
--

DROP TABLE IF EXISTS `tbl_auction_won`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auction_won` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_auction_won_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_auction_won_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auction_won`
--

LOCK TABLES `tbl_auction_won` WRITE;
/*!40000 ALTER TABLE `tbl_auction_won` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auction_won` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auctions`
--

DROP TABLE IF EXISTS `tbl_auctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `unit_number` text NOT NULL,
  `disclose_unit_number_on_website` text NOT NULL,
  `outstanding_dept` decimal(15,2) NOT NULL,
  `starting_price` decimal(15,2) NOT NULL,
  `cleaning_deposit_price` decimal(15,2) DEFAULT '0.00',
  `cleaning_deposit_payment_type` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `start_time` text NOT NULL,
  `end_date` date NOT NULL,
  `end_time` text NOT NULL,
  `youtube_video_url` text NOT NULL,
  `unit_size` text NOT NULL,
  `tags` text NOT NULL,
  `additional_features` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_staged` tinyint(1) DEFAULT '0',
  `master_data_profile_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auctions`
--

LOCK TABLES `tbl_auctions` WRITE;
/*!40000 ALTER TABLE `tbl_auctions` DISABLE KEYS */;
INSERT INTO `tbl_auctions` VALUES (1,'Storage unit containing household items, furniture, and boxes. Unit has been abandoned for 90+ days.','A-123','Yes',500.00,100.00,0.00,NULL,'2025-12-20','09:00:00','2025-12-29','17:00:00','','10x10','Furniture,Boxes,Household','Climate controlled, Ground floor access','2025-12-22 14:27:38','2025-12-22 14:27:38',0,NULL),(2,'Storage unit containing household items, furniture, and boxes. Unit has been abandoned for 90+ days.','A-123','Yes',500.00,100.00,0.00,NULL,'2025-12-22','09:00:00','2025-12-29','17:00:00','','10x10','Furniture,Boxes,Household','Climate controlled, Ground floor access','2025-12-22 14:28:12','2025-12-22 14:28:12',0,NULL),(3,'Storage unit containing household items, furniture, and boxes. Unit has been abandoned for 90+ days.','A-123','Yes',500.00,100.00,0.00,NULL,'2025-12-22','09:00:00','2025-12-29','17:00:00','','10x10','Furniture,Boxes,Household','Climate controlled, Ground floor access','2025-12-22 14:35:13','2025-12-22 14:35:13',0,NULL),(4,'Storagess unit containing household items, furniture, and boxes. Unit has been abandoned for 90+ days.','A-123','Yes',500.00,100.00,0.00,NULL,'2025-12-20','09:00:00','2025-12-29','17:00:00','','10x10','Furniture,Boxes,Household','Climate controlled, Ground floor access','2025-12-22 14:36:19','2025-12-22 14:36:19',0,NULL),(5,'Sssstorage unit containing household items, furniture, and boxes. Unit has been abandoned for 90+ days.','A-123','Yes',500.00,100.00,0.00,NULL,'2025-12-21','09:00:00','2025-12-27','17:00:00','','10x10','Furniture,Boxes,Household','Climate controlled, Ground floor access','2025-12-22 14:53:35','2025-12-22 14:53:35',0,NULL),(6,'Storage unit containing electronics, tools, and personal belongings. Unit unpaid for over 60 days.','B-245','No',350.00,50.00,0.00,NULL,'2025-12-22','10:00:00','2025-12-25','18:00:00','','8x12','Electronics,Tools,Personal Items','24/7 access, Secure facility','2025-12-22 23:39:08','2025-12-22 23:39:08',0,NULL);
/*!40000 ALTER TABLE `tbl_auctions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_auctions_release_payment_schedule`
--

DROP TABLE IF EXISTS `tbl_auctions_release_payment_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_auctions_release_payment_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `invoice_amount` decimal(15,2) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `schedule_status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_auctions_release_payment_schedule`
--

LOCK TABLES `tbl_auctions_release_payment_schedule` WRITE;
/*!40000 ALTER TABLE `tbl_auctions_release_payment_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_auctions_release_payment_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_bank`
--

DROP TABLE IF EXISTS `tbl_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `stripe_bank_account_id` text NOT NULL,
  `account_name` text,
  `status_type_id` int(11) NOT NULL,
  `is_default` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_bank`
--

LOCK TABLES `tbl_bank` WRITE;
/*!40000 ALTER TABLE `tbl_bank` DISABLE KEYS */;
INSERT INTO `tbl_bank` VALUES (1,1,'cus_TmHa5yEKG8k4bn','acct_1Soj4r0eRIMzTgIX','Elizabeth Hampton',2,'false','2026-01-12 21:14:32','2026-01-12 21:14:32'),(2,1,'cus_TmHa5yEKG8k4bn','acct_1Soj971qzDzLV3yy','Nita Cox',2,'false','2026-01-12 21:18:56','2026-01-12 21:18:56'),(3,1,'cus_TmHa5yEKG8k4bn','acct_1SojGi0kUA7PD0wU','Nita Cox',2,'false','2026-01-12 21:26:47','2026-01-12 21:26:47'),(4,1,'cus_TmHa5yEKG8k4bn','acct_1SojIX1pWvqlg4jF','Nita Cox',2,'false','2026-01-12 21:28:40','2026-01-12 21:28:40'),(5,1,'cus_TmHa5yEKG8k4bn','acct_1SojK11qqYojtYqm','Nita Cox',2,'false','2026-01-12 21:30:13','2026-01-12 21:30:13'),(6,1,'cus_TmHa5yEKG8k4bn','acct_1Sol1a1DzK33oING','hassan',2,'false','2026-01-12 23:19:17','2026-01-12 23:19:17');
/*!40000 ALTER TABLE `tbl_bank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_bidder_subscription_plans`
--

DROP TABLE IF EXISTS `tbl_bidder_subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_bidder_subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `fee_reduction` decimal(5,2) NOT NULL,
  `price_28_days` decimal(10,2) NOT NULL,
  `price_year` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_bidder_subscription_plans`
--

LOCK TABLES `tbl_bidder_subscription_plans` WRITE;
/*!40000 ALTER TABLE `tbl_bidder_subscription_plans` DISABLE KEYS */;
INSERT INTO `tbl_bidder_subscription_plans` VALUES (1,'Adara Martinez',100.00,104.00,370.00,'2025-12-23 12:59:54','2025-12-23 12:59:54');
/*!40000 ALTER TABLE `tbl_bidder_subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_bids`
--

DROP TABLE IF EXISTS `tbl_bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_bids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bid_amount` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_bids_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_bids_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_bids`
--

LOCK TABLES `tbl_bids` WRITE;
/*!40000 ALTER TABLE `tbl_bids` DISABLE KEYS */;
INSERT INTO `tbl_bids` VALUES (1,6,17,500.00,'2026-01-14 05:35:23','2026-01-14 05:35:23'),(2,6,17,500.00,'2026-01-14 05:35:38','2026-01-14 05:35:38'),(3,6,17,500.00,'2026-01-14 05:36:55','2026-01-14 05:36:55'),(4,6,17,500.00,'2026-01-14 05:37:10','2026-01-14 05:37:10');
/*!40000 ALTER TABLE `tbl_bids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_coin_rules`
--

DROP TABLE IF EXISTS `tbl_coin_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_coin_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coins_per_bid` int(11) DEFAULT '10',
  `coins_per_dollar_bidded` int(11) DEFAULT '0',
  `coins_on_auction_win` int(11) DEFAULT '150',
  `coins_per_dollar_won` int(11) DEFAULT '0',
  `coins_on_subscription_renewal` int(11) DEFAULT '500',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_coin_rules`
--

LOCK TABLES `tbl_coin_rules` WRITE;
/*!40000 ALTER TABLE `tbl_coin_rules` DISABLE KEYS */;
INSERT INTO `tbl_coin_rules` VALUES (1,10,0,150,0,500,'2025-12-24 01:08:12','2025-12-24 01:08:12'),(2,10,0,150,0,500,'2026-01-13 21:36:53','2026-01-13 21:36:53');
/*!40000 ALTER TABLE `tbl_coin_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_coin_transactions`
--

DROP TABLE IF EXISTS `tbl_coin_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_coin_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('bid_placed','auction_won','subscription_renewal','coin_spent_entries','admin_adjustment') NOT NULL,
  `coins_delta` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_coin_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_coin_transactions`
--

LOCK TABLES `tbl_coin_transactions` WRITE;
/*!40000 ALTER TABLE `tbl_coin_transactions` DISABLE KEYS */;
INSERT INTO `tbl_coin_transactions` VALUES (1,6,'coin_spent_entries',-100,'package','1','Purchased 1 entries','2025-12-24 00:22:44'),(2,6,'coin_spent_entries',-100,'package','1','Purchased 1 entries','2025-12-24 23:14:50'),(3,6,'coin_spent_entries',-450,'package','2','Purchased 5 entries','2025-12-24 23:14:53'),(4,6,'coin_spent_entries',-1000,'package','3','Purchased 12 entries','2025-12-24 23:14:55'),(5,1,'coin_spent_entries',-450,'vault_draw_id','4','Purchased 5 Entries','2026-01-13 21:54:46'),(6,1,'coin_spent_entries',-400,'vault_draw_id','4','Purchased 5 Entries','2026-01-13 21:54:49'),(7,17,'subscription_renewal',500,'subscription_id','sub_1SpGZV08JMAyNQJTxbISWG20','Monthly Renewal Bonus (whale)','2026-01-14 09:28:03'),(8,17,'subscription_renewal',500,'subscription_id','sub_1SpGZV08JMAyNQJTxbISWG20','Monthly Renewal Bonus (whale)','2026-01-14 10:21:25'),(9,17,'bid_placed',10,'auction_id','3','Bid placed on Auction #3','2026-01-14 00:25:16'),(10,17,'auction_won',150,'auction_id','3','Won Auction #3','2026-01-14 00:25:29'),(11,17,'coin_spent_entries',-100,'vault_draw_id','6','Purchased 1 Entries','2026-01-14 05:20:05'),(12,17,'bid_placed',10,'auction_id','6','Bid placed on Auction #6 (Test)','2026-01-14 05:33:14'),(13,17,'bid_placed',10,'auction_id','6','Bid placed on Auction #6 (Test)','2026-01-14 05:35:23'),(14,17,'bid_placed',10,'auction_id','6','Bid placed on Auction #6 (Test)','2026-01-14 05:35:38'),(15,17,'bid_placed',10,'auction_id','6','Bid placed on Auction #6 (Test)','2026-01-14 05:36:55'),(16,17,'bid_placed',10,'auction_id','6','Bid placed on Auction #6 (Test)','2026-01-14 05:37:10'),(17,17,'coin_spent_entries',-100,'vault_draw_id','6','Purchased 1 Entries','2026-01-14 05:37:57'),(18,17,'coin_spent_entries',-450,'vault_draw_id','6','Purchased 5 Entries','2026-01-14 05:38:08'),(19,17,'subscription_renewal',500,'subscription_id','sub_1SpGZV08JMAyNQJTxbISWG20','Monthly Renewal Bonus (whale)','2026-01-14 15:39:10'),(20,17,'subscription_renewal',500,'subscription_id','sub_1Qzkiv08JMAyNQJT','Monthly Renewal Bonus (whale)','2026-01-14 15:48:45'),(21,17,'subscription_renewal',500,'subscription_id','sub_1Qzkiv08JMAyNQJT','Monthly Renewal Bonus (whale)','2026-01-14 15:49:20');
/*!40000 ALTER TABLE `tbl_coin_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_companies`
--

DROP TABLE IF EXISTS `tbl_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `website` text NOT NULL,
  `email` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_companies`
--

LOCK TABLES `tbl_companies` WRITE;
/*!40000 ALTER TABLE `tbl_companies` DISABLE KEYS */;
INSERT INTO `tbl_companies` VALUES (4,'Mammoth Storage','https://storagepunters.com/','hello@mammothstorage.com.au','2025-05-07 19:36:46','2025-05-08 05:53:11'),(5,'Oliver and Beard Plc','https://bergmediadigital.com/','bilogu@mailinator.com','2025-12-22 19:08:39','2025-12-22 19:08:39'),(6,'Mammoth Storage Company','https://mammothstorage.com.au','contact@mammothstorage.com.au','2025-12-22 14:50:06','2025-12-22 14:50:06');
/*!40000 ALTER TABLE `tbl_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_company_auction_settings`
--

DROP TABLE IF EXISTS `tbl_company_auction_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_company_auction_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `estimate_best_photo` tinyint(1) DEFAULT '0',
  `starting_price_percentage` int(11) DEFAULT '0',
  `display_debt_value` tinyint(1) DEFAULT '0',
  `default_auction_duration` int(11) DEFAULT '7',
  `start_immediately` tinyint(1) DEFAULT '0',
  `cleaning_deposit_amount` decimal(10,2) DEFAULT '0.00',
  `has_no_deposit` tinyint(1) DEFAULT '0',
  `default_terms_conditions` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_company_auction_settings`
--

LOCK TABLES `tbl_company_auction_settings` WRITE;
/*!40000 ALTER TABLE `tbl_company_auction_settings` DISABLE KEYS */;
INSERT INTO `tbl_company_auction_settings` VALUES (1,5,1,23,0,7,0,0.00,0,'','2026-01-12 15:54:18','2026-01-12 15:54:18');
/*!40000 ALTER TABLE `tbl_company_auction_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_company_location_relationship`
--

DROP TABLE IF EXISTS `tbl_company_location_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_company_location_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `tbl_company_location_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  CONSTRAINT `tbl_company_location_relationship_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_company_location_relationship`
--

LOCK TABLES `tbl_company_location_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_company_location_relationship` DISABLE KEYS */;
INSERT INTO `tbl_company_location_relationship` VALUES (4,4,7,'2025-05-07 19:36:46','2025-05-07 19:36:46'),(5,5,9,'2025-12-22 19:08:39','2025-12-22 19:08:39');
/*!40000 ALTER TABLE `tbl_company_location_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_company_status_relationship`
--

DROP TABLE IF EXISTS `tbl_company_status_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_company_status_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `status_id` (`status_type_id`),
  CONSTRAINT `tbl_company_status_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  CONSTRAINT `tbl_company_status_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_company_status_relationship`
--

LOCK TABLES `tbl_company_status_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_company_status_relationship` DISABLE KEYS */;
INSERT INTO `tbl_company_status_relationship` VALUES (4,4,1,'2025-05-07 19:36:46','2025-05-07 19:36:46'),(5,5,1,'2025-12-22 19:08:39','2025-12-22 19:08:39');
/*!40000 ALTER TABLE `tbl_company_status_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_company_verfication_relationship`
--

DROP TABLE IF EXISTS `tbl_company_verfication_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_company_verfication_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `verfication_type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `verfication_type_id` (`verfication_type_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `tbl_company_verfication_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  CONSTRAINT `tbl_company_verfication_relationship_ibfk_2` FOREIGN KEY (`verfication_type_id`) REFERENCES `tbl_verfication_types` (`id`),
  CONSTRAINT `tbl_company_verfication_relationship_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_company_verfication_relationship`
--

LOCK TABLES `tbl_company_verfication_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_company_verfication_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_company_verfication_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_email_queue`
--

DROP TABLE IF EXISTS `tbl_email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_email_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `to_email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `bank_id` int(11) DEFAULT NULL,
  `onboarding_url` text COLLATE utf8mb4_unicode_ci,
  `auction_id` int(11) DEFAULT NULL,
  `auction_item` text COLLATE utf8mb4_unicode_ci,
  `auction_amount` decimal(15,2) DEFAULT NULL,
  `auction_total` decimal(15,2) DEFAULT NULL,
  `auction_url` text COLLATE utf8mb4_unicode_ci,
  `attachment` text COLLATE utf8mb4_unicode_ci,
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','processing','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_scheduled_at` (`status`,`scheduled_at`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_email_queue`
--

LOCK TABLES `tbl_email_queue` WRITE;
/*!40000 ALTER TABLE `tbl_email_queue` DISABLE KEYS */;
INSERT INTO `tbl_email_queue` VALUES (1,'tobias_test1@mammothstorage.com.au','Your Payment Account Is Pending - Please Complete Verification','connected_account_inactive_verification_email','Tobias Constantinides',1,5,'https://connect.stripe.com/setup/e/acct_1SojK11qqYojtYqm/apt3dgDJplAe',NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-12 21:30:13','sent','','2026-01-12 21:30:13','2026-01-14 10:07:16'),(2,'tobias_test1@mammothstorage.com.au','Your Payment Account Is Pending - Please Complete Verification','connected_account_inactive_verification_email','Tobias Constantinides',1,6,'https://connect.stripe.com/setup/e/acct_1Sol1a1DzK33oING/jNNdHsszI9wn',NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-12 23:19:17','sent','','2026-01-12 23:19:17','2026-01-14 10:07:16'),(3,'tobias_test1@mammothstorage.com.au','? Winner Announced: asdsd','vault_winner_announcement','Tobias',1,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(4,'specialemailid2050@gmail.com','? Winner Announced: asdsd','vault_winner_announcement','Ali',2,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(5,'andoroid.mobile2050@gmail.com','? Winner Announced: asdsd','vault_winner_announcement','Ali',3,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(6,'simatrixsolution@gmail.com','? Winner Announced: asdsd','vault_winner_announcement','Hassan',4,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(7,'staff@storagepunters.com','? Winner Announced: asdsd','vault_winner_announcement','Staff',5,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(8,'admin@storagepunters.com','? Winner Announced: asdsd','vault_winner_announcement','Admin',6,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(9,'nuvabimu@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Asdasd',7,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(10,'xato@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Rajah',8,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(11,'tyqimoxe@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Laurel',9,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(12,'dyweqoryco@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Zeph',10,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(13,'xoqys@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Quyn',11,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(14,'gogujuver@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Armando',12,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(15,'wirezyr@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Veronica',13,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(16,'wihenytak@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','McKenzie',14,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(17,'hello@jayx.com','? Winner Announced: asdsd','vault_winner_announcement','JayX',16,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(18,'hello@jayxd.com','? Winner Announced: asdsd','vault_winner_announcement','Jay',17,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 00:07:02','sent','','2026-01-14 00:07:02','2026-01-14 10:07:16'),(19,'tobias_test1@mammothstorage.com.au','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Tobias',1,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(20,'specialemailid2050@gmail.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Ali',2,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(21,'andoroid.mobile2050@gmail.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Ali',3,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(22,'simatrixsolution@gmail.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Hassan',4,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(23,'staff@storagepunters.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Staff',5,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(24,'admin@storagepunters.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Admin',6,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(25,'nuvabimu@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Asdasd',7,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(26,'xato@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Rajah',8,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(27,'tyqimoxe@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Laurel',9,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(28,'dyweqoryco@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Zeph',10,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(29,'xoqys@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Quyn',11,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(30,'gogujuver@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Armando',12,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(31,'wirezyr@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Veronica',13,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(32,'wihenytak@mailinator.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','McKenzie',14,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(33,'hello@jayx.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','JayX',16,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(34,'hello@jayxd.com','⏰ 1 Hour Left: asdsd','vault_drop_reminder','Jay',17,NULL,NULL,4,'asdsd',0.00,0.00,'https://storagepunters.com/vault',NULL,'2026-01-14 10:14:32','sent','','2026-01-14 10:14:32','2026-01-14 10:14:50'),(35,'tobias_test1@mammothstorage.com.au','? Winner Announced: asdsd','vault_winner_announcement','Tobias',1,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(36,'specialemailid2050@gmail.com','? Winner Announced: asdsd','vault_winner_announcement','Ali',2,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(37,'andoroid.mobile2050@gmail.com','? Winner Announced: asdsd','vault_winner_announcement','Ali',3,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(38,'simatrixsolution@gmail.com','? Winner Announced: asdsd','vault_winner_announcement','Hassan',4,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(39,'staff@storagepunters.com','? Winner Announced: asdsd','vault_winner_announcement','Staff',5,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(40,'admin@storagepunters.com','? Winner Announced: asdsd','vault_winner_announcement','Admin',6,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(41,'nuvabimu@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Asdasd',7,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(42,'xato@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Rajah',8,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(43,'tyqimoxe@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Laurel',9,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(44,'dyweqoryco@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Zeph',10,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(45,'xoqys@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Quyn',11,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(46,'gogujuver@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Armando',12,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(47,'wirezyr@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','Veronica',13,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(48,'wihenytak@mailinator.com','? Winner Announced: asdsd','vault_winner_announcement','McKenzie',14,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(49,'hello@jayx.com','? Winner Announced: asdsd','vault_winner_announcement','JayX',16,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(50,'hello@jayxd.com','? Winner Announced: asdsd','vault_winner_announcement','Jay',17,NULL,NULL,4,'JAY X SP-0017',0.00,0.00,'SP-0017',NULL,'2026-01-14 01:08:45','pending','','2026-01-14 01:08:45','2026-01-14 01:08:45'),(51,'hello@jayxd.com','Receipt for Your Vault Purchase','vault_purchase_receipt','Jay',17,NULL,NULL,0,'Single Entry',2.25,1.00,'VLT-17B502E2',NULL,'2026-01-14 05:19:51','pending','','2026-01-14 05:19:51','2026-01-14 05:19:51'),(52,'hello@jayxd.com','Receipt for Your Vault Purchase','vault_purchase_receipt','Jay',17,NULL,NULL,0,'Single Entry',2.25,1.00,'VLT-6EF845DF',NULL,'2026-01-14 05:38:29','pending','','2026-01-14 05:38:29','2026-01-14 05:38:29'),(53,'hello@jayxd.com','Receipt for Your Vault Purchase','vault_purchase_receipt','Jay',17,NULL,NULL,0,'Entry Bundle',20.00,12.00,'VLT-41593B58',NULL,'2026-01-14 05:38:45','pending','','2026-01-14 05:38:45','2026-01-14 05:38:45');
/*!40000 ALTER TABLE `tbl_email_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_entry_shop_cash_packages`
--

DROP TABLE IF EXISTS `tbl_entry_shop_cash_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_entry_shop_cash_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `entry_count` int(11) NOT NULL,
  `bonus_entries` int(11) DEFAULT '0',
  `price_cents` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_entry_shop_cash_packages`
--

LOCK TABLES `tbl_entry_shop_cash_packages` WRITE;
/*!40000 ALTER TABLE `tbl_entry_shop_cash_packages` DISABLE KEYS */;
INSERT INTO `tbl_entry_shop_cash_packages` VALUES (4,'Single Entry',1,0,900,1,'2026-01-13 21:36:53','2026-01-13 21:36:53'),(5,'Entry Bundle',10,2,8000,1,'2026-01-13 21:36:53','2026-01-13 21:36:53'),(6,'Super Pack',50,15,35000,1,'2026-01-13 21:36:53','2026-01-13 21:36:53');
/*!40000 ALTER TABLE `tbl_entry_shop_cash_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_entry_shop_coin_packages`
--

DROP TABLE IF EXISTS `tbl_entry_shop_coin_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_entry_shop_coin_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `entry_count` int(11) NOT NULL,
  `bonus_entries` int(11) DEFAULT '0',
  `coin_price` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_entry_shop_coin_packages`
--

LOCK TABLES `tbl_entry_shop_coin_packages` WRITE;
/*!40000 ALTER TABLE `tbl_entry_shop_coin_packages` DISABLE KEYS */;
INSERT INTO `tbl_entry_shop_coin_packages` VALUES (1,'Starter Entry',1,0,100,1,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(2,'Value entries',5,0,450,1,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(3,'Heavy Hitter',12,0,1000,1,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(4,'Starter Entries',5,0,400,1,'2026-01-13 21:36:53','2026-01-13 21:36:53'),(5,'Pro Bundle',15,2,1000,1,'2026-01-13 21:36:53','2026-01-13 21:36:53'),(6,'Whale Pack',50,10,3000,1,'2026-01-13 21:36:53','2026-01-13 21:36:53');
/*!40000 ALTER TABLE `tbl_entry_shop_coin_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_facility_plan_features`
--

DROP TABLE IF EXISTS `tbl_facility_plan_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_facility_plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_facility_plan_features`
--

LOCK TABLES `tbl_facility_plan_features` WRITE;
/*!40000 ALTER TABLE `tbl_facility_plan_features` DISABLE KEYS */;
INSERT INTO `tbl_facility_plan_features` VALUES (1,3,'Advanced AI Auction creation process','2026-01-13 19:14:40'),(2,3,'AI Estimate value (based on images)','2026-01-13 19:14:40'),(3,3,'Run local ads for the duration of my auction','2026-01-13 19:14:40');
/*!40000 ALTER TABLE `tbl_facility_plan_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_facility_subscription_plans`
--

DROP TABLE IF EXISTS `tbl_facility_subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_facility_subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL,
  `annual_price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_facility_subscription_plans`
--

LOCK TABLES `tbl_facility_subscription_plans` WRITE;
/*!40000 ALTER TABLE `tbl_facility_subscription_plans` DISABLE KEYS */;
INSERT INTO `tbl_facility_subscription_plans` VALUES (1,'Preston Vinson',668.00,0.00,'2025-12-23 13:00:21','2026-01-10 09:48:30'),(2,'Daphne Mcdaniel',578.00,1000.00,'2025-12-23 13:00:49','2026-01-10 09:48:05'),(3,'Katelyn Murphy',305.00,1500.00,'2026-01-13 19:14:40','2026-01-13 19:14:40');
/*!40000 ALTER TABLE `tbl_facility_subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_facility_subscriptions`
--

DROP TABLE IF EXISTS `tbl_facility_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_facility_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `cycle` enum('monthly','annual') NOT NULL DEFAULT 'monthly',
  `status` enum('paid','due','pending','cancelled') NOT NULL DEFAULT 'pending',
  `next_billing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_facility_subscriptions`
--

LOCK TABLES `tbl_facility_subscriptions` WRITE;
/*!40000 ALTER TABLE `tbl_facility_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_facility_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_global_settings`
--

DROP TABLE IF EXISTS `tbl_global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_global_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_global_settings`
--

LOCK TABLES `tbl_global_settings` WRITE;
/*!40000 ALTER TABLE `tbl_global_settings` DISABLE KEYS */;
INSERT INTO `tbl_global_settings` VALUES (1,'coins_per_bid','10',NULL),(2,'coins_per_win','150',NULL),(3,'coins_renewal_bonus','500',NULL);
/*!40000 ALTER TABLE `tbl_global_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_invoice_auction_relationship`
--

DROP TABLE IF EXISTS `tbl_invoice_auction_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_invoice_auction_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `invoice_type_id` int(11) NOT NULL,
  `invoice_payment_method_type_id` int(11) NOT NULL,
  `invoice_payment_status_type_id` int(11) NOT NULL,
  `auction_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `auction_id` (`auction_id`),
  KEY `invoice_payment_method_type_id` (`invoice_payment_method_type_id`),
  KEY `invoice_type_id` (`invoice_type_id`),
  KEY `invoice_payment_status_type_id` (`invoice_payment_status_type_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_invoice_auction_relationship_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`id`),
  CONSTRAINT `tbl_invoice_auction_relationship_ibfk_2` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  CONSTRAINT `tbl_invoice_auction_relationship_ibfk_3` FOREIGN KEY (`invoice_payment_method_type_id`) REFERENCES `tbl_invoice_payment_method_types` (`id`),
  CONSTRAINT `tbl_invoice_auction_relationship_ibfk_4` FOREIGN KEY (`invoice_type_id`) REFERENCES `tbl_invoice_types` (`id`),
  CONSTRAINT `tbl_invoice_auction_relationship_ibfk_5` FOREIGN KEY (`invoice_payment_status_type_id`) REFERENCES `tbl_payment_types` (`id`),
  CONSTRAINT `tbl_invoice_auction_relationship_ibfk_6` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_invoice_auction_relationship`
--

LOCK TABLES `tbl_invoice_auction_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_invoice_auction_relationship` DISABLE KEYS */;
INSERT INTO `tbl_invoice_auction_relationship` VALUES (3,3,3,2,2,NULL,17,'2026-01-14 05:19:51','2026-01-14 05:19:51'),(4,4,3,2,2,NULL,17,'2026-01-14 05:38:29','2026-01-14 05:38:29'),(5,5,3,2,2,NULL,17,'2026-01-14 05:38:45','2026-01-14 05:38:45');
/*!40000 ALTER TABLE `tbl_invoice_auction_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_invoice_payment_method_types`
--

DROP TABLE IF EXISTS `tbl_invoice_payment_method_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_invoice_payment_method_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_invoice_payment_method_types`
--

LOCK TABLES `tbl_invoice_payment_method_types` WRITE;
/*!40000 ALTER TABLE `tbl_invoice_payment_method_types` DISABLE KEYS */;
INSERT INTO `tbl_invoice_payment_method_types` VALUES (1,'Cash','Payment made using physical currency.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(2,'Credit Card','Payment made using a credit card.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(3,'Debit Card','Payment made using a debit card.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(4,'Bank Transfer','Direct payment via bank transfer.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(5,'PayPal','Online payment through PayPal.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(6,'Cryptocurrency','Payment made using Bitcoin, Ethereum, or other cryptocurrencies.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(7,'Google Pay','Digital payment via Google Pay.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(8,'Apple Pay','Digital payment via Apple Pay.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(9,'Cheque','Payment made using a bank cheque.','2025-03-18 22:24:51','2025-03-18 22:24:51'),(10,'Wire Transfer','Electronic funds transfer between banks.','2025-03-18 22:24:51','2025-03-18 22:24:51');
/*!40000 ALTER TABLE `tbl_invoice_payment_method_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_invoice_stripe_logs_relationship`
--

DROP TABLE IF EXISTS `tbl_invoice_stripe_logs_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_invoice_stripe_logs_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stripe_event_id` text NOT NULL,
  `stripe_payment_intent_id` text NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `stripe_charge_id` text NOT NULL,
  `amount_received` text NOT NULL,
  `currency` text NOT NULL,
  `payment_status` text NOT NULL,
  `event_type` text NOT NULL,
  `payment_at` datetime NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `tbl_invoice_stripe_logs_relationship_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_invoice_stripe_logs_relationship`
--

LOCK TABLES `tbl_invoice_stripe_logs_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_invoice_stripe_logs_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_invoice_stripe_logs_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_invoice_stripe_relationship`
--

DROP TABLE IF EXISTS `tbl_invoice_stripe_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_invoice_stripe_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `stripe_invoice_id` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `tbl_invoice_stripe_relationship_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_invoice_stripe_relationship`
--

LOCK TABLES `tbl_invoice_stripe_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_invoice_stripe_relationship` DISABLE KEYS */;
INSERT INTO `tbl_invoice_stripe_relationship` VALUES (2,3,'pi_3SpMUk08JMAyNQJT0Zs9wWHJ','2026-01-14 05:19:51','2026-01-14 05:19:51'),(3,4,'pi_3SpMmn08JMAyNQJT0Kn9zplF','2026-01-14 05:38:29','2026-01-14 05:38:29'),(4,5,'pi_3SpMn208JMAyNQJT1cWJyMef','2026-01-14 05:38:45','2026-01-14 05:38:45');
/*!40000 ALTER TABLE `tbl_invoice_stripe_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_invoice_types`
--

DROP TABLE IF EXISTS `tbl_invoice_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_invoice_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_invoice_types`
--

LOCK TABLES `tbl_invoice_types` WRITE;
/*!40000 ALTER TABLE `tbl_invoice_types` DISABLE KEYS */;
INSERT INTO `tbl_invoice_types` VALUES (1,'Draft','Invoice is created but not yet submitted.','2025-03-18 22:31:30','2025-03-18 22:31:30'),(2,'Submitted','Invoice has been submitted for review.','2025-03-18 22:31:30','2025-03-18 22:31:30'),(3,'Approved','Invoice has been reviewed and approved.','2025-03-18 22:31:30','2025-03-18 22:31:30'),(4,'Pending','Invoice is awaiting payment.','2025-03-18 22:31:30','2025-03-18 22:31:30'),(5,'Cancelled','Invoice has been canceled.','2025-03-18 22:31:30','2025-03-18 22:31:30'),(6,'Voided','Invoice has been invalidated and holds no financial impact.','2025-03-18 22:31:30','2025-03-18 22:31:30'),(7,'Disputed','Invoice is under dispute and being reviewed.','2025-03-18 22:31:30','2025-03-18 22:31:30');
/*!40000 ALTER TABLE `tbl_invoice_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_invoices`
--

DROP TABLE IF EXISTS `tbl_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` text NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_invoices`
--

LOCK TABLES `tbl_invoices` WRITE;
/*!40000 ALTER TABLE `tbl_invoices` DISABLE KEYS */;
INSERT INTO `tbl_invoices` VALUES (3,'VLT-17B502E2',2.25,0.20,2.25,'AUD','2026-01-14','2026-01-14','2026-01-14','2026-01-14 05:19:51','2026-01-14 05:19:51'),(4,'VLT-6EF845DF',2.25,0.20,2.25,'AUD','2026-01-14','2026-01-14','2026-01-14','2026-01-14 05:38:29','2026-01-14 05:38:29'),(5,'VLT-41593B58',20.00,1.82,20.00,'AUD','2026-01-14','2026-01-14','2026-01-14','2026-01-14 05:38:45','2026-01-14 05:38:45');
/*!40000 ALTER TABLE `tbl_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_locations`
--

DROP TABLE IF EXISTS `tbl_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `street_address` text NOT NULL,
  `apt_suite` text NOT NULL,
  `city` text NOT NULL,
  `postal_code` text NOT NULL,
  `state` text NOT NULL,
  `country` text NOT NULL,
  `phone` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_locations`
--

LOCK TABLES `tbl_locations` WRITE;
/*!40000 ALTER TABLE `tbl_locations` DISABLE KEYS */;
INSERT INTO `tbl_locations` VALUES (1,'380 Mons Road','','Forest Glen','4556','Queensland','Australia','0431736798','2025-05-07 17:22:33','2025-05-07 17:22:33'),(2,'Al Hamra','1','Sydney','12345','Queensland','Australia','1212','2025-05-07 18:42:36','2025-05-07 19:08:20'),(3,'Al Hamra','1','Sydney','123456','Queensland','Australia','1212','2025-05-07 19:20:29','2025-05-07 19:22:08'),(4,'al hamra','1','Sydney','123456','Queensland','Australia','1212111','2025-05-07 19:26:16','2025-05-07 19:26:16'),(5,'al hamra','1','Sydney','123456','Queensland','Australia','1212111','2025-05-07 19:26:25','2025-05-07 19:26:25'),(6,'al hamra','1','Sydney','123456','Queensland','Australia','1212111','2025-05-07 19:34:19','2025-05-07 19:34:19'),(7,'al hamra','1','Sydney','123456','Queensland','Australia','1212111','2025-05-07 19:36:46','2025-05-08 05:53:11'),(8,'Shop 14 /380 Mons Road, Forest Glen QLD 4556','1','Sydney','123456','Queensland','Australia','1300 136 851','2025-05-07 20:03:20','2025-05-07 20:12:18'),(9,'Beatae omnis qui in','','In nihil id maiores','95777','Victoria','Australia','4234354545','2025-12-22 19:08:39','2025-12-22 19:08:39'),(10,'123 Staff Street','Suite 1','Brisbane','4000','Queensland','Australia','0400000000','2025-12-22 14:15:44','2025-12-22 14:15:44'),(11,'123 Storage Street','Unit 1','Brisbane','4000','Queensland','Australia','1300 123 456','2025-12-22 14:40:09','2025-12-22 14:40:09'),(12,'Vitae in rem archite','Nesciunt corporis a','Occaecat qui placeat','15585','Queensland','Australia','+1 (735) 837-6667','2025-12-24 21:45:39','2025-12-24 21:45:39'),(13,'N/A','N/A','N/A','0000','N/A','N/A','+1 (777) 174-3634','2026-01-10 09:16:26','2026-01-10 09:16:26'),(14,'Excepturi et ad veni','','Et dolores magnam te','54919','South Australia','Australia','+1 (737) 822-7712','2026-01-12 11:45:45','2026-01-12 11:45:45'),(15,'In consequatur nece','','Id laudantium cons','45554','Northern Territory','Australia','+1 (543) 777-9385','2026-01-12 11:46:03','2026-01-12 11:46:03');
/*!40000 ALTER TABLE `tbl_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_notification_types`
--

DROP TABLE IF EXISTS `tbl_notification_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_notification_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_notification_types`
--

LOCK TABLES `tbl_notification_types` WRITE;
/*!40000 ALTER TABLE `tbl_notification_types` DISABLE KEYS */;
INSERT INTO `tbl_notification_types` VALUES (1,'New Auction','A new auction has been listed. Check out the details and place your bids before the auction ends.','2025-02-09 22:47:22','2025-02-09 22:47:22'),(2,'Favorite Auction','A reminder for an auction you favorited. Stay updated as it approaches the closing time!','2025-02-09 22:47:22','2025-02-09 22:47:22'),(3,'Outbid Auction','You have been outbid on an auction. Click the link to place a higher bid and stay in the race.','2025-02-09 22:47:36','2025-02-09 22:47:36'),(4,'Winning Auction','Congratulations! You have won the auction. Here’s how to contact the seller and proceed with payment and collection.','2025-02-09 22:47:36','2025-02-09 22:47:36'),(5,'Auction Closed','The auction you participated in has ended. View the final results and explore other ongoing auctions.','2025-02-09 22:47:56','2025-02-09 22:47:56');
/*!40000 ALTER TABLE `tbl_notification_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_payment_types`
--

DROP TABLE IF EXISTS `tbl_payment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_payment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_payment_types`
--

LOCK TABLES `tbl_payment_types` WRITE;
/*!40000 ALTER TABLE `tbl_payment_types` DISABLE KEYS */;
INSERT INTO `tbl_payment_types` VALUES (1,'Unpaid','2025-02-09 22:31:00','2025-02-09 22:31:00'),(2,'Paid','2025-02-09 22:31:00','2025-02-09 22:31:00'),(3,'Pending','2025-02-09 22:31:10','2025-02-09 22:31:10'),(4,'Failed','2025-02-09 22:31:10','2025-02-09 22:31:10'),(5,'Refunded','2025-02-09 22:31:25','2025-02-09 22:31:25'),(6,'Partially Paid','2025-02-09 22:31:25','2025-02-09 22:31:25'),(7,'Overpaid','2025-02-09 22:31:35','2025-02-09 22:31:35'),(8,'Cancelled','2025-02-09 22:31:35','2025-02-09 22:31:35');
/*!40000 ALTER TABLE `tbl_payment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_platform_settings`
--

DROP TABLE IF EXISTS `tbl_platform_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_platform_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_fee` decimal(5,2) DEFAULT '17.50',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_platform_settings`
--

LOCK TABLES `tbl_platform_settings` WRITE;
/*!40000 ALTER TABLE `tbl_platform_settings` DISABLE KEYS */;
INSERT INTO `tbl_platform_settings` VALUES (1,18.50,'2025-12-23 12:59:43');
/*!40000 ALTER TABLE `tbl_platform_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_preference_types`
--

DROP TABLE IF EXISTS `tbl_preference_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_preference_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_preference_types`
--

LOCK TABLES `tbl_preference_types` WRITE;
/*!40000 ALTER TABLE `tbl_preference_types` DISABLE KEYS */;
INSERT INTO `tbl_preference_types` VALUES (1,'Email','2025-03-18 20:14:23','2025-03-18 20:14:23'),(2,'Text','2025-03-18 20:14:23','2025-03-18 20:14:23');
/*!40000 ALTER TABLE `tbl_preference_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_role_types`
--

DROP TABLE IF EXISTS `tbl_role_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_role_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_role_types`
--

LOCK TABLES `tbl_role_types` WRITE;
/*!40000 ALTER TABLE `tbl_role_types` DISABLE KEYS */;
INSERT INTO `tbl_role_types` VALUES (1,'Storage Facility','Manages and lists storage units for auctions.','2025-02-08 15:26:26','2025-02-08 15:26:26'),(2,'Punter','Participates in auctions by placing bids.','2025-02-08 15:26:26','2025-02-08 15:26:26'),(3,'Staff','Assists in managing storage facilities or platform operations.','2025-02-09 22:54:22','2025-02-09 22:54:22'),(4,'Admin','Platform Administrator','2025-12-22 14:58:17','2025-12-22 14:58:17');
/*!40000 ALTER TABLE `tbl_role_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_signup_bonus_settings`
--

DROP TABLE IF EXISTS `tbl_signup_bonus_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_signup_bonus_settings` (
  `id` int(11) NOT NULL,
  `enabled` tinyint(1) DEFAULT '0',
  `amount` decimal(10,2) DEFAULT '0.00',
  `expiration_days` int(11) DEFAULT '90',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_signup_bonus_settings`
--

LOCK TABLES `tbl_signup_bonus_settings` WRITE;
/*!40000 ALTER TABLE `tbl_signup_bonus_settings` DISABLE KEYS */;
INSERT INTO `tbl_signup_bonus_settings` VALUES (1,1,40.00,90,'2025-12-23 17:37:24');
/*!40000 ALTER TABLE `tbl_signup_bonus_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_staff_company_access_relationship`
--

DROP TABLE IF EXISTS `tbl_staff_company_access_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_staff_company_access_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_id` (`status_type_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `tbl_staff_company_access_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_staff_company_access_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`),
  CONSTRAINT `tbl_staff_company_access_relationship_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_staff_company_access_relationship`
--

LOCK TABLES `tbl_staff_company_access_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_staff_company_access_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_staff_company_access_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_staff_permissions`
--

DROP TABLE IF EXISTS `tbl_staff_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_staff_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `storage_facility_id` (`storage_facility_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_staff_permissions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  CONSTRAINT `tbl_staff_permissions_ibfk_2` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  CONSTRAINT `tbl_staff_permissions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_staff_permissions`
--

LOCK TABLES `tbl_staff_permissions` WRITE;
/*!40000 ALTER TABLE `tbl_staff_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_staff_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_status_types`
--

DROP TABLE IF EXISTS `tbl_status_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_status_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_status_types`
--

LOCK TABLES `tbl_status_types` WRITE;
/*!40000 ALTER TABLE `tbl_status_types` DISABLE KEYS */;
INSERT INTO `tbl_status_types` VALUES (1,'Active','Currently enabled and operational.','2025-02-09 21:15:23','2025-02-09 21:15:23'),(2,'Inactive','Currently disabled and not operational.','2025-02-09 21:15:23','2025-02-09 21:15:23'),(3,'Pending','','2025-12-23 17:55:16','2025-12-23 17:55:16');
/*!40000 ALTER TABLE `tbl_status_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facilities`
--

DROP TABLE IF EXISTS `tbl_storage_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `abn` text NOT NULL,
  `website` text NOT NULL,
  `description` text NOT NULL,
  `timezone` text NOT NULL,
  `contact_email` text NOT NULL,
  `additional_email` text NOT NULL,
  `office_hours` text NOT NULL,
  `payout_details` text NOT NULL,
  `youtube_video_url` text NOT NULL,
  `branding_logo` text NOT NULL,
  `notice_to_buyers` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facilities`
--

LOCK TABLES `tbl_storage_facilities` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facilities` DISABLE KEYS */;
INSERT INTO `tbl_storage_facilities` VALUES (1,'Forest Glan','1300 136 851','https://mammothselfstore.com.au/locations/storage-forest-glen/','Whether you’re getting ready to renovate, looking for a safe place to store your boat, or you’re heading off on that long-awaited trip of a lifetime, whatever your storage needs, Mammoth Storage Forest Glen can help you. As a locally owned, highly secure self storage facility located in Forest Glen, Sunshine Coast, we offer a range of long-term and short-term self storage options in a wide range of sizes to meet your storage needs. Our moving supplies shop makes storing with us even easier. Plus, you’ll be able to access your stored items at any time of the day or night with our secure 24/7 access system. *24 hours by request only*','Australia/Brisbane','hello@mammothstorage.com.au','hello@mammothstorage.com.au','Access Hours: 5am – 9pm or 24 hours by request.\nReception Office Hours:\nMondays to Fridays: 8:30am to 5pm\nSaturdays: 8:30am to 1pm\nSunday: 9am to 12pm\nClosed on Public Holidays','Bank','','https://mammothselfstore.com.au/locations/storage-forest-glen/',NULL,'2025-05-07 20:03:20','2025-05-07 20:12:18'),(2,'My Storage Facility','12 345 678 901','https://mystorageplace.com','Premium self-storage facility offering secure, climate-controlled units in various sizes. We provide 24/7 access, moving supplies, and excellent customer service.','Australia/Brisbane','contact@mystorageplace.com','admin@mystorageplace.com','Access Hours: 24/7\nReception Office Hours:\nMondays to Fridays: 9am to 5pm\nSaturdays: 9am to 1pm\nSunday: Closed\nClosed on Public Holidays','Bank','','',NULL,'2025-12-22 14:40:09','2025-12-22 14:40:09'),(3,'Ulla Dodson','Omnis non excepteur','https://www.fyf.info','Corporis excepturi q','Australia/Victoria','casyfotejo@mailinator.com','vara@mailinator.com','Fuga Mollit consequ','','','Et ea numquam at Nam','Debitis Nam sint ten','2025-12-24 21:45:39','2025-12-24 21:45:39'),(4,'Maggie Chang','Sint dolores aute it','https://example.com','Fugit esse ea ad ip','Australia/Victoria','cedigoto@mailinator.com','','Mon-Fri 9am-5pm','','','','Est ratione rerum il','2026-01-12 11:45:45','2026-01-12 11:45:45'),(5,'Rachel Mayer','Repudiandae quis ad','https://example.com','Rerum totam est in o','Australia/NSW','hecekipomo@mailinator.com','','Mon-Fri 9am-5pm','','','','Perferendis commodi','2026-01-12 11:46:03','2026-01-12 11:46:03');
/*!40000 ALTER TABLE `tbl_storage_facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facility_bank_relationship`
--

DROP TABLE IF EXISTS `tbl_storage_facility_bank_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facility_bank_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facility_bank_relationship`
--

LOCK TABLES `tbl_storage_facility_bank_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facility_bank_relationship` DISABLE KEYS */;
INSERT INTO `tbl_storage_facility_bank_relationship` VALUES (1,3,1,'2026-01-12 16:26:47','2026-01-12 16:26:47'),(2,4,1,'2026-01-12 16:28:40','2026-01-12 16:28:40'),(3,5,1,'2026-01-12 16:30:13','2026-01-12 16:30:13'),(4,6,1,'2026-01-12 18:19:17','2026-01-12 18:19:17');
/*!40000 ALTER TABLE `tbl_storage_facility_bank_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facility_company_relationship`
--

DROP TABLE IF EXISTS `tbl_storage_facility_company_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facility_company_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_facility_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `storage_facility_id` (`storage_facility_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `tbl_storage_facility_company_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  CONSTRAINT `tbl_storage_facility_company_relationship_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facility_company_relationship`
--

LOCK TABLES `tbl_storage_facility_company_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facility_company_relationship` DISABLE KEYS */;
INSERT INTO `tbl_storage_facility_company_relationship` VALUES (1,1,4,'2025-05-07 20:03:20','2025-05-07 20:03:20'),(2,1,6,'2025-12-22 14:50:06','2025-12-22 14:50:06'),(3,3,5,'2025-12-24 21:45:39','2025-12-24 21:45:39'),(4,4,5,'2026-01-12 11:45:45','2026-01-12 11:45:45'),(5,5,5,'2026-01-12 11:46:03','2026-01-12 11:46:03');
/*!40000 ALTER TABLE `tbl_storage_facility_company_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facility_location_relationship`
--

DROP TABLE IF EXISTS `tbl_storage_facility_location_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facility_location_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_facility_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `storage_facility_id` (`storage_facility_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `tbl_storage_facility_location_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  CONSTRAINT `tbl_storage_facility_location_relationship_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facility_location_relationship`
--

LOCK TABLES `tbl_storage_facility_location_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facility_location_relationship` DISABLE KEYS */;
INSERT INTO `tbl_storage_facility_location_relationship` VALUES (1,1,8,'2025-05-07 20:03:20','2025-05-07 20:03:20'),(2,2,11,'2025-12-22 14:40:09','2025-12-22 14:40:09'),(3,3,12,'2025-12-24 21:45:39','2025-12-24 21:45:39'),(4,4,14,'2026-01-12 11:45:45','2026-01-12 11:45:45'),(5,5,15,'2026-01-12 11:46:03','2026-01-12 11:46:03');
/*!40000 ALTER TABLE `tbl_storage_facility_location_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facility_staff_relationship`
--

DROP TABLE IF EXISTS `tbl_storage_facility_staff_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facility_staff_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_facility_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `storage_facility_id` (`storage_facility_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_storage_facility_staff_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  CONSTRAINT `tbl_storage_facility_staff_relationship_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facility_staff_relationship`
--

LOCK TABLES `tbl_storage_facility_staff_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facility_staff_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_storage_facility_staff_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facility_status_relationship`
--

DROP TABLE IF EXISTS `tbl_storage_facility_status_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facility_status_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_facility_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `storage_facility_id` (`storage_facility_id`),
  KEY `status_type_id` (`status_type_id`),
  CONSTRAINT `tbl_storage_facility_status_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  CONSTRAINT `tbl_storage_facility_status_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facility_status_relationship`
--

LOCK TABLES `tbl_storage_facility_status_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facility_status_relationship` DISABLE KEYS */;
INSERT INTO `tbl_storage_facility_status_relationship` VALUES (1,1,1,'2025-05-07 20:03:20','2025-05-07 20:04:03'),(2,2,1,'2025-12-22 14:40:09','2025-12-22 14:40:09'),(3,3,1,'2025-12-24 21:45:39','2025-12-24 21:45:39'),(4,4,1,'2026-01-12 11:45:45','2026-01-12 11:45:45'),(5,5,1,'2026-01-12 11:46:03','2026-01-12 11:46:03');
/*!40000 ALTER TABLE `tbl_storage_facility_status_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_storage_facility_upload_relationship`
--

DROP TABLE IF EXISTS `tbl_storage_facility_upload_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_storage_facility_upload_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_facility_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_storage_facility_upload_relationship`
--

LOCK TABLES `tbl_storage_facility_upload_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_storage_facility_upload_relationship` DISABLE KEYS */;
INSERT INTO `tbl_storage_facility_upload_relationship` VALUES (1,4,23,'2026-01-12 11:45:45','2026-01-12 11:45:45');
/*!40000 ALTER TABLE `tbl_storage_facility_upload_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_system_commission_settings`
--

DROP TABLE IF EXISTS `tbl_system_commission_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_system_commission_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commission_type` varchar(100) DEFAULT NULL,
  `commission_value` decimal(10,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_system_commission_settings`
--

LOCK TABLES `tbl_system_commission_settings` WRITE;
/*!40000 ALTER TABLE `tbl_system_commission_settings` DISABLE KEYS */;
INSERT INTO `tbl_system_commission_settings` VALUES (1,'standard',17.50,'2025-12-23 17:39:10');
/*!40000 ALTER TABLE `tbl_system_commission_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_system_credit_settings`
--

DROP TABLE IF EXISTS `tbl_system_credit_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_system_credit_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_type` varchar(50) NOT NULL,
  `credit_value` decimal(10,2) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_system_credit_settings`
--

LOCK TABLES `tbl_system_credit_settings` WRITE;
/*!40000 ALTER TABLE `tbl_system_credit_settings` DISABLE KEYS */;
INSERT INTO `tbl_system_credit_settings` VALUES (1,'base_credit',10.00,'2025-12-23 17:54:56');
/*!40000 ALTER TABLE `tbl_system_credit_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_token_activation_types`
--

DROP TABLE IF EXISTS `tbl_token_activation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_token_activation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_token_activation_types`
--

LOCK TABLES `tbl_token_activation_types` WRITE;
/*!40000 ALTER TABLE `tbl_token_activation_types` DISABLE KEYS */;
INSERT INTO `tbl_token_activation_types` VALUES (1,'Email Activation','2025-05-04 07:34:22','2025-05-04 07:34:22'),(2,'Forgot Password','2025-05-04 07:34:22','2025-05-04 07:34:22'),(3,'Invitation Staff','2025-05-05 05:06:55','2025-05-05 05:06:55');
/*!40000 ALTER TABLE `tbl_token_activation_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_uploads`
--

DROP TABLE IF EXISTS `tbl_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `path` text NOT NULL,
  `type` text NOT NULL,
  `size` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_uploads`
--

LOCK TABLES `tbl_uploads` WRITE;
/*!40000 ALTER TABLE `tbl_uploads` DISABLE KEYS */;
INSERT INTO `tbl_uploads` VALUES (1,'47583714_2499249720089619_3219922323087818752_n (3).jpg','http://localhost:8000/media/uploads/auctions/img_69490ad893e55.jpg','image/jpeg','15315','2025-12-22 19:09:44','2025-12-22 19:09:44'),(2,'47583714_2499249720089619_3219922323087818752_n (3).jpg','http://localhost:8000/media/uploads/auctions/img_69490ae7c132f.jpg','image/jpeg','15315','2025-12-22 19:09:59','2025-12-22 19:09:59'),(3,'47583714_2499249720089619_3219922323087818752_n (3).jpg','http://localhost:8000/media/uploads/auctions/img_69490b03f0797.jpg','image/jpeg','15315','2025-12-22 19:10:27','2025-12-22 19:10:27'),(4,'47583714_2499249720089619_3219922323087818752_n (3).jpg','http://localhost:8000/media/uploads/auctions/img_69490b09848cc.jpg','image/jpeg','15315','2025-12-22 19:10:33','2025-12-22 19:10:33'),(5,'47583714_2499249720089619_3219922323087818752_n (3).jpg','http://localhost:8000/media/uploads/auctions/img_69490b10e3a07.jpg','image/jpeg','15315','2025-12-22 19:10:40','2025-12-22 19:10:40'),(6,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_6949101ae48ea.jpeg','image/jpeg','315434','2025-12-22 19:32:10','2025-12-22 19:32:10'),(7,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_694910209b88d.jpeg','image/jpeg','315434','2025-12-22 19:32:16','2025-12-22 19:32:16'),(8,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_69491028470c8.jpeg','image/jpeg','315434','2025-12-22 19:32:24','2025-12-22 19:32:24'),(9,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_69491037d1cb3.jpeg','image/jpeg','315434','2025-12-22 19:32:39','2025-12-22 19:32:39'),(10,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_6949103c7da00.jpeg','image/jpeg','315434','2025-12-22 19:32:44','2025-12-22 19:32:44'),(11,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_6949103e87209.jpeg','image/jpeg','315434','2025-12-22 19:32:46','2025-12-22 19:32:46'),(12,'WhatsApp Image 2024-02-11 at 11.01.07 PM.jpeg','http://localhost:8000/media/uploads/auctions/img_6949104dab5f5.jpeg','image/jpeg','315434','2025-12-22 19:33:01','2025-12-22 19:33:01'),(13,'logo (2).png','http://localhost:8000/media/uploads/auctions/img_694914f890986.png','image/png','169904','2025-12-22 19:52:56','2025-12-22 19:52:56'),(14,'Phonebot-Referbishment-Lab.jpg.jpg','http://localhost:8000/media/uploads/auctions/img_6949b239b0b73.jpg','image/jpeg','169923','2025-12-23 07:03:53','2025-12-23 07:03:53'),(15,'Phonebot-Referbishment-Lab.jpg.jpg','http://localhost:8000/media/uploads/auctions/img_6949b26221e07.jpg','image/jpeg','169923','2025-12-23 07:04:34','2025-12-23 07:04:34'),(16,'logo (2).png','http://localhost:8000/media/uploads/auctions/img_694b033a6a94b.png','image/png','169904','2025-12-24 07:01:46','2025-12-24 07:01:46'),(17,'Phonebot-Referbishment-Lab.jpg.jpg','http://storagepunters.test/media/uploads/auctions/ai_695e3aa69331d.jpg','image/jpeg','169923','2026-01-07 10:51:18','2026-01-07 10:51:18'),(18,'Phonebot-Referbishment-Lab.jpg.jpg','http://storagepunters.test/media/uploads/auctions/ai_695e3b1aeab0b.jpg','image/jpeg','169923','2026-01-07 10:53:14','2026-01-07 10:53:14'),(19,'channels4_profile.jpg','http://storagepunters.test/media/uploads/auctions/ai_695e3b85ec695.jpg','image/jpeg','22288','2026-01-07 10:55:01','2026-01-07 10:55:01'),(20,'images (2).jpeg','http://storagepunters.test/media/uploads/auctions/ai_695e3c463b640.jpeg','image/jpeg','9215','2026-01-07 10:58:14','2026-01-07 10:58:14'),(21,'premium_photo-1663956108845-ad0432896f06.jpeg','http://storagepunters.test/media/uploads/auctions/ai_6960ce121041a.jpeg','image/jpeg','377253','2026-01-09 09:44:50','2026-01-09 09:44:50'),(22,'dIFqWx6dnqxZ8U50kjJY9FUOk.webp','http://storagepunters.test/media/uploads/auctions/ai_6964cf0d8865d.webp','image/webp','105290','2026-01-12 10:38:05','2026-01-12 10:38:05'),(23,'channels4_profile.jpg','http://storagepunters.test/media/uploads/auctions/img_6964dee97e118.jpg','image/jpeg','22288','2026-01-12 21:45:45','2026-01-12 21:45:45'),(24,'channels4_profile.jpg','http://storagepunters.test/media/uploads/auctions/ai_69669b6c85f20.jpg','image/jpeg','22288','2026-01-13 19:22:20','2026-01-13 19:22:20'),(25,'channels4_profile.jpg','http://storagepunters.test/media/uploads/auctions/ai_69669cee9d197.jpg','image/jpeg','22288','2026-01-13 19:28:46','2026-01-13 19:28:46');
/*!40000 ALTER TABLE `tbl_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_activation_token`
--

DROP TABLE IF EXISTS `tbl_user_activation_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_activation_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `token` text NOT NULL,
  `expires_at` datetime NOT NULL,
  `token_activation_type_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `token_activation_type_id` (`token_activation_type_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_user_activation_token_ibfk_1` FOREIGN KEY (`token_activation_type_id`) REFERENCES `tbl_token_activation_types` (`id`),
  CONSTRAINT `tbl_user_activation_token_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_activation_token`
--

LOCK TABLES `tbl_user_activation_token` WRITE;
/*!40000 ALTER TABLE `tbl_user_activation_token` DISABLE KEYS */;
INSERT INTO `tbl_user_activation_token` VALUES (1,'tobias@mammothstorage.com.au','8b18b661f655e71d4a0aea8b1c4ef07ed397836165b9e3939eae87f807db323c','2025-05-08 17:22:33',1,0,1,'2025-05-07 17:22:33','2025-05-07 17:22:33'),(2,'simatrixsolution@gmail.com','5dc347cf4b24914c0a93a041273ff98d6b4e7d7c5026bcdce5b13f49a9352fbb','2025-12-24 19:13:51',3,4,4,'2025-12-22 19:13:51','2025-12-22 19:13:51'),(3,'nuvabimu@mailinator.com','1df980792e643ea291f167071358008f0f85731b809e84e5ce714e66489414e4','2026-01-04 05:06:33',3,5,7,'2026-01-02 05:06:33','2026-01-02 05:06:33');
/*!40000 ALTER TABLE `tbl_user_activation_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_activity`
--

DROP TABLE IF EXISTS `tbl_user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `tbl_user_activity_ibfk_1` FOREIGN KEY (`id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_activity`
--

LOCK TABLES `tbl_user_activity` WRITE;
/*!40000 ALTER TABLE `tbl_user_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_alert_settings`
--

DROP TABLE IF EXISTS `tbl_user_alert_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_alert_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preference_type_id` int(11) NOT NULL,
  `notification_type_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `notification_type_id` (`notification_type_id`),
  KEY `preference_type_id` (`preference_type_id`),
  KEY `user_id` (`user_id`),
  KEY `status_type_id` (`status_type_id`),
  CONSTRAINT `tbl_user_alert_settings_ibfk_1` FOREIGN KEY (`notification_type_id`) REFERENCES `tbl_notification_types` (`id`),
  CONSTRAINT `tbl_user_alert_settings_ibfk_2` FOREIGN KEY (`preference_type_id`) REFERENCES `tbl_preference_types` (`id`),
  CONSTRAINT `tbl_user_alert_settings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_alert_settings_ibfk_4` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_alert_settings`
--

LOCK TABLES `tbl_user_alert_settings` WRITE;
/*!40000 ALTER TABLE `tbl_user_alert_settings` DISABLE KEYS */;
INSERT INTO `tbl_user_alert_settings` VALUES (1,1,4,1,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(2,2,4,1,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(3,1,5,1,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(4,2,5,1,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(5,1,3,1,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(6,2,3,1,2,'2025-05-07 18:42:36','2025-05-07 18:42:36');
/*!40000 ALTER TABLE `tbl_user_alert_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_bank_informations_relationship`
--

DROP TABLE IF EXISTS `tbl_user_bank_informations_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_bank_informations_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `bank_id` (`bank_id`),
  CONSTRAINT `tbl_user_bank_informations_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_bank_informations_relationship_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `tbl_bank` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_bank_informations_relationship`
--

LOCK TABLES `tbl_user_bank_informations_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_bank_informations_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_bank_informations_relationship` VALUES (2,3,1,2,'2026-01-12 21:26:47','2026-01-12 21:26:47'),(3,4,1,2,'2026-01-12 21:28:40','2026-01-12 21:28:40'),(4,5,1,2,'2026-01-12 21:30:13','2026-01-12 21:30:13'),(5,6,1,2,'2026-01-12 23:19:17','2026-01-12 23:19:17');
/*!40000 ALTER TABLE `tbl_user_bank_informations_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_company_relationship`
--

DROP TABLE IF EXISTS `tbl_user_company_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_company_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `user_id` (`user_id`),
  KEY `status_type_id` (`status_type_id`),
  CONSTRAINT `tbl_user_company_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  CONSTRAINT `tbl_user_company_relationship_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_company_relationship_ibfk_3` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_company_relationship`
--

LOCK TABLES `tbl_user_company_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_company_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_company_relationship` VALUES (4,4,3,1,'2025-05-07 19:36:46','2025-05-07 19:36:46'),(5,5,1,1,'2025-12-22 19:08:39','2025-12-22 19:08:39'),(6,4,4,2,'2025-12-22 19:13:51','2025-12-22 19:13:51'),(7,6,1,1,'2025-12-22 14:50:06','2025-12-22 14:50:06'),(8,5,7,2,'2026-01-02 05:06:33','2026-01-02 05:06:33');
/*!40000 ALTER TABLE `tbl_user_company_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_credentials`
--

DROP TABLE IF EXISTS `tbl_user_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tbl_user_credentials_ibfk_1` (`user_id`),
  CONSTRAINT `tbl_user_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_credentials`
--

LOCK TABLES `tbl_user_credentials` WRITE;
/*!40000 ALTER TABLE `tbl_user_credentials` DISABLE KEYS */;
INSERT INTO `tbl_user_credentials` VALUES (1,'tobias_test1@mammothstorage.com.au','Password123!',1,'2025-05-07 17:22:33','2025-05-07 17:22:33'),(2,'specialemailid2050@gmail.com','123456',2,'2025-05-07 18:42:36','2025-05-07 19:08:20'),(3,'andoroid.mobile2050@gmail.com','123456',3,'2025-05-07 19:20:29','2025-05-07 19:22:08'),(4,'simatrixsolution@gmail.com','12345',4,'2025-12-22 19:13:51','2025-12-22 19:13:51'),(5,'staff@storagepunters.com','StaffPassword123!',5,'2025-12-22 14:15:44','2025-12-22 14:15:44'),(6,'admin@storagepunters.com','AdminPassword123!',6,'2025-12-22 14:58:17','2025-12-22 14:58:17'),(7,'nuvabimu@mailinator.com','12345',7,'2026-01-02 05:06:33','2026-01-02 05:06:33'),(8,'xato@mailinator.com','$2y$10$nLRfh1wcIFGcoDAGFUQNDeaMZrdNVt2ubzgIrxuVQO0quMcjskwBa',8,'2026-01-10 09:07:56','2026-01-10 09:07:56'),(9,'tyqimoxe@mailinator.com','$2y$10$HAuIxR5eWwL5WVmC7enHGeUu.BHETbGJr.i7zNBMENenwgp5cOWuO',9,'2026-01-10 09:09:49','2026-01-10 09:09:49'),(10,'dyweqoryco@mailinator.com','$2y$10$Zx5DEhXMC.OFu5.k8OSUBuYMaeSm9AfeIDuMuB2Al6FzOPrtbM3H6',10,'2026-01-10 09:11:50','2026-01-10 09:11:50'),(11,'xoqys@mailinator.com','$2y$10$MYEhscH2hXjd.S8RHAYBYOYtY/EcWxfC8XgJ9/kIuruLXlyuDeeb.',11,'2026-01-10 09:12:10','2026-01-10 09:12:10'),(12,'gogujuver@mailinator.com','$2y$10$gBhwdgTYqahiymPCV/i2m.uC18ALHebgDDnQIYP/Akp2QfNeChBN.',12,'2026-01-10 09:14:01','2026-01-10 09:14:01'),(13,'wirezyr@mailinator.com','$2y$10$dZzKDKfQ6TsOWjeCbINRXeM2TFmbqCQ9RDLsr7c9IhAc.LTPBsnC6',13,'2026-01-10 09:15:42','2026-01-10 09:15:42'),(14,'wihenytak@mailinator.com','$2y$10$ubLJtjbaeC3ksmN0g4mDqOz648QnEUWobGcln94Yy1OGC/CKUlqGW',14,'2026-01-10 09:16:26','2026-01-10 09:16:26'),(16,'hello@jayx.com','password',16,'2026-01-13 22:16:42','2026-01-13 22:16:42'),(17,'hello@jayxd.com','password',17,'2026-01-13 22:58:44','2026-01-13 22:58:44');
/*!40000 ALTER TABLE `tbl_user_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_location_relationship`
--

DROP TABLE IF EXISTS `tbl_user_location_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_location_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `tbl_user_location_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_location_relationship_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_location_relationship`
--

LOCK TABLES `tbl_user_location_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_location_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_location_relationship` VALUES (1,1,1,'2025-05-07 17:22:33','2025-05-07 17:22:33'),(2,2,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(3,3,3,'2025-05-07 19:20:29','2025-05-07 19:20:29'),(4,5,10,'2025-12-22 14:15:44','2025-12-22 14:15:44'),(5,14,13,'2026-01-10 09:16:26','2026-01-10 09:16:26');
/*!40000 ALTER TABLE `tbl_user_location_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_notification_settings`
--

DROP TABLE IF EXISTS `tbl_user_notification_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_notification_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `audio_alert_enabled` tinyint(4) DEFAULT '1',
  `winning_auction_email` tinyint(4) DEFAULT '1',
  `winning_auction_sms` tinyint(4) DEFAULT '1',
  `outbid_auction_email` tinyint(4) DEFAULT '0',
  `outbid_auction_sms` tinyint(4) DEFAULT '0',
  `auction_closed_email` tinyint(4) DEFAULT '1',
  `auction_closed_sms` tinyint(4) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_notification_settings`
--

LOCK TABLES `tbl_user_notification_settings` WRITE;
/*!40000 ALTER TABLE `tbl_user_notification_settings` DISABLE KEYS */;
INSERT INTO `tbl_user_notification_settings` VALUES (1,1,1,1,1,1,0,1,0,'2026-01-12 12:07:07','2026-01-12 12:07:07');
/*!40000 ALTER TABLE `tbl_user_notification_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_payment_methods_relationship`
--

DROP TABLE IF EXISTS `tbl_user_payment_methods_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_payment_methods_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `stripe_payment_method_id` text NOT NULL,
  `is_default` text NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `card_holder_first_name` text NOT NULL,
  `card_holder_last_name` text NOT NULL,
  `card_holder_email` text NOT NULL,
  `card_holder_phone` text NOT NULL,
  `card_billing_address` text NOT NULL,
  `card_billing_zip` text NOT NULL,
  `card_type` text NOT NULL,
  `card_holder_card_number` text NOT NULL,
  `card_expiration_month` text NOT NULL,
  `card_expiration_year` int(11) NOT NULL,
  `card_cvv` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_type_id` (`status_type_id`),
  CONSTRAINT `tbl_user_payment_methods_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_payment_methods_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_payment_methods_relationship`
--

LOCK TABLES `tbl_user_payment_methods_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_payment_methods_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_payment_methods_relationship` VALUES (1,1,'cus_RW6fQp4QnqmWqP','pm_card_visa','true',1,'2026-01-13 21:49:02','2026-01-13 21:49:02','Tobias','Tester','tobias@example.com','12345678','123 Test St','4000','Visa','4242','12',2030,'123'),(4,16,'cus_TmpjDF4YK6AjhN','pm_1SpGOg08JMAyNQJTZ0FNR47d','true',1,'2026-01-14 08:49:12','2026-01-14 08:49:12','JayX','Hello','hello@jayx.com','9491234567','Excepturi accusantiu','12312','Visa','4242','12',2028,'XXX'),(5,17,'cus_TmqEypSOIZnvuH','pm_1SpGZG08JMAyNQJTSjpJRN1W','true',1,'2026-01-14 09:00:08','2026-01-14 09:00:09','Jay','XD','hello@jayxd.com','9491234567','Excepturi accusantiu','12312','Visa','4242','12',2028,'XXX');
/*!40000 ALTER TABLE `tbl_user_payment_methods_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_profile_relationship`
--

DROP TABLE IF EXISTS `tbl_user_profile_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_profile_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_of_birth` date NOT NULL,
  `time_zone` text NOT NULL,
  `hear_us` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_user_profile_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_profile_relationship`
--

LOCK TABLES `tbl_user_profile_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_profile_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_profile_relationship` VALUES (1,'1995-08-23','Australia/Brisbane','tv_advertisement',1,'2025-05-07 17:22:33','2025-05-07 17:22:33'),(2,'1995-04-27','Australia/Brisbane','referral',2,'2025-05-07 18:42:36','2025-05-07 19:08:20'),(3,'1995-04-27','Australia/Brisbane','referral',3,'2025-05-07 19:20:29','2025-05-07 19:22:08'),(5,'1990-01-01','Australia/Brisbane','Word of Mouth',16,'2026-01-13 22:16:42','2026-01-13 22:16:42'),(6,'2026-01-13','Australia/Brisbane','Seeder',17,'2026-01-13 22:58:44','2026-01-13 22:58:44');
/*!40000 ALTER TABLE `tbl_user_profile_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_role_relationship`
--

DROP TABLE IF EXISTS `tbl_user_role_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_role_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `tbl_user_role_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_role_relationship_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `tbl_role_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_role_relationship`
--

LOCK TABLES `tbl_user_role_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_role_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_role_relationship` VALUES (1,1,1,'2025-05-07 17:22:33','2025-05-07 17:22:33'),(2,2,2,'2025-05-07 18:42:36','2025-05-07 18:42:36'),(3,3,1,'2025-05-07 19:20:29','2025-05-07 19:20:29'),(4,4,3,'2025-12-22 19:13:51','2025-12-22 19:13:51'),(5,5,3,'2025-12-22 14:15:44','2025-12-22 14:15:44'),(6,6,4,'2025-12-22 14:58:17','2025-12-22 14:58:17'),(7,7,3,'2026-01-02 05:06:33','2026-01-02 05:06:33'),(12,12,4,'2026-01-10 09:14:01','2026-01-10 09:14:01'),(13,13,4,'2026-01-10 09:15:42','2026-01-10 09:15:42'),(14,14,4,'2026-01-10 09:16:26','2026-01-10 09:16:26'),(16,16,2,'2026-01-13 22:16:42','2026-01-13 22:16:42'),(17,17,2,'2026-01-13 22:58:44','2026-01-13 22:58:44');
/*!40000 ALTER TABLE `tbl_user_role_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_status_relationship`
--

DROP TABLE IF EXISTS `tbl_user_status_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_status_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_id` (`status_type_id`),
  CONSTRAINT `tbl_user_status_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_status_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_status_relationship`
--

LOCK TABLES `tbl_user_status_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_status_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_status_relationship` VALUES (1,1,1,'2025-05-07 17:22:33','2025-12-22 13:34:28'),(2,2,1,'2025-05-07 18:42:36','2025-05-07 18:46:39'),(3,3,1,'2025-05-07 19:20:29','2025-05-07 19:20:57'),(4,4,2,'2025-12-22 19:13:51','2025-12-22 19:13:51'),(5,5,1,'2025-12-22 14:15:44','2025-12-22 14:15:44'),(6,6,1,'2025-12-22 14:58:17','2025-12-22 14:58:17'),(7,7,2,'2026-01-02 05:06:33','2026-01-02 05:06:33'),(8,12,2,'2026-01-10 09:14:01','2026-01-10 09:34:48'),(9,13,1,'2026-01-10 09:15:42','2026-01-10 09:15:42'),(10,14,1,'2026-01-10 09:16:26','2026-01-10 09:16:26'),(12,16,1,'2026-01-13 22:16:42','2026-01-13 22:16:42'),(13,17,1,'2026-01-13 22:58:44','2026-01-13 22:58:44');
/*!40000 ALTER TABLE `tbl_user_status_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_stripe_relationship`
--

DROP TABLE IF EXISTS `tbl_user_stripe_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_stripe_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_user_stripe_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_stripe_relationship`
--

LOCK TABLES `tbl_user_stripe_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_stripe_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_stripe_relationship` VALUES (2,17,'cus_TmqEypSOIZnvuH','2026-01-13 22:58:44','2026-01-13 22:58:44');
/*!40000 ALTER TABLE `tbl_user_stripe_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_subscription_relationship`
--

DROP TABLE IF EXISTS `tbl_user_subscription_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_subscription_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_id` varchar(100) DEFAULT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `billing_cycle` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `current_period_start` datetime DEFAULT NULL,
  `current_period_end` datetime DEFAULT NULL,
  `product_id` varchar(100) DEFAULT NULL,
  `price_id` varchar(100) DEFAULT NULL,
  `status_type_id` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_subscription_relationship`
--

LOCK TABLES `tbl_user_subscription_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_subscription_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_subscription_relationship` VALUES (2,17,'sub_1SpGZV08JMAyNQJTxbISWG20',NULL,NULL,NULL,NULL,NULL,'2026-01-14 09:01:52','2026-01-14 09:01:52','2026-01-14 09:00:21','2026-01-14 09:00:21','2027-01-14 09:00:21','prod_Tmhk7MvDflpgP1','price_1Sp8L408JMAyNQJTQ3hqlgNu',1);
/*!40000 ALTER TABLE `tbl_user_subscription_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_verfication_relationship`
--

DROP TABLE IF EXISTS `tbl_user_verfication_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_verfication_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `verfication_type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `verfication_type_id` (`verfication_type_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `tbl_user_verfication_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  CONSTRAINT `tbl_user_verfication_relationship_ibfk_2` FOREIGN KEY (`verfication_type_id`) REFERENCES `tbl_verfication_types` (`id`),
  CONSTRAINT `tbl_user_verfication_relationship_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `tbl_status_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_verfication_relationship`
--

LOCK TABLES `tbl_user_verfication_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_verfication_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_user_verfication_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_user_wallet_relationship`
--

DROP TABLE IF EXISTS `tbl_user_wallet_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_user_wallet_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_user_wallet_relationship`
--

LOCK TABLES `tbl_user_wallet_relationship` WRITE;
/*!40000 ALTER TABLE `tbl_user_wallet_relationship` DISABLE KEYS */;
INSERT INTO `tbl_user_wallet_relationship` VALUES (1,2,12.00,'2026-01-10 09:57:58','2026-01-10 09:57:58');
/*!40000 ALTER TABLE `tbl_user_wallet_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `punter_id` varchar(50) DEFAULT NULL,
  `subscription_tier` enum('none','player','shark','whale') DEFAULT 'none',
  `coin_balance` int(11) DEFAULT '0',
  `notification_prefs` json DEFAULT NULL,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `current_draw_entries` int(11) DEFAULT '0',
  `notification_preferences` text,
  `subscription_status` varchar(20) DEFAULT 'active',
  `subscription_end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `punter_id` (`punter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_users`
--

LOCK TABLES `tbl_users` WRITE;
/*!40000 ALTER TABLE `tbl_users` DISABLE KEYS */;
INSERT INTO `tbl_users` VALUES (1,NULL,'shark',650,NULL,'Tobias','Constantinides','2025-05-07 17:22:33','2026-01-13 21:54:49',15,NULL,'active',NULL),(2,NULL,'none',0,NULL,'Ali','Jamil','2025-05-07 18:42:36','2025-05-07 18:42:36',0,NULL,'active',NULL),(3,NULL,'none',0,NULL,'Ali','Jamil','2025-05-07 19:20:29','2025-05-07 19:20:29',0,NULL,'active',NULL),(4,NULL,'none',0,NULL,'Hassan','Shahid','2025-12-22 19:13:51','2025-12-22 19:13:51',0,NULL,'active',NULL),(5,NULL,'none',0,NULL,'Staff','User','2025-12-22 14:15:44','2025-12-22 14:15:44',0,NULL,'active',NULL),(6,NULL,'none',498350,NULL,'Admin','User','2025-12-22 14:58:17','2025-12-22 14:58:17',0,NULL,'active',NULL),(7,NULL,'none',0,NULL,'Asdasd','Adad','2026-01-02 05:06:33','2026-01-02 05:06:33',0,NULL,'active',NULL),(8,NULL,'none',0,NULL,'Rajah','Austin','2026-01-10 09:07:55','2026-01-10 09:07:55',0,NULL,'active',NULL),(9,NULL,'none',0,NULL,'Laurel','Mills','2026-01-10 09:09:49','2026-01-10 09:09:49',0,NULL,'active',NULL),(10,NULL,'none',0,NULL,'Zeph','Holden','2026-01-10 09:11:50','2026-01-10 09:11:50',0,NULL,'active',NULL),(11,NULL,'none',0,NULL,'Quyn','Bray','2026-01-10 09:12:10','2026-01-10 09:12:10',0,NULL,'active',NULL),(12,NULL,'none',0,NULL,'Armando','Hartman','2026-01-10 09:14:01','2026-01-10 09:14:01',0,NULL,'active',NULL),(13,NULL,'none',0,NULL,'Veronica','Henson','2026-01-10 09:15:42','2026-01-10 09:15:42',0,NULL,'active',NULL),(14,NULL,'none',0,NULL,'McKenzie','Alvarez','2026-01-10 09:16:26','2026-01-10 09:16:26',0,NULL,'active',NULL),(16,NULL,'shark',5000,NULL,'JayX','Hello','2026-01-13 22:16:42','2026-01-13 22:16:42',10,NULL,'active',NULL),(17,NULL,'whale',2060,NULL,'Jay','XD','2026-01-13 22:58:44','2026-01-14 15:49:20',208,NULL,'active',NULL);
/*!40000 ALTER TABLE `tbl_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_vault_draw_items`
--

DROP TABLE IF EXISTS `tbl_vault_draw_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vault_draw_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vault_draw_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text,
  `estimated_value` decimal(10,2) DEFAULT NULL,
  `image_url` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vault_draw_id` (`vault_draw_id`),
  CONSTRAINT `tbl_vault_draw_items_ibfk_1` FOREIGN KEY (`vault_draw_id`) REFERENCES `tbl_vault_draws` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_vault_draw_items`
--

LOCK TABLES `tbl_vault_draw_items` WRITE;
/*!40000 ALTER TABLE `tbl_vault_draw_items` DISABLE KEYS */;
INSERT INTO `tbl_vault_draw_items` VALUES (3,1,'MacBook Pro M3 Max','The ultimate professional laptop with M3 Max chip, 64GB RAM, and 2TB SSD.',4500.00,'https://images.unsplash.com/photo-1517336714460-4c504009f9a4?q=80&w=1000&auto=format&fit=crop','2025-12-24 04:07:48'),(4,1,'iPhone 15 Pro Titanium','Natural Titanium, 256GB. The first iPhone with an aerospace-grade titanium design.',1599.00,'https://images.unsplash.com/photo-1696446701796-da61225697cc?q=80&w=1000&auto=format&fit=crop','2025-12-24 04:07:48'),(5,1,'Sony WH-1000XM5','Industry-leading noise canceling overhead headphones with exceptional sound.',399.00,'https://images.unsplash.com/photo-1648447226217-040748450213?q=80&w=1000&auto=format&fit=crop','2025-12-24 04:07:48'),(6,1,'Dyson V15 Detect','Powerful, intelligent cordless vacuum. Laser reveals microscopic dust.',899.00,'https://images.unsplash.com/photo-1558317374-067df5f1541a?q=80&w=1000&auto=format&fit=crop','2025-12-24 04:07:48'),(102,4,'aaa','asdsad',200.00,'','2025-12-24 04:18:21'),(103,1,'Neque sit dolorem qu','Deleniti atque recus',24.00,'','2025-12-24 04:18:47'),(104,1,'Aspernatur dolor aut','Aut minim hic dolore',54.00,'','2025-12-24 04:19:02'),(106,4,'Dolores et voluptate','Voluptatibus harum s',47.00,'Alias iusto proident','2025-12-24 05:00:25'),(107,6,'iPhone 15 Pro Max','256GB, Natural Titanium',1800.00,'https://via.placeholder.com/150','2026-01-14 01:53:38'),(108,6,'MacBook Air 15\"','M3 Chip, 16GB RAM',2200.00,'https://via.placeholder.com/150','2026-01-14 01:53:38'),(109,6,'iPad Air','M2 Chip, 128GB',800.00,'https://via.placeholder.com/150','2026-01-14 01:53:38'),(110,6,'AirPods Pro (2nd Gen)','USB-C Charging Case',300.00,'https://via.placeholder.com/150','2026-01-14 01:53:38');
/*!40000 ALTER TABLE `tbl_vault_draw_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_vault_draws`
--

DROP TABLE IF EXISTS `tbl_vault_draws`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vault_draws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `draw_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `prize_value_cents` int(11) NOT NULL,
  `cover_image_url` text,
  `description` text,
  `draw_at` datetime NOT NULL,
  `status` enum('scheduled','active','locked','completed') DEFAULT 'scheduled',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `draw_code` (`draw_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_vault_draws`
--

LOCK TABLES `tbl_vault_draws` WRITE;
/*!40000 ALTER TABLE `tbl_vault_draws` DISABLE KEYS */;
INSERT INTO `tbl_vault_draws` VALUES (1,'MEGA-DEC-2025','Monthly Mega Drawer',750000,NULL,'Our biggest giveaway of the month! WIN a 4K Professional Camera.','2025-12-31 23:59:59','scheduled','2025-12-24 01:59:12','2025-12-24 04:47:54'),(4,'DW-171FE9','asdsd',1500000,NULL,'','2026-01-14 01:12:20','completed','2025-12-24 02:05:15','2026-01-14 05:21:36'),(5,'VAULT-JAN-26','January $20,000 Major Giveaway',2000000,NULL,'Our biggest draw yet! Win a share of $20,000 plus a custom tool kit.','2026-01-31 18:00:00','completed','2026-01-13 21:36:53','2026-01-14 01:53:38'),(6,'VAULT-TECH-26','Ultimate Apple Tech Bundle',500000,'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80','Win the ultimate productivity and entertainment setup! Includes the latest iPhone, MacBook, iPad, and AirPods.','2026-01-15 10:53:38','active','2026-01-14 01:53:38','2026-01-14 05:21:09');
/*!40000 ALTER TABLE `tbl_vault_draws` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_vault_entries`
--

DROP TABLE IF EXISTS `tbl_vault_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vault_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vault_draw_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entries` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vault_draw_id` (`vault_draw_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_vault_entries_ibfk_1` FOREIGN KEY (`vault_draw_id`) REFERENCES `tbl_vault_draws` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_vault_entries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_vault_entries`
--

LOCK TABLES `tbl_vault_entries` WRITE;
/*!40000 ALTER TABLE `tbl_vault_entries` DISABLE KEYS */;
INSERT INTO `tbl_vault_entries` VALUES (1,4,6,19,'2025-12-24 05:22:44','2025-12-25 04:14:55'),(2,4,1,10,'2026-01-13 21:54:46','2026-01-13 21:54:49'),(3,4,17,122,'2026-01-14 09:28:03','2026-01-14 10:21:25'),(6,6,17,86,'2026-01-14 05:19:51','2026-01-14 15:52:18');
/*!40000 ALTER TABLE `tbl_vault_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_vault_entry_transactions`
--

DROP TABLE IF EXISTS `tbl_vault_entry_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vault_entry_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vault_draw_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('subscription_credit','coin_purchase','cash_purchase','retention_bonus','admin_adjustment') NOT NULL,
  `entries_delta` int(11) NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vault_draw_id` (`vault_draw_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_vault_entry_transactions_ibfk_1` FOREIGN KEY (`vault_draw_id`) REFERENCES `tbl_vault_draws` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_vault_entry_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_vault_entry_transactions`
--

LOCK TABLES `tbl_vault_entry_transactions` WRITE;
/*!40000 ALTER TABLE `tbl_vault_entry_transactions` DISABLE KEYS */;
INSERT INTO `tbl_vault_entry_transactions` VALUES (1,4,6,'coin_purchase',1,'{\"package_id\": \"1\"}','2025-12-24 00:22:44'),(2,4,6,'coin_purchase',1,'{\"package_id\": \"1\"}','2025-12-24 23:14:50'),(3,4,6,'coin_purchase',5,'{\"package_id\": \"2\"}','2025-12-24 23:14:53'),(4,4,6,'coin_purchase',12,'{\"package_id\": \"3\"}','2025-12-24 23:14:55'),(5,4,1,'coin_purchase',5,'{\"package_id\": \"2\", \"package_title\": \"Value entries\"}','2026-01-13 21:54:46'),(6,4,1,'coin_purchase',5,'{\"package_id\": \"4\", \"package_title\": \"Starter Entries\"}','2026-01-13 21:54:49'),(7,4,17,'subscription_credit',20,'{\"plan\": \"whale\", \"subscription_id\": \"sub_1SpGZV08JMAyNQJTxbISWG20\"}','2026-01-14 09:28:03'),(8,4,17,'cash_purchase',12,'{\"package_id\": \"5\", \"package_title\": \"Entry Bundle\", \"stripe_payment_intent_id\": \"pi_3SpHBi08JMAyNQJT01xs74IT\"}','2026-01-13 23:39:51'),(9,4,17,'cash_purchase',65,'{\"package_id\": \"6\", \"package_title\": \"Super Pack\", \"stripe_payment_intent_id\": \"pi_3SpHBp08JMAyNQJT1AnPFmW0\"}','2026-01-13 23:39:57'),(10,4,17,'retention_bonus',5,'{\"description\": \"User accepted retention offer\"}','2026-01-14 09:54:32'),(11,4,17,'subscription_credit',20,'{\"plan\": \"whale\", \"subscription_id\": \"sub_1SpGZV08JMAyNQJTxbISWG20\"}','2026-01-14 10:21:25'),(14,6,17,'cash_purchase',1,'{\"package_id\": \"4\", \"package_title\": \"Single Entry\", \"stripe_payment_intent_id\": \"pi_3SpMUk08JMAyNQJT0Zs9wWHJ\"}','2026-01-14 05:19:51'),(15,6,17,'coin_purchase',1,'{\"package_id\": \"1\", \"package_title\": \"Starter Entry\"}','2026-01-14 05:20:05'),(16,6,17,'coin_purchase',1,'{\"package_id\": \"1\", \"package_title\": \"Starter Entry\"}','2026-01-14 05:37:57'),(17,6,17,'coin_purchase',5,'{\"package_id\": \"2\", \"package_title\": \"Value entries\"}','2026-01-14 05:38:08'),(18,6,17,'cash_purchase',1,'{\"package_id\": \"4\", \"package_title\": \"Single Entry\", \"stripe_payment_intent_id\": \"pi_3SpMmn08JMAyNQJT0Kn9zplF\"}','2026-01-14 05:38:29'),(19,6,17,'cash_purchase',12,'{\"package_id\": \"5\", \"package_title\": \"Entry Bundle\", \"stripe_payment_intent_id\": \"pi_3SpMn208JMAyNQJT1cWJyMef\"}','2026-01-14 05:38:45'),(20,6,17,'subscription_credit',20,'{\"plan\": \"whale\", \"subscription_id\": \"sub_1SpGZV08JMAyNQJTxbISWG20\"}','2026-01-14 15:39:10'),(21,6,17,'subscription_credit',20,'{\"plan\": \"whale\", \"subscription_id\": \"sub_1Qzkiv08JMAyNQJT\"}','2026-01-14 15:48:45'),(22,6,17,'subscription_credit',20,'{\"plan\": \"whale\", \"subscription_id\": \"sub_1Qzkiv08JMAyNQJT\"}','2026-01-14 15:49:20'),(23,6,17,'retention_bonus',5,'{\"description\": \"User accepted retention offer\"}','2026-01-14 15:52:18');
/*!40000 ALTER TABLE `tbl_vault_entry_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_vault_winners`
--

DROP TABLE IF EXISTS `tbl_vault_winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_vault_winners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vault_draw_id` int(11) NOT NULL,
  `vault_prize_item_id` int(11) DEFAULT NULL,
  `winning_entries` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `winner_display` varchar(255) NOT NULL,
  `punter_id` varchar(50) NOT NULL,
  `won_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `prize_value_cents` int(11) NOT NULL,
  `items_snapshot` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vault_draw_id` (`vault_draw_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tbl_vault_winners_ibfk_1` FOREIGN KEY (`vault_draw_id`) REFERENCES `tbl_vault_draws` (`id`),
  CONSTRAINT `tbl_vault_winners_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_vault_winners`
--

LOCK TABLES `tbl_vault_winners` WRITE;
/*!40000 ALTER TABLE `tbl_vault_winners` DISABLE KEYS */;
INSERT INTO `tbl_vault_winners` VALUES (1,4,NULL,102,17,'JAY X SP-0017','SP-0017','2026-01-14 00:07:02',1500000,NULL,'2026-01-14 00:07:02'),(2,4,NULL,122,17,'JAY X SP-0017','SP-0017','2026-01-14 01:08:45',1500000,NULL,'2026-01-14 01:08:45');
/*!40000 ALTER TABLE `tbl_vault_winners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_verfication_types`
--

DROP TABLE IF EXISTS `tbl_verfication_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_verfication_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_verfication_types`
--

LOCK TABLES `tbl_verfication_types` WRITE;
/*!40000 ALTER TABLE `tbl_verfication_types` DISABLE KEYS */;
INSERT INTO `tbl_verfication_types` VALUES (1,'Email','2025-02-09 22:20:16','2025-02-09 22:20:16'),(2,'Phone','2025-02-09 22:20:16','2025-02-09 22:20:16');
/*!40000 ALTER TABLE `tbl_verfication_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_video_episodes`
--

DROP TABLE IF EXISTS `tbl_video_episodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_video_episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `episode_name` varchar(255) NOT NULL,
  `youtube_url` text NOT NULL,
  `description` text,
  `thumbnail_url` text,
  `episode_number` int(11) DEFAULT '1',
  `duration_seconds` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `tbl_video_episodes_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `tbl_video_seasons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_video_episodes`
--

LOCK TABLES `tbl_video_episodes` WRITE;
/*!40000 ALTER TABLE `tbl_video_episodes` DISABLE KEYS */;
INSERT INTO `tbl_video_episodes` VALUES (1,1,'The First Hunt','https://www.youtube.com/watch?v=dQw4w9WgXcQ','Our team finds a hidden gem in Melbourne.','https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg',1,600,'2025-12-24 01:51:15','2025-12-24 01:51:15'),(2,1,'Hidden Treasures','https://www.youtube.com/watch?v=dQw4w9WgXcQ','A unit full of antique furniture.','https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg',2,720,'2025-12-24 01:51:15','2025-12-24 01:51:15'),(3,2,'The 100k Vault','https://www.youtube.com/watch?v=dQw4w9WgXcQ','A legendary vault reveal.','https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg',1,900,'2025-12-24 01:51:15','2025-12-24 01:51:15'),(4,1,'The First Hunt','https://www.youtube.com/watch?v=dQw4w9WgXcQ','Our team finds a hidden gem in Melbourne.','https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg',1,600,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(5,1,'Hidden Treasures','https://www.youtube.com/watch?v=dQw4w9WgXcQ','A unit full of antique furniture.','https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg',2,720,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(6,2,'The 100k Vault','https://www.youtube.com/watch?v=dQw4w9WgXcQ','A legendary vault reveal.','https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg',1,900,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(7,5,'First','https://www.youtube.com/shorts/ClH0w1w5eDM','asdasd','',1,600,'2025-12-25 04:31:58','2025-12-25 04:31:58'),(8,5,'Second','https://www.youtube.com/shorts/ClH0w1w5eDM','adasd','https://www.youtube.com/shorts/ClH0w1w5eDM',1,600,'2025-12-25 04:32:56','2025-12-25 04:32:56');
/*!40000 ALTER TABLE `tbl_video_episodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_video_seasons`
--

DROP TABLE IF EXISTS `tbl_video_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_video_seasons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_name` varchar(255) NOT NULL,
  `description` text,
  `order_index` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_video_seasons`
--

LOCK TABLES `tbl_video_seasons` WRITE;
/*!40000 ALTER TABLE `tbl_video_seasons` DISABLE KEYS */;
INSERT INTO `tbl_video_seasons` VALUES (1,'Storage Hunting Pro S1','The beginning of the ultimate storage hunting journey.',1,'2025-12-24 01:51:15','2025-12-24 01:51:15'),(2,'Vault Legends','Exclusive insights into the most valuable vaults ever found.',2,'2025-12-24 01:51:15','2025-12-24 01:51:15'),(3,'Storage Hunting Pro S1','The beginning of the ultimate storage hunting journey.',1,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(4,'Vault Legends','Exclusive insights into the most valuable vaults ever found.',2,'2025-12-24 01:52:39','2025-12-24 01:52:39'),(5,'STORAGE PUNTERS TV','',0,'2025-12-25 04:31:25','2025-12-25 04:31:25');
/*!40000 ALTER TABLE `tbl_video_seasons` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-14  7:10:53
