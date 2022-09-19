<?php

/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME'))
{
    exit('Access Denied');
}

class ThreadUtils
{

    //    public function run($boardId, $page = 1, $pageSize = 10, $orderby = '', $order = '', $types = '', $sorts = '', $circle = 0)


    /**
     * @param int $fid 板块ID
     * @param int $page 页码
     * @param int $pageSize 每页大小
     * @param string $orderby 排序规则
     * @param int $typeid 主题分类ID
     * @param int $sortid 分类主题ID
     * @param array $sorts 分类信息筛选ID
     * @return array
     */
    public function getInfo($fid, $page, $pageSize, $orderby, $typeid, $sortid, $sorts)
    {
        global $_G;
        //排序

        $ascdesc = isset($_G['cache']['forums'][$_G['fid']]['ascdesc']) ? $_G['cache']['forums'][$_G['fid']]['ascdesc'] : 'DESC';
        $filterarr['sticky'] = 4;
        $filterarr['displayorder'] = array(0);
        //板块
        if (!empty($fid))
        {
            $filterarr['inforum'] = $fid;
        }else{
            $filterarr['inforum'] = ForumUtils::getForumShowFids();
        }
        //主题分类
        if (!empty($typeid))
            $filterarr['intype'] = $typeid;
        //分类主题
        if (!empty($sortid))
            $filterarr['insort'] = $sortid;
        //主题分类筛选
        if (!empty($sorts))
        {
            $filterarr['intids'] = $this->makeSortSearch($sortid, $sorts, $fid);
        }
        $start = ($page - 1) * $pageSize;
        $_order = "displayorder DESC";
        switch ($orderby)
        {
            case 'new':
                $_order .= ' , dateline ' . $ascdesc;
                break;
            case 'top':
                return array('thread' => ForumUtils::getTopicList($fid,$page,$pageSize,array('sort'=>'top')), 'count' => ForumUtils::getTopicCount($fid,array('sort'=>'top')));
                break;
            case 'marrow':
                $filterarr['digest'] = 1;
                $_order .= ' , lastpost ' . $ascdesc;
                break;
            default:
                $_order .= ' , lastpost ' . $ascdesc;
                break;
        }
        $threadlist = C::t('forum_thread')->fetch_all_search($filterarr, 0, $start, $pageSize, $_order, '');

        $filterarr['sticky'] = 0;
        $count = C::t('forum_thread')->count_search($filterarr, 0);
        return array('thread' => $threadlist, 'count' => $count);
    }


    protected function makeSortSearch($sortid, $sorts, $fid)
    {
        global $_G;
        loadcache(array('threadsort_option_' . $sortid, 'threadsort_template_' . $sortid));
        sortthreadsortselectoption($sortid);
        $_G['forum_optionlist'] = $sortoption = $_G['cache']['threadsort_option_' . $sortid];
        require_once libfile('function/threadsort');
        foreach ($sorts as $key =>$value){
            $sort[$key]=array(
                'value'=>WebUtils::t($value),
                'type'=>$sortoption[$key]['type']
            );
//            $sortAdd[$sortoption[$key]['identifier']] = WebUtils::t($value);
        }
//        debug($sortoption);
        $searchsorttids = sortsearch($sortid, array($sortid=>$sortoption), $sort, array(), $fid);
//        debug($searchsorttids);
        return $searchsorttids ? $searchsorttids : array(0);;
    }

