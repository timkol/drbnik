-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `auth_token`;
CREATE TABLE `auth_token` (
  `auth_token_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` int(11) NOT NULL,
  `type` enum('WS','PHP','PHP-PERM') COLLATE utf8_bin NOT NULL,
  `token` char(100) COLLATE utf8_bin NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`auth_token_id`),
  UNIQUE KEY `token` (`token`),
  KEY `login_id` (`login_id`),
  CONSTRAINT `auth_token_ibfk_2` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback` text NOT NULL,
  `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `feedback_author`;
CREATE TABLE `feedback_author` (
  `feedback_author_id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`feedback_author_id`),
  UNIQUE KEY `feedback_author_UNIQUE` (`feedback_id`,`author_id`),
  KEY `author_id` (`author_id`),
  KEY `feedback_id` (`feedback_id`),
  CONSTRAINT `feedback_author_ibfk_12` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`feedback_id`),
  CONSTRAINT `feedback_author_ibfk_7` FOREIGN KEY (`author_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `gossip`;
CREATE TABLE `gossip` (
  `gossip_id` int(11) NOT NULL AUTO_INCREMENT,
  `gossip` text NOT NULL,
  `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`gossip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `gossip_author`;
CREATE TABLE `gossip_author` (
  `gossip_author_id` int(11) NOT NULL AUTO_INCREMENT,
  `gossip_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`gossip_author_id`),
  UNIQUE KEY `gossip_author_UNIQUE` (`gossip_id`,`author_id`),
  KEY `author_id` (`author_id`),
  KEY `gossip_id` (`gossip_id`),
  CONSTRAINT `gossip_author_ibfk_12` FOREIGN KEY (`gossip_id`) REFERENCES `gossip` (`gossip_id`),
  CONSTRAINT `gossip_author_ibfk_7` FOREIGN KEY (`author_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `gossip_history`;
CREATE TABLE `gossip_history` (
  `gossip_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `gossip_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `login_id` int(11) DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`gossip_history_id`),
  KEY `gossip_id` (`gossip_id`),
  KEY `status_id` (`status_id`),
  KEY `login_id` (`login_id`),
  CONSTRAINT `gossip_history_ibfk_1` FOREIGN KEY (`gossip_id`) REFERENCES `gossip` (`gossip_id`),
  CONSTRAINT `gossip_history_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  CONSTRAINT `gossip_history_ibfk_5` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `gossip_victim`;
CREATE TABLE `gossip_victim` (
  `gossip_victim_id` int(11) NOT NULL AUTO_INCREMENT,
  `gossip_id` int(11) NOT NULL,
  `victim_id` int(11) NOT NULL,
  PRIMARY KEY (`gossip_victim_id`),
  UNIQUE KEY `gossip_victim_UNIQUE` (`gossip_id`,`victim_id`),
  KEY `victim_id` (`victim_id`),
  KEY `gossip_id` (`gossip_id`),
  CONSTRAINT `gossip_victim_ibfk_1` FOREIGN KEY (`gossip_id`) REFERENCES `gossip` (`gossip_id`),
  CONSTRAINT `gossip_victim_ibfk_2` FOREIGN KEY (`victim_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `grant`;
CREATE TABLE `grant` (
  `grant_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`grant_id`),
  UNIQUE KEY `grant_UNIQUE` (`role_id`,`login_id`),
  KEY `login_id` (`login_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `grant_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE,
  CONSTRAINT `grant_ibfk_3` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `login` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Login name',
  `hash` char(60) CHARACTER SET utf8 DEFAULT NULL COMMENT 'sha1(login_id . md5(password)) as hexadecimal',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`login_id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `person_id_UNIQUE` (`person_id`),
  CONSTRAINT `login_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) DEFAULT NULL,
  `family_name` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Příjmení (nebo více příjmení oddělených jednou mezerou)',
  `other_name` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Křestní jména, von, de atd., oddělená jednou mezerou',
  `display_name` varchar(511) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'zobrazované jméno, liší-li se od <other_name> <family_name>',
  `gender` enum('M','F') CHARACTER SET utf8 NOT NULL,
  `person_type` enum('pako','org','visit') CHARACTER SET utf8 NOT NULL DEFAULT 'pako',
  `lang` enum('cs-CZ','sk-SK') CHARACTER SET utf8 NOT NULL DEFAULT 'cs-CZ',
  `foto_filename` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'jméno souboru s fotkou (bez cesty)',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `person_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='řazení: <family_name><other_name>, zobrazení <other_name> <f';


DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `description` text,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `status`;
CREATE TABLE `status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `description` text,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `team_points`;
CREATE TABLE `team_points` (
  `team_points_id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL COMMENT 'autorizovaná osoba',
  `points_change` int(11) NOT NULL,
  `note` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`team_points_id`),
  KEY `team_id` (`team_id`),
  KEY `org_id` (`org_id`),
  CONSTRAINT `team_points_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`team_id`),
  CONSTRAINT `team_points_ibfk_3` FOREIGN KEY (`org_id`) REFERENCES `login` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `token`;
CREATE TABLE `token` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` char(10) NOT NULL,
  `cd_time` datetime NOT NULL,
  `previous` int(11) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `token` (`token`),
  KEY `previous` (`previous`),
  CONSTRAINT `token_ibfk_1` FOREIGN KEY (`previous`) REFERENCES `token` (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `trial`;
CREATE TABLE `trial` (
  `trial_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`trial_id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `trial_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `trial_pass`;
CREATE TABLE `trial_pass` (
  `trial_pass_id` int(11) NOT NULL AUTO_INCREMENT,
  `trial_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`trial_pass_id`),
  KEY `trial_id` (`trial_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `trial_pass_ibfk_1` FOREIGN KEY (`trial_id`) REFERENCES `trial` (`trial_id`),
  CONSTRAINT `trial_pass_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2017-02-27 12:04:02
