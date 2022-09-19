<?php

/**
 * 安米插件配置model
 *
 * @author 徐少伟 <xushaowei@mobcent.com>
 * @copyright 2012-2014 Appbyme
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AppbymeConfig extends DiscuzAR {

    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{appbyme_config}}';
    }

    public function rules() {
        return array(
        );
    }

    public static function getDownloadOptions() {
        $data = DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', 'app_download_options')
        );
        return $data ? (array)unserialize($data) : array();
    }

    public static function saveDownloadOptions($appInfo)
    {
        $appDownloadOptions = array('ckey' => 'app_download_options', 'cvalue' => serialize($appInfo));
        $tempData = DB::insert('appbyme_config',$appDownloadOptions,false,true);
        return $tempData;
    }

    //通用取出forumKey
    public static function getForumKey_other($ckey) {
        $data = DB::fetch_first("SELECT * FROM " . DB::table('appbyme_config') . " WHERE ckey='".$ckey."'");
        return $data ? (array)unserialize($data['cvalue']) : array();
    }
    //通用forumKey保存
    public static function saveForumKey_other($ckey, $appInfo)
    {
        $appDownloadOptions = array('ckey' => $ckey, 'cvalue' => serialize($appInfo));
        $tempData = DB::insert('appbyme_config',$appDownloadOptions,false,true);
        return $tempData;
    }
    public static function saveForumkey($forumKey)
    {
        $appForumKey = array('ckey' => 'app_forumkey', 'cvalue' => $forumKey);
        $res = DB::insert('appbyme_config',$appForumKey,false,true);
        return $res;
    }
    public static function saveSecretkey($secretKey)
    {
        $appSecretkey = array('ckey' => 'secretKey', 'cvalue' => $secretKey);
        $tempData = DB::fetch_first("SELECT * FROM ".DB::table('appbyme_config')." WHERE ckey='secretKey'");
        if (empty($tempData)) {
            DB::insert('appbyme_config', $appSecretkey);
        } else {
            DB::update('appbyme_config', $appSecretkey, array('ckey' => 'secretKey'));
        }
    }


    public static function getForumkey() {
        return (string)DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', 'app_forumkey')
        );
    }
    
    public static function setAPNsCertfilePassword($password) {
        $key = 'certfile_apns_passphrase';

        $data = array(
            'ckey' => $key,
            'cvalue' => base64_encode($password),
        );
        $tempData = DbUtils::createDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ',
            array('appbyme_config', $key)
        );
        if (empty($tempData)) {
            DbUtils::createDbUtils(true)->insert('appbyme_config', $data);
        } else {
            DbUtils::createDbUtils(true)->update('appbyme_config', $data, array('ckey' => $key));
        }
    }

    public static function getAPNsCertfilePassword() {
        $data = DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', 'certfile_apns_passphrase')
        );
        return (string)base64_decode($data);
    }

    public static function getShareActivityId() {
        return DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', 'share_activityid')
        );
    }

    public static function saveShareActivityId($id) {
        $shareActivityIdKey = array('ckey' => 'share_activityid', 'cvalue' => $id);
        $tempData = DB::fetch_first("SELECT * FROM " . DB::table('appbyme_config') . " WHERE ckey='share_activityid'");
        if (empty($tempData)) {
            DB::insert('appbyme_config', $shareActivityIdKey);
        } else {
            DB::update('appbyme_config', $shareActivityIdKey, array('ckey' => 'share_activityid'));
        }
    }

    public static function delShareActivityId(){
        DB::delete('appbyme_config', array('ckey' => 'share_activityid'));
    }

    public static function getWebAppInfo() {
        $data =  DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', 'webapp_config')
        );
        return $data ? (array)unserialize($data) : array();

    }

    public static function getCvalue($key) {
        $data =  DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', $key)
        );
        return $data ? (array)unserialize($data) : array();
    }

    public static function getCvalueData($key,$default=null) {
        $data =  DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', $key)
        );
        $data = unserialize($data) !== false ? unserialize($data):$data;
        if(null === $default){
            return $data;
        }else{

            return $data!==false?$data:$default;
        }
    }

    public static function saveCvalue($ckey, $cvalue) {
        $Info= array('ckey' => $ckey, 'cvalue' =>is_array($cvalue)?serialize($cvalue):$cvalue);
        return DB::insert('appbyme_config',$Info,false,true);
    }

    public static function saveWebAppInfo($info) {
        $webappInfoKey = array('ckey' => 'webapp_config', 'cvalue' =>serialize($info));
        $tempData = DB::fetch_first("SELECT * FROM " . DB::table('appbyme_config') . " WHERE ckey='webapp_config'");
        if (empty($tempData)) {
            DB::insert('appbyme_config', $webappInfoKey);
        } else {
            DB::update('appbyme_config', $webappInfoKey, array('ckey' => 'webapp_config'));
        }
    }

    //查出forumkey_secretkey
    public static function getForumKeySecretKey($ckey) {
        $data = DB::fetch_first("SELECT * FROM " . DB::table('appbyme_config') . " WHERE ckey='".$ckey."'");
        return $data ? (array)unserialize($data['cvalue']) : array();
    }

    public static function saveCkey($ckey) {
        return DB::insert('appbyme_config',$ckey,false,true);
    }

    public static function getSetting() {
        static $data;
        if(empty($data)){
            $data =  DbUtils::createDbUtils(true)->queryScalar('
                SELECT cvalue
                FROM %t
                WHERE ckey = %s
            ',
                array('appbyme_config', 'app_setting')
            );
        }

        return $data ? (array)unserialize($data) : array();
    }
    public static function saveSetting($info) {
        $settingKey = array('ckey' => 'app_setting', 'cvalue' =>serialize($info));
        $tempData = DB::fetch_first("SELECT * FROM " . DB::table('appbyme_config') . " WHERE ckey='app_setting'");
        if (empty($tempData)) {
            DB::insert('appbyme_config', $settingKey);
        } else {
            DB::update('appbyme_config', $settingKey, array('ckey' => 'app_setting'));
        }
    }


    public static  function getAppInfoByForumKey($forumKey){
        $data = DB::fetch_first("SELECT * FROM " . DB::table('appbyme_config') . " WHERE ckey='ForumKey_app_download_options'");
        $array = unserialize($data['cvalue']);
        if(!is_array($array)){
            $array = array();
        }
        return $array[$forumKey] ? $array[$forumKey] : array();
    }
}