
CREATE DATABASE IF NOT EXISTS mixup;
GRANT ALL ON `mixup`.* TO `mixup-user`@localhost IDENTIFIED BY 'mixup-user-99';

USE mixup;

SET foreign_key_checks = 0;

SET NAMES utf8;
SET CHARACTER SET utf8;

SELECT 'creating: mu_users';

DROP TABLE IF EXISTS mu_users;
CREATE TABLE mu_users (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `facebook_id` bigint(20) unsigned DEFAULT NULL,
  `google_id` varchar(22) DEFAULT NULL,
  `twitter_id` bigint(20) unsigned DEFAULT NULL,
  `json` text,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `facebook_id` (`facebook_id`),
  UNIQUE KEY `google_id` (`google_id`),
  UNIQUE KEY `twitter_id` (`twitter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `mu_users` SET id = 1, email = 'ace@mixup.com', json = '{"name":"Ace","age":20}', created = NOW();
INSERT INTO `mu_users` SET id = 2, email = 'chase@mixup.com', json = '{"name":"Chase","age":25}', created = NOW();
INSERT INTO `mu_users` SET id = 3, email = 'maize@mixup.com', json = '{"name":"Maize","age":23}', created = NOW();

SELECT 'creating: mu_media';

DROP TABLE IF EXISTS mu_media;
CREATE TABLE mu_media (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bucket` varchar(64) DEFAULT NULL,
  `s3_path` varchar(255) NOT NULL DEFAULT '',
  `web_path` varchar(255) NOT NULL DEFAULT '',
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NULL DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `total_likes` int(11) unsigned NOT NULL DEFAULT '0',
  `total_dislikes` int(11) unsigned NOT NULL DEFAULT '0',
  `media_type` int(11) unsigned NOT NULL DEFAULT '0',
  `md` text,
  `caption` varchar(255) DEFAULT NULL,
  `stared` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `s3_path` (`s3_path`),
  FOREIGN KEY (user_id) REFERENCES mu_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `mu_media` SET user_id = 3, media_type = 1, created = NOW();

-- cool !

SET foreign_key_checks = 1;

