<?php

/**
 * 发送验证码
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class GetCodeAction extends MobcentAction {
    public function run($mobile, $act = 'register', $key = '', $imgCode = 0) {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_sendSms($res, $mobile, $act, $key, $imgCode);
        WebUtils::outputWebApi($res);
    }

    private function _sendSms($res, $mobile, $act, $key, $imgCode) {
        //校验手机号是否有问题
        $checkMobile = UserUtils::checkMobileFormat($mobile);
        if (!$checkMobile) {
            return $this->makeErrorInfo($res, 'mobcent_mobile_error');
        }
        //校验手机号唯一
        $bindInfo = UserUtils::checkMobile($mobile);
        if ($bindInfo) {
            return $this->makeErrorInfo($res, 'mobcent_mobile_repeat');
        }
        //图片验证码判断
        if(WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_image')) {
            if($key == '') {
                return $this->makeErrorInfo($res, 'mobcent_code_update');
            }
            //判断验证码错误
            if(!$imgCode || !UserUtils::checkImg($key, $imgCode)) {
                return $this->makeErrorInfo($res, 'mobcent_code_img_error');
            }
            if(!UserUtils::checkImgCode($key, $imgCode)) {
                return $this->makeErrorInfo($res, 'mobcent_code_images');
            }
        }
        $code = rand(100000, 999999);
        $messageUtils = new MessageUtils();
        $res = $messageUtils->sendCode($res,$mobile,$code,$act);
        if($res['rs']==1){
            if(WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_image')) {
                $this->updateKey($key);
            }
            $this->updateTable($code,$mobile);
        }
        return $res;
    }

    private static function updateTable($code, $mobile) {
        $time = time();
        $inserArray = array(
            'id' => '',
            'mobile' => $mobile,
            'code' => $code,
            'time' => $time,
            'uid' => 0
        );
        $mobileInfo = AppbymeSendsms::getMobileUidInfo($mobile);
        if ($mobileInfo) {
            $updataArr = array('time' => $time, 'code' => $code);
            AppbymeSendsms::updateMobile($mobile, $updataArr);
        } else {
            AppbymeSendsms::insertMobile($inserArray);
        }
    }

    private function updateKey($key)
    {
        DbUtils::createDbUtils(true)->update('appbyme_getpwd', array('rate' => 1), array('pwdkey' => $key));

    }
}