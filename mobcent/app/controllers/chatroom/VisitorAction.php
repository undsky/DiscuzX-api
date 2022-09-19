<?php
/**
 * 游客进入聊天室
 * User: congjie
 * Date: 16/8/11
 * Time: 下午8:40
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class VisitorAction extends MobcentAction
{
    private $db;
    private $server;

    public function run($cid)
    {
        $this->db = DbUtils::createDbUtils(true);
        $this->server = $this->controller->server;
        $outdata = WebUtils::initWebApiArray_oldVersion();
        $uid = $this->db->queryScalar('SELECT `uid` FROM %t WHERE `time` < %d', array('appbyme_visitor', strtotime('-1 day')));
        $uavatar = UserUtils::getUserAvatar('default');
        if(!$uid) {
            $username = WebUtils::t('【直播观赏】游客');
            $uid = rand(100000000,999999999);
            $this->db->insert('appbyme_visitor', array(
                'uid'        => $uid,
                'username'   => $username,
                'uavatar' => $uavatar,
                'time'     => time()
            ), true, false);
        }else {
            //更新用户当前时间信息
            $this->db->update('appbyme_visitor', array('time' => time()), array('uid' => $uid));
        }
        $visitor = $this->db->queryRow('SELECT * FROM %t WHERE `uid`=%d', array('appbyme_visitor', $uid));
        $uid   = $visitor['uid'];
        $username = $visitor['username'];
        $re = $this->db->insert('appbyme_chatuser',array(
            'uid' => $uid,
            'uname' => $username,
            'uavatar' => $uavatar,
            'cid' => $cid
        ), true, true);
        if($re)
        {
            $outdata['body']['uid'] = $uid;
            $outdata['body']['username'] = $username;
            $outdata['body']['avatar']   = $uavatar;
        } else {
            $outdata['rs'] = 0;
            $outdata['head']['errInfo'] = '进入聊天室失败';
        }

        $result = $this->server->getToken($uid,$username,$uavatar);
        $result = json_decode($result,true);
        if($result['code'] != 200){
            WebUtils::outputWebApi(WebUtils::makeErrorInfo_oldVersion($outdata,$result['errorMessage']));
        }
        $outdata['body']['token'] = $result['token'];
        $outdata['body']['appKey'] = $this->getController()->appKey;
        WebUtils::outputWebApi($outdata);
    }

}
