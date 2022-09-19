<?php
/**
 * 聊天室和console通信接口
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/15
 * Time: 14:20
 * @property ServerAPI $server
 */


//错误码  110 00000参数错误  110 00001还用户已进入聊天室  110 00002进入聊天室失败
//       110 00003容云错误  110 00004聊天室id不能为空    110 00005销毁失败!


class ChatController extends ApiController
{
    private $appSecret;
    private $appKey;
    private $server;

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        $cvalue   = $this->db->queryScalar('select cvalue from %t where ckey = %s', array('appbyme_config', 'RONGYUN_KEY'));
        $cvalue   = unserialize($cvalue);
        $this->appSecret = $cvalue[$this->data['forumKey']]['appSecret'];
        $this->appKey    = $cvalue[$this->data['forumKey']]['appKey'];
        $this->server    = new ServerAPI($this->appKey, $this->appSecret);
        return true;
    }

    /**
     * 保存融云信息
     * @param $appKey
     * @param $appSecret
     */
    public function actionSave()
    {
        $forumKey = $this->data['forumKey'];
        $data     = $this->db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s',array('appbyme_config','RONGYUN_KEY'));
        $data     = unserialize($data);
        $data[$forumKey]['appSecret'] = $this->data['appSecret'];
        $data[$forumKey]['appKey']    = $this->data['appKey'];
        $data                         = serialize($data);
        $this->db->insert('appbyme_config',array(
                'ckey' => 'RONGYUN_KEY',
                'cvalue' => $data
            ),
            false,
            true
        );
    }

    /**
     * 获得粉丝列表
     * @param $uid 用户id
     */
    public function actionUserList()
    {
        $outdata = array();
        $tmpdata = $this->db->queryAll('SELECT `uid` FROM %t WHERE `followuid`=%d LIMIT %d,%d',array(
            'home_follow',
            intval($this->data['uid']),
            intval(($this->data['page']-1)*$this->data['pageSize']),
            intval($this->data['pageSize'])
        ));
        foreach($tmpdata as $v)
        {
            $outdata[] = $v['uid'];
        }
        $this->setData(array('userlist' => $outdata));
    }

    //创建聊天室
    public function actionChatRoomCreate()
    {
        $result = $this->server->chatroomCreate(array($this->data['cid'] => $this->data['name']));
        $result = json_decode($result, true);
        if ($result['code'] != 200) {
            $this->error('11000003');
            //$result['errorMessage'];
        } else {
            $data['uid'] = $this->data['uid'];//用户ID
            $data['cid'] = $this->data['cid'];//聊天室ID
            $data['cname'] = $this->data['name'];//聊天室名称
            $data['ctime'] = time();//创建时间
            $this->db->insert('appbyme_chat', $data, true, true);
        }
    }

    //摧毁聊天室
    public function actionChatRoomDestroy()
    {
        if(!$this->data['cid']){
            $this->error('11000004');
        }else{
            $result = $this->server->chatroomDestroy($this->data['cid']);
            $result = json_decode($result, true);
            if ($result['code'] != 200) {
                $this->error('11000005');
            } else {
                $this->db->delete('appbyme_chat', array('cid' => $this->data['cid']));
                $this->db->delete('appbyme_chatuser', array('cid' => $this->data['cid']));
            }
        }
    }
    //加入聊天室
    public function actionInRoom()
    {
        if(!($this->data['uid'] && $this->data['cid'])) {
            $this->error('11000000');
        }
        $inroom = $this->db->queryScalar('SELECT COUNT(*) FROM %t WHERE `cid`=%d AND `uid` =%d',array('appbyme_chatuser', $this->data['cid'], $this->data['uid']));
        if(intval($inroom)) {
            $this->error('11000001');
        }
        $re = $this->db->insert('appbyme_chatuser',array(
            'uid' => intval($this->data['uid']),
            'uname' => strval($this->data['uname']),
            'uavatar' => strval($this->data['avatar']),
            'cid' => intval($this->data['cid'])
        ));
        if(!$re) {
            $this->error('11000002');
        }
    }
    //退出聊天室
    public function actionOutRoom()
    {
        if(!($this->data['uid'] && $this->data['cid'])) {
            $this->error('11000000');
        }
        $this->db->delete('appbyme_chatuser',array(
            'uid' => $this->data['uid'],
            'cid' => $this->data['cid']
        ));
    }
    protected function setRules()
    {
        return array(
            'save' => array(
                'appKey' => 'string',
                'appSecret' => 'string'
            ),
            'userlist' => array(
                'uid' => 'int'
            )
        );
    }
}