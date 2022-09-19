<?php

/**
 *  推送Controller
 * 
 *  @author   耐小心<nxx@yytest.cn>
 *  @copyright (c) 2012-2016 Appbyme
 */
class PushController extends AdminController {

    function actionIndex() {
        $appInfo = AppbymeConfig::getForumKey_other('ForumKey_app_download_options');
        foreach ($appInfo as $k=>$v){
            $tmp['name'] = WebUtils::u($v['appName']);
            $tmp['forumKey'] = $k;
            $info[] = $tmp;
        }
        if(empty($info)){
            $app = AppbymeConfig::getDownloadOptions();
            $tmp['name'] = WebUtils::u($app['appName']);
            $tmp['forumKey'] = AppUtils::getAppId();
            $info[] = $tmp;
        }
        $this->renderPartial('index',array('info'=>$info));
    }

    function actionPushApi() {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res['rs'] = 0;
        $post = array();
        $post['forumKey'] = $_GET['forumKey'];
        if (empty($post['forumKey'])) {
            $res['head']['errInfo'] = 'ForumKey不存在，请重新打包或者联系管理员';
            WebUtils::outputWebApi($res,'utf-8');
        }
        //赋值推送内容
        if ($_GET['phoneType'] == 'all' || $_GET['phoneType'] == 'android') {
            if (empty($_GET['androidTitle']) || empty($_GET['content'])) {
                $res['head']['errInfo'] = 'Android标题和内容不得为空';
                WebUtils::outputWebApi($res,'utf-8');
            }
            $post['androidTitle'] = $_GET['androidTitle'];
            $post['content'] = $_GET['content'];
        }
        if ($_GET['phoneType'] == 'all' || $_GET['phoneType'] == 'ios') {
            if (empty($_GET['iosTitle'])) {
                $res['head']['errInfo'] = 'IOS标题不得为空';
                WebUtils::outputWebApi($res,'utf-8');
            }
            $post['iosTitle'] = $_GET['iosTitle'];
        }
        $post['phoneType'] = $_GET['phoneType'];
        //赋值推送ID
        if ($_GET['pushType'] == 'url') {
            if (empty($_GET['pushUrl'])) {
                $res['head']['errInfo'] = '推送URL不得为空';
                WebUtils::outputWebApi($res,'utf-8');
            }
            $post['pushType'] = 'url';
            $post['pushUrl'] = $_GET['pushUrl'];
        } elseif ($_GET['pushType'] == 'article') {
            $topicId = (int) $_GET['topicId'];
            if (empty($topicId)) {
                $res['head']['errInfo'] = '文章ID不得为空';
                WebUtils::outputWebApi($res,'utf-8');
            }
            $post['pushType'] = 'article';
            $post['topicId '] = $topicId;
        } elseif ($_GET['pushType'] == 'topic') {
            $topicId = (int) $_GET['topicId'];
            if (empty($topicId)) {
                $res['head']['errInfo'] = '帖子ID不得为空';
                WebUtils::outputWebApi($res,'utf-8');
            }
            $post['pushType'] = 'topic';
            $post['topicId '] = $topicId;
        } else {
            if (empty($topicId)) {
                $res['head']['errInfo'] = '推送类型选择错误';
                WebUtils::outputWebApi($res,'utf-8');
            }
        }
        if ($_GET['isTest'] == '1') {
            $uid = (int) $_GET['dzUserId'];
            $userInfo = getuserbyuid($uid);
            if (empty($userInfo)) {
                $res['head']['errInfo'] = '用户不存在，请填写正确的UID';
                WebUtils::outputWebApi($res,'utf-8');
            }
            $post['isTest'] = 1;
            $post['dzUserId'] = $uid;
        } else {
            $post['isTest'] = 0;
        }
        $post['deviceNo'] = null;
        $post['sendTime'] = 0;
        $post['msgDelay'] = '0';
        $post['secondCatId'] = '901';
        $url = 'http://www.appbyme.com/mobcentACA/appToPush.do';
        $return = WebUtils::httpRequestByDiscuzApi($url, $post);
        switch ($return) {
            case '"success"';
                $res['rs'] = 1;
                break;
            case '"fail"';
                $res['head']['errInfo'] = '您未开通该功能，请联系管理员';
                break;
            case '"pass"';
                $res['head']['errInfo'] = '每天只能推送5条，带来不便敬请谅解！';
                break;
            case '"noregister"';
                $res['head']['errInfo'] = '用户ID不存在或该用户未登陆手机客户端！';
                break;
            case '"pushTypeError"';
                $res['head']['errInfo'] = '推送类型错误！';
                break;
            case '"error"';
                $res['head']['errInfo'] = '推送错误，请重新推送！';
                break;
            case '"error"';
                $res['head']['errInfo'] = '推送异常，请重新推送！';
                break;
            default :
                $res['head']['errInfo'] = '推送错误。请联系管理员！';
        }
        WebUtils::outputWebApi($res,'utf-8');
    }

}
