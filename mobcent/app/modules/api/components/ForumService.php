<?php

/**
 * Created by PhpStorm.
 * User: congjie
 * Date: 16/7/18
 * Time: 下午3:40
 */
class ForumService
{
    private $uid;
    private $username;
    private $displayorder;
    private $fid;
    private $hash;
    private $error;
    private $MyRepeatsUid;
    public $type;

    public function __construct($data) {
        $this->db           = DbUtils::createDbUtils(true);
        $this->data         = $data;
        $this->uid          = $this->data['uid'];
        $this->displayorder = isset($this->data['displayorder']) ? $this->data['displayorder'] :0;
        $this->fid          = $this->data['fid'];
        $this->hash         = $this->data['hash'];
        $this->MyRepeatsUid = 1; //默认的导贴用户马甲id是管理员id
        $this->type         = isset($this->data['type']) ? $this->data['type'] : 0;
        $this->setUsername();
    }

    public function setUsername()
    {
        $this->username = $this->db->queryFirst('SELECT username FROM %t WHERE uid = %d',array('appbyme_special_users', $this->uid));
    }
    /**
     * @param $ids = [1, 2,h19]
     */
    protected function setUid($ids)
    {
        $this->uid = array_rand($ids, 1);
    }

    public function insertForum()
    {
        //引入html2bbcode
        require_once libfile('function/editor');
        include_once libfile('function/profile');

        if(empty($this->data['subject'])||empty($this->data['message'])){
            return $this->setError ('参数不正确!');
        }
        if($this->HashCheck()) {
            return $this->setError('贴子已存在');
        }
        $this->data['message'] = urldecode($this->data['message']);
        $this->data['message'] = html2bbcode($this->data['message']);
        $this->data['message'] = $this->sizeCharts($this->data['message']);
        $this->data['message'] = $this->sizeFloat($this->data['message']);
        $this->data['message'] = str_replace("amp;","",$this->data['message']);
        //获得TID
        $tid = $this->db->insert('forum_thread',array(
            'fid'=>$this->fid,
            'author'=>$this->username,
            'authorid'=>$this->uid,
            'subject'=>$this->data['subject'],
            'dateline'=>time(),
            'lastpost'=>time(),
            'lastposter'=>$this->username,
            'displayorder'=>$this->displayorder  // 3全局置顶,0普通,-1删除,1当前置顶,2分区置顶
        ),true);
        //获得pid
        $pid = $this->db->insert('forum_post_tableid',array('pid'=>''),true);
        global $_G;

        $this->db->insert('forum_post',array(
            'pid' => $pid,
            'fid' => $this->fid,
            'tid' => $tid,
            'first' => 1,
            'htmlon' => 1,//启用HTML
            'usesig' => 1,//启用签名
            'smileyoff' => -1,
            'author'=>$this->username,
            'authorid'=>$this->uid,
            'subject' => $this->data['subject'],
            'message'=>$this->data['message'],
            'dateline' => time(),
            'useip'=>$_G['clientip'],
        ));
        $this->updateStickCache();
        return $tid;
    }

    /**
     * 更新全局缓存,app缓存
     */
    private function updateStickCache(){
        require_once libfile('function/cache');
        updatecache('globalstick');
        $this->updateForum();
        Yii::app()->cache->flush();
    }


    private function updateForum()
    {
        //今日发帖数加一
        $plus = 1;
        DB::query("UPDATE ".DB::table("forum_forum")." SET todayposts=todayposts+".intval($plus). " WHERE fid=".$this->fid, 'UNBUFFERED');
        DB::query("UPDATE ".DB::table("forum_forum")." SET threads=threads+".intval($plus). " WHERE fid=".$this->fid, 'UNBUFFERED');
        DB::query("UPDATE ".DB::table("forum_forum")." SET posts=posts+".intval($plus). " WHERE fid=".$this->fid, 'UNBUFFERED');
    }

    public function getForumList()
    {
        global $_G;
        loadcache('forums');
        $forums = array();
        $forumCache = $_G['cache']['forums'];
        foreach ($_G['cache']['forums'] as $fid => $forum) {
            if ($forum['type'] == 'group') {
                $forums[$fid]['id'] = $fid;
                $forums[$fid]['name'] = $forum['name'];
            } elseif ($forum['type'] == 'forum') {
                $forums[$forum['fup']]['sub'][$fid]['id'] = $fid;
                $forums[$forum['fup']]['sub'][$fid]['name'] = $forum['name'];
            } elseif ($forum['type'] == 'sub') {
                $subForum = $forumCache[$forum['fup']];
                $forums[$subForum['fup']]['sub'][$forum['fup']]['sub'][$fid]['id'] = $fid;
                $forums[$subForum['fup']]['sub'][$forum['fup']]['sub'][$fid]['name'] = $forum['name'];
            }
        }
        return $forums;
    }

