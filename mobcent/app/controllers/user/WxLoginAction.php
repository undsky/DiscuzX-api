<?php
/**
 * 微信快速登录接口
 * Created by PhpStorm.
 * User: onmylifejie
 * Date: 2016/6/21
 * Time: 20:11
 **/
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class WxLoginAction extends MobcentAction {

    private $_wxLogin = 'wxLogin_config';

    public function run($json){
        $res = WebUtils::initWebApiArray_oldVersion();
        $config = AppbymeConfig::getCvalue($this->_wxLogin);
        if(isset($config['wxLogin']) && $config['wxLogin'] == 0) {
            $res = $this->makeErrorInfo($res,'mobcent_error_wxlogin_config');
            WebUtils::outputWebApi($res);
        }
        $json = rawurldecode($json);
        $json = WebUtils::jsonDecode($json);
//        if(!$this->_ckSign($json)){
//            $res = $this->makeErrorInfo($res,'mobcent_result_error');
//            WebUtils::outputWebApi($res);
//        }
        $res = $this->wxlogin($json, $res);

        WebUtils::outputWebApi($res);
    }

    private function wxlogin($json, $res) {
        //查appbyme_connection表中的openid
        if($json['unionid'] == '' && $json['openid'] == '') {
            $res = $this->makeErrorInfo($res,'mobcent_error_wxlogin_params');
            WebUtils::outputWebApi($res);
        }
        if($json['unionid'] != '') {
            $wxtUserInfo = AppbymeConnection::getMobcentWxinfoByUnionId($json['unionid']);
        }else {
            $wxtUserInfo = AppbymeConnection::getMobcentWxinfoByOpenId($json['openid']);
        }
        if (!$wxtUserInfo) {
            $json['nickname'] = urldecode($json['nickname']);
            //去除emjo表情
            $json['nickname'] = WebUtils::removeEmoji($json['nickname']);
            //空格用户打回
            if(trim($json['nickname']) == null) {
                return $this->makeErrorInfo($res, 'mobcent_error_wxlogin_users');
            }
            if($json['unionid'] == '') {
                $wxUserInfo = AppbymeConnection::getUserInfoFromWeiXin($json['openid'], $json['accessToken']);
                if(!empty($wxUserInfo['errcode'])){
                    return $this->makeErrorInfo($res,'mobcent_error_wxlogin_wx');
                }
                $json['unionid'] = $wxtUserInfo['unionid'];
            }
            $username = UserUtils::UserFilter($json['nickname']);
            $password = '1q2@Ws'.time();
            $email = rawurldecode($json['email']);
            $regInfo = UserUtils::register($username, $password, $email, 'general', 1);
            if ($regInfo['errcode']) {
                return $this->makeErrorInfo($res, $regInfo['message']);
            }
            $uid = $regInfo['info']['uid'];
            //微信注册写入注册表
            $data = array('uid' => $uid, 'status' => 1, 'type' => 1, 'param' => $json['openid']);
            if($json['unionid']) {
                $data = array('uid' => $uid, 'status' => 1, 'type' => 1, 'param' => $json['unionid']);
            }
            AppbymeConnection::insertMobcentWx($data);
            $res['avatar'] = AppbymeConnection::syncAvatar($uid, $json['headimgurl']);
        }else {
            $uid = $wxtUserInfo['uid'];
        }
        $userInfo = AppbymeUserAccess::registerProcess($uid, time());
        $res['token'] = strval($userInfo['token']);
        $res['secret'] = strval($userInfo['secret']);
        $user = UserUtils::getUserInfomation($uid);
        $res = array_merge($res,$user);
        return $res;
    }
}