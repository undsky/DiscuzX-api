ALTER TABLE `表前缀_forum_memberrecommend`
ADD COLUMN `username` varchar(32) NULL DEFAULT 1;

CREATE TABLE `表前缀_appbyme_config` (
  `ckey` varchar(255) NOT NULL DEFAULT '',
  `cvalue` mediumtext NOT NULL,
  PRIMARY KEY (`ckey`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `表前缀_appbyme_sendsms` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(15) NOT NULL DEFAULT '',
  `code` varchar(20) NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `param` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_mobile` (`mobile`) USING BTREE,
  KEY `idx_mobile_uid` (`mobile`,`uid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `表前缀_appbyme_tpctopost` (
  `tpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ti_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tpid`) USING BTREE,
  KEY `ti_id` (`ti_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `表前缀_appbyme_user_access` (
  `user_access_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_access_token` varchar(36) NOT NULL DEFAULT '',
  `user_access_secret` varchar(36) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_access_id`) USING BTREE,
  UNIQUE KEY `user_access_token` (`user_access_token`,`user_access_secret`) USING BTREE,
  UNIQUE KEY `user_id` (`user_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `表前缀_appbyme_user_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ukey` char(20) NOT NULL DEFAULT '',
  `uvalue` text,
  `type` int(11) unsigned NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `key` (`uid`,`ukey`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `表前缀_home_surrounding_user` (
  `poi_id` bigint(12) NOT NULL AUTO_INCREMENT,
  `longitude` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `latitude` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `object_id` bigint(12) NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`poi_id`) USING BTREE,
  UNIQUE KEY `object_id` (`object_id`,`type`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;