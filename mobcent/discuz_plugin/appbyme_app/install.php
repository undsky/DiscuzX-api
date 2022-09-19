<?php

/**
 * 插件安装时执行此文件
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 * @license http://opensource.org/licenses/LGPL-3.0
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `cdb_home_surrounding_user` (
    `poi_id` bigint(12) NOT NULL AUTO_INCREMENT,
    `longitude` decimal(10,7) NOT NULL DEFAULT '0',
    `latitude` decimal(10,7) NOT NULL DEFAULT '0',
    `object_id` bigint(12) NOT NULL DEFAULT '0',
    `type` tinyint(2) NOT NULL DEFAULT '0',
    `location` varchar(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`poi_id`),
    UNIQUE KEY `object_id` (`object_id`, `type`),
    KEY `type` (`type`)
) ENGINE=MyISAM;

# 用户登陆表
DROP TABLE IF EXISTS `cdb_appbyme_user_access`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_user_access` (
    `user_access_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_access_token` varchar(36) NOT NULL DEFAULT '',
    `user_access_secret` varchar(36) NOT NULL DEFAULT '',
    `user_id` int(11) NOT NULL DEFAULT '0',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0',
     PRIMARY KEY (`user_access_id`),
     UNIQUE KEY `user_access_token` (`user_access_token`, `user_access_secret`),
     UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM;

# 用户设置表
# DROP TABLE IF EXISTS `cdb_appbyme_user_setting`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_user_setting` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `ukey` CHAR(20) NOT NULL DEFAULT '',
    `uvalue` TEXT NOT NULL DEFAULT '',
    `type` INT(11) UNSIGNED NOT NULL DEFAULT '5',
    PRIMARY KEY (`id`),
    UNIQUE KEY `key` (`uid`, `ukey`)
) ENGINE=MyISAM;

# 设置表
# DROP TABLE IF EXISTS `cdb_appbyme_config`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_config` (
  `ckey` varchar(255) NOT NULL DEFAULT '',
  `cvalue` mediumtext NOT NULL,
  PRIMARY KEY (`ckey`)
) ENGINE=MyISAM;

# 门户模块表
# DROP TABLE IF EXISTS `cdb_appbyme_portal_module`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_portal_module` (
    `mid` int(12) NOT NULL AUTO_INCREMENT,
    `name` varchar(230) NOT NULL DEFAULT '',
    `type` tinyint(2) NOT NULL DEFAULT '0',
    `displayorder` int(12) NOT NULL DEFAULT '0',
    `param` text NOT NULL,
    PRIMARY KEY (`mid`),
    KEY `displayorder` (`displayorder`)
) ENGINE=MyISAM;

# 门户模块数据表
# DROP TABLE IF EXISTS `cdb_appbyme_portal_module_source`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_portal_module_source` (
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
    KEY `mid` (`mid`, `type`, `idtype`, `imgtype`),
    KEY `displayorder` (`mid`, `type`, `displayorder`)
) ENGINE=MyISAM;

# 第三方登陆接口绑定
# DROP TABLE IF EXISTS `cdb_appbyme_connection`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_connection` (
    `id` int(12) NOT NULL AUTO_INCREMENT,
    `uid` mediumint(8) unsigned NOT NULL,
    `openid` char(32) NOT NULL DEFAULT '',
    `status` tinyint(1) NOT NULL DEFAULT '0',
    `type` tinyint(1) NOT NULL DEFAULT '0',
    `param` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`, `type`),
    KEY `openid` (`openid`, `type`)
) ENGINE=MyISAM;

# 公共服务
# DROP TABLE IF EXISTS `cdb_appbyme_service`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_service` (
    `id` int(12) NOT NULL AUTO_INCREMENT,
    `title` varchar(20) NOT NULL DEFAULT '',
    `icon` varchar(255) NOT NULL DEFAULT '',
    `type` char(10) NOT NULL DEFAULT '',
    `keyword` char(20) NOT NULL DEFAULT '',
    `param` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `title` (`title`, `type`),
    KEY `keyword` (`keyword`, `type`)
) ENGINE=MyISAM;

# 手机绑定表
# DROP TABLE IF EXISTS `cdb_appbyme_sendsms`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_sendsms` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(15) NOT NULL DEFAULT '',
  `code` varchar(20) NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10)  unsigned NOT NULL DEFAULT '0',
  `param` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_mobile_uid` (`mobile`,`uid`)
) ENGINE=MyISAM;

# 活动主表
# DROP TABLE IF EXISTS `cdb_appbyme_activity`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_activity` (
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
) ENGINE=MyISAM;

#邀请注册活动表
# DROP TABLE IF EXISTS `cdb_appbyme_activity_invite`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_activity_invite` (
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
) ENGINE=MyISAM;

#邀请注册用户表
# DROP TABLE IF EXISTS `cdb_appbyme_activity_invite_user`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_activity_invite_user` (
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
) ENGINE=MyISAM;

#快速注册表
#DROP TABLE IF EXISTS `cdb_appbyme_fastregister`;
CREATE TABLE IF NOT EXISTS `cdb_appbyme_fastregister` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `USERNAME` varchar(100) NOT NULL DEFAULT '',
  `DEVICE` varchar(100) DEFAULT '',
  `passwd` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM;

# 插件表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_plugin` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(20) NOT NULL,
  `menu` char(20) NOT NULL,
  `version` decimal(2,1) NOT NULL,
  `plugin_id` char(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  ;
# 权限控制表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_auth` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `allow` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

 # 多UI表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_uidiyconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `app_uidiy_nav_info_temp` MEDIUMTEXT,
  `app_uidiy_nav_info` MEDIUMTEXT,
  `app_uidiy_modules_temp` MEDIUMTEXT,
  `app_uidiy_modules` MEDIUMTEXT,
  `icon` text,
  `status` int(1) NOT NULL DEFAULT '1',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `cdb_appbyme_share_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activityid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `type` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `param` text NOT NULL,
  `form` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time`  (`activityid`,`uid`,`time`) ,
  KEY `uid` (`activityid`,`uid`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `cdb_appbyme_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `credit` text NOT NULL,
  `type` varchar(255) NOT NULL ,
  `param` text ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `cdb_appbyme_tempcode` (
  `code` varchar(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `time` int(12) NOT NULL
) ENGINE=MyISAM;


#话题表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_topic_items` (
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
  `ti_fid` int(11)  NOT NULL DEFAULT '0',
  PRIMARY KEY (`ti_id`),
  KEY `ti_title` (`ti_title`,`ti_authorid`)
) ENGINE=MyISAM;

#话题帖子关联表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_tpctopost` (
  `tpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ti_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tpid`),
  KEY `ti_id` (`ti_id`)
) ENGINE=MyISAM;


#话题关注表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_tpctou` (
  `ttid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ti_id` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ttid`),
  UNIQUE KEY `tiu` (`ti_id`,`uid`)
) ENGINE=MyISAM;

#聊天室
CREATE TABLE IF NOT EXISTS `cdb_appbyme_chat` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' ,
  `cid` varchar(50) NOT NULL DEFAULT '' ,
  `cname` varchar(100) NOT NULL DEFAULT '' ,
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`cid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

#聊天室用户
CREATE TABLE IF NOT EXISTS `cdb_appbyme_chatuser` (
  `cuid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `cid` varchar(50) NOT NULL DEFAULT '',
  `uname` varchar(50) NOT NULL DEFAULT '',
  `uavatar` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`cuid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;

#插件Token
CREATE TABLE IF NOT EXISTS `cdb_appbyme_plugs_token` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `plugsid` varchar(50) NOT NULL DEFAULT '',
  `token` varchar(60) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM;

#用户OPENID
CREATE TABLE IF NOT EXISTS `cdb_appbyme_user_openid` (
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `plugsid` varchar(50) NOT NULL DEFAULT '',
  `openid` varchar(60) NOT NULL DEFAULT '',
  UNIQUE KEY `uid_p` (`uid`,`plugsid`)
) ENGINE=MyISAM;

#批量导贴用户表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_special_users` (
  `uid` mediumint(8) unsigned NOT NULL,
  `username` varchar(15) NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `type` TINYINT NOT NULL DEFAULT '0',
    PRIMARY KEY (uid,username)
) ENGINE=MyISAM;

#hash判断表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_hash` (
  `text` varchar(100) NOT NULL DEFAULT '' PRIMARY KEY
) ENGINE=MyISAM;

#直播游客表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_visitor` (
    `uid` CHAR(32) NOT NULL ,
    `username` VARCHAR(20) NOT NULL DEFAULT '',
    `uavatar` VARCHAR(200) NOT NULL DEFAULT '',
    `time` INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`uid`)
) ENGINE = MyISAM;

#找回密码表
CREATE TABLE IF NOT EXISTS `cdb_appbyme_getpwd` (
  `code` int(11) NOT NULL,
  `pwdkey` varchar(200) NOT NULL,
  `time` int(11) NOT NULL,
  `rate` tinyint(4) NOT NULL DEFAULT '0',
  `coderate` tinyint(4) NOT NULL DEFAULT '0',
   PRIMARY KEY (`pwdkey`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `cdb_appbyme_recommend_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `moduleId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `cdb_appbyme_recommend_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `ext` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `cdb_appbyme_recommend_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `listId` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) 

ALTER TABLE `cdb_appbyme_config` CHANGE cvalue cvalue MEDIUMTEXT;
ALTER TABLE `cdb_appbyme_plugin` CHANGE menu menu VARCHAR(100) NOT NULL ;
ALTER TABLE `cdb_appbyme_plugin` CHANGE plugin_id plugin_id VARCHAR(20) NOT NULL ;
ALTER TABLE `cdb_appbyme_plugin` CHANGE plugin_name plugin_name VARCHAR(40) NOT NULL ;
ALTER TABLE `cdb_appbyme_uidiyconfig` CHANGE app_uidiy_nav_info_temp app_uidiy_nav_info_temp MEDIUMTEXT;
ALTER TABLE `cdb_appbyme_uidiyconfig` CHANGE app_uidiy_nav_info app_uidiy_nav_info MEDIUMTEXT;
ALTER TABLE `cdb_appbyme_uidiyconfig` CHANGE app_uidiy_modules_temp app_uidiy_modules_temp MEDIUMTEXT;
ALTER TABLE `cdb_appbyme_uidiyconfig` CHANGE app_uidiy_modules app_uidiy_modules MEDIUMTEXT;
ALTER TABLE `cdb_appbyme_user_setting` CHANGE uvalue uvalue TEXT;
EOF;

runquery($sql);


$username = DB::fetch_all("SHOW COLUMNS FROM %t", array('appbyme_user_setting'));
$usernamearray = mysqltoarray($username);
if (!in_array('type', $usernamearray)) {
    $sql1 = <<<EOF
ALTER TABLE `cdb_appbyme_user_setting` ADD COLUMN `type` int(11) NOT NULL DEFAULT '5';
EOF;
    runquery($sql1);
}

$fidtables = DB::fetch_all("SHOW COLUMNS FROM %t", array('appbyme_topic_items'));
$fidarray = mysqltoarray($fidtables);
if (!in_array('ti_fid', $fidarray)) {
    $sql1 = <<<EOF
ALTER TABLE `cdb_appbyme_topic_items` ADD COLUMN `ti_fid` int(11) NOT NULL DEFAULT '0';
EOF;
runquery($sql1);

  $fid = DB::result_first("SELECT cvalue from %t where ckey='topic_bind_fid'",array('appbyme_config'));
  if(!empty($fid)){
    DB::update('appbyme_topic_items',array('ti_fid'=>$fid),'');
  }
}

$username = DB::fetch_all("SHOW COLUMNS FROM %t", array('forum_memberrecommend'));
$usernamearray = mysqltoarray($username);
if (!in_array('username', $usernamearray)) {
    $sql1 = <<<EOF
ALTER TABLE `cdb_forum_memberrecommend` ADD COLUMN `username` varchar(32) NOT NULL DEFAULT '1';
EOF;
    runquery($sql1);
}



$finish = TRUE;

function mysqltoarray($test) {
    $temp = array();
    foreach ($test as $k => $s) {
        $temp[] = $s['Field'];
    }
    return $temp;
}
