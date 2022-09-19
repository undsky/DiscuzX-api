<?php
/**
 * 用户进入聊天室
 * @author tanguanghua <18725648509@163.com>
 **/
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class InroomAction extends MobcentAction
{
    /**
     * @param $cid  要进入的聊天室id
     */
    public function run($cid)
    {
        $outdata = WebUtils::initWebApiArray_oldVersion();
        $controller = $this->getController();
        $have  = $controller->db->queryScalar('SELECT * FROM %t WHERE `uid`=%d AND `cid`=%d',array('appbyme_chatuser',$controller->uid,$cid));
        if($have){
            $outdata['body']['uid'] = $controller->uid;
            WebUtils::outputWebApi($outdata);
        }
        $uname = $controller->db->queryScalar('SELECT `username` FROM %t WHERE `uid`=%d',array('common_member',$controller->uid));
        $uavatar = UserUtils::getUserAvatar($controller->uid);
        $re = $controller->db->insert('appbyme_chatuser',array(
            'uid' => $controller->uid,
            'uname' => $uname,
            'uavatar' => $uavatar,
            'cid' => $cid
        ));
        if($re)
        {
            $outdata['body']['uid'] = $controller->uid;
        }else{
            $outdata['rs'] = 0;
            $outdata['head']['errInfo'] = '进入聊天室失败';
        }
        WebUtils::outputWebApi($outdata);
    }
}
