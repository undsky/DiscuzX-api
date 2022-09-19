<?php
/**
 * 用户退出聊天室
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/15
 * Time: 16:35
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class OutroomAction extends MobcentAction
{
    /**
     * @param $cid  要离开的聊天室id
     */
    public function run($cid)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $this->getController()->db->delete('appbyme_chatuser',array(
            'uid' => $this->getController()->uid,
            'cid' => $cid
        ));
        WebUtils::outputWebApi($res);
    }
}