<?php
/**
 * QQ快速登录
 *
 * User: 蒙奇·D·jie
 * Date: 16/11/28
 * Time: 下午4:29
 * Email: mqdjie@gmail.com
 */


if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class QQFastLoginAction extends MobcentAction
{
    private $url = 'https://graph.qq.com/user/get_user_info?';
    private $key = 'connectappid';
    public function run($openId, $oauthToken, $platformId = 20)
    {
        $res = $this->initWebApiArray();
        if(empty($openId) || empty($oauthToken)) {
            $this->makeErrorInfo($res, 'mobcent_error_qqhtmllogin_params');
        }
        $openId = rawurldecode($openId);
        if($platformId == 20) {
            $res = $this->_qqInfo($res, $openId, $oauthToken);
        }
        WebUtils::outputWebApi($res);
    }

    /**
     * QQ互联快速登录
     * @param $res
     * @param $openId
     * @param $oauthToken
     */
    private function _qqInfo($res, $openId, $oauthToken) {
        global $_G;
        $password = '@12wW'.md5(random(5));
        $appid = $this->getAppid();
        $this->url .= 'oauth_consumer_key='. $appid .'&access_token='. $oauthToken .'&openid='. $openId .'&format=json';
        require_once libfile('function/member');
        $qqUserInfo = $this->_getQQinfoByOpenId($openId);
        $userInfo = UserUtils::getUserInfo($qqUserInfo['uid']);
        if (!empty($qqUserInfo) && empty($userInfo)) {
            C::t('#qqconnect#common_member_connect')->delete($qqUserInfo['uid']);
            $qqUserInfo = $userInfo = array();
        }
        if (isset($qqUserInfo) && !empty($qqUserInfo)) {
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
            $res['body']['token'] = strval($userAccess['token']);
            $res['body']['secret'] = strval($userAccess['secret']);
            $user = UserUtils::getUserInfomation($_G['uid']);
            $res['body'] = array_merge($res['body'],$user);
        } else {
            $json = WebUtils::jsonDecode(WebUtils::httpRequest($this->url), true);
            $res = $this->regis($res, $json['nickname'], $password, $oauthToken, $openId);
        }
        return $res;
    }

    private function regis($res, $username, $password, $oauthToken, $openId)
    {
        $username = UserUtils::UserFilter($username);
        $email = UserUtils::RandChar(10) . "@appbyme.com";
        $regInfo = UserUtils::register($username, $password, $email, 'qq');
        if ($regInfo['errcode']) {
            return $this->makeErrorInfo($res, $regInfo['message']);
        }
        $uid = $regInfo['info']['uid'];
        $userAccess = AppbymeUserAccess::registerProcess($uid, $password);
        $user = UserUtils::getUserInfomation($uid);
        $this->_updateQqMember($uid, $oauthToken, $openId, $user['gender']);
        $res['body']['token'] = strval($userAccess['token']);
        $res['body']['secret'] = strval($userAccess['secret']);
        $res['body'] = array_merge($res['body'],$user);
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

    //获取QQ互联的key
    private function getAppid()
    {
       $res = DZCommonSetting::getSvalueUns($this->key);
        if(empty($res)) {
            $this->makeErrorInfo($res, 'mobcent_error_qqhtmllogin_appid');
        }
        return $res;
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
            'uid' => $_G['uid'],
            'username' => $_G['username'],
            'groupid' => $_G['groupid'],
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

    private function _inserBindlog($data) {
        return DbUtils::getDzDbUtils(true)->insert('connect_memberbindlog', $data);
    }

    private function _inserConnect($data) {
        return DbUtils::getDzDbUtils(true)->insert('common_member_connect', $data);
    }

}
