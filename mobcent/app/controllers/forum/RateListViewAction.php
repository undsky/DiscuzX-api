<?php

/**
 * 显示全部评分接口
 *
 * @author 耐小心
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

// Mobcent::setErrors();
class RateListViewAction extends MobcentAction {

    public function run($pid) {
        $res = $this->_getRate($pid);
        $arr = AppbymeConfig::getForumKey_other('ForumKey_app_download_options');
        $forumkey = isset($_GET['forumKey']) ? $_GET['forumKey'] : AppbymeConfig::getForumkey();
        $this->getController()->renderPartial('rateListViewNew',
            array(
                'loglist'  => $res['log'],
                'logcount' => $res['jf'],
                'color'    => $arr[$forumkey]['appColor'],
            ));
    }

    private function _getRate($pid) {
        global $_G;
        list($ratelogs, $postlist, $postcache) = ForumUtils::fetch_postrate_by_pid(array($pid), $postlist, $postcache, $_G['setting']['ratelogrecord']);
        $log = $postlist[$pid]['ratelog'];  //人数
        $rateItem = $postlist[$pid]['ratelogextcredits']; //项目
        ksort($rateItem);
        return array('log' => $log, 'jf' => $rateItem);
    }

}
