<?php

/**
 * Utils about user
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @author HanPengyu
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @author XiaoCongjj<xiaocongjie@goyoo.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
//Mobcent::setErrors();

class UserUtils {

    /**
     * 用户登陆状态
     */
    const STATUS_OFFLINE = 0;
    const STATUS_ONLINE_INVISIBLE = 1;
    const STATUS_ONLINE = 2;

    /**
     * 用户性别
     */
    const GENDER_SECRET = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * 验证码
     */
    const ImgCodeTime = 1800;
    const CodeTime = 600;
    const CodeRate = 3;
    const Rate = 0;
    /**
     * get user's avatar
     * copy and modify by DISCUZ avatar function
     *
     * @param int $uid
     * @param string $size
     * @return string
     */
    public static function getUserAvatar($uid, $size = 'middle') {
        global $_G;
        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
        if (Yii::app()->params['qiniu']) {
            $haveImg = Qiniuup::haveImg(Yii::app()->params['discuz']['globals']['config']['db'][1]['dbname'] . '/' . 'upload' . $uid . $size . '.jpg');
            if ($haveImg == null) {
                return $_G['siteurl'] . 'data/noavatar_small.gif';
            } else {
                return Qiniuup::getqiniuurl('upload' . $uid . $size . '.jpg')."?v=".time();
            }
        } else {
            $ucenterurl = $_G['setting']['ucenterurl'];
            $uid = abs(intval($uid));
            return $ucenterurl . '/avatar.php?uid=' . $uid . '&size=' . $size;
        }
    }

    public static function getUserName($uid, $userInfo = array()) {
        empty($userInfo) && $userInfo = self::getUserInfo($uid);
        return !empty($userInfo) ? $userInfo['username'] : '';
    }

    public static function getUCUserName($uid) {
        if(!function_exists('uc_get_user')){
            loaducenter();
        }
        $userInfo  =uc_get_user($uid,1);
        return $userInfo[1];
    }

    public static function UpdateUserName($username, $uid) {
        return DbUtils::getDzDbUtils(true)->update('common_member',
            array(
                'username' => $username),
            array(
                'uid' => $uid)
        );
    }

    public static function getUserCount($username) {
        return DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(uid) as cou
            FROM %t
            WHERE username=%s
            ', array('common_member', $username)
        );
    }

    public static function getUserCenterCount($username){
        loaducenter();
        return uc_get_user($username);
    }


    public static function getUserGender($uid, $userProfile = array()) {
        empty($userProfile) && $userProfile = self::getUserProfile($uid);
        return !empty($userProfile) ? (int)$userProfile['gender'] : self::GENDER_SECRET;
    }

    // 获取用户等级名称
    public static function getUserTitle($uid, $userInfo = array()) {
        $userTitle = '';
        empty($userInfo) && $userInfo = self::getUserInfo($uid);
        if (!empty($userInfo)) {
            $groupId = $userInfo['groupid'];
            $userGroup = UserUtils::getUserGroupsByGids($groupId);
            $userTitle = (string)WebUtils::emptyHtml($userGroup[$groupId]['grouptitle']);
        }
        return $userTitle;
    }

    public static function getUserColor($uid,$userInfo=array())
    {
        $userColor = '';
        empty($userInfo) && $userInfo = self::getUserInfo($uid);
        if (!empty($userInfo)) {
            $groupId = $userInfo['groupid'];
            $userGroup = UserUtils::getUserGroupsByGids($groupId);
            $userColor = (string)WebUtils::emptyHtml($userGroup[$groupId]['color']);
        }
        return $userColor;
    }

    public static function getUserLevelIcon($uid, $user = array()) {
        // from funtion_forumlist showstars
        $icon = array('sun' => 0, 'moon' => 0, 'star' => 0);

        global $_G;
        empty($user) && $user = self::getUserInfo($uid);
        if (!empty($user)) {
            $num = $stars = $_G['cache']['usergroups'][$user['groupid']]['stars'];
            if (empty($_G['setting']['starthreshold'])) {
                for ($i = 0; $i < $num; $i++) {
                    $icon['star']++;
                }
            } else {
                $maps = array('1' => 'star', 'moon', 'sun');
                for ($i = 3; $i > 0; $i--) {
                    $numlevel = intval($num / pow($_G['setting']['starthreshold'], ($i - 1)));
                    $num = ($num % pow($_G['setting']['starthreshold'], ($i - 1)));
                    for ($j = 0; $j < $numlevel; $j++) {
                        $icon[$maps[$i]]++;
                    }
                }
            }
        }
        return $icon;
    }

    /**
     * get user's info
     * copy from DISCUZ getuserbyuid function
     */
    public static function getUserInfo($uid) {
        return getuserbyuid($uid);
    }

    public static function getUserProfile($uid) {
        return DbUtils::createDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE uid=%d
            ', array('common_member_profile', $uid)
        );
    }

    /**
     * 判断用户登陆状态
     *
     * @param int $uid 用户id
     *
     * @return int 0为不在线, 1为隐身登陆, 2为在线登陆
     */
    public static function getUserLoginStatus($uid) {
        $invisible = DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT invisible
            FROM %t
            WHERE uid=%d
            ', array('common_session', $uid)
        );
        return $invisible !== false ?
            ($invisible == 1 ? self::STATUS_ONLINE_INVISIBLE : self::STATUS_ONLINE) :
            self::STATUS_OFFLINE;
    }

    /**
     * 判断用户是否为好友
     *
     * @param int $uid 主用户id
     * @param int $fuid 要检测的用户id
     *
     * @return bool false为非好友, true为好友
     */
    public static function isFriend($uid, $fuid) {
        $res = (int)DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(*)
            FROM %t
            WHERE uid=%d AND fuid=%d
            ', array('home_friend', $uid, $fuid)
        );
        return $res !== 0;
    }
    public static function UserFilter($username) {
        global $_G;
        $username = str_replace(array(" ","　","\t","\n","\r"),array("","","","",""),$username);
        $username = WebUtils::t($username);
        $usernamelen = strlen($username);
        //如果小于3个字 ?
        if ($usernamelen < 4) {
            $username = $username .self::RandChar(3);
        }
        //判断用户名是否超过15位，15位的时候截出12位
        if($usernamelen >= 15) {
            $name = '';
            $length = WebUtils::getLength($username);
            for($i = 0; $i < $length; $i++) {
                $name  .= WebUtils::getSubstr($username, $i);
                if(isset($name{12})) {
                    break;
                }
            }
            $username =  $name;
        }
        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')) . ')$/i';
        if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
            $username = self::RandChar(10);
        }
        $count = UserUtils::getUserCenterCount($username);
        if(!empty($count)) {
            $username = $username.self::RandChar(3);
        }
        return $username;
    }
    public static function RandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }
    /**
     * 判断用户是否在黑名单
     *
     * @param int $uid 主用户id
     * @param int $buid 要检测的用户id
     *
     * @return bool true为加入黑名单, false为没有加入黑名单
     */
    public static function isBlacklist($uid, $buid) {
        $res = (int)DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(*)
            FROM %t
            WHERE uid=%d AND buid=%d
            ', array('home_blacklist', $uid, $buid)
        );
        return $res !== 0;
    }

    /**
     * 判断用户是否关注了某个用户
     *
     * @param int $uid 用户id
     * @param int $fuid 关注的用户id
     *
     * @return bool
     */
    public static function isFollow($uid, $fuid) {
        $res = (int)DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(*)
            FROM %t
            WHERE uid=%d AND followuid=%d
            ', array('home_follow', $uid, $fuid)
        );
        return $res !== 0;
    }

    /**
     * 判断该用户是否开启GPS定位功能
     */
    public static function isGPSLocationOn($uid) {
        return AppbymeUserSetting::isGPSLocationOn($uid);
    }

    /**
     * 获取用户组信息
     *
     * @param string|array $gids 用户组id
     *
     * @return array
     */
    public static function getUserGroupsByGids($gids) {
        return DzCommonUserGroup::getUserGroupsByGids($gids);
    }

    /**
     * 获取当前用户及其对应版块的权限
     *
     * @param string $fids 版块id集合
     *
     * @return array
     */
    public static function getPermission($fids) {
        $permission = array();

        global $_G;
        $tempGroupAllowPostPoll = $_G['group']['allowpostpoll'];
        $tempGroupAllowPostImage = $_G['group']['allowpostimage'];
        $tempGroupAllowPostAttach = $_G['group']['allowpostattach'];
        $tempGroupAttachExtensions = $_G['group']['attachextensions'];
        $tempSetting = AppbymeConfig::getSetting();
        $tempIsOnlyAuthor = intval($tempSetting['reply']);
        $forumInfos = ForumUtils::getForumInfos($fids);
        foreach ($forumInfos as $forum) {
            $fid = (int)$forum['fid'];

            ForumUtils::initForum($fid);

            $_G['group']['allowpostpoll'] = $tempGroupAllowPostPoll;

            // 获取上传图片权限
            $_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
            $_G['group']['allowpostimage'] = $tempGroupAllowPostImage;
            $_G['group']['allowpostimage'] = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
            $_G['group']['attachextensions'] = $tempGroupAttachExtensions;
            require_once libfile('function/upload');
            $swfconfig = getuploadconfig($_G['uid'], $_G['fid']);
            $imgexts = str_replace(array(';', '*.'), array(', ', ''), $swfconfig['imageexts']['ext']);
            $allowpostimg = $_G['group']['allowpostimage'] && $imgexts;
            $allowPostImage = $allowpostimg ? 1 : 0;

            $allowAnonymous = $_G['forum']['allowanonymous'] || $_G['group']['allowanonymous'] ? 1 : 0;
            $_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
            $_G['group']['allowpostattach'] = $tempGroupAllowPostAttach;
            $_G['group']['allowpostattach'] = $_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm'])));
            $allowPostAttachment = $_G['group']['allowpostattach'] ? 1 : 0;

            $topicClassfications = ForumUtils::getTopicClassificationInfos($fid);

            $permission[] = array(
                'fid' => $fid,
                'topic' => array(
                    'isHidden' => 0,
                    'isAnonymous' => $allowAnonymous,
                    'isOnlyAuthor' => $tempIsOnlyAuthor,
                    'allowPostAttachment' => $allowPostAttachment,
                    'allowPostImage' => $allowPostImage,
                    'newTopicPanel' => ForumUtils::getNewTopicPanel(),
                    'classificationType_list' => $topicClassfications['types'],
                    'isOnlyTopicType' => $topicClassfications['requireTypes'] ? 1 : 0,
                ),
                'post' => array(
                    'isHidden' => 0,
                    'isAnonymous' => $allowAnonymous,
                    'isOnlyAuthor' => 0,
                    'allowPostAttachment' => $allowPostAttachment,
                    'allowPostImage' => $allowPostImage,
                ),
            );
        }

        return $permission;
    }

    public static function getUserIdByAccess() {
        $accessToken = isset($_GET['accessToken']) ? $_GET['accessToken'] : '';
        $accessSecret = isset($_GET['accessSecret']) ? $_GET['accessSecret'] : '';
        return AppbymeUserAccess::getUserIdByAccess($accessToken, $accessSecret);
    }

    public static function checkAccess() {
        global $_G;
        return $_G['uid'] > 0;
    }

    /**
     * 获取马甲列表
     *
     * @author HanPengyu
     * @param int $uid
     * @author HanPengyu
     * @return array $userlist
     */
    public static function getRepeatList($uid) {
        global $_G;
        $userList = array();
        if ($_G['setting']['plugins']['spacecp']['myrepeats:memcp']) {
            foreach (C::t('#myrepeats#myrepeats')->fetch_all_by_uid($uid) as $user) {
                $userlist[] = $user['username'];
            }
        }
        return $userlist;
    }

    /**
     * 用户登录操作
     *
     * @author HanPengyu
     * @param string $username 用户名.
     * @param string $password 用户密码.
     * @return
     */
    public static function login($username, $password) {
        global $_G;
        $_GET['username'] = $username;
        $_GET['password'] = $password;
        $_GET['questionid'] = $_GET['answer'] = '';
        $_GET['loginfield'] = 'username';

        require_once libfile('function/member');
        require_once libfile('class/member');
        require_once libfile('function/misc');
        require_once libfile('function/mail');

        loaducenter();

        $invite = getinvite();

        $_G['uid'] = $_G['member']['uid'] = 0;
        $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
        if (trim($_GET['username']) == '') {
            return self::errorInfo('user_name_null');
        }

        if (!($_G['member_loginperm'] = logincheck($_GET['username']))) {
            // 密码错误次数过多，请 15 分钟后重新登录,后面还会进行判断
            return self::errorInfo(lang('message', 'login_strike'));
        }

        if (!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
            // 抱歉，密码空或包含非法字符
            return self::errorInfo(lang('message', 'profile_passwd_illegal'));
        }
        //支持UID Username Email登陆
        $result = userlogin($_GET['username'], $_GET['password'], $_GET['questionid'], $_GET['answer'], 'auto', $_G['clientip']);

        if ($result['freeze'] == 1) {
            return self::errorInfo(Webutils::t('账号已注销'));
        }

        if ($result['ucresult']['uid'] == '-3') {
            $userInfo = DzCommonMember::getUidByUsername($result['ucresult']['username']);
            $result['ucresult']['uid'] = $userInfo['uid'];
            $result['member'] = $userInfo;
            $result['status'] = 1;
        }

        $uid = $_G['uid'] = $result['ucresult']['uid'];
        $userName = $result['ucresult']['username'];
        $userAvatar = UserUtils::getUserAvatar($uid);
        $ctlObj = new logging_ctl();
        $ctlObj->setting = $_G['setting'];

        if ($result['status'] == -1) {
            if (!$ctlObj->setting['fastactivation']) {
                // 帐号没有激活
                return self::errorInfo(Yii::t('mobcent', 'location_activation'));
            } else {
                // 自动激活
                $init_arr = explode(',', $ctlObj->setting['initcredits']);
                $groupid = $ctlObj->setting['regverify'] ? 8 : $ctlObj->setting['newusergroupid'];
                C::t('common_member')->insert($uid, $result['ucresult']['username'], md5(random(10)), $result['ucresult']['email'], $_G['clientip'], $groupid, $init_arr);
                $result['member'] = getuserbyuid($uid);
                $result['status'] = 1;
            }
        }

        if ($result['status'] > 0) {

            // [?]额外的文件
            if ($ctlObj->extrafile && file_exists($ctlObj->extrafile)) {
                require_once $ctlObj->extrafile;
            }

            // [封装]把登录信息写入到cookie，并且更新登录的状态等。Author:HanPengyu,Data:04.09.28
            self::updateCookie($result['member'], $_G['uid']);

            return self::errorInfo('', 0);
        } else {
            $password = preg_replace("/^(.{" . round(strlen($_GET['password']) / 4) . "})(.+?)(.{" . round(strlen($_GET['password']) / 6) . "})$/s", "\\1***\\3", $_GET['password']);
            $errorlog = dhtmlspecialchars(
                TIMESTAMP . "\t" .
                ($result['ucresult']['username'] ? $result['ucresult']['username'] : $_GET['username']) . "\t" .
                $password . "\t" .
                "Ques #" . intval($_GET['questionid']) . "\t" .
                $_G['clientip']);
            writelog('illegallog', $errorlog);
            loginfailed($_GET['username']);

            if ($_G['member_loginperm'] > 1) {
                // 登录失败,还可以尝试几次
                return self::errorInfo(lang('message', 'login_invalid', array('loginperm' => $_G['member_loginperm'] - 1)));
            } elseif ($_G['member_loginperm'] == -1) {
                // 抱歉，您输入的密码有误
                return self::errorInfo(lang('message', 'login_password_invalid'));
            } else {
                // 密码错误次数过多，请 15 分钟后重新登录
                return self::errorInfo(lang('message', 'login_strike'));
            }
        }
    }

    /**
     * 退出登录
     *
     * @author HanPengyu
     * @return 退出登录信息
     */
    public static function logout() {
        global $_G;
        require_once libfile('function/member');
        require_once libfile('class/member');
        $ctlObj = new logging_ctl();
        $ctlObj->setting = $_G['setting'];
        AppbymeUserSetting::delUserToken($_G['uid']);
        clearcookies();
        $_G['groupid'] = $_G['member']['groupid'] = 7;
        $_G['uid'] = $_G['member']['uid'] = 0;
        $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
        $_G['setting']['styleid'] = $ctlObj->setting['styleid'];
        if (empty($_G['uid']) && empty($_G['username'])) {
            $accessToken = (string)$_GET['accessToken'];
            $accessSecret = (string)$_GET['accessSecret'];
            $userId = AppbymeUserAccess::getUserIdByAccess($accessToken, $accessSecret);
            if ($userId) {
                DB::query('DELETE FROM ' . DB::table('common_session') . ' WHERE uid=' . $userId);
            }
        }
        return self::errorInfo(lang('message', 'modcp_logout_succeed'));
    }

    /**
     * 用户注册
     *
     * @author HanPengyu
     * @param string $username 用户名.
     * @param string $password 用户密码.
     * @param string $email 用户邮件.s
     * @param string $type 注册类型,默认general.
     * @param int $FastRegister 是否快速注册 默认为0
     * @return array .
     */
    public static function register($username, $password, $email, $type = 'otherReg', $FastRegister = 0, $WxRegister = 0) {
        global $_G;
        require_once libfile('function/member');
        require_once libfile('class/member');
        require_once libfile('class/credit');

        require_once libfile('function/misc');
        loaducenter();

        $ctlObj = new register_ctl();
        $ctlObj->setting = $_G['setting'];

        // 客户端是否开启注册功能
        $mobAllowReg = WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_register');
        if ($mobAllowReg === '0') {
            return self::errorInfo(Webutils::t('客户端不允许注册'));
        }

        // 客户端是否开启跳转web页注册
        // 系统是否允许注册
        if (!$ctlObj->setting['regclosed'] && (!$ctlObj->setting['regstatus'] || !$ctlObj->setting['ucactivation'])) {
            if (!$ctlObj->setting['regstatus']) {
                $message = !$ctlObj->setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $ctlObj->setting['regclosemessage']);
                return self::errorInfo(lang('message', $message));
            }
        }
        //当后台开启不填邮件且邮箱为空的时候自动生成邮箱 ByNxx 注册中的生成邮箱移到这里 方便第三方登陆生成邮箱
        $isCloseEmail = WebUtils::getDzPluginAppbymeAppConfig('close_email_register');
        if (($isCloseEmail && empty($email)) || $FastRegister == '1') {
            $randEmail = 1;
            $email = UserUtils::_generateChars() . "@appbyme.com";
        }
        // $username = isset($username) ? trim(WebUtils::t($username)) : '';
        $password = isset($password) ? $password : '';
        // $password2 = isset($password2) ? $password2 : '';
        $email = strtolower(trim($email));

        if ($ctlObj->setting['regverify']) {
            // 对注册 IP 的限制
            if ($ctlObj->setting['areaverifywhite']) {
                $location = $whitearea = '';
                $location = trim(convertip($_G['clientip'], "./"));
                if ($location) {
                    $whitearea = preg_quote(trim($ctlObj->setting['areaverifywhite']), '/');
                    $whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
                    $whitearea = '.*' . $whitearea . '.*';
                    $whitearea = '/^(' . str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea) . ')$/i';
                    if (@preg_match($whitearea, $location)) {
                        $ctlObj->setting['regverify'] = 0;
                    }
                }
            }

            if ($_G['cache']['ipctrl']['ipverifywhite']) {
                foreach (explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {
                    if (preg_match("/^(" . preg_quote(($ctrlip = trim($ctrlip)), '/') . ")/", $_G['clientip'])) {
                        $ctlObj->setting['regverify'] = 0;
                        break;
                    }
                }
            }
        }
        $config = AppbymeConfig::getSetting();
        if ($ctlObj->setting['regverify'] && $type == 'general' && !$randEmail && $config['email'] == '1') {
            //进审核
            $groupinfo['groupid'] = 8;
        } else {
            $groupinfo['groupid'] = $ctlObj->setting['newusergroupid'];
            // 在插件中设置的用户组 date:2015.01.15
            $registerGroup = WebUtils::getDzPluginAppbymeAppConfig('mobile_register_group');
            if ($registerGroup) {
                $groupinfo['groupid'] = $registerGroup;
            }
        }


        if ($FastRegister) { //判断是否是快速注册
            $groupinfo['groupid'] = Webutils::getDzPluginAppbymeAppConfig('fast_register_group');
        }
        $usernamelen = dstrlen($username);
        /**
         * 微信注册跳过原有判断方法
         */
        if($WxRegister) {
            $usernamelen = mb_strlen($username, 'utf-8');
        }
        if ($usernamelen < 3) {
            return self::errorInfo(lang('message', 'profile_username_tooshort'));
        } elseif ($usernamelen > 15) {
            return self::errorInfo(lang('message', 'profile_username_toolong'));
        }

        if ($ctlObj->setting['pwlength']) {
            if (strlen($password) < $ctlObj->setting['pwlength']) {
                // 密码最小的长度
                return self::errorInfo(lang('message', 'profile_password_tooshort', array('pwlength' => $ctlObj->setting['pwlength'])));
            }
        }

        // 密码复杂度的限制
        if ($ctlObj->setting['strongpw']) {
            $strongpw_str = array();
            if (in_array(1, $ctlObj->setting['strongpw']) && !preg_match("/\d+/", $password)) {
                $strongpw_str[] = lang('member/template', 'strongpw_1');
            }
            if (in_array(2, $ctlObj->setting['strongpw']) && !preg_match("/[a-z]+/", $password)) {
                $strongpw_str[] = lang('member/template', 'strongpw_2');
            }
            if (in_array(3, $ctlObj->setting['strongpw']) && !preg_match("/[A-Z]+/", $password)) {
                $strongpw_str[] = lang('member/template', 'strongpw_3');
            }
            if (in_array(4, $ctlObj->setting['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $password)) {
                $strongpw_str[] = lang('member/template', 'strongpw_4');
            }
            if ($strongpw_str) {
                // 密码太弱，密码中必须包含什么
                return self::errorInfo(lang('member/template', 'password_weak') . implode(',', $strongpw_str));
            }
        }

        // if($password !== $password2) {
        //     // 两次输入的密码不同
        //     return WebUtils::makeErrorInfo_oldVersion($res, lang('message', 'profile_passwd_notmatch'));
        // }

        if (!$password || $password != addslashes($password)) {
            // 密码有特殊的字符
            return self::errorInfo(lang('message', 'profile_passwd_illegal'));
        }

        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($ctlObj->setting['censoruser'] = trim($ctlObj->setting['censoruser'])), '/')) . ')$/i';

        if ($ctlObj->setting['censoruser'] && @preg_match($censorexp, $username)) {
            // 用户名包含被系统屏蔽的字符
            return self::errorInfo(lang('message', 'profile_username_protect'));
        }

        // 这里是对ip注册的限制
        if ($_G['cache']['ipctrl']['ipregctrl']) {
            foreach (explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
                if (preg_match("/^(" . preg_quote(($ctrlip = trim($ctrlip)), '/') . ")/", $_G['clientip'])) {
                    $ctrlip = $ctrlip . '%';
                    $ctlObj->setting['regctrl'] = $ctlObj->setting['ipregctrltime'];
                    break;
                } else {
                    $ctrlip = $_G['clientip'];
                }
            }
        } else {
            $ctrlip = $_G['clientip'];
        }

        // ip在一定时间内不能注册
        if ($ctlObj->setting['regctrl']) {
            if (C::t('common_regip')->count_by_ip_dateline($ctrlip, $_G['timestamp'] - $ctlObj->setting['regctrl'] * 3600)) {
                return self::errorInfo(lang('message', 'register_ctrl', array('regctrl' => $ctlObj->setting['regctrl'])));
            }
        }

        // IP 地址在 24 小时内只能注册几次
        $setregip = null;
        if ($ctlObj->setting['regfloodctrl']) {
            $regip = C::t('common_regip')->fetch_by_ip_dateline($_G['clientip'], $_G['timestamp'] - 86400);
            if ($regip) {
                if ($regip['count'] >= $ctlObj->setting['regfloodctrl']) {
                    return self::errorInfo(lang('message', 'register_flood_ctrl', array('regfloodctrl' => $ctlObj->setting['regfloodctrl'])));
                } else {
                    $setregip = 1;
                }
            } else {
                $setregip = 2;
            }
        }
        /**
         * 微信快速注册,绕过正常注册
         * by onmylifejie
         * 200835893@qq.com
         */
        if($WxRegister) {
            $uid = self::insert('ucenter_members', array(
                'username' => $username,
                'regdate'  => time(),
                'regip'    => $_G['clientip']
            ));
            self::insert('ucenter_memberfields',array('uid' => $uid));
        } else {
            //如果在F1.0内自动引入扩展函数 By:NaiXIaoXin<nxx@yytest.cn>
            if (function_exists('uc_user_register_new')) {
                //   $sms = '';
                if (!empty($_GET['mobile']) && $_GET['isValidation'] == 1) {
                    $sms = $_GET['mobile'];
                } else {
                    $sms = self::getRandChar('9');
                }
                $uid = uc_user_register_new(addslashes($username), $password, $email, $sms, '', '', $_G['clientip']);
            } else {
                $uid = uc_user_register(addslashes($username), $password, $email, '', '', $_G['clientip']);
            }
        }

        if ($uid <= 0) {
            if ($uid == -1) {
                // 用户名包含敏感字符
                return self::errorInfo(lang('message', 'profile_username_illegal'));
            } elseif ($uid == -2) {
                // 用户名包含被系统屏蔽的字符
                return self::errorInfo(lang('message', 'profile_username_protect'));
            } elseif ($uid == -3) {
                // 该用户名已被注册
                return self::errorInfo(lang('message', 'profile_username_duplicate'));
            } elseif ($uid == -4) {
                // Email 地址无效
                return self::errorInfo(lang('message', 'profile_email_illegal'));
            } elseif ($uid == -5) {
                // 抱歉，Email 包含不可使用的邮箱域名
                return self::errorInfo(lang('message', 'profile_email_domain_illegal'));
            } elseif ($uid == -6) {
                // 该 Email 地址已被注册
                return self::errorInfo(lang('message', 'profile_email_duplicate'));
            } elseif ($uid == -7) {
                // 该 Email 地址已被注册
                return self::errorInfo(lang('message', 'profile_sms_illegal'));
            } elseif ($uid == -8) {
                // 该 Email 地址已被注册
                return self::errorInfo(lang('message', 'profile_sms_duplicate'));
            }
        }

        $_G['username'] = $username;
        $password = md5(random(10));
        if ($setregip !== null) {
            if ($setregip == 1) {
                C::t('common_regip')->update_count_by_ip($_G['clientip']);
            } else {
                C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => 1, 'dateline' => $_G['timestamp']));
            }
        }

        $profile = $verifyarr = array();
        $emailstatus = 0;
        $init_arr = array('credits' => explode(',', $ctlObj->setting['initcredits']), 'profile' => $profile, 'emailstatus' => $emailstatus);
        //如果在F1.0内自动引入扩展函数 By:NaiXIaoXin<nxx@yytest.cn>
        if (function_exists('uc_user_register_new')) {
            C::t('common_member')->insert_new($uid, $username, $password, $email, $sms, $_G['clientip'], $groupinfo['groupid'], $init_arr);
        } else {
            C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupinfo['groupid'], $init_arr);
        }

        if ($ctlObj->setting['regctrl'] || $ctlObj->setting['regfloodctrl']) {
            C::t('common_regip')->delete_by_dateline($_G['timestamp'] - ($ctlObj->setting['regctrl'] > 72 ? $ctlObj->setting['regctrl'] : 72) * 3600);
            if ($ctlObj->setting['regctrl']) {
                C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
            }
        }
        //   debug($_G);
        if ($ctlObj->setting['regverify'] == 1) {
            $idstring = random(6);
            $authstr = $ctlObj->setting['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';
            C::t('common_member_field_forum')->update($uid, array('authstr' => $authstr));
            $verifyurl = "{$_G[siteurl]}member.php?mod=activate&amp;uid=$uid&amp;id=$idstring";
            $email_verify_message = lang('email', 'email_verify_message', array(
                'username' => $username,
                'bbname' => $ctlObj->setting['bbname'],
                'siteurl' => $_G['siteurl'],
                'url' => $verifyurl
            ));
            if (!sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message)) {
                runlog('sendmail', "$email sendmail failed.");
            }
        }

        setloginstatus(getuserbyuid($uid), 2592000);
        // [add]更新欢迎注册等 data:2015.01.04
        require_once libfile('cache/userstats', 'function');
        build_cache_userstats();

        $_GET['regmessage'] = Webutils::t('来自手机客户端注册');
        $regmessage = dhtmlspecialchars($_GET['regmessage']);
        if ($ctlObj->setting['regverify'] == 2) {
            C::t('common_member_validate')->insert(array(
                'uid' => $uid,
                'submitdate' => $_G['timestamp'],
                'moddate' => 0,
                'admin' => '',
                'submittimes' => 1,
                'status' => 0,
                'message' => $regmessage,
                'remark' => '',
            ), false, true);
            manage_addnotify('verifyuser');
        }
        // 统计用户表
        include_once libfile('function/stat');
        updatestat('register');

        return self::errorInfo('', 0, array('uid' => $uid));
    }
    /**
     * 插入数据,by肖聪杰
     */
    protected static function insert($table, $data, $returnid = true) {
       return DbUtils::createDbUtils(true)->insert($table, $data, $returnid);
    }

    /**
     * @author XiaoCongjj
     *5个接口用户信息返回
     **/
    public static function getUserInfomation($uid) {
        $space = getuserbyuid($uid);
        space_merge($space, 'profile');
        space_merge($space, 'count');
        $res['score'] = intval($space['credits']);

        $res['uid'] = $uid;
        $res['userName'] = UserUtils::getUserName($uid);
        $userAvatar = UserUtils::getUserAvatar($uid);
        $res['avatar'] = strval($userAvatar);
        $mobile = AppbymeSendsms::getPhoneByUid($uid);//获得手机号
        $res['gender'] = intval(UserUtils::getUserGender($uid, $space));
        $res['userTitle'] = UserUtils::getUserTitle($uid, $space);
        $res['repeatList'] = UserUtils::getVestList($uid);
        $res['verify'] = UserUtils::getVerify($uid);
        $res['creditShowList'] = UserUtils::getPersonalDataInfo($uid, $space);
        $res['mobile'] = strval($mobile['mobile']);//就算手机号为空也可以转
        $res['groupid'] = intval($space['groupid']);
        
        return $res;
    }

    /**
     * 返回错误信息数组
     *
     * @author HanPengyu
     * @param string $message 错误信息.
     * @param int $errcode 错误码.
     * @param array $info
     * @return mixed Value.
     */
    public static function errorInfo($message = '', $errcode = 1, $info = array()) {
        return array(
            'message' => $message,
            'errcode' => $errcode,
            'info' => $info
        );
    }

    /**
     * 登录写入缓存并改变登录状态
     *
     * @param array $userInfo 将要登录的用户信息.
     * @param mixed $uid 将要登录用户的uid.
     *
     */
    public static function updateCookie($userInfo = array(), $uid) {

        require_once libfile('function/member');
        global $_G;
        // discuz的源码,修改有未知风险，所以采用赋值的方式.
        $result['member'] = $userInfo;
        $_G['uid'] = $uid;

        setloginstatus($result['member'], 2592000);
        checkfollowfeed();

        C::t('common_member_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));

        // uc同步登录写入cookie有问题，暂时注释，待以后修复 9.26
        // $ucsynlogin = $ctlObj->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

    }

    /**
     * 通过username删除用户的accessToken、accessSecret
     *
     * @param string $username Description.
     * @static
     *
     */
    public static function delUserAccessByUsername($username) {
        $userInfo = DzCommonMember::getUidByUsername($username);
        $delUid = $userInfo['uid'];
        AppbymeUserAccess::delUserAccess($delUid);
    }

    /**
     * 判断此当前用户是否在安米后台管理允许登陆的用户组内
     *
     * @return bool
     */
    public static function isInAppbymeAdminGroup() {
        global $_G;
        $allowUsers = ArrayUtils::explode(WebUtils::getDzPluginAppbymeAppConfig('appbyme_allow_admin_users'));
        $allowGroupIds = unserialize(WebUtils::getDzPluginAppbymeAppConfig('appbyme_allow_admin_usergroups'));
        $allowGroupIds || $allowGroupIds = array(1);

        return ($_G['username'] != '' && in_array($_G['username'], $allowUsers)) || in_array($_G['groupid'], $allowGroupIds);
    }

    public static function pushIOSMessage($uid, $type, $message = '',$pushData = array()) {
        $payload = array();
        switch ($type) {
            case 'reply':
            case 'at':
            case 'friend':
            case 'pm':
                break;
            default:
                $type = '';
                break;
        }
        if (!empty($type)) {
            $payload['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
                'badge' => 1,
            );
            $payload['type']  = $type;
            $payload = array_merge($payload,$pushData);
            //            $payload['appbymeData'] = $data;
        }

        return Webutils::doAppAPNsHelper($uid, $payload);
    }

    public static function checkMobileCode($res, $mobile, $code) {
        // 验证注册时候手机号是否可以用
        if (!self::checkMobileRepeat($mobile)) {
            // return WebUtils::makeErrorInfo_oldVersion($res, 'mobcent_mobile_not');
            return array('rs' => 0, 'errcode' => 'mobcent_mobile_not');
        }
        // 验证手机号和code是否匹配
        $codeMobile = AppbymeSendsms::getBindByMobileCode($mobile, $code);
        if (!$codeMobile) {
            // return WebUtils::makeErrorInfo_oldVersion($res, 'mobcent_code_error');
            return array('rs' => 0, 'errcode' => 'mobcent_code_error');
        }
        // 验证验证码是否过期
        if (time() - $codeMobile['time'] > 5 * 60) {
            // return WebUtils::makeErrorInfo_oldVersion($res, 'mobcent_code_overdue');
            return array('rs' => 0, 'errcode' => 'mobcent_code_overdue');
        }
        return $res;
    }

    // 判断手机号码是否已经绑定过
    public static function checkMobile($mobile) {
        return AppbymeSendsms::checkMobile($mobile);
    }

    // 验证手机格式
    public static function checkMobileFormat($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,3,5,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }

    // 验证手机号码是否可以用
    public static function checkMobileRepeat($mobile) {
        $mobileFormat = self::checkMobileFormat($mobile);
        if (!$mobileFormat) {
            return false;
        }
        // 验证手机是否已经绑定过
        $mobileRegisterInfo = UserUtils::checkMobile($mobile);
        if ($mobileRegisterInfo) {
            return false;
        }
        return true;
    }

    //随机生成字符串
    public function _generateChars() {
        $chars = 'abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $randChars = '';
        for ($i = 0; $i < 8; $i++) {
            $randChars .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $randChars;
    }

    /**
     * 检查积分下限
     *  Copy Forum Discuz libfile('function/credit')
     * @author NaiXiaoXin
     * @param string $action : 策略动作Action或者需要检测的操作积分值使如extcredits1积分进行减1操作检测array('extcredits1' => -1)
     * @param Integer $uid : 用户UID
     * @param Integer $coef : 积分放大倍数/负数为减分操作
     * @param Integer $returnonly : 只要返回结果，不用中断程序运行
     */
    public static function checkCredit($action, $uid = 0, $coef = 1, $fid = 0, $returnonly = 0) {
        global $_G;
        $res['error'] = '0';
        include_once libfile('class/credit');
        $credit = &credit::instance();
        $limit = $credit->lowerlimit($action, $uid, $coef, $fid);
        if ($returnonly)
            return $limit;
        if ($limit !== true) {
            $res['error'] = '1';
            $GLOBALS['id'] = $limit;
            $lowerlimit = is_array($action) && $action['extcredits' . $limit] ? abs($action['extcredits' . $limit]) + $_G['setting']['creditspolicy']['lowerlimit'][$limit] : $_G['setting']['creditspolicy']['lowerlimit'][$limit];
            $rulecredit = array();
            if (!is_array($action)) {
                $rule = $credit->getrule($action, $fid);
                foreach ($_G['setting']['extcredits'] as $extcreditid => $extcredit) {
                    if ($rule['extcredits' . $extcreditid]) {
                        $rulecredit[] = $extcredit['title'] . ($rule['extcredits' . $extcreditid] > 0 ? '+' . $rule['extcredits' . $extcreditid] : $rule['extcredits' . $extcreditid]);
                    }
                }
            } else {
                $rule = array();
            }
            $values = array(
                '{title}' => $_G['setting']['extcredits'][$limit]['title'],
                '{lowerlimit}' => $lowerlimit,
                '{unit}' => $_G['setting']['extcredits'][$limit]['unit'],
                '{ruletext}' => $rule['rulename'],
                '{rulecredit}' => implode(', ', $rulecredit)
            );
            if (!is_array($action)) {
                if (!$fid) {
                    $res['msg'] = 'mobcent_credits_policy_lowerlimit';
                    $res['values'] = $values;
                    //showmessage('credits_policy_lowerlimit', '', $values);
                } else {
                    $res['msg'] = 'mobcent_credits_policy_lowerlimit_fid';
                    $res['values'] = $values;
                    //showmessage('credits_policy_lowerlimit_fid', '', $values);
                }
            } else {
                $res['msg'] = 'mobcent_credits_policy_lowerlimit_norule';
                $res['values'] = $values;
                // showmessage('credits_policy_lowerlimit_norule', '', $values);
            }
        }
        return $res;
    }

    public static function periodsCheck($periods) {
        global $_G;
        $res['rs'] = '0';
        if (($periods == 'postmodperiods' || $periods == 'postbanperiods') && ($_G['setting']['postignorearea'] || $_G['setting']['postignoreip'])) {
            if ($_G['setting']['postignoreip']) {
                foreach (explode("\n", $_G['setting']['postignoreip']) as $ctrlip) {
                    if (preg_match("/^(" . preg_quote(($ctrlip = trim($ctrlip)), '/') . ")/", $_G['clientip'])) {
                        return $res;
                        break;
                    }
                }
            }
            if ($_G['setting']['postignorearea']) {
                $location = $whitearea = '';
                require_once libfile('function/misc');
                $location = trim(convertip($_G['clientip'], "./"));
                if ($location) {
                    $whitearea = preg_quote(trim($_G['setting']['postignorearea']), '/');
                    $whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
                    $whitearea = '.*' . $whitearea . '.*';
                    $whitearea = '/^(' . str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea) . ')$/i';
                    if (@preg_match($whitearea, $location)) {
                        return $res;
                    }
                }
            }
        }
        if (!$_G['group']['disableperiodctrl'] && $_G['setting'][$periods]) {
            $now = dgmdate(TIMESTAMP, 'G.i', $_G['setting']['timeoffset']);
            foreach (explode("\r\n", str_replace(':', '.', $_G['setting'][$periods])) as $period) {
                list($periodbegin, $periodend) = explode('-', $period);
                if (($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend)) || ($periodbegin < $periodend && $now >= $periodbegin && $now < $periodend)) {
                    $banperiods = str_replace("\r\n", ', ', $_G['setting'][$periods]);
                    $res['msg'] = 'period_nopermission';
                    $res['values'] = array('{banperiods}' => $banperiods);
                    $res['rs'] = '1';
                    return $res;
                }
            }
        }
        return $res;
    }



    // 获取用户统计信息
    public static function getCommonMemberCount($uid) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE uid=%d
            ', array('common_member_count', $uid)
        );
    }

    public function getPersonalDataInfo($uid, $space) {
        global $_G;

        $extcreditSet = WebUtils::getDzPluginAppbymeAppConfig('user_extcredit_show');

        $statisticalInfos = array();
        $statisticalInfos[] = array('type' => 'credits', 'title' => Yii::t('mobcent', 'mobcent_personage_integral', array()), 'data' => (int)$space['credits']);
        if ($extcreditSet) {
            if (is_array($_G['setting']['extcredits'])) {
                foreach ($_G['setting']['extcredits'] as $key => $value) {
                    if ($value['title'] && $extcreditSet == $key) {
                        $statisticalInfos[] = array('type' => 'extcredits' . $key, 'title' => $value['title'], 'data' => (int)$space["extcredits$key"]);
                    }
                }
            }
        }
        return $statisticalInfos;
    }

    public static function getVestList($uid) {
        $repeatList = array();
        foreach (UserUtils::getRepeatList($uid) as $user) {
            $repeatList[] = array(
                'userName' => $user,
            );
        }
        return $repeatList;
    }

    public static function getVerify($uid) {
        global $_G;
        $result = array();
        if ($_G['setting']['verify']['enabled']) {
            $memberVerify = C::t('common_member_verify')->fetch($uid);
            foreach ($_G['setting']['verify'] as $vid => $verify) {
                $temp = array();
                $temp['icon'] = '';
                if ($verify['available']) {
                    if ($memberVerify['verify' . $vid] == 1) {
                        //    $member_verify[]['verifyicon'][] = $vid;
                        $temp['vid'] = $vid;
                        $temp['verifyName'] = $verify['title'];
                        if ($verify['showicon'] == 1) {
                            $temp['icon'] = WebUtils::getHttpFileName($verify['icon']);
                        }
                        $result[] = $temp;
                    }
                }

            }
        }
        return $result;
    }

    function getRandChar($length) {
        $str = null;
        $strPol = "1234567890";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return '13' . $str;
    }

    /**
     * 生成KEY
     * @param $res
     * @return mixed
     */
    public static function createKey()
    {
        $code = rand(1000, 9999);
        $key = md5(random(3).time());
        $result = DbUtils::createDbUtils(true)->insert('appbyme_getpwd', array('pwdkey' => $key, 'time' => time(), 'code' => $code));
        if($result) {
            return $key;
        }
        return false;
    }

    public static function getImgCode($key)
    {
        $vcodes = DbUtils::createDbUtils(true)->queryScalar('
            SELECT code
            FROM %t
            WHERE pwdkey = %s
            AND (%s - time) < %d
        ', array('appbyme_getpwd', $key, time(), self::ImgCodeTime));
        if($vcodes) {
            return $vcodes;
        }
        return false;
    }

    public static function checkImg($key, $code)
    {
        $keyCode = DbUtils::createDbUtils(true)->queryScalar('
            SELECT code
            FROM %t
            WHERE pwdkey = %s
        ', array('appbyme_getpwd', $key));
        if($keyCode == $code && $code != '') {
            return true;
        }
        return false;
    }

    public static function checkImgCode($key, $code)
    {
        $keyCode = DbUtils::createDbUtils(true)->queryScalar('
            SELECT code
            FROM %t
            WHERE pwdkey = %s
            AND (%s - time) < %d
            AND rate = %d
        ', array('appbyme_getpwd', $key, time(), self::ImgCodeTime, self::Rate));
        if($keyCode == $code && $code != '') {
            DbUtils::createDbUtils(true)->update('appbyme_getpwd', array('rate' => 1), array('pwdkey' => $key));
            return true;
        }
        return false;
    }

    public static function checkCode($key, $code)
    {
        $keyCode = DbUtils::createDbUtils(true)->queryRow('
            SELECT code, coderate
            FROM %t
            WHERE pwdkey = %s
            AND (%s - time) < %d
            AND rate = %d
        ', array('appbyme_getpwd', $key, time(), self::ImgCodeTime, self::Rate));
        if(!empty($keyCode) && $keyCode['code'] == $code) {
            return $keyCode;
        }
        DbUtils::createDbUtils(true)->update('appbyme_getpwd', array('coderate' => $keyCode['coderate'] + 1), array('pwdkey' => $key));
        return false;
    }

    public static function updateImgCode($key)
    {
        $code = rand(1000, 9999);
        DbUtils::createDbUtils(true)->update('appbyme_getpwd', array('code' => $code, 'time' => time(), 'rate' => 0), array('pwdkey' => $key));
    }

    public static function createPwdCode($key, $code)
    {
        DbUtils::createDbUtils(true)->insert('appbyme_getpwd', array('pwdkey' => $key, 'code' => $code, 'time' => time()), false, true);

    }
}
