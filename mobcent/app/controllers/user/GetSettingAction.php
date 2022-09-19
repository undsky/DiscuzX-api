<?php

/**
 * 获取用户设置 接口
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class GetSettingAction extends MobcentAction {
    private $_wxLogin = 'wxLogin_config';

    public function run($getSetting, $longitude = 0, $latitude = 0, $location = '') {
        $res = $this->initWebApiArray();

        // $longitude='116.3093650';$latitude='40.0611250';$location='北京市海淀区上地东路';
        $location = WebUtils::t(rawurldecode($location));
        global $_G;
        ($uid = $_G['uid']) && $this->_saveUserLocation($uid, $longitude, $latitude, $location);

        // $getSetting ="{'body': {'postInfo': {'forumIds': '0'}}}";
        $settings = rawurldecode($getSetting);
        $settings = WebUtils::jsonDecode($settings);
        $postInfo = isset($settings['body']['postInfo']) ? $settings['body']['postInfo'] : array();

        if (!empty($postInfo)) {
            $res['body']['postInfo'] = $this->_getPostInfo($postInfo);
        }
        $res['body']['serverTime'] = time() . '000';
        $res['body']['misc'] = $this->_getMiscSetting();
        $res['body']['plugin'] = $this->_getPluginSetting();
        $res['body']['forum'] = $this->_getForumSetting();
        $res['body']['portal'] = $this->_getPortalSetting();
        $res['body']['user'] = $this->_getUserSetting();
        $res['body']['message'] = $this->_getMessageSetting();
        $res['body']['moduleList'] = PortalUtils::getModuleList(6); // 2014/11/4 门户资讯分类模块列表
        $res['body']['topicVerify'] = $this->_topicVerify();
        $res['body']['hideTpcTitle'] = $this->_hideTpcTitle();
        $res['body']['html5'] = $this->_htmlSetting();
        $res['body']['discuz_version'] = DISCUZ_VERSION;
        echo WebUtils::outputWebApi($res, '', false);
    }

    private function _saveUserLocation($uid, $longitude, $latitude, $location) {
        // 插入用户定位开关设置
        $count = (int)DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(*)
            FROM %t
            WHERE uid=%d
            AND ukey=%s
        ', array('appbyme_user_setting', $uid, AppbymeUserSetting::KEY_GPS_LOCATION)
        );
        if (!$count) {
            AppbymeUserSetting::saveNewSettings($uid, array(
                AppbymeUserSetting::KEY_GPS_LOCATION => AppbymeUserSetting::VALUE_GPS_LOCATION_ON,
            ));
        }
        !empty($location) && SurroundingInfo::saveUserLocation($uid, $longitude, $latitude, $location);
    }

    private function _getPostInfo($postInfo) {
//        return UserUtils::getPermission($postInfo['forumIds']);
        return UserUtils::getPermission('all');
    }

    private function _getMiscSetting() {
        $misc = array(
            'weather' => $this->_getWeatherConfig(),
        );
        return $misc;
    }

    private function _getWeatherConfig() {
//        $weather = array('allowUsage' => 1, 'allowCityQuery' => 1);
//        $forumKey = isset($_GET['forumKey']) ? $_GET['forumKey'] : '';
//        $platType = isset($_GET['platType']) ? $_GET['platType'] : APP_TYPE_ANDROID;
//        $url = 'http://sdk.mobcent.com/baikesdk/phpapi/settings';
//         $url = 'http://192.168.1.213/forum/phpapi/settings';
//        $url .= sprintf('?forumKey=%s&platType=%s&gzip=false', $forumKey, $platType);
//        $res = WebUtils::httpRequest($url, 10);
//        $res = WebUtils::jsonDecode($res);
//        isset($res['data']['weather']['show_weather']) && $weather['allowUsage'] = (int)$res['data']['weather']['show_weather'];
//        isset($res['data']['weather']['city_query_setting']) && $weather['allowCityQuery'] = (int)$res['data']['weather']['city_query_setting'];
//        return $weather;
        return array('allowUsage' => 1, 'allowCityQuery' => 1);
    }

    // 获取插件设置
    private function _getPluginSetting() {
        global $openSetting;
        $openSetting = $this->_getOpenSetting();
        $plugin = array(
            'qqconnect' => $this->_isQQConnect(),
            'dsu_paulsign' => $this->_isDsuPaulsign(),
            'wxconnect' => $this->_isWechatConnect(),
            'isMobileRegisterValidation' => (int)$this->_isMobileRegisterValidation(),
            'isInviteActivity' => (int)$this->_isInviteActivity(),
            'activityId' => (int)$this->_getActivityId(),
            'isFastRegister' => (int)$this->_isFastRegister(),
            'isCustomUserSetting' => (int)$this->_isCustomUserSetting(),
            //'userSettingUrl' => (string) $this->userSettingUrl(),
            'userSettingUrl' => (string)$openSetting['userinfo'],
            'isCloseEmail' => (int)$this->_isCloseEmail(),
            'aboutUrl' => (string)$openSetting['aboutus'],
            'threadTemplate' => $this->ThreadTemplate(),
            'foundUrl' => (string)$openSetting['foundpw'],
            'webviewTopbarMore' => (int)$this->getWebviewTopMore(),
            'gotye' => (int)$this->getGotye(),
            'changeMobile'=>intval($this->getChangeMobile()),
            'privacyUrl' => $this->getPrivacyUrl(),
            'registrationUrl' => $this->getRegistrationUrl(),
            'isQQSdk'=>$this->_isQQSdk(),
            'isVideo'=>intval($this->_isVideo()),
            'imFriend'=>intval($this->_imFriend()),
            'allow_image' => intval($this->_allowImg()),
        );
        return $plugin;
    }

    // 获取版块设置
    private function _getForumSetting() {
        $plugin = array(
            'isSummaryShow' => $this->_isForumSummaryShow(),
            'isTodayPostCount' => $this->_isForumShowTodayPost(),
            'postlistOrderby' => (int)WebUtils::getDzPluginAppbymeAppConfig('forum_postlist_orderby'),
            'postAudioLimit' => $this->_getAudioLimit('forum_audio_limit'),
            'isShowLocationTopic' => (int)WebUtils::getDzPluginAppbymeAppConfig('forum_allow_newtopic_location'),
            'isShowLocationPost' => (int)WebUtils::getDzPluginAppbymeAppConfig('forum_allow_newpost_location'),
            'isH5Post'=>intval($this->_isH5()),
            // 'defaultNewImageTopicFid' => (int)WebUtils::getDzPluginAppbymeAppConfig('forum_new_image_topic'),
        );
        return $plugin;
    }

    // 获取门户设置
    private function _getPortalSetting() {
        $portal = array(
            'isSummaryShow' => $this->_isPortalSummaryShow(),
        );
        return $portal;
    }

    /**
     * 获取消息设置
     */
    private function _getMessageSetting() {
        return array(
            'pmAudioLimit' => $this->_getAudioLimit('message_pm_audio_limit'),
            'allowPostImage' => 1,
        );
    }

    // 是否开启qq登陆
    private function _isQQConnect() {
        $setting = AppbymeConfig::getSetting();
        if($setting['qqsdk']==1){
            return $this->_isMobileAllowQQlogin() ? 1 : 0;
        }
        return DzCommonPlugin::isQQConnectionAvailable() && $this->_isMobileAllowQQlogin() ? 1 : 0;
    }

    private function _isQQSdk(){
        $setting = AppbymeConfig::getSetting();
        return $setting['qqsdk'] ? 1 : 0;
    }
    // 是否开启签到
    private function _isDsuPaulsign() {
        return DzCommonPlugin::isDsuPaulsignAvailable() && $this->_isMobileAllowPaulsign() ? 1 : 0;
    }

    private function _isWechatConnect() {
        return $this->_isMobileAllowWechatlogin() ? 1 : 0;
    }

    private function _isMobileAllowPaulsign() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_sign');
        return !($config !== false && $config == 0);
    }

    // 是否显示论坛摘要
    private function _isForumSummaryShow() {
        $forumSummaryLenth = WebUtils::getDzPluginAppbymeAppConfig('forum_summary_length');
        return $forumSummaryLenth !== false && $forumSummaryLenth == 0 ? 0 : 1;
    }

    // 是否显示门户摘要
    private function _isPortalSummaryShow() {
        $portalSummaryLenth = WebUtils::getDzPluginAppbymeAppConfig('portal_summary_length');
        return $portalSummaryLenth !== false && $portalSummaryLenth == 0 ? 0 : 1;
    }

    // 是否开启获取当天发帖总数
    private function _isForumShowTodayPost() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('forum_show_today_post');
        return $config !== false && $config == 0 ? 0 : 1;
    }

    // 是否允许qq登陆
    private function _isMobileAllowQQlogin() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_qqlogin');
        return !($config !== false && $config == 0);
    }

    private function _isMobileAllowWechatlogin() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_wxlogin');
        return !($config !== false && $config == 0);
    }

    // 是否开启注册手机验证
    private function _isMobileRegisterValidation() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
        return !($config !== false && $config == 0);
    }

    // 是否开启邀请活动
    private function _isCloseEmail() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('close_email_register');
        return !($config !== false && $config == 0);
    }

    // 是否开启邀请活动
    private function _isInviteActivity() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('appbyme_invite_activity');
        return !($config !== false && $config == 0);
    }

    //是否开启快速注册
    private function _isFastRegister() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('fast_register');
        return !($config !== false && $config == 0);
    }

    //快速注册用户组
    private function fastRegisterGroup() {
        $fastRegisterGroup = WebUtils::getDzPluginAppbymeAppConfig('fast_register_group');
        return $fastRegisterGroup;
    }

    //是否开启自定义个人设置
    private function _isCustomUserSetting() {
        //$config = WebUtils::getDzPluginAppbymeAppConfig('custom_user_setting');
        //return !($config !== false && $config == 0);
        global $openSetting;
        if (empty($openSetting['userinfo'])) {
            return '0';
        }
        return '1';
    }

    //自定义个人设置地址
    private function userSettingUrl() {
        $userSettingUrl = WebUtils::getDzPluginAppbymeAppConfig('user_setting_url');
        return $userSettingUrl;
    }

    // 关于URL
    private function AboutUrl() {
        $AboutUrl = WebUtils::getDzPluginAppbymeAppConfig('about_url');
        return $AboutUrl;
    }

    //帖子H5
    private function ThreadTemplate() {
        global $openSetting;
        $return = array();

        foreach ($openSetting['thread'] as $k => $s) {
            if (!empty($s)) {
                $temp['id'] = (int)$k;
                $temp['url'] = $s;
                $return[] = $temp;
            }
        }

        return $return;
    }

    private function getWebviewTopMore() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('topbar_more');
        return !($config !== false && $config == 0);
    }

    private function getGotye() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('gotye');
        if ($config == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function _getActivityId() {
        $activityId = WebUtils::getDzPluginAppbymeAppConfig('invite_activity_id');
        return $activityId;
    }

    private function _getUserSetting() {
        global $_G;
        $res = array(
            'allowAt' => (int)$_G['group']['allowat'],
            'allowRegister' => (int)WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_register'),
            'wapRegisterUrl' => (string)WebUtils::getDzPluginAppbymeAppConfig('mobile_register_url'),
            'maxSignSize' => (int)$_G['group']['maxsigsize'],
            'shareActivity' => $this->_getShareStatus(),
            'search'=>$this->getSearch(),
            'verify'=>$this->getVerify(),
            'mustBindThread'=>(int)WebUtils::getDzPluginAppbymeAppConfig('must_bind_thread'),
            'mustBindPost'=>(int)WebUtils::getDzPluginAppbymeAppConfig('must_bind_post'),

        );
        return $res;
    }

    private function _getAudioLimit($key) {
        $limit = (int)WebUtils::getDzPluginAppbymeAppConfig($key);
        $limit < 0 && $limit = -1;
        $limit > 600 && $limit = 600;
        return $limit;
    }

    private function _isH5(){
        $setting = WebUtils::getDzPluginAppbymeAppConfig('h5_post');
        return $setting!==false ? $setting : 1;
    }

    private function _getOpenSetting() {
        $setting = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ', array('appbyme_config', 'app_open')
        );
        $setting = unserialize($setting['cvalue']);
        return $setting;
    }

    private function _getShareStatus() {
        $res = array('status'=>0);
        $activityId = AppbymeConfig::getShareActivityId();
        if(empty($activityId)){
            return $res;
        }
        $info = AppbymeShareModel::getInfoByIDCache($activityId);

        if (empty($info)) {
            return $res;
        }
        if ($info['starttime'] < time() && $info['endtime'] > time()) {
            $res['status']  = 1;
            if($info['param']['topic']){
                $res['param']['topic']=1;
            }
            if($info['param']['portal']){
                $res['param']['portal']=1;
            }
            if($info['param']['app']){
                $res['param']['app']=1;
            }
            if($info['param']['live']){
                $res['param']['live']=1;
            }
        }
        return $res;
    }

    private function getChangeMobile() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobcent_user_change_mobile');
        if ($config == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function  getPrivacyUrl(){
        $info = AppbymeConfig::getSetting();
        return strval($info['reg']);
    }

    private function  getRegistrationUrl(){
        $info = AppbymeConfig::getSetting();
        return strval($info['registration']);
    }
    private function getSearch(){
        $result['topic'] = 1;
        $result['portal'] = 1;
        $result['user']  = 1;
        $info = AppbymeConfig::getSetting();
        $dbResult = $info['search'];
        if(empty($dbResult)){
            return $result;
        }
        if(!in_array('topic',$dbResult)){
            $result['topic']=0;
        }
        if(!in_array('portal',$dbResult)){
            $result['portal']=0;
        }
        if(!in_array('user',$dbResult)){
            $result['user']=0;
        }
        return $result;
    }

    private function getVerify(){
        global $_G;
        return intval($_G['setting']['verify']['enabled']);
    }
    //通过认证
    private function _topicVerify(){
        $db = DbUtils::getDzDbUtils(true);
        $uid = $this->getController()->uid;
        $verify = $db->queryRow('SELECT `cvalue` FROM %t WHERE `ckey`=%s LIMIT 1',array('appbyme_config','topic_bind_verify'));
        if(!empty($verify)){
            $ck = $db->queryRow('SELECT COUNT(0) FROM %t WHERE `uid`=%d AND `verify'.$verify['cvalue'].'`=1',array('common_member_verify',$uid));
        }
        if(!isset($ck) || !$ck['COUNT(0)']){
            return 0;
        }
        return 1;
    }
    //是否显示话题标题
    private function _hideTpcTitle(){
        $db = DbUtils::getDzDbUtils(true);
        $return = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s LIMIT 1',array('appbyme_config','topic_settitle_hide'));
        return intval($return);
    }

    //h5返回
    private function _htmlSetting(){
        $arr = array();
        $arr['wxLogin'] = $this->_wxLogin();
        $arr['jumpPc'] = $this->jumpPc();
        return $arr;
    }

    //wxLogin
    private function _wxLogin()
    {
        $config = AppbymeConfig::getCvalue($this->_wxLogin);
        return empty($config) ? 1 : intval($config['wxLogin']);
    }

    protected function _isVideo()
    {
        $config = WebUtils::getDzPluginAppbymeAppConfig('isVideo');
        if ($config != '1') {
            return false;
        } else {
            return true;
        }
    }

    protected function _imFriend()
    {
        $config = WebUtils::getDzPluginAppbymeAppConfig('imFriend');
        if ($config == '1') {
            return true;
        } else {
            return false;
        }
    }

    private function jumpPc()
    {
        $setting = AppbymeConfig::getCvalueData('app_jump',array('wzjump'=>1));
        if($setting['wzjump']){
            return 1;
        }else{
            return 0;
        }
    }

    private function _allowImg()
    {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_image');
        if ($config == '1') {
            return true;
        } else {
            return false;
        }
    }
}
