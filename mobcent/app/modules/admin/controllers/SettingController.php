<?php
/**
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class SettingController extends AdminController{
    public function actionIndex(){
        $info = AppbymeConfig::getSetting();
        if(empty($info['search'])){
            $info['search'][] = 'portal';
            $info['search'][] = 'topic';
            $info['search'][] = 'user';
        }
        if (!empty($_POST)) {
            $info['reg'] = $_POST['reg'];
            $info['registration'] = $_POST['registration'];
            $info['email'] = $_POST['email'];
            $info['reply'] = $_POST['reply'];
            if(empty($_POST['search'])){
                $_POST['search'][] = 'portal';
                $_POST['search'][] = 'topic';
                $_POST['search'][] = 'user';
            }
            $info['search'] = $_POST['search'];
            $info['qqsdk'] = $_POST['qqsdk'];
            AppbymeConfig::saveSetting($info);
            echo ' <script>alert(\'修改成功\');</script>';
        }
        $this->renderPartial('index', array('info' => $info));
    }
}