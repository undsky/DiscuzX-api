<?php

/**
 *
 * 粉丝相关的帖子列表
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
class FollowListAction extends MobcentAction
{

    protected $db;
    protected $userId = '';
    protected $userArray = array();
    protected $forumId = '';
    protected $forumArray = array();
    protected $threadId = '';
    protected $threadArray = array();
    protected $friendId = '';
    protected $friendArray = array();
    protected $uid = array();

    public function run($page = 1, $pageSize = 20, $orderBy = 'lastpost')
    {
        global $_G;
        $this->uid = $_G['uid'];
        $this->db  = DbUtils::createDbUtils(true);
        $res       = WebUtils::initWebApiArray_oldVersion();
        $res       = $this->_getFollowList($res, $page, $pageSize, $orderBy);
        WebUtils::outputWebApi($res);
    }

    protected function _getFollowList($res, $page, $pageSize, $orderBy)
    {
        $this->topic();
        $this->user();
        $this->forum();
        $this->friend();
        //组sql
        $addsql = '';
        if ($this->threadId)
        {
            $addsql .= ' tid IN (' . $this->threadId . ') OR';
        }
        if ($this->userId)
        {
            $addsql .= '  authorid IN (' . $this->userId . ') OR';
        }
        if ($this->forumId)
        {
            $addsql .= '  fid IN (' . $this->forumId . ') OR';
        }
        if ($this->friendId)
        {
            $addsql .= '  authorid IN (' . $this->friendId . ') OR';
        }
        if (empty($addsql))
        {
            return $res;
        }
        $addsql = rtrim($addsql, "OR");
        $sql = "`author`!='' AND `displayorder`>=0 AND (".$addsql.")";
        //增加排序
        if ($orderBy == 'lastpost')
        {
            $order = ' ORDER BY `lastpost` DESC';
        } else
        {
            $order = ' ORDER BY `dateline` DESC';
        }
        $thread     = new ThreadUtils();
        $threadList = $this->db->queryAll(
            'SELECT * FROM %t WHERE ' . $sql . $order . ' LIMIT ' . intval(($page - 1) * $pageSize) . ',' . $pageSize
            , array('forum_thread'));
        foreach ($threadList as $item)
        {

            $res['list'][] = array_merge($thread->makeThreadInfo($item), array('from' => $this->from($item)));
        }
        $count = $this->db->queryFirst("SELECT COUNT(*) FROM %t WHERE " . $sql, array('forum_thread'));
        $res   = array_merge($res, WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count));
        return $res;
    }

    protected function topic()
    {
        //先查出用户关注话题
        $topicList = $this->db->queryFirst("SELECT group_concat(ti_id) from %t WHERE `uid`=%d ", array('appbyme_tpctou', $this->uid));
        //获得帖子ID
        if ($topicList)
        {
            $this->threadId    = $this->db->queryFirst("SELECT group_concat(pid) from %t WHERE `ti_id`  IN (%n) ", array('appbyme_tpctopost', $topicList));
            $this->threadArray = explode(',', $this->threadId);
        }
    }

    protected function user()
    {
        $this->userId    = $this->db->queryFirst("SELECT group_concat(followuid) from %t WHERE `uid`=%s ", array('home_follow', $this->uid));
        $this->userArray = explode(',', $this->userId);
    }

    protected function forum()
    {
        $this->forumId    = $this->db->queryFirst("SELECT group_concat(id) from %t WHERE `idtype`=%s AND `uid`=%s ", array('home_favorite', 'fid', $this->uid));
        $this->forumArray = explode(',', $this->forumId);
    }

    protected function friend()
    {
        $this->friendId    = $this->db->queryFirst("SELECT group_concat(fuid) from %t WHERE  `uid`=%s ", array('home_friend', $this->uid));
        $this->friendArray = explode(',', $this->friendId);
    }

    protected function from($thread)
    {
        //好友
        if (in_array($thread['authorid'], $this->friendArray))
        {
            return "FRIEND";
        }
        //关注的人
        if (in_array($thread['authorid'], $this->userArray))
        {
            return 'PEOPLE';
        }
        //关注的话题
        if (in_array($thread['tid'], $this->threadArray))
        {
            return 'TALK';
        }
        return 'BOARD';
    }
}