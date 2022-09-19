<?php

/**
 * 第三方绑定登录
 *
 * @author HanPengyu 
 * @author 耐小心<nxx@yytest.cn>
 * @author XiaoCongjj<xiaocongjie@goyoo.com>
 * @copyright 2012-2016 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

//Mobcent::setErrors();

class PlatFormInfoAction extends MobcentAction {

    public $password = MOBCENT_HACKER_PASSWORD;

    public function run($openId, $oauthToken, $platformId = 20, $isValidation = 0, $mobile = '', $code = '') {
        $this->password .= FileUtils::getRandomFileName('', 3);
        $res = $this->initWebApiArray();
        $openId = rawurldecode($openId);
        $res = $this->getBindInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code);
        echo WebUtils::outputWebApi($res, '', false);
    }

    public function getBindInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code) {
        if ($platformId == 20) {
            $res = $this->_qqInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code);
        } elseif ($platformId == 30||$platformId==70) {
            $res = $this->_wxInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code);
        } elseif ($platformId == 40) {
            $res = $this->_fbInfo($res, $openId, $oauthToken, $platformId);
        }elseif($platformId==50){
            $res = $this->_qqSdkInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code);
        }
        return $res;
    }

    private function _qqInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code) {
        global $_G;
        $password = MOBCENT_HACKER_PASSWORD . FileUtils::getRandomFileName('', 3);
        require_once libfile('function/member');
        if (!empty($platformId) && $platformId == 20) {

            $qqUserInfo = $this->_getQQinfoByOpenId($openId);
            $userInfo = UserUtils::getUserInfo($qqUserInfo['uid']);
            if (!empty($qqUserInfo) && empty($userInfo)) {
                C::t('#qqconnect#common_member_connect')->delete($qqUserInfo['uid']);
                $qqUserInfo = $userInfo = array();
            }
            if (isset($qqUserInfo) && !empty($qqUserInfo)) {
                if ($isValidation == 1) {
                    // 是否开启了登录手机验证
                    $isLoginValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_login_validation');
                    if ($isLoginValidation) {
                        $userMobileBind = AppbymeSendsms::checkUserBindMobile($qqUserInfo['uid']);
                        if (!$userMobileBind) { // 当前登录的用户没有绑定手机号码
                            if ($mobile == '' && $code == '') {
                                $res['isValidation'] = 1;
                                return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
                            }

                            $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                            if ($checkInfo['rs'] == 0) {
                                return $this->makeErrorInfo($res, $checkInfo['errcode']);
                            }

                            $updataArr = array('uid' => $qqUserInfo['uid']);
                            AppbymeSendsms::updateMobile($mobile, $updataArr);
                        }
                    }
                }
                setloginstatus($userInfo, $_GET['cookietime'] ? 2592000 : 0);
                C::t('common_member_status')->update($userInfo['uid'], array('lastip' => $_G['clientip'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));

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

                $comSess = DzCommonSession::getComSessByUid($userInfo['uid']);
                if (!empty($comSess)) {
                    DzCommonSession::delComSess($userInfo['uid']);
                }
                DzCommonSession::insertComSess($data);

                $userAccess = AppbymeUserAccess::loginProcess($userInfo['uid'], $password);
                $res['body']['token'] = (string) $userAccess['token'];
                $res['body']['secret'] = (string) $userAccess['secret'];
                $user = UserUtils::getUserInfomation($_G['uid']);
                $res['body'] = array_merge($res['body'],$user);
                return $res;
            } else {
                $res['body']['register'] = 1;
                $res['body']['openId'] = (string) $openId;
                $res['body']['oauthToken'] = (string) $oauthToken;
                $res['body']['platformId'] = (int) $platformId;
                return $res;
            }
        }

        // 客户端参数不正确
        return $this->makeErrorInfo($res, 'mobcent_error_params');
    }

    private function _wxInfo($res, $openId, $oauthToken, $platformId, $isValidation, $mobile, $code) {
        global $_G;
        if($platformId==70){
            $result = WebUtils::jsonDecode(rawurldecode($_GET['json']));
        }else{
            $result = AppbymeConnection::getUserInfoFromWeiXin($openId, $oauthToken);
            if(!empty($result['errcode'])){
                return $this->makeErrorInfo($res,WebUtils::t('请求微信服务器错误,请重试'));
            }
        }

        if (!empty($result['unionid'])) {
            $wxLogin = AppbymeConnection::getMobcentWxinfoByUnionId($result['unionid']);
        }
        if (empty($wxLogin)) {
            $wxLogin = AppbymeConnection::getMobcentWxinfoByOpenId($openId);
        }
        $member = getuserbyuid($wxLogin['uid'], 1);
        if ($wxLogin && empty($member)) {
            DB::delete('appbyme_connection', array('uid' => $wxLogin['uid'], 'type' => 1));
            $wxLogin = $member = array();
        }
        if ($wxLogin) {
            if ($isValidation == 1) {
                // 是否开启了登录手机验证
                $isLoginValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_login_validation');
                if ($isLoginValidation) {
                    $userMobileBind = AppbymeSendsms::checkUserBindMobile($member['uid']);
                    if (!$userMobileBind) { // 当前登录的用户没有绑定手机号码
                        if ($mobile == '' && $code == '') {
                            $res['isValidation'] = 1;
                            return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
                        }

                        $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                        if ($checkInfo['rs'] == 0) {
                            return $this->makeErrorInfo($res, $checkInfo['errcode']);
                        }

                        $updataArr = array('uid' => $member['uid']);
                        AppbymeSendsms::updateMobile($mobile, $updataArr);
                    }
                }
            }
            UserUtils::updateCookie($member, $member['uid']);
            $userAccess = AppbymeUserAccess::loginProcess($member['uid'], $this->password);
            if (empty($wxLogin['param']) && !empty($result['unionid'])) {
                AppbymeConnection::updateWeiXinUserInfo(array('param' => $result['unionid']), $wxLogin['id']);
            }
           // AppbymeConnection::syncAvatar($member['uid'], $result['headimgurl']);
            $res['body']['register'] = 0;
            $res['body']['token'] = (string) $userAccess['token'];
            $res['body']['secret'] = (string) $userAccess['secret'];
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
        } else {
            $is_wechat_register = WebUtils::getDzPluginAppbymeAppConfig('is_wechat_register');
            //开启快速注册
            if ($is_wechat_register) {
                //global $_G;
                $res['body']['userName'] = WebUtils::t($result['nickname']);
                $res['body']['isFastreg'] = 1;
            }
            // 低版本的discuz！或者是没有装微信插件
            $res['body']['register'] = 1;
            $res['body']['openId'] = (string) $openId;
            $res['body']['oauthToken'] = (string) $oauthToken;
            $res['body']['platformId'] = (int) $platformId;
        }

        return $res;
    }

    //FB绑定
    private function _fbInfo($res, $openId, $oauthToken, $platformId) {
        global $_G;
        $bindInfo = AppbymeConnection::getFBInfoByOpenId($openId);
        $member = getuserbyuid($bindInfo['uid'], 1);
        if ($bindInfo && empty($member)) {
            DB::delete('appbyme_connection', array('uid' => $bindInfo['uid'], 'type' => 2));
            $bindInfo = $member = array();
        }
        if ($bindInfo) {
            UserUtils::updateCookie($member, $member['uid']);
            $userAccess = AppbymeUserAccess::loginProcess($member['uid'], $this->password);
            $res['body']['register'] = 0;
            $res['body']['token'] = (string) $userAccess['token'];
            $res['body']['secret'] = (string) $userAccess['secret'];
            $user = UserUtils::getUserInfomation($member['uid']);
            $res['body'] = array_merge($res['body'],$user);
        } else {
            $res['body']['register'] = 1;
            $res['body']['openId'] = (string) $openId;
            $res['body']['oauthToken'] = (string) $oauthToken;
            $res['body']['platformId'] = (int) $platformId;
        }
        return $res;
    }
    //QQSDK绑定
    private function _qqSdkInfo($res, $openId, $oauthToken, $platformId,$isValidation, $mobile, $code) {
        global $_G;
        $bindInfo = AppbymeConnection::getQqSdkInfoByOpenId($openId);
        $member = getuserbyuid($bindInfo['uid'], 1);
        if ($bindInfo && empty($member)) {
            DB::delete('appbyme_connection', array('uid' => $bindInfo['uid'], 'type' => 3));
            $bindInfo = $member = array();
        }
        if ($bindInfo) {
            if ($isValidation == 1) {
                // 是否开启了登录手机验证
                $isLoginValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_login_validation');
                if ($isLoginValidation) {
                    $userMobileBind = AppbymeSendsms::checkUserBindMobile($member['uid']);
                    if (!$userMobileBind) { // 当前登录的用户没有绑定手机号码
                        if ($mobile == '' && $code == '') {
                            $res['isValidation'] = 1;
                            return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
                        }

                        $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                        if ($checkInfo['rs'] == 0) {
                            return $this->makeErrorInfo($res, $checkInfo['errcode']);
                        }

                        $updataArr = array('uid' => $member['uid']);
                        AppbymeSendsms::updateMobile($mobile, $updataArr);
                    }
                }
            }
            UserUtils::updateCookie($member, $member['uid']);
            $userAccess = AppbymeUserAccess::loginProcess($member['uid'], $this->password);
            $res['body']['register'] = 0;
            $res['body']['token'] = (string) $userAccess['token'];
            $res['body']['secret'] = (string) $userAccess['secret'];
            $user = UserUtils::getUserInfomation($member['uid']);
            $res['body'] = array_merge($res['body'],$user);
        } else {
            $res['body']['register'] = 1;
            $res['body']['openId'] = (string) $openId;
            $res['body']['oauthToken'] = (string) $oauthToken;
            $res['body']['platformId'] = (int) $platformId;
        }
        return $res;
    }
    // QQ
    private function _getQQinfoByOpenId($openId) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE conopenid=%s
            ', array('common_member_connect', $openId)
        );
    }

}
