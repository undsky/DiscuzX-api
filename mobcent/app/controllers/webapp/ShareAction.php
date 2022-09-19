<?php

/**
 * WebApp 帖子分享
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2012-2016 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

//Mobcent::setErrors();

class ShareAction extends MobcentAction {

    public function run()
    {
        if(!$_GET['forumKey']) {
            $_GET['forumKey'] = AppbymeConfig::getForumkey();
        }
        $url = 'http://'.$_GET['forumKey'].'.xiaoyun.com/m/post/'.$_GET['tid'];
        Header("HTTP/1.1 301 Moved Permanently");
        Header("Location: ".$url);
        exit();
    }
}


