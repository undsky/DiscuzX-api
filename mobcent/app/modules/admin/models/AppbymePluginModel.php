<?php
/**
 * WSH model类
 *
 * @author denny
 * @copyright 2012-2015 Appbyme
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class AppbymePluginModel extends DiscuzAR {

    public function tableName() {
        return '{{appbyme_plugin}}';
    }

    public static function allPlugin() {
        return DbUtils::getDzDbUtils(true)->queryAll('
            SELECT *
            FROM %t
            ',
            array('appbyme_plugin')
        );
    }

    public static function getPlugin($plugin_id) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE plugin_id=%s
            ',
            array('appbyme_plugin', $plugin_id)
        );
    }

    public static function insertPlugin($data) {
        return DbUtils::getDzDbUtils(true)->insert('appbyme_plugin', $data);
    }

    public static function updatePlugin($mid, $data) {
        return DbUtils::getDzDbUtils(true)->update('appbyme_plugin', $data, array('id' => $mid));
    }

    public static function delPlugin($mid) {
        return DbUtils::getDzDbUtils(true)->delete('appbyme_plugin', array('id' => $mid));
    }


}