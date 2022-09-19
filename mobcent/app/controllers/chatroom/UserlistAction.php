<?php
/**
 * 聊天室用户列表
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/15
 * Time: 17:44
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class UserlistAction extends MobcentAction
{
    /**
     * @param $cid 聊天室id
     */
    public function run($cid,$page = 1,$pageSize = 10)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        if($pageSize > 50){
            $res['rs'] = 0;
            $res['head']['errInfo'] = '你的页面过大';
        }
        $str = ($page-1)*$pageSize;
        $count = $this->getController()->db->queryScalar('SELECT count(0) FROM %t WHERE `cid`=%d',array('appbyme_chatuser',$cid));
        $tmpdata = $this->getController()->db->queryAll('SELECT `uname`,`uavatar`,`uid` FROM %t WHERE `cid`=%d LIMIT %d,%d',array(
            'appbyme_chatuser',
            $cid,
            $str,
            $pageSize
        ));
        $res['body']['userlist'] = $tmpdata;
        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count,$res);
        WebUtils::outputWebApi($res);
    }
}