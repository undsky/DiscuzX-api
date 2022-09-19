<?php
/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */


if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class LiveController extends ApiController {

    public function actionGetpushtoken() {
        $array = $this->db->queryAll('SELECT ff.`uvalue` FROM %t f,%t ff WHERE f.`followuid`=%d AND f.`uid`=ff.`uid` AND ff.`ukey`=%s  AND ff.`type`=%d',
            array(
                'home_follow', 'appbyme_user_setting', $this->data['uid'], AppbymeUserSetting::KEY_DEVICE_TOKEN, $this->data['platType']
            )
        );
        $data = array();
        foreach ($array as $key=>$value){
//            $temp = unserialize($value['uvalue']);
//            if($temp ===false){
//                $data[] = $temp;
//            }else{
//                if($temp[$this->data['forumKey']]){
//                    $data[] = $temp[$this->data['forumKey']];
//                }
//            }
            $data[] = $value['uvalue'];
        }
        $this->setData($data);
    }


    protected function setRules() {
        return array(
            'getpushtoken' => array(
                'uid' => 'int',
                'platType' => 'int',
            )
        );
    }
}