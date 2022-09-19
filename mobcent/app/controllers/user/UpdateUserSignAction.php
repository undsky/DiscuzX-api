<?php

/**
 * 更新用户签名
 *
 * @author NaiXiaoXin<nxx@yytest.cn> 
 * @copyright 2012-2016 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

// Mobcent::setErrors();
class UpdateUserSignAction extends MobcentAction {

    public function run($sign = '') {
        $res = $this->initWebApiArray();
        $sign = rawurldecode($sign);
        $sign = WebUtils::t($sign);
        $res = $this->editSign($res, $sign);
        WebUtils::outputWebApi($res);
    }

    public function editSign($res, $sign) {
        global $_G;
        if (empty($_G['group']['maxsigsize'])) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'no_privilege_editsign');
        }
        if (dstrlen($sign) > $_G['group']['maxsigsize']) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'too_long_sign', array('{number}' => $_G['group']['maxsigsize']));
        }
        $censor = discuz_censor::instance();
        $censor->check($sign);
        if ($censor->modbanned() || $censor->modmoderated()) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'profile_censor_sign');
        }
        DB::update('common_member_field_forum', array('sightml' => $sign), array('uid' => $_G['uid']));
        return $res;
    }

}
