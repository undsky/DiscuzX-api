<?php

/**
 * 后台管理模块类
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AdminModule extends CWebModule
{
    public $controllerMap = array(
        'uidiy' => 'application.modules.admin.controllers.UIDiyController',
        'wshdiy' => 'application.modules.admin.controllers.WshDiyController',
    );

    public function init()
    {
        $this->setImport(array(
            // 'admin.models.*',
            'admin.components.*',
        ));

        header("Content-Type: text/html; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        header('Pragma: no-cache');
    }

    public function beforeControllerAction($controller, $action)
    {
        global $_G;
        if (!($controller->id == 'index' && $action->id == 'login') && !UserUtils::isInAppbymeAdminGroup()) {
            $controller->redirect(Yii::app()->createAbsoluteUrl('admin/index/login'));
        }
        if (!$this->checkfounder()) {
            $allowUsers = ArrayUtils::explode(WebUtils::getDzPluginAppbymeAppConfig('appbyme_allow_admin_users'));
            $r = explode('/', Yii::app()->request->getParam('r'));
            if ($r[1] != 'index' && $_G['username'] != $allowUsers[0]) {
                $this->getUserAllow($_G['username'], $r[1]);
            }
        }
        return true;
    }
    private function checkfounder() {
        global $_G;
        $founders = $_G['config']['admincp']['founder'];
        if (!$_G['uid'] || $_G['groupid'] != 1 || $_G['adminid'] != 1) {
            return false;
        } elseif (empty($founders)) {
            return true;
        } elseif ($this->strexists(",$founders,", ",$_G[uid],")) {
            return true;
        } elseif (!is_numeric($_G['username']) && strexists(",$founders,", ",$_G[username],")) {
            return true;
        } else {
            return FALSE;
        }
    }
    private function strexists($haystack, $needle) {
        return !(strpos($haystack, $needle) === FALSE);
    }
    //获取用户权限
    private function getUserAllow($username, $router) {
        $sql = '
            SELECT *
            FROM %t
            WHERE username =%s
            ';
        $result = DbUtils::getDzDbUtils(true)->queryRow($sql, array('appbyme_auth', $username));
        if ($result) {
            $auth = explode(',', $result['allow']);
            if (!in_array($router, $auth)) {
                $this->success('没有权限，请联系插件后台管理员进行权限配置！');
            }
        } else {
            $this->success('没有权限，请联插件后台管理员进行权限配置！');
        }
    }
    /**
     * 操作提示
     *
     * @param mixed $msg 消息.
     * @param mixed $url 跳转地址.
     */
    public function success($msg, $url = '') {
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>提示</title>
            </head>

            <body>
            <div style="width:380px;border:1px #CCCCCC solid;height:180px; margin:0 auto; margin-top:100px; line-height:20px; text-align:center;font-size:14px;padding-top:50px;border-radius:5px">&nbsp;&nbsp;( ^_^ )
            ' . $msg;
        if ($url) {
            $html .='<meta http-equiv="refresh" content="2;URL=' . $url . '"><a  href="' . $url . '" style="text-decoration:none;font-size:14px;color:#00C">[继续]</a>';
        } else {
            $html .='<a  href="javascript:history.back()" style="text-decoration:none;font-size:14px;color:#00C">[返回]</a>';
        }
        $html .='</div></body></html>';
        echo $html;
        exit;
    }
}
