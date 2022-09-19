<?php

/**
 * 用户设置 model类
 *
 * @author 谢建平 <xiejianping@mobcent.com>
 * @copyright 2012-2014 Appbyme
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AppbymeUserSetting extends DiscuzAR {

    const KEY_GPS_LOCATION = 'hidden';
    const KEY_DEVICE_TOKEN = 'deviceToken';

    const VALUE_GPS_LOCATION_ON = 0;
    const VALUE_GPS_LOCATION_OFF = 1;

    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{appbyme_user_setting}}';
    }

    public function rules() {
        return array(
            array('uid, ukey, uvalue', 'safe'),
        );
    }

    // public function attributeLabels() {
    //  return array(
    //  );
    // }

    /**
     * saveNewSetting
     * 
     * @param int $uid.
     * @param array $settings.
     * @param bool $return.
     *
     * @return bool|array.
     */
    public static function saveNewSettings($uid, $settings) {
        // save new settings
        foreach ($settings as $key => $value) {
            if($key == 'deviceToken') {
                DbUtils::getDzDbUtils(true)->delete('appbyme_user_setting',array(
                    'ukey' => $key,
                    'uvalue' => $value,
                ));
            }
            $array['id'] = DbUtils::getDzDbUtils(true)->queryFirst("SELECT `id` FROM %t WHERE `uid`=%d AND `ukey`=%s",array('appbyme_user_setting',$uid,$key));
            $array['uid'] = $uid;
            $array['ukey'] = $key;
            $array['uvalue'] = $value;
            $array['type'] = $_GET['platType']?$_GET['platType']:APP_TYPE_APPLE;
            DbUtils::getDzDbUtils(true)->insert('appbyme_user_setting',$array,true,true);
        }
        return true;
    }

    public static function isGPSLocationOn($uid) {
        $config = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT * 
            FROM %t
            WHERE uid=%d
            AND ukey=%s
        ',
            array('appbyme_user_setting', $uid, self::KEY_GPS_LOCATION)
        );
        return !(!empty($config) && $config['uvalue'] == 1);
    }

    public static function getUserDeviceToken($uid) {
        return (string)DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT uvalue
            FROM %t
            WHERE uid=%d
            AND ukey=%s
            AND `type`=%d
        ',
            array('appbyme_user_setting', $uid, self::KEY_DEVICE_TOKEN,APP_TYPE_APPLE)
        );
    }

    public static function delUserDeviceToken($deviceToken) {
        return DbUtils::getDzDbUtils(true)->delete('appbyme_user_setting',array('uvalue'=>$deviceToken,'type'=>APP_TYPE_APPLE));
    }

    public static function delUserToken($uid) {
        return DbUtils::getDzDbUtils(true)->delete('appbyme_user_setting',array('ukey'=>self::KEY_DEVICE_TOKEN,'uid'=>$uid));
    }

    public static function getUvalue($uid)
    {
        $forumKey = $_GET['forumKey'];
        $uvalue =DbUtils::getDzDbUtils(true)->queryFirst("SELECT `uvalue` FROM %t WHERE `uid`=%d AND `type`=%s",array('appbyme_user_setting',$uid, APP_TYPE_APPLE));
        $uvalue = unserialize($uvalue);
        if(!$uvalue) {
            return false;
        }
        unset($uvalue[$forumKey]);
        return $uvalue;
    }

}