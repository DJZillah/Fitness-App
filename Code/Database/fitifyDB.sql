-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com    Database: fitifyDB
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
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

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '';

--
-- Table structure for table `Simple_Cal_Log`
--

DROP TABLE IF EXISTS `Simple_Cal_Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Simple_Cal_Log` (
  `LogID` int NOT NULL AUTO_INCREMENT,
  `TotalCal` int NOT NULL,
  `LogDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LogID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Simple_Cal_Log`
--

LOCK TABLES `Simple_Cal_Log` WRITE;
/*!40000 ALTER TABLE `Simple_Cal_Log` DISABLE KEYS */;
INSERT INTO `Simple_Cal_Log` VALUES (1,890,'2025-03-21 15:24:28'),(2,1074,'2025-03-21 15:46:10');
/*!40000 ALTER TABLE `Simple_Cal_Log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `activity_id` int NOT NULL AUTO_INCREMENT,
  `activity_name` varchar(30) NOT NULL,
  `activity_calories` decimal(5,2) NOT NULL,
  PRIMARY KEY (`activity_id`),
  UNIQUE KEY `activity_name` (`activity_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bmi_records`
--

DROP TABLE IF EXISTS `bmi_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bmi_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `height` float NOT NULL,
  `weight` float NOT NULL,
  `bmi` float NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bmi_records`
--

LOCK TABLES `bmi_records` WRITE;
/*!40000 ALTER TABLE `bmi_records` DISABLE KEYS */;
INSERT INTO `bmi_records` VALUES (1,'goob',67,180,28.1889,'Overweight','2025-03-25 02:53:33'),(2,'goob',67,180,28.1889,'Overweight','2025-03-25 03:01:47');
/*!40000 ALTER TABLE `bmi_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calories_log`
--

DROP TABLE IF EXISTS `calories_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calories_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `activity_id` int NOT NULL,
  `total_calories` decimal(6,2) NOT NULL,
  `log_date` date NOT NULL,
  `duration_minutes` int NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `activity_id` (`activity_id`),
  CONSTRAINT `calories_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `calories_log_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calories_log`
--

LOCK TABLES `calories_log` WRITE;
/*!40000 ALTER TABLE `calories_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `calories_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exercises`
--

DROP TABLE IF EXISTS `exercises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exercises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exercise_name` varchar(50) NOT NULL,
  `muscle_group` varchar(50) NOT NULL,
  `equipment_needed` varchar(50) DEFAULT 'Bodyweight',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercises`
--

LOCK TABLES `exercises` WRITE;
/*!40000 ALTER TABLE `exercises` DISABLE KEYS */;
INSERT INTO `exercises` VALUES (1,'Bench Press','Chest, Triceps, Shoulders','Barbell'),(2,'Push-Ups','Chest, Triceps, Shoulders','Bodyweight'),(3,'Pull-Ups','Back, Biceps','Pull-Up Bar'),(4,'Bent-Over Rows','Back, Biceps','Barbell'),(5,'Squats','Quads, Hamstrings, Glutes','Barbell'),(6,'Deadlifts','Hamstrings, Glutes, Lower Back','Barbell'),(7,'Lunges','Quads, Hamstrings, Glutes','Bodyweight or Dumbbells'),(8,'Leg Press','Quads, Hamstrings, Glutes','Leg Press Machine'),(9,'Overhead Press','Shoulders, Triceps','Barbell or Dumbbells'),(10,'Lateral Raises','Shoulders','Dumbbells'),(11,'Bicep Curls','Biceps','Dumbbells'),(12,'Triceps Dips','Triceps','Parallel Bars or Bench'),(13,'Hammer Curls','Biceps','Dumbbells'),(14,'Planks','Core, Shoulders','Bodyweight'),(15,'Hanging Leg Raises','Abs, Hip Flexors','Pull-Up Bar'),(16,'Russian Twists','Obliques','Bodyweight or Medicine Ball'),(17,'Burpees','Full Body, Conditioning','Bodyweight'),(18,'Jump Rope','Cardio, Calves','Jump Rope'),(19,'Kettlebell Swings','Glutes, Hamstrings, Core','Kettlebell'),(20,'Standing Calf Raises','Calves','Bodyweight or Machine');
/*!40000 ALTER TABLE `exercises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fitness_goals`
--

DROP TABLE IF EXISTS `fitness_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fitness_goals` (
  `goal_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `goal_type` enum('weight loss','muscle gain','endurance') DEFAULT NULL,
  `target_value` decimal(6,2) DEFAULT NULL,
  `current_progress` decimal(6,2) DEFAULT '0.00',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('in progress','completed','failed') DEFAULT 'in progress',
  PRIMARY KEY (`goal_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fitness_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fitness_goals`
--

LOCK TABLES `fitness_goals` WRITE;
/*!40000 ALTER TABLE `fitness_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `fitness_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meals`
--

DROP TABLE IF EXISTS `meals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meals` (
  `meal_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `day_of_week` varchar(10) NOT NULL,
  `meal_name` varchar(100) NOT NULL,
  `meal_type` varchar(10) NOT NULL,
  `calories` decimal(6,2) NOT NULL,
  `protein` decimal(5,2) DEFAULT NULL,
  `carbs` decimal(5,2) DEFAULT NULL,
  `fats` decimal(5,2) DEFAULT NULL,
  `meal_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`meal_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meals`
--

LOCK TABLES `meals` WRITE;
/*!40000 ALTER TABLE `meals` DISABLE KEYS */;
/*!40000 ALTER TABLE `meals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_plans`
--

DROP TABLE IF EXISTS `training_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_plans` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `instructor_id` int NOT NULL,
  `student_id` int NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  PRIMARY KEY (`plan_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `training_plans_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `training_plans_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_plans`
--

LOCK TABLES `training_plans` WRITE;
/*!40000 ALTER TABLE `training_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `age` int DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weight_log`
--

DROP TABLE IF EXISTS `weight_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `weight_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `weight` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weight_log`
--

LOCK TABLES `weight_log` WRITE;
/*!40000 ALTER TABLE `weight_log` DISABLE KEYS */;
INSERT INTO `weight_log` VALUES (2,150.00,'2025-03-25 22:07:53'),(5,180.00,'2025-03-25 22:31:07');
/*!40000 ALTER TABLE `weight_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workout_logs`
--

DROP TABLE IF EXISTS `workout_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workout_logs` (
  `workout_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `workout_type` varchar(50) NOT NULL,
  `duration_minutes` int NOT NULL,
  `calories_burned` decimal(6,2) NOT NULL,
  `weight_used` decimal(10,2) DEFAULT NULL,
  `reps` int DEFAULT NULL,
  `sets` int DEFAULT NULL,
  `log_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`workout_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `workout_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workout_logs`
--

LOCK TABLES `workout_logs` WRITE;
/*!40000 ALTER TABLE `workout_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `workout_logs` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-26 18:54:08
