<?php

/**
 *
 *
 *
 * @author  NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 GoYoo Inc.
 */

class WebappController extends MobcentController {


    public function actions() {
        return array(
            'share' => 'application.controllers.webapp.ShareAction',
            'wxcall' => 'application.controllers.webapp.WxCallAction',
        );
    }
    public function mobcentAccessRules(){
        return array(
            'share'=>false,
            'index'=>false,
            'wxcall'=>false,
            'sharelogin'=>false,
            'wxfastlogin' => false
        );
    }

    public function actionIndex(){
        header("Content-Type: text/html; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        header('Pragma: no-cache');
        $userinfo = Yii::app()->session['uinfo'];
        $userinfo = $userinfo ? urldecode($userinfo) : '{}';
        $downloadInfo = AppbymeConfig::getDownloadOptions();
        $BaiSetting= AppbymeConfig::getCvalue('BaiId_config');
        $openDownBar = AppbymeConfig::getCvalue('openDownBar');
        $downloadInfo['census'] = strval($BaiSetting['census']);
        $downloadInfo['openDownBar'] = isset($openDownBar['openDownBar']) ? $openDownBar['openDownBar'] : 1;
        $this->renderPartial('index',array('downInfo'=>$downloadInfo,'userinfo'=>$userinfo));
    }
    
    public function actionShareLogin($act='login'){
        header("Content-Type: text/html; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        header('Pragma: no-cache');
        switch ($act){
            case 'login':
                $this->renderPartial('login');
                break;
            case 'reg':
                $this->renderPartial('reg');
                break;
            case 'wxlogin':
                $this->renderPartial('wxlogin');
                break;
            case 'reply':
                $this->renderPartial('reply');
                break;
            case 'error':
                $this->renderPartial('error');
                break;
            default:
                $this->renderPartial('login');
                break;
        }
        exit();
    }
    public function ActionWxfastlogin($userinfo)
    {
        Yii::app()->session['uinfo'] = $userinfo;
        $this->redirect(Yii::app()->createUrl('webapp/index'));
    }

    
}
