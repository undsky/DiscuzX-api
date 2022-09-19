<?php
/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */


/**
 * Class TopicService
 * @property DiscuzDbUtils $db
 */
class TopicService {

    private $db;

    private $data;

    private $error;

    const  fid = 73;
    const username = 'admin';
    const uid = '1';


    public function __construct() {
        $this->db = DbUtils::createDbUtils(true);
        $this->data = WebUtils::tarr($_GET);
    }

    public function getList() {
        return $this->db->queryAll("SELECT f.`tid`,f.`subject`,f.`dateline`,f.`views`,ff.`message` FROM %t f,%t ff WHERE f.`displayorder`  = 3 AND ff.tid=f.tid AND ff.`first`=1 ", array('forum_thread','forum_post'));
    }

    public function count() {
        return $this->db->queryFirst("SELECT count(*) FROM %t WHERE `displayorder` IN ('3')", array('forum_thread'));
    }

    public function getTopicInfoByTid() {
        $tid = $this->data['tid'];
        if (empty($tid)) {
            return array();
        }
        return $this->db->queryRow("SELECT `tid`,`subject`,`dateline`,`views` FROM %t WHERE `tid`=%s AND `displayorder`=3", array('forum_thread', $tid));
    }

    public function updateThread($data) {
        $this->db->update('forum_thread',$data,array('tid'=>$this->data['tid']));
        $this->updateStickCache();
        return true;
    }


    public function updatePost($data){
        return $this->db->update('forum_post',$data,array('tid'=>$this->data['tid'],'first'=>1));
    }

    public function update(){
        $topicInfo = $this->getTopicInfoByTid();
        if(empty($topicInfo)){
            $this->error = '公告不存在,或者非公告!';
            return false;
        }
        $post = $thread = array();
        $threadarr = array('displayorder','subject');
        $postarr = array('message','invisible');
        foreach ($this->data as  $key=>$value){
            if(in_array($key,$threadarr)){
                $thread[$key] = $value;
            }
            if(in_array($key,$postarr)){
                $post[$key] = $value;
            }
        }
        $this->updateThread($thread);
        $this->updatePost($post);
        return true;
    }


    public function insert() {
        global $_G;
        if($this->count()>=5){
            $this->error = '只能设置5个置顶帖!';
            return false;
        }
        if(empty($this->data['subject'])||empty($this->data['message'])){
            $this->error = '参数不正确!';
            return false;
        }
        //获得TID
        $tid = $this->db->insert('forum_thread',array(
            'fid'=>self::fid,
            'author'=>self::username,
            'authorid'=>self::uid,
            'subject'=>$this->data['subject'],
            'dateline'=>time(),
            'lastpost'=>time(),
            'lastposter'=>self::username,
            'displayorder'=>3
        ),true);
        //获得pid
        $pid = $this->db->insert('forum_post_tableid',array('pid'=>''),true);
        $this->db->insert('forum_post',array(
            'pid' => $pid,
            'fid' => self::fid,
            'tid' => $tid,
            'first' => 1,
            'author'=>self::username,
            'authorid'=>self::uid,
            'subject' => $this->data['subject'],
            'message'=>$this->data['message'],
            'dateline' => time(),
            'useip'=>$_G['clientip'],
        ));
        $this->updateStickCache();
        return $tid;
    }

    /**
     * 更新全局缓存
     */
    private  function  updateStickCache(){
        require_once libfile('function/cache');
        updatecache('globalstick');
    }


    /**
     * @param $key string
     * @param $value array/string
     */
    public function setData($key,$value) {
        $this->data[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getError() {
        return $this->error;
    }

}