<?php

/**
 * 验证手机短信验证码接口
 *
 * @author HanPengyu
 * @copyright 2012-2015 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class CheckMobileCodeAction extends MobcentAction {

    public function run($mobile, $code, $type = '') {
        $res = $this->initWebApiArray();
        $res = $this->_checkMobileCode($res, $mobile, $code, $type);
        echo WebUtils::outputWebApi($res, '', true);
    }

    private function _checkMobileCode($res, $mobile, $code,$type) {
        global $_G;
        $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
        if ($checkInfo['rs'] == 0) {
            return $this->makeErrorInfo($res, $checkInfo['errcode']);
        } else {
            if(in_array($type,array('bind','change'))){
                if (empty($_G['uid'])) {
                    WebUtils::endAppWithErrorInfo(array('rs' => 0, 'errcode' => 50000000), 'to_login');
                }
                $bindTemp = AppbymeSendsms::checkUserBindMobile($_G['uid']);
                if ($type == 'bind') {
                    //绑定
                    if($bindTemp){
                        return $this->makeErrorInfo($res,WebUtils::t('此账号已绑定手机号'));
                    }
                    $updataArr = array('uid' => $_G['uid']);
                    AppbymeSendsms::updateMobile($mobile, $updataArr);
                }elseif($type=='change'){
                    $config = WebUtils::getDzPluginAppbymeAppConfig('mobcent_user_change_mobile');
                    if ($config != '1') {
                        return $this->makeErrorInfo($res,WebUtils::t('修改手机号未开启'));
                    }
                    if($bindTemp){
                        $updataArr = array('uid' => 0);
                        AppbymeSendsms::updateMobile($bindTemp['mobile'], $updataArr);
                    }
                    $updataArr = array('uid' => $_G['uid']);
                    AppbymeSendsms::updateMobile($mobile, $updataArr);
                }
            }
        }
        return $res;
    }

}

?>