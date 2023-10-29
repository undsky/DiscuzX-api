/*
!!! 将 bbs_ 替换为你论坛的表前缀 !!!
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bbs_appbyme_activity
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_activity`;
CREATE TABLE `bbs_appbyme_activity` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `stop_time` int(10) unsigned NOT NULL DEFAULT '0',
  `activity_name` varchar(20) NOT NULL DEFAULT '',
  `people` int(5) NOT NULL DEFAULT '0',
  `pic` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `is_run` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `is_run` (`is_run`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_activity_invite
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_activity_invite`;
CREATE TABLE `bbs_appbyme_activity_invite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL DEFAULT '0',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `stop_time` int(10) unsigned NOT NULL DEFAULT '0',
  `sponsor` varchar(30) NOT NULL DEFAULT '',
  `first_reward` int(5) NOT NULL DEFAULT '0',
  `invite_reward` int(5) NOT NULL DEFAULT '0',
  `exchange_min` int(5) NOT NULL DEFAULT '0',
  `virtual_name` varchar(20) NOT NULL DEFAULT '',
  `exchange_ratio` int(5) NOT NULL DEFAULT '0',
  `limit_user` tinyint(1) DEFAULT '0',
  `limit_device` tinyint(1) DEFAULT '0',
  `limit_time` tinyint(1) DEFAULT '0',
  `limit_days` int(5) NOT NULL DEFAULT '0',
  `limit_num` int(5) NOT NULL DEFAULT '0',
  `activity_rule` varchar(100) NOT NULL DEFAULT '',
  `share_appurl` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `activity_id` (`activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_activity_invite_user
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_activity_invite_user`;
CREATE TABLE `bbs_appbyme_activity_invite_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `activity_id` int(11) NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `joining` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `invite_count` int(5) NOT NULL DEFAULT '0',
  `reward_sum` int(5) NOT NULL DEFAULT '0',
  `available_reward` int(5) NOT NULL DEFAULT '0',
  `mobile` varchar(15) NOT NULL DEFAULT '',
  `exchange_status` tinyint(1) NOT NULL DEFAULT '0',
  `exchange_type` varchar(20) NOT NULL DEFAULT '',
  `exchange_num` varchar(20) NOT NULL DEFAULT '',
  `exchange_count` int(5) NOT NULL DEFAULT '0',
  `device` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `username` (`username`),
  KEY `flag` (`flag`),
  KEY `exchange_num` (`exchange_num`),
  KEY `exchange_type` (`exchange_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_auth
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_auth`;
CREATE TABLE `bbs_appbyme_auth` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `allow` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_chat
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_chat`;
CREATE TABLE `bbs_appbyme_chat` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `cid` varchar(50) NOT NULL DEFAULT '',
  `cname` varchar(100) NOT NULL DEFAULT '',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_chatuser
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_chatuser`;
CREATE TABLE `bbs_appbyme_chatuser` (
  `cuid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `cid` varchar(50) NOT NULL DEFAULT '',
  `uname` varchar(50) NOT NULL DEFAULT '',
  `uavatar` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`cuid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_config
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_config`;
CREATE TABLE `bbs_appbyme_config` (
  `ckey` varchar(255) NOT NULL DEFAULT '',
  `cvalue` mediumtext NOT NULL,
  PRIMARY KEY (`ckey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_connection
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_connection`;
CREATE TABLE `bbs_appbyme_connection` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL,
  `openid` char(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `param` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`),
  KEY `openid` (`openid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_fastregister
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_fastregister`;
CREATE TABLE `bbs_appbyme_fastregister` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERNAME` varchar(100) NOT NULL DEFAULT '',
  `DEVICE` varchar(100) DEFAULT '',
  `passwd` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_getpwd
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_getpwd`;
CREATE TABLE `bbs_appbyme_getpwd` (
  `code` int(11) NOT NULL,
  `pwdkey` varchar(200) NOT NULL,
  `time` int(11) NOT NULL,
  `rate` tinyint(4) NOT NULL DEFAULT '0',
  `coderate` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pwdkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_hash
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_hash`;
CREATE TABLE `bbs_appbyme_hash` (
  `text` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_plugin
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_plugin`;
CREATE TABLE `bbs_appbyme_plugin` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(40) NOT NULL,
  `menu` varchar(100) NOT NULL,
  `version` decimal(2,1) NOT NULL,
  `plugin_id` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_plugs_token
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_plugs_token`;
CREATE TABLE `bbs_appbyme_plugs_token` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `plugsid` varchar(50) NOT NULL DEFAULT '',
  `token` varchar(60) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_portal_module
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_portal_module`;
CREATE TABLE `bbs_appbyme_portal_module` (
  `mid` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(230) NOT NULL DEFAULT '',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `displayorder` int(12) NOT NULL DEFAULT '0',
  `param` text NOT NULL,
  PRIMARY KEY (`mid`),
  KEY `displayorder` (`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_portal_module_source
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_portal_module_source`;
CREATE TABLE `bbs_appbyme_portal_module_source` (
  `sid` int(12) NOT NULL AUTO_INCREMENT,
  `mid` int(12) DEFAULT '0',
  `id` int(12) DEFAULT '0',
  `url` varchar(500) DEFAULT '',
  `idtype` varchar(10) DEFAULT '',
  `imgid` int(12) DEFAULT '0',
  `imgurl` varchar(500) DEFAULT '',
  `imgtype` varchar(10) DEFAULT '',
  `title` varchar(200) DEFAULT '',
  `type` tinyint(2) DEFAULT '1',
  `displayorder` int(12) NOT NULL DEFAULT '0',
  `param` text NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `mid` (`mid`,`type`,`idtype`,`imgtype`),
  KEY `displayorder` (`mid`,`type`,`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_recommend_bind
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_recommend_bind`;
CREATE TABLE `bbs_appbyme_recommend_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `moduleId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_recommend_list
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_recommend_list`;
CREATE TABLE `bbs_appbyme_recommend_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `ext` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_recommend_module
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_recommend_module`;
CREATE TABLE `bbs_appbyme_recommend_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `listId` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_sendsms
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_sendsms`;
CREATE TABLE `bbs_appbyme_sendsms` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(15) NOT NULL DEFAULT '',
  `code` varchar(20) NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `param` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_mobile_uid` (`mobile`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_service
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_service`;
CREATE TABLE `bbs_appbyme_service` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `type` char(10) NOT NULL DEFAULT '',
  `keyword` char(20) NOT NULL DEFAULT '',
  `param` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`,`type`),
  KEY `keyword` (`keyword`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_share
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_share`;
CREATE TABLE `bbs_appbyme_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `credit` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `param` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_share_user
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_share_user`;
CREATE TABLE `bbs_appbyme_share_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activityid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `type` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `param` text NOT NULL,
  `form` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`activityid`,`uid`,`time`),
  KEY `uid` (`activityid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_special_users
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_special_users`;
CREATE TABLE `bbs_appbyme_special_users` (
  `uid` mediumint(8) unsigned NOT NULL,
  `username` varchar(15) NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_tempcode
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_tempcode`;
CREATE TABLE `bbs_appbyme_tempcode` (
  `code` varchar(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `time` int(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_topic_items
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_topic_items`;
CREATE TABLE `bbs_appbyme_topic_items` (
  `ti_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ti_title` varchar(200) NOT NULL DEFAULT '',
  `ti_content` varchar(1000) NOT NULL DEFAULT '',
  `ti_cover` varchar(200) NOT NULL DEFAULT '',
  `ti_starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `ti_endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `ti_topiccount` int(10) unsigned NOT NULL DEFAULT '0',
  `ti_topicimg` int(10) unsigned DEFAULT '0',
  `ti_authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `ti_authorname` varchar(50) NOT NULL DEFAULT '',
  `ti_remote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ti_fid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ti_id`),
  KEY `ti_title` (`ti_title`,`ti_authorid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_tpctopost
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_tpctopost`;
CREATE TABLE `bbs_appbyme_tpctopost` (
  `tpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ti_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tpid`),
  KEY `ti_id` (`ti_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_tpctou
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_tpctou`;
CREATE TABLE `bbs_appbyme_tpctou` (
  `ttid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ti_id` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ttid`),
  UNIQUE KEY `tiu` (`ti_id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_uidiyconfig
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_uidiyconfig`;
CREATE TABLE `bbs_appbyme_uidiyconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `app_uidiy_nav_info_temp` mediumtext,
  `app_uidiy_nav_info` mediumtext,
  `app_uidiy_modules_temp` mediumtext,
  `app_uidiy_modules` mediumtext,
  `icon` text,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_user_access
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_user_access`;
CREATE TABLE `bbs_appbyme_user_access` (
  `user_access_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_access_token` varchar(36) NOT NULL DEFAULT '',
  `user_access_secret` varchar(36) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_access_id`),
  UNIQUE KEY `user_access_token` (`user_access_token`,`user_access_secret`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_user_openid
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_user_openid`;
CREATE TABLE `bbs_appbyme_user_openid` (
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `plugsid` varchar(50) NOT NULL DEFAULT '',
  `openid` varchar(60) NOT NULL DEFAULT '',
  UNIQUE KEY `uid_p` (`uid`,`plugsid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_user_setting
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_user_setting`;
CREATE TABLE `bbs_appbyme_user_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ukey` char(20) NOT NULL DEFAULT '',
  `uvalue` text,
  `type` int(11) unsigned NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`uid`,`ukey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_appbyme_visitor
-- ----------------------------
DROP TABLE IF EXISTS `bbs_appbyme_visitor`;
CREATE TABLE `bbs_appbyme_visitor` (
  `uid` char(32) NOT NULL,
  `username` varchar(20) NOT NULL DEFAULT '',
  `uavatar` varchar(200) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bbs_home_surrounding_user
-- ----------------------------
DROP TABLE IF EXISTS `bbs_home_surrounding_user`;
CREATE TABLE `bbs_home_surrounding_user` (
  `poi_id` bigint(12) NOT NULL AUTO_INCREMENT,
  `longitude` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `latitude` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `object_id` bigint(12) NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `location` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`poi_id`),
  UNIQUE KEY `object_id` (`object_id`,`type`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `bbs_forum_memberrecommend` ADD COLUMN `username` varchar(32) NOT NULL DEFAULT '1';

SET FOREIGN_KEY_CHECKS = 1;
