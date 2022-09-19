<?php

/**
 *
 * HtmlApi接口
 *
 * @author tantan
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
class HtmlapiController extends MobcentController
{
    private $_oldkey = 'app_download_options';
    private $_newkey = 'ForumKey_app_download_options';

    //获得appinfo
    public function actionGetappinfo()
    {
        $db = DbUtils::createDbUtils(true);
        $outString = WebUtils::initWebApiArray_oldVersion();
        $forumKey = isset($_GET['forumKey']) ? $_GET['forumKey'] : 0;
        if (!$forumKey)
        {
            $data = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s', array('appbyme_config', $this->_oldkey));
        } else
        {
            $data = $db->queryScalar("SELECT `cvalue` FROM %t WHERE `ckey`=%s", array('appbyme_config', $this->_newkey));
        }
        $data = unserialize($data);
        if (empty($data))
        {
            $outString['rs'] = 0;
            $outString['head']['errInfo'] = '没有数据';
        } else
        {
            $outString['body']['data'] = $forumKey ? $data[$forumKey] : $data;
            $outString['body']['data']['appInfo'] = $outString['body']['data']['appDescribe'];
            $outString['body']['data']['downId'] = $outString['body']['data']['appVersion'];
            unset($outString['body']['data']['appDescribe']);
            unset($outString['body']['data']['appVersion']);
            $outString['body']['data']['appDownUrl'] = $this->dzRootUrl . '/mobcent/download/down.php';
            $wxSetting = AppbymeConfig::getWebAppInfo();
            $BaiSetting = AppbymeConfig::getCvalue('BaiId_config');
            $outString['body']['data']['wxAppid'] = strval($wxSetting['wxappid']);
            $outString['body']['data']['census'] = strval($BaiSetting['census']);
            $openDownBar = AppbymeConfig::getCvalue('openDownBar');
            $outString['body']['data']['openDownBar'] = isset($openDownBar['openDownBar']) ? $openDownBar['openDownBar'] : 1;
        }
        echo WebUtils::outputWebApi($outString, '', false);
        exit();
    }

    public function actionGetUserInfo($token)
    {
        global $_G;
        $res = WebUtils::initWebApiArray_oldVersion();
        $dbResult = DB::fetch_first('SELECT uid,time FROM %t WHERE `code`=%s', array('appbyme_tempcode', $token));
        if (empty($dbResult))
        {
            WebUtils::outputWebApi(WebUtils::makeErrorInfo_oldVersion($res, WebUtils::t('Token不存在')));
        }
        if (time() - $dbResult['time'] > '300')
        {
            WebUtils::outputWebApi(WebUtils::makeErrorInfo_oldVersion($res, WebUtils::t('Token有效期已过期')));
        }
        DB::delete('appbyme_tempcode', array('code' => $token));
        $uid = $dbResult['uid'];
        $result['member'] = getuserbyuid($dbResult['uid']);
        $_G['username'] = $result['member']['username'];
        // 把登录信息写入cookie中，并且更新登录的状态
        UserUtils::updateCookie($result['member'], $uid);
        // 需要整理token和secret再返回给客户端
        $userInfo = AppbymeUserAccess::loginProcess($_G['uid'], random('10'));
        $userAvatar = UserUtils::getUserAvatar($_G['uid']);

        $res['isValidation'] = 0;
        $res['token'] = (string)$userInfo['token'];
        $res['secret'] = (string)$userInfo['secret'];
        $res['uid'] = (int)$_G['uid'];
        $res['avatar'] = (string)$userAvatar;
        $res['userName'] = (string)$_G['username'];
        $space = getuserbyuid($_G['uid']);
        space_merge($space, 'profile');
        space_merge($space, 'count');
        $res['score'] = (int)$space['credits'];
        $res['gender'] = (int)UserUtils::getUserGender($_G['uid'], $space);
        $res['userTitle'] = UserUtils::getUserTitle($_G['uid'], $space);
        $res['repeatList'] = UserUtils::getVestList($_G['uid']);
        $res['creditShowList'] = UserUtils::getPersonalDataInfo($_G['uid'], $space);
        $res['creditShowList'] = UserUtils::getPersonalDataInfo($_G['uid'], $space);
        $mobile = AppbymeSendsms::getPhoneByUid($_G['uid']);//获得手机号
        $res['mobile'] = strval($mobile['mobile']);//就算手机号为空也可以转
        WebUtils::outputWebApi($res);
    }

    public function actionGetUidiyVersion($id = 0)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $key = $this->getDiyVersionKey($id);
        $res['version'] =  intval(DB::result_first('SELECT `cvalue` FROM  %t WHERE  `ckey`=%s', array('appbyme_config', $key)));
        WebUtils::outputWebApi($res);
    }


    protected function mobcentAccessRules()
    {
        return array(
            'views'           => false,
            'getappinfo'      => false,
            'getuserinfo'     => false,
            'getuidiyversion' => false,
            'checkimg' => false,
        );
    }

    public function actions() {
        return array(
            'views' => 'application.controllers.Htmlapi.ViewsAction',
            'checkimg' => 'application.controllers.Htmlapi.CheckImgAction',
        );
    }

    private function getDiyVersionKey($id)
    {
        $data = 'app_uidiy_';
        $data .= 'stable';
        $data .= '_' . $id . '_version';
        return $data;
    }
}