    protected function getForumFids()
    {
        $fids = array();
        $ids = $this->db->queryAll('SELECT `fid` FROM %t WHERE `status` = %d',array('forum_forum', 1));
        foreach($ids as $v) {
            $fids[] = $v['fid'];
        }
        return $fids;
    }

    public function addUser()
    {
        global $_G;
        loadcache('plugin');
        if(empty($_G['cache']['plugin']['myrepeats'])) {
            return $this->setError('未开启马甲功能,无法导入用户');
        }
        $loginData = addslashes(authcode($this->data['password']."\t".'0'."\t".'', 'ENCODE', $_G['config']['security']['authkey']));
        $this->username = $this->data['username'];
        $password = $this->data['password'];
        $email = rawurldecode($this->data['email']);
        $regInfo = UserUtils::register($this->username, $password, $email, 'general', 1);
        if ($regInfo['errcode']) {
            return $this->setError($regInfo['message']);
        }
        $this->uid = $regInfo['info']['uid'];
        /*
         *马甲表插入
         */
        $this->addMyRepeats($loginData);
        $res = $this->addSpecial();
        if($res) {
            $this->setUsername();
        }
        return $res;
    }

    public function addLiveUser()
    {
        $this->username = $this->data['username'];
        $this->username = WebUtils::t(urldecode($_GET['username']));
        $password = isset($this->data['password']) ? $this->data['password'] : '@W'.md5(random(5));
        $email = rawurldecode($this->data['email']);
        $regInfo = UserUtils::register($this->username, $password, $email, 'general', 1);
        if ($regInfo['errcode']) {
            return $this->setError($regInfo['message']);
        }
        $this->uid = $regInfo['info']['uid'];
        $res = $this->addSpecial();
        return $res;
    }

    private function addSpecial()
    {
       return $this->db->insert(
            'appbyme_special_users',
            array(
                'uid'      => $this->uid,
                'username' => $this->username,
                'time'     => time(),
                'type'     => $this->type
            ));
    }

    private function addMyRepeats($loginData)
    {
        return $this->db->insert(
            'myrepeats',
            array(
                'uid'       => $this->MyRepeatsUid,
                'username'  => $this->username,
                'logindata' => $loginData
            ),false,true);
    }
    private function setError($error)
    {
        $this->error = $error;
        return false;
    }
    public function getError()
    {
        return $this->error;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getUserInfo()
    {
        if($this->type) {
            return array('avatar' =>$this->getAvatar(), 'username' => $this->getUsername(), 'uid' => $this->getUid());
        } else {
            return array('username' => $this->getUsername(), 'uid' => $this->getUid());
        }
    }
    private function getAvatar()
    {
        global $_G;
        $_G['siteurl'] = substr($_G['siteurl'], 0, -16);
        return UserUtils::getUserAvatar($this->uid);
    }

    public function UserList()
    {
        return $this->db->queryAll('SELECT `uid`,`username` FROM %t WHERE `type` = %d',array('appbyme_special_users', $this->type));
    }

    protected function HashCheck()
    {
        if(!isset($this->data['hash'])) {
            return false;
        }
        $res = $this->db->queryFirst('SELECT `text` FROM %t WHERE `text` = %s',array('appbyme_hash', $this->data['hash']));
        if($res) {
            return true;
        }
        $this->db->insert('appbyme_hash',array('text' => $this->data['hash']));
        return false;
    }

    //过滤size标签等于em的标签
    protected function sizeCharts($message)
    {
        return preg_replace_callback('/\[size=((\d+(\.\d+)?)(in|cm|mm|pc|em|ex|%)+?)\]((.|\s)*)/iU',
            create_function('$matches','
            {
                $size = "16px";
                switch($matches[4])
                {
                    case "em":
                        $size = intval($matches[2]*16)."px";
                        break;
                    case "cm":
                        break;
                    default:
                        $size = "16px";
                }
                return "[size=$size]$matches[5][/size]";
            },
            '),$message
        );
    }
    //过滤size小数
    protected function sizeFloat($message)
    {
        return preg_replace_callback(
            '/\[size=((\d+(\.\d+)?)(px)+?)\]((.|\s)*)/iU', create_function('$matches','
            {
                $size = intval($matches[2])."px";
                return "[size=$size]$matches[5][/size]";
            },
            '),$message
        );
    }
}




