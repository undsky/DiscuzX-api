<?php

/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
class FriendAction extends MobcentAction
{
    public function run($uid)
    {
        global $_G;
        $res = WebUtils::initWebApiArray_oldVersion();

        $count                   = DB::result_first("SELECT COUNT(*) FROM %t WHERE `uid`=%d AND `fuid`=%d ", array('home_friend', $_G['uid'], $uid));
        $res['body']['isFriend'] = $count == 0 ? false : true;

        WebUtils::outputWebApi($res);
    }

}