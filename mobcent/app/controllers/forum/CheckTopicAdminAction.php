<?php

/**
 *  校验发帖/回帖是否需要手机号
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class CheckTopicAdminAction extends MobcentAction {
    function run($act = 'new') {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_checkTopicAdmin($res, $act);
        WebUtils::outputWebApi($res);
    }

    private function _checkTopicAdmin($res, $act) {
        global $_G;
        if ($act == 'new') {
            $check = WebUtils::getDzPluginAppbymeAppConfig('must_bind_thread');
        } else {
            $check = WebUtils::getDzPluginAppbymeAppConfig('must_bind_post');
        }
        if ($check) {
            if (!AppbymeSendsms::checkUserBindMobile($_G['uid'])) {
                $res['isValidation'] = 1;
                return $this->makeErrorInfo($res, '', array('noError' => 0, 'alert' => 0));
            }
        }
        return $res;
    }
}