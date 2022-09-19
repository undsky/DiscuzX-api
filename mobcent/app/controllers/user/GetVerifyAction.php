<?php
/**
 * 获得认证信息
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class GetVerifyAction extends MobcentAction {

    private $verifyInfo = array();

    public function run() {
        $res = WebUtils::initWebApiArray_oldVersion();
        $this->setVerifyInfo();
        $res = $this->_getVerify($res);
        WebUtils::outputWebApi($res);
    }

    private function _getVerify($res) {
        global $_G;
        $space = getuserbyuid($_G['uid']);
        space_merge($space, 'field_home');
        space_merge($space, 'profile');
        //获取认证信息
        $verifyInfo = $_G['setting']['verify'];
        if ($verifyInfo['enabled'] != '1') {
            return $this->makeErrorInfo($res, WebUtils::t('当前没有可认证信息'));
        }
        unset($verifyInfo['enabled']);
        $verify = C::t('common_member_verify')->fetch($_G['uid']);
        foreach ($verifyInfo as $vid => $info) {
            if (is_array($info) && $info['available'] != 1) {
                unset($verifyInfo[$vid]);
            }
            if (!empty($info['groupid']) && !in_array($_G['groupid'], $info['groupid'])) {
                unset($verifyInfo[$vid]);
            }
        }
        if (empty($verifyInfo)) {
            return $this->makeErrorInfo($res, WebUtils::t('当前没有可认证信息'));
        }
        $result = array();
        ProfileUtils::init();
        foreach ($verifyInfo as $vid => $info) {
            if ($vid == 'enabled') {
                continue;
            }
            $temp = array();
            $temp['name'] = $info['title'];
            $temp['vid'] = $vid;
            $temp['flag'] = intval($this->getVerifyFlag($verify['verify' . $vid], $vid));
            $temp['field'] = ProfileUtils::_getProFile($info['field'], $space);
            if ($info['showicon']) {
                if ($temp['flag'] == 1) {
                    $temp['icon'] = WebUtils::getHttpFileName($info['icon']);
                } else {
                    $temp['icon'] = WebUtils::getHttpFileName($info['unverifyicon']);
                }
            }
            $temp['icon'] = strval($temp['icon']);
            $result[] = $temp;
        }
        $res['list'] = $result;
        return $res;
    }


    private function getVerifyFlag($flag, $vid) {
        if ($flag == '0') {
            if (in_array($vid, $this->verifyInfo)) {
                $flag = 2;
            }
        }
        return $flag;
    }

    private function setVerifyInfo() {
        global $_G;
        $result = DbUtils::getDzDbUtils(true)->queryAll('SELECT `verifytype` FROM %t WHERE `uid`=%d AND `flag`=%d AND verifytype>%d', array('common_member_verify_info', $_G['uid'], 0, 0));
        if (!empty($result)) {
            foreach ($result as $k => $v) {
                $this->verifyInfo[] = $v['verifytype'];
            }
        }
    }
}