    public function makeThreadInfo($topic)
    {
        global $_G;
        $forum = $_G['forum'];

        $isTopicMoved = false;
        $movedTitle = '';
        if ($topic['closed'] > 1)
        {
            $movedTitle = WebUtils::t('移动: ');
            $isTopicMoved = true;
            $topic['tid'] = $topic['closed'];
        }

        $tid = (int)$topic['tid'];
        $topicFid = (int)$topic['fid'];

        // 主题分类标题
        $typeTitle = '';
        if (WebUtils::getDzPluginAppbymeAppConfig('forum_allow_topictype_prefix'))
        {
            if (isset($forum['threadtypes']['prefix']) &&
                $forum['threadtypes']['prefix'] == 1 &&
                isset($forum['threadtypes']['types'][$topic['typeid']])
            )
            {
                $typeTitle = '[' . $forum['threadtypes']['types'][$topic['typeid']] . ']';
            }
        }
        // 分类信息标题
        $sortTitle = '';
        if (WebUtils::getDzPluginAppbymeAppConfig('forum_allow_topicsort_prefix'))
        {
            if (!empty($forum['threadsorts']['prefix']) &&
                isset($forum['threadsorts']['types'][$topic['sortid']])
            )
            {
                $sortTitle = '[' . $forum['threadsorts']['types'][$topic['sortid']] . ']';
            }
        }
        $isTopicMoved && $typeTitle = $sortTitle = '';
        $topicInfo = array();
        $topicInfo['board_id'] = $topicFid;
        $topicInfo['board_name'] = ForumUtils::getForumName($topicFid);
        $topicInfo['board_name'] = WebUtils::emptyHtml($topicInfo['board_name']);

        $topicInfo['topic_id'] = $tid;
        $topicInfo['type']     = ForumUtils::getTopicType($topic);
        $topicInfo['title']    = $movedTitle . $typeTitle . $sortTitle . $topic['subject'];
        $topicInfo['title']    = WebUtils::emptyHtml($topicInfo['title']);
        $isTopic = ForumUtils::TopicPost($tid);
        $topicInfo['isTopic']  = empty($isTopic) ? 0 : 1;
        // 修正帖子查看数
        if (isset($_G['forum_thread']['views']) &&
            $_G['forum_thread']['tid'] == $topic['tid'] &&
            $_G['forum_thread']['views'] > $topic['views']
        )
        {
            $topic['views'] = $_G['forum_thread']['views'];
        }

        if ($topic['author'] == NULL)
        {  //Fix 匿名显示头像和可点击 ByNaiXiaoXin Data 20150910
            global $_G;
            $topicInfo['user_id'] = (int)'0';
            $topicInfo['user_nick_name'] = $_G['setting']['anonymoustext'];
            $topicInfo['userAvatar'] = UserUtils::getUserAvatar('0');
        } else
        {
            $topicInfo['user_id'] = (int)$topic['authorid'];
            $topicInfo['user_nick_name'] = $topic['author'];
            $topicInfo['userAvatar'] = UserUtils::getUserAvatar($topic['authorid']);
        }

        $topicInfo['last_reply_date'] = $topic['lastpost'] . '000';
        $topicInfo['vote'] = ForumUtils::isVoteTopic($topic) ? 1 : 0;
        $topicInfo['hot'] = ForumUtils::isHotTopic($topic) ? 1 : 0;
        $topicInfo['hits'] = (int)$topic['views'];
        $topicInfo['replies'] = (int)$topic['replies'];
        $topicInfo['essence'] = ForumUtils::isMarrowTopic($topic) ? 1 : 0;
        $topicInfo['top'] = ForumUtils::isTopTopic($topic) ? 1 : 0;
        $topicInfo['status'] = (int)$topic['status'];
        $cache = Yii::app()->params['mobcent']['cache']['topicSummary'];
        $key = sprintf('mobcentTopicSummary_%s_%s_%s', $tid, $_G['groupid'], 1);
        if (!$cache['enable'] || ($topicSummary = Yii::app()->cache->get($key)) === false)
        {
            $topicSummary = ForumUtils::getTopicSummary($tid, 'forum', true, array('imageList' => 1, 'imageListLen' => 9, 'imageListThumb' => 1));
            if ($cache['enable'])
            {
                Yii::app()->cache->set($key, $topicSummary, $cache['expire']);
            }
        }
        $topicInfo['subject'] = $topicSummary['msg'];

        $tempTopicInfo = ImageUtils::getThumbImageEx($topicSummary['image'], 15, true, false);
        $topicInfo['pic_path'] = $tempTopicInfo['image'];
        $topicInfo['ratio'] = $tempTopicInfo['ratio'];
        $topicInfo['gender'] = (int)UserUtils::getUserGender($topic['authorid']);
        $topicInfo['userTitle'] = UserUtils::getUserTitle($topicInfo['user_id']);
        $topicInfo['recommendAdd'] = (int)ForumUtils::getRecommendAdd($tid);
        $topicInfo['special'] = (int)$topic['special'];
        $topicInfo['isHasRecommendAdd'] = ForumUtils::isHasRecommendAdd($tid);
        $topicInfo['imageList'] = (array)$topicSummary['imageList'];
        $topicInfo['sourceWebUrl'] = (string)ForumUtils::getSourceWebUrl($tid, 'topic');
        $topicInfo['videoList'] = $topicSummary['videoList'];

        $topicInfo['verify'] = UserUtils::getVerify($topicInfo['user_id']);
        if ($_GET['circle'] == '1')
        {
            $topicInfo['zanList'] = (array)DzForumThread::getZanList($tid, '20', '1');
            $reply = array();
            $reply = DzForumThread::Reply($tid);
            if (!empty($reply))
            {
                $topicInfo['reply'] = $reply;
            }
        }
        return $topicInfo;
    }


    public function getTop($fid, $page, $pageSize,$topOrder)
    {
        global $_G;
        $topTopicList = array();
        if ($page == 1 && $topOrder != 0) {
            $status = $_GET['topOrder'];
            $fids = array();
            if($_G['forum']['allowglobalstick']!=1){
                $status = 4;
            }
            if ($status != '1' && !$_G['forum']['allowglobalstick'] ) {
                switch ($status) {
                    case DzForumThread::DISPLAY_ORDER_GLOBAL :
                        $fids = ForumUtils::getForumShowFids();
                        break;
                    case DzForumThread::DISPLAY_ORDER_GROUP :
                        $fids = DzForumForum::getFidsByGid(DzForumForum::getGidByFid($fid));
                        break;
                    case 4:
                        $fids = array($fid);
                        $status = DzForumThread::DISPLAY_ORDER_FORUM;
                        break;
                    default:
                        break;
                }
                $topTopicListTmp = DzForumThread::getByFidData($fids, 0, 4, array('topic_stick' => array($status)));
            } else {
                $topTopicListTmp =  ForumUtils::getTopicList($fid, $page, $pageSize, array('sort'=>'top'));

            }

            foreach ($topTopicListTmp as $top) {
                $topList['id'] = (int)$top['tid'];
                $topList['title'] = (string)$top['subject'];
                $topTopicList[] = $topList;
            }
        }
        return $topTopicList;
    }
}