-- Adminer 4.7.6 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `fyp` /*!40100 DEFAULT CHARACTER SET utf8 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `fyp`;

DROP TABLE IF EXISTS `experiment`;
CREATE TABLE `experiment` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) NOT NULL,
                              `time` datetime NOT NULL,
                              `iterations` int NOT NULL,
                              `sleep` int NOT NULL,
                              `thread_count` int NOT NULL,
                              `content_length` int NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `thread`;
CREATE TABLE `thread` (
                          `id` int NOT NULL AUTO_INCREMENT,
                          `name` varchar(255) NOT NULL,
                          `experiment_id` int NOT NULL,
                          PRIMARY KEY (`id`),
                          KEY `experiment_id` (`experiment_id`),
                          CONSTRAINT `thread_ibfk_1` FOREIGN KEY (`experiment_id`) REFERENCES `experiment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `thread_run`;
CREATE TABLE `thread_run` (
                              `id` int NOT NULL AUTO_INCREMENT,
                              `start` bigint NOT NULL,
                              `end` bigint NOT NULL,
                              `time` datetime NOT NULL,
                              `thread_id` int NOT NULL,
                              `local_duration` int NOT NULL,
                              `server_duration` int NOT NULL,
                              PRIMARY KEY (`id`),
                              KEY `thread_id` (`thread_id`),
                              CONSTRAINT `thread_run_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `thread` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2020-03-29 16:50:05