<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/11/28
 * Time: 下午7:41
 * Email: mqdjie@gmail.com
 */

class DZCommonSetting extends DiscuzAR
{
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{common_setting}}';
    }

    public function rules() {
        return array(
        );
    }

    public static function getSvalueUns($skey)
    {
        $res = DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT svalue
            FROM %t
            WHERE skey=%s
        ',
            array('common_setting', $skey)
        );
        return $res ? $res : '';
    }
}