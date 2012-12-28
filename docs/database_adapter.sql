SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;

DROP TABLE IF EXISTS `cache_entries`;
CREATE TABLE IF NOT EXISTS `cache_entries` (
  `key` varchar(255) NOT NULL,
  `contents` blob,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cache_infos`;
CREATE TABLE IF NOT EXISTS `cache_infos` (
  `key` varchar(255) NOT NULL,
  `created_on` int(11) NOT NULL,
  `max_age` text,
  `tags` text,
  PRIMARY KEY (`key`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;
