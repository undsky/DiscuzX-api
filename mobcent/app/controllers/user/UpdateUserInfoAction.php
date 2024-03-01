<?php

/**
 * Updated user information interface
 *
 * @author HanPengyu 
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

// Mobcent::setErrors();
class UpdateUserInfoAction extends MobcentAction {

    public function run($type, $gender = '', $oldPassword = '', $newPassword = '', $userInfo = '', $sign = '') {
        $res = $this->initWebApiArray();
        if ($type == 'lock') {
            global $_G;
            C::t('common_member')->update($_G['uid'], array('status'=>'-1'));
        } elseif ($type == 'info') {
            $res = $this->_updateUser($res, $gender, $sign);
        } elseif ($type == 'password') {
            $res = $this->_updatePass($res, $oldPassword, $newPassword);
        } elseif ($type == 'userInfo') {
            $res = $this->_updateUserInfo($res, $userInfo);
        }
        echo WebUtils::outputWebApi($res, '', false);
    }

    private function _updateUser($res, $gender,$sign) {
        global $_G;
        include_once libfile('function/profile');
        $sign = WebUtils::removeEmoji($sign);
        $sign = WebUtils::t($sign);
        if (!empty($sign)) {
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
        }else{
            DB::update('common_member_field_forum', array('sightml' => ''), array('uid' => $_G['uid']));
        }
        $setarr ['gender'] = intval($gender);
        if ($setarr) {
            C::t('common_member_profile')->update($_G['uid'], $setarr);
        }
        manyoulog('user', $_G['uid'], 'update');
        $operation = 'gender';
        include_once libfile('function/feed');
        feed_add('profile', 'feed_profile_update_' . $operation, array('hash_data' => 'profile'));
        countprofileprogress();


        return $this->makeErrorInfo($res, lang('message', 'profile_succeed'), array('noError' => 1));
    }

    private function _updatePass($res, $oldpassword, $newpassword) {
        global $_G;
        $oldpassword = $oldpassword ? urldecode($oldpassword) : '';
        $newpassword = $newpassword ? urldecode($newpassword) : '';
        if (!empty($newpassword) && $newpassword != addslashes($newpassword)) {
            // 抱歉，密码空或包含非法字符:新密码
            return $this->makeErrorInfo($res, lang('message', 'profile_passwd_illegal'));
        }

        //基于Discuz判断密码强度
        if (!empty($newpassword) && $_G['setting']['strongpw']) {
            $strongpw_str = array();
            if (in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_1');
            }
            if (in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_2');
            }
            if (in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_3');
            }
            if (in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_4');
            }
            if ($strongpw_str) {
                $msg = lang('member/template', 'password_weak') . implode(',', $strongpw_str);
                return $this->makeErrorInfo($res, $msg);
            }
        }
        loaducenter();
        $ucresult = uc_user_edit(addslashes($_G['username']), $oldpassword, $newpassword, 1);
        if ($ucresult == -1) {
            // 原密码不正确，您不能修改密码或 Email 或安全提问
            return $this->makeErrorInfo($res, lang('message', 'profile_passwd_wrong'));
        }

        $setarr['password'] = md5(random(10));
        C::t('common_member')->update($_G['uid'], $setarr);

        $secretStr = AppbymeUserAccess::getSecretStr($_G['uid'], $newpassword);
        $newAccessSecret = $secretStr['accessSecret'];
        $data = array('user_access_secret' => $newAccessSecret);
        $result = AppbymeUserAccess::updateUserAccess($data, $_G['uid']);
        // if (!$result) {
        //     return $this->makeErrorInfo($res, 'user_info_edit_error');
        // }
        $res['token'] = $secretStr['accessToken'];
        $res['secret'] = $newAccessSecret;
        return $res;
    }

    private function _updateUserInfo($res, $userInfo) {
        global $_G;
        $userInfo = rawurldecode($userInfo);
        $userInfoArr = json_decode($userInfo, true);
        $userInfoArr = $userInfoArr ?$userInfoArr:array();
        $check = ProfileUtils::checkUserInfo(array_merge($userInfoArr,$_FILES));
        if ($check['errCode'] != 0) {
            return $this->makeErrorInfo($res, $check['errMsg']);
        }
        if(empty($check['return'])){
            return $this->makeErrorInfo($res, WebUtils::t('您没有需要修改的数据.'));
        }
        ProfileUtils::updateUserInfo($_G['uid'], $check['return']);
        return $res;
    }

}
