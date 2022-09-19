<?php

/**
 * Created by PhpStorm.
 * User: byd
 * Date: 16/6/15
 * Time: 下午2:31
 */

/**
 * Class ChatroomController
 * @property ServerAPI $server
 */
class ChatroomController extends MobcentController
{
    public $appKey = '';
    public $appSecret = '';
    public $server = null;
    public $db = null;

    public function init()
    {
        parent::init();
        $formkey = $_GET['forumKey'];
        if(!$formkey){
            $res = WebUtils::initWebApiArray_oldVersion();
            $res['rs'] = 0;
            $res['head']['errInfo'] = '传入参数错误!';
            WebUtils::outputWebApi($res);
        }
        $this->db = DbUtils::createDbUtils(true);
        $cvalue   = $this->db->queryScalar('select cvalue from %t where ckey = %s', array('appbyme_config', 'RONGYUN_KEY'));
        $cvalue   = unserialize($cvalue);
        $this->appSecret = $cvalue[$formkey]['appSecret'];
        $this->appKey    = $cvalue[$formkey]['appKey'];
        if(empty($this->appSecret)&&empty($this->appkey)){
            $res = WebUtils::initWebApiArray_oldVersion();
            $res['rs'] = 0;
            $res['head']['errInfo'] = '请申请融云key!';
            WebUtils::outputWebApi($res);
        }
        $this->server    = new ServerAPI($this->appKey, $this->appSecret);
    }

    protected function mobcentAccessRules()
    {
        return array(
            'ChatRoomCreate' => false,
            'visitor'        => false,
        );
    }
    public function actions() {
        return array(
            'inroom' => 'application.controllers.chatroom.InroomAction',
            'outroom' => 'application.controllers.chatroom.OutroomAction',
            'userlist' => 'application.controllers.chatroom.UserlistAction',
            'visitor' => 'application.controllers.chatroom.VisitorAction',
        );
    }

    /**
     * 创建聊天室
     * @param $cid
     * @param $name
     */
    public function actionChatRoomCreate($cid, $name)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $result = $this->server->chatroomCreate(array($cid => $name));
        $result = json_decode($result, true);
        if ($result['code'] != 200) {
            $res['rs'] = 0;
            $res['head']['errInfo'] = "容云错误:".$result['errorMessage'];
        } else {
            $data['uid'] = $this->uid;//用户ID
            $data['cid'] = $cid;//聊天室ID
            $data['cname'] = $name;//聊天室名称
            $data['ctime'] = time();//创建时间
            $this->db->insert('appbyme_chat', $data, true, true);
        }
        WebUtils::outputWebApi($res);
    }

    /**
     * 销毁聊天室
     * @param $cid
     */
    public function actionChatRoomDestroy($cid)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        if(!$cid){
            $res['rs'] = 0;
            $res['head']['errInfo'] = '聊天室id不能为空';
            WebUtils::outputWebApi($res);
        }else{
            $result = $this->server->chatroomDestroy($cid);
            $result = json_decode($result, true);
            if ($result['code'] != 200) {
                $res['rs'] = 0;
                $res['head']['errInfo'] = '销毁失败!';
            } else {
                $this->db->delete('appbyme_chat', array('cid' => $cid));
                $this->db->delete('appbyme_chatuser', array('cid' => $cid));
            }
            WebUtils::outputWebApi($res);
        }
    }

    /**
     * 添加禁言聊天室成员
     * @param $cid 聊天室ID
     * @param string $gag_time $gag_time 禁言时长，以分钟为单位，最大值为43200分钟。（必传）
     */
    public function actionChatRoomUserGagAdd($uid,$cid, $gag_time = '120')
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $result = $this->server->chatroomUserGagAdd($uid, $cid, $gag_time);
        $result = json_decode($result, true);
        if ($result['code'] != 200) {
            $res['rs'] = 0;
            $res['head']['errInfo'] = '禁言失败!';
        }
        WebUtils::outputWebApi($res);
    }

    /**
     * 移除禁言聊天室成员
     * @param $cid 聊天室ID
     */
    public function actionChatRoomUserGagRollback($uid,$cid)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $result = $this->server->chatroomUserGagRollback($uid, $cid);
        $result = json_decode($result, true);
        if ($result['code'] != 200) {
            $res['rs'] = 0;
            $res['head']['errInfo'] = '移除禁言失败!';
        }
        WebUtils::outputWebApi($res);
    }

    /**
     * 获取用户Token和userId
     */
    public function actionUserInfo(){
        $res = WebUtils::initWebApiArray_oldVersion();
        $uname = $this->db->queryScalar('SELECT `username` FROM %t WHERE `uid`=%d',array('common_member',$this->uid));
        $uavatar = UserUtils::getUserAvatar($this->uid);
        $result = $this->server->getToken($this->uid,WebUtils::u($uname),$uavatar);
        $result = json_decode($result,true);
        if($result['code'] != 200){
            WebUtils::outputWebApi(WebUtils::makeErrorInfo_oldVersion($res,$result['errorMessage']));
        }
        $res['body']['token'] = $result['token'];
        $res['body']['appKey'] = $this->appKey;
        WebUtils::outputWebApi($res);

    }

}