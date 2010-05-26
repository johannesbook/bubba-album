SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS album;
DROP TABLE IF EXISTS file;
DROP TABLE IF EXISTS image;
DROP TABLE IF EXISTS exif;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS access;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `access` (
  `username` varchar(255) NOT NULL,
  `album` mediumint(9) NOT NULL,
  PRIMARY KEY  (`username`,`album`),
  KEY `album` (`album`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `album` (
  `id` mediumint(9) NOT NULL auto_increment COMMENT 'album identifier',
  `name` varchar(255) NOT NULL COMMENT 'The name of the album',
  `path` varchar(4096) NOT NULL COMMENT 'path to the album',
  `caption` text COMMENT 'The caption of the album',
  `parent` mediumint(9) default NULL COMMENT 'Parent album, null if top level album',
  `public` tinyint(1) default '1' COMMENT 'if the album public',
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `path_index` (`path`(255)),
  CONSTRAINT `album_parent_id` FOREIGN KEY (`parent`) REFERENCES `album` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `exif` (
  `image` mediumint(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text,
  KEY `image` (`image`),
  CONSTRAINT `exif_image_id` FOREIGN KEY (`image`) REFERENCES `image` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `image` (
  `id` mediumint(9) NOT NULL auto_increment,
  `path` varchar(4096) NOT NULL COMMENT 'The path to the file in question',
  `album` mediumint(9) default NULL COMMENT 'In what album it is',
  `name` varchar(255) NOT NULL COMMENT 'The name of the file',
  `caption` text COMMENT 'Caption of the image',
  `width` smallint(5) unsigned default NULL COMMENT 'width of image',
  `height` smallint(5) unsigned default NULL COMMENT 'height of image',
  PRIMARY KEY  (`id`),
  KEY `album` (`album`),
  CONSTRAINT `image_album_id` FOREIGN KEY (`album`) REFERENCES `album` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `user_data` text NOT NULL,
  PRIMARY KEY  (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
