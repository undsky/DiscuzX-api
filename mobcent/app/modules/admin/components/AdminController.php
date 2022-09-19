<?php

/**
 * 后台管理控制器基类
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AdminController extends Controller {

    public $rootUrl = '';
    public $dzRootUrl = '';

    public function init() {
        parent::init();
        global $_G;
        $_G['siteurl'] = substr($_G['siteurl'], 0, -16);
        $_G['siteroot'] = substr($_G['siteroot'], 0, -16);
        $this->rootUrl = Yii::app()->getBaseUrl(true);
        $this->dzRootUrl = substr($this->rootUrl, 0, -16);
        loadcache('plugin');
        loadcache(MOBCENT_DZ_PLUGIN_ID);
        DbUtils::init(false);
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
