/*
SQLyog Community v12.5.1 (64 bit)
MySQL - 5.6.35 : Database - camagru
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `email` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `confirmed` int(1) NOT NULL DEFAULT '0',
  `notification` int(1) NOT NULL DEFAULT '0'
);

/*Table structure for table `confirmations` */

DROP TABLE IF EXISTS `confirmations`;

CREATE TABLE `confirmations` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `user_id` int(10) NOT NULL,
  `uuid` text NOT NULL,
  CONSTRAINT `confirmations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);

/*Table structure for table `images` */

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `user_id` int(10) NOT NULL,
  `path` text NOT NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `images_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);

/*Table structure for table `image_comments` */

DROP TABLE IF EXISTS `image_comments`;

CREATE TABLE `image_comments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `user_id` int(10) NOT NULL,
  `image_id` int(10) NOT NULL,
  `text` text NOT NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `image_comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `image_comments_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`)
);

/*Table structure for table `image_likes` */

DROP TABLE IF EXISTS `image_likes`;

CREATE TABLE `image_likes` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `user_id` int(10) NOT NULL,
  `image_id` int(10) NOT NULL,
  CONSTRAINT `image_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `image_likes_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`)
);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
