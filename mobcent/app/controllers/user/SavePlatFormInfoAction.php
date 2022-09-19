<?php

/**
 * 保存第三方绑定登录信息
 *
 * @author HanPengyu 
 * @author 耐小心<nxx@yytest.cn>
 * @copyright 2012-2016 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

//Mobcent::setErrors();

class SavePlatFormInfoAction extends MobcentAction {

    public $password = MOBCENT_HACKER_PASSWORD;

    public function run($username, $oauthToken, $password = '', $openId, $email = '', $gender = 0, $act = 'register', $platformId = 20, $isValidation = 0, $mobile = '', $code = '') {
        $username = WebUtils::t(rawurldecode($username));
        $email = WebUtils::t(rawurldecode($email));
        $password = rawurldecode($password);
        $res = $this->initWebApiArray();
        $res = $this->getPlatFormInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code);
        echo WebUtils::outputWebApi($res, '', false);
    }

    public function getPlatFormInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code) {
        if ($platformId == 20) {
            $res = $this->_saveQqInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code);
        } elseif ($platformId == 30||$platformId==60||$platformId==70) {
            $res = $this->_saveWxInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code);
        } elseif ($platformId == 40) {
            $res = $this->_saveFBInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId);
        } elseif($platformId==50){
            $res = $this->_saveQqSdkInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code);
        }
        return $res;
    }

    private function _saveQqInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code) {
        global $_G;
        if ($act == 'register') {
            if ($isValidation == 1) {
                // 是否开启注册手机验证
                $isRegisterValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
                if ($isRegisterValidation) {
                    $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                    if ($checkInfo['rs'] == 0) {
                        return $this->makeErrorInfo($res, $checkInfo['errcode']);
                    }
                }
            }
            $regInfo = UserUtils::register($username, $password, $email, 'qq');
            if ($regInfo['errcode']) {
                return $this->makeErrorInfo($res, $regInfo['message']);
            }

            $uid = $regInfo['info']['uid'];
            $userInfo = UserUtils::getUserInfo($uid);
            $userAccess = AppbymeUserAccess::registerProcess($uid, $password);
            if ($isValidation) {
                if ($isRegisterValidation) {
                    // 注册完毕之后更新手机验证信息
                    $updataArr = array('uid' => $regInfo['info']['uid']);
                    AppbymeSendsms::updateMobile($mobile, $updataArr);
                }
            }
            $this->_updateQqMember($uid, $oauthToken, $openId, $gender);

            $res['body']['token'] = (string) $userAccess['token'];
            $res['body']['secret'] = (string) $userAccess['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        } elseif ($act == 'bind' && !empty($openId)) {

            global $_G;
            $logInfo = UserUtils::login($username, $password);
            if ($logInfo['errcode']) {
                UserUtils::delUserAccessByUsername($username);
                return $this->makeErrorInfo($res, 'mobcent_bind_error');
            }
            if ($isValidation == 1) {
                // 是否开启了登录手机验证
                $isLoginValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_login_validation');
                if ($isLoginValidation) {
                    $userMobileBind = AppbymeSendsms::checkUserBindMobile($_G['uid']);
                    if (!$userMobileBind) { // 当前登录的用户没有绑定手机号码
                        if ($mobile == '' && $code == '') {
                            $res['isValidation'] = 1;
                            return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
                        }

                        $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                        if ($checkInfo['rs'] == 0) {
                            return $this->makeErrorInfo($res, $checkInfo['errcode']);
                        }

                        $updataArr = array('uid' => $_G['uid']);
                        AppbymeSendsms::updateMobile($mobile, $updataArr);
                    }
                }
            }


            $isBind = $this->_getUserBindInfo($_G['uid']);
            if ($isBind) {
                return $this->makeErrorInfo($res, 'mobcent_bind_error_repeat');
            }

            $this->_updateQqMember($_G['uid'], $oauthToken, $openId, $gender);

            $userInfo = AppbymeUserAccess::loginProcess($_G['uid'], $password);
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        }

        // 客户端参数不正确
        return $this->makeErrorInfo($res, 'mobcent_error_params');
    }

    private function _getUserBindInfo($uid) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE uid=%d
            ', array('common_member_connect', $uid)
        );
    }

    private function _inserBindlog($data) {
        return DbUtils::getDzDbUtils(true)->insert('connect_memberbindlog', $data);
    }

    private function _inserConnect($data) {
        return DbUtils::getDzDbUtils(true)->insert('common_member_connect', $data);
    }

    private function _updateQqMember($uid, $oauthToken, $openId, $gender) {
        global $_G;
        $qqdata = array(
            'uid' => $uid,
            'conuin' => $oauthToken,
            'conuinsecret' => '',
            'conopenid' => $openId,
            'conisfeed' => 1,
            'conispublishfeed' => 1,
            'conispublisht' => 1,
            'conisregister' => 1,
            'conisqqshow' => 1,
        );
        $qqbind = array('mblid' => '', 'uid' => $uid, 'uin' => $openId, 'type' => 1, 'dateline' => time());
        $this->_inserBindlog($qqbind);
        $this->_inserConnect($qqdata);
        $updateInfo = array('avatarstatus' => 1, 'conisbind' => 1); // 用户是否绑定QQ
        DzCommonMember::updateMember($updateInfo, array('uid' => $uid));
        $setarr ['gender'] = intval($gender);
        C::t('common_member_profile')->update($uid, $setarr);

        $ipArray = explode('.', $_G['clientip']);
        $sid = FileUtils::getRandomFileName('', 6);
        $data = array(
            'sid' => $sid,
            'ip1' => $ipArray[0],
            'ip2' => $ipArray[1],
            'ip3' => $ipArray[2],
            'ip4' => $ipArray[3],
            'uid' => $userInfo['uid'],
            'username' => $userInfo['username'],
            'groupid' => $userInfo['groupid'],
            'invisible' => '0',
            'action' => '',
            'lastactivity' => time(),
            'fid' => '0',
            'tid' => '0',
            'lastolupdate' => '0'
        );
        DzCommonSession::insertComSess($data);
        require_once libfile('cache/userstats', 'function');
        build_cache_userstats();
    }

    private function _saveWxInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code) {
        global $_G;
        if($platformId==70){
            $wxUserInfo = WebUtils::jsonDecode(rawurldecode($_GET['json']));
        }else{
            $wxUserInfo = AppbymeConnection::getUserInfoFromWeiXin($openId, $oauthToken);
            if(!empty($wxUserInfo['errcode'])){
                return $this->makeErrorInfo($res,WebUtils::t('请求微信服务器错误,请重试'));
            }
        }
        $data['status'] = 1;
        if ($act == 'fastreg') {
            $is_wechat_register = WebUtils::getDzPluginAppbymeAppConfig('is_wechat_register');
            if ($is_wechat_register) {
                $password = MOBCENT_HACKER_PASSWORD . FileUtils::getRandomFileName('', 3);
                $email = random('5') . '@appbyme.com';
            }
            $data['status'] = 2;
            $act = 'register';
        }
        if ($act == 'register') {
            if ($isValidation == 1) {
                // 是否开启注册手机验证
                $isRegisterValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
                if ($isRegisterValidation) {
                    $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                    if ($checkInfo['rs'] == 0) {
                        return $this->makeErrorInfo($res, $checkInfo['errcode']);
                    }
                }
            }
            $regInfo = UserUtils::register($username, $password, $email);
            if ($regInfo['errcode']) {
                return $this->makeErrorInfo($res, $regInfo['message']);
            }

            $uid = $regInfo['info']['uid'];
            $member = UserUtils::getUserInfo($uid);
            $userInfo = AppbymeUserAccess::registerProcess($regInfo['info']['uid'], $password);

//            $data = array('uid' => $uid, 'openid' => $openId, 'status' => 2, 'type' => 1, 'param' => $wxUserInfo['unionid']);
            $data['uid'] = $uid;
            $data['openid'] = $openId;
            $data['type'] = AppbymeConnection::WECHAT_TYPE;
            $data['param'] = $wxUserInfo['unionid'];
            if(($platformId==60||$platformId==70)&&!empty($wxUserInfo['unionid'])){
                unset($data['openid']);
            }
            AppbymeConnection::syncAvatar($member['uid'], $wxUserInfo['headimgurl']);
            AppbymeConnection::insertMobcentWx($data);

            if ($isValidation) {
                if ($isRegisterValidation) {
                    // 注册完毕之后更新手机验证信息
                    $updataArr = array('uid' => $regInfo['info']['uid']);
                    AppbymeSendsms::updateMobile($mobile, $updataArr);
                }
            }
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        } elseif ($act == 'bind' && !empty($openId)) {

            global $_G;
            $logInfo = UserUtils::login($username, $password);
            if (!empty($logInfo['errcode'])) {
                UserUtils::delUserAccessByUsername($username);
                return $this->makeErrorInfo($res, 'mobcent_bind_error');
            }
            if ($isValidation == 1) {
                // 是否开启了登录手机验证
                $isLoginValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_login_validation');
                if ($isLoginValidation) {
                    $userMobileBind = AppbymeSendsms::checkUserBindMobile($_G['uid']);
                    if (!$userMobileBind) { // 当前登录的用户没有绑定手机号码
                        if ($mobile == '' && $code == '') {
                            $res['isValidation'] = 1;
                            return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
                        }

                        $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                        if ($checkInfo['rs'] == 0) {
                            return $this->makeErrorInfo($res, $checkInfo['errcode']);
                        }

                        $updataArr = array('uid' => $_G['uid']);
                        AppbymeSendsms::updateMobile($mobile, $updataArr);
                    }
                }
            }
            $isBind = AppbymeConnection::getUserBindInfo($_G['uid']);
            if ($isBind) {
                return $this->makeErrorInfo($res, 'mobcent_bind_error_repeat');
            }

            $data = array('uid' => $_G['uid'], 'openid' => $openId, 'status' => 1, 'type' => 1, 'param' => $wxUserInfo['unionid']);
            if(($platformId==60||$platformId==70)&&!empty($wxUserInfo['unionid'])){
                unset($data['openid']);
            }
            AppbymeConnection::insertMobcentWx($data);

            $userInfo = AppbymeUserAccess::loginProcess($_G['uid'], $password);
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_params');
    }

    private function _saveFBInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId) {
        if ($act == 'register') {

            $regInfo = UserUtils::register($username, $password, $email);
            if ($regInfo['errcode']) {
                return $this->makeErrorInfo($res, $regInfo['message']);
            }

            $uid = $regInfo['info']['uid'];
            $member = UserUtils::getUserInfo($uid);
            $userInfo = AppbymeUserAccess::registerProcess($regInfo['info']['uid'], $password);

            $data = array('uid' => $uid, 'openid' => $openId, 'status' => 1, 'type' => 2);
            AppbymeConnection::insertMobcentWx($data);
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($uid);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        } elseif ($act == 'bind' && !empty($openId)) {

            global $_G;
            $logInfo = UserUtils::login($username, $password);
            if ($logInfo['errcode']) {
                UserUtils::delUserAccessByUsername($username);
                return $this->makeErrorInfo($res, 'mobcent_bind_error');
            }

            $isBind = AppbymeConnection::getUserBindInfo($_G['uid'], '2');
            if ($isBind) {
                return $this->makeErrorInfo($res, 'mobcent_bind_error_repeat');
            }

            $data = array('uid' => $_G['uid'], 'openid' => $openId, 'status' => 1, 'type' => 2);
            AppbymeConnection::insertMobcentWx($data);

            $userInfo = AppbymeUserAccess::loginProcess($_G['uid'], $password);
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_params');
    }



    private function _saveQqSdkInfo($res, $username, $oauthToken, $password, $openId, $email, $gender, $act, $platformId, $isValidation, $mobile, $code) {
        global $_G;
        if ($act == 'register') {
            if ($isValidation == 1) {
                // 是否开启注册手机验证
                $isRegisterValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
                if ($isRegisterValidation) {
                    $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                    if ($checkInfo['rs'] == 0) {
                        return $this->makeErrorInfo($res, $checkInfo['errcode']);
                    }
                }
            }
            $regInfo = UserUtils::register($username, $password, $email);
            if ($regInfo['errcode']) {
                return $this->makeErrorInfo($res, $regInfo['message']);
            }

            $uid = $regInfo['info']['uid'];
            $member = UserUtils::getUserInfo($uid);
            $userInfo = AppbymeUserAccess::registerProcess($regInfo['info']['uid'], $password);

            $data = array('uid' => $uid, 'openid' => $openId, 'status' => 1, 'type' => 3);
            AppbymeConnection::insertMobcentWx($data);

            if ($isValidation) {
                if ($isRegisterValidation) {
                    // 注册完毕之后更新手机验证信息
                    $updataArr = array('uid' => $regInfo['info']['uid']);
                    AppbymeSendsms::updateMobile($mobile, $updataArr);
                }
            }
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        } elseif ($act == 'bind' && !empty($openId)) {

            global $_G;
            $logInfo = UserUtils::login($username, $password);
            if (!empty($logInfo['errcode'])) {
                UserUtils::delUserAccessByUsername($username);
                return $this->makeErrorInfo($res, 'mobcent_bind_error');
            }
            if ($isValidation == 1) {
                // 是否开启了登录手机验证
                $isLoginValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_login_validation');
                if ($isLoginValidation) {
                    $userMobileBind = AppbymeSendsms::checkUserBindMobile($_G['uid']);
                    if (!$userMobileBind) { // 当前登录的用户没有绑定手机号码
                        if ($mobile == '' && $code == '') {
                            $res['isValidation'] = 1;
                            return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
                        }

                        $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                        if ($checkInfo['rs'] == 0) {
                            return $this->makeErrorInfo($res, $checkInfo['errcode']);
                        }

                        $updataArr = array('uid' => $_G['uid']);
                        AppbymeSendsms::updateMobile($mobile, $updataArr);
                    }
                }
            }
            $isBind = AppbymeConnection::getUserBindInfo($_G['uid']);
            if ($isBind) {
                return $this->makeErrorInfo($res, 'mobcent_bind_error_repeat');
            }

            $data = array('uid' => $_G['uid'], 'openid' => $openId, 'status' => 1, 'type' => 3);
            AppbymeConnection::insertMobcentWx($data);

            $userInfo = AppbymeUserAccess::loginProcess($_G['uid'], $password);
            $res['body']['token'] = (string) $userInfo['token'];
            $res['body']['secret'] = (string) $userInfo['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
            return $res;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_params');
    }

}
