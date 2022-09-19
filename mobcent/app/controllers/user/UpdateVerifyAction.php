<?php
/**
 *
 * 提交认证
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class  UpdateVerifyAction extends MobcentAction{
    public function run($vid,$userInfo){
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_updateVerify($res,$vid,$userInfo);
        WebUtils::outputWebApi($res);
    }


    private function _updateVerify($res,$vid,$userInfo){
        global $_G;
        if (empty($vid)) {
            return $this->makeErrorInfo($res, WebUtils::t('认证ID不存在,请确定认证ID存在'));
        }
        //获取认证信息
        $verifyInfo = $_G['setting']['verify'];
        if ($verifyInfo['enabled'] != '1') {
            return $this->makeErrorInfo($res, WebUtils::t('当前没有可认证信息'));
        }
        $verify = C::t('common_member_verify')->fetch($_G['uid']);
        if (!empty($verify) && is_array($verify)) {
            foreach ($verify as $key => $flag) {
                if (in_array($key, array('verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7')) && $flag == 1) {
                    $verifyid = intval(substr($key, -1, 1));
                    unset($verifyInfo[$verifyid]);
                }
            }
        }
        foreach ($verifyInfo as $key => $info) {
            if (is_array($info) && $info['available'] != 1) {
                unset($verifyInfo[$key]);
            }
            if (!empty($info['groupid']) && !in_array($_G['groupid'], $info['groupid'])) {
                unset($verifyInfo[$key]);
            }
        }
        $verifyResult = $verifyInfo[$vid];
        if (empty($verifyResult)) {
            return $this->makeErrorInfo($res, WebUtils::t('您已认证或当前认证不存在'));

        }
        $verifyconfig = $_G['setting']['verify'][$vid];
        if ($verifyconfig['available'] && (empty($verifyconfig['groupid']) || in_array($_G['groupid'], $verifyconfig['groupid']))) {
            $verifyinfo = C::t('common_member_verify_info')->fetch_by_uid_verifytype($_G['uid'], $vid);
            if (!empty($verifyinfo)) {
                $verifyinfo['field'] = dunserialize($verifyinfo['field']);
            }
            foreach ($verifyconfig['field'] as $key => $field) {
                if (!isset($verifyinfo['field'][$key])) {
                    $verifyinfo['field'][$key] = $key;
                }
            }
        }
        $userInfo = rawurldecode($userInfo);
        $userInfoArr = WebUtils::jsonDecode($userInfo, true);
        //  debug($verifyResult);
        $proFile = new ProfileUtils();
        foreach ($verifyResult['field'] as $key=>$value){
            if(in_array($value,$proFile->filterArray)){
                continue;
            }
            $dbArr[$value] = $userInfoArr[$value];
        }
        $dbArr = $dbArr ?$dbArr:array();
        $check = ProfileUtils::checkUserInfo(array_merge($dbArr,$_FILES));
        if ($check['errCode'] != 0) {
            return $this->makeErrorInfo($res, $check['errMsg']);
        }
        ProfileUtils::updateUserInfo($_G['uid'], $check['return']);


        C::t('common_member_verify_info')->delete_by_uid($_G['uid'], $vid);
        $setverify = array(
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'verifytype' => $vid,
            'field' => serialize($check['return']),
            'dateline' => $_G['timestamp']
        );

        C::t('common_member_verify_info')->insert($setverify);
        if(!(C::t('common_member_verify')->count_by_uid($_G['uid']))) {
            C::t('common_member_verify')->insert(array('uid' => $_G['uid']));
        }else{
            C::t('common_member_verify')->update($_G['uid'],array('verify'.$vid=>0));
        }
        if($_G['setting']['verify'][$vid]['available']) {
            manage_addnotify('verify_'.$vid, 0, array('langkey' => 'manage_verify_field', 'verifyname' => $_G['setting']['verify'][$vid]['title'], 'doid' => $vid));
        }
        return  WebUtils::makeErrorInfo_oldVersion($res,WebUtils::t('申请认证成功,请等待管理员审核'),array('noError'=>1));
        //return $res;
    }
}