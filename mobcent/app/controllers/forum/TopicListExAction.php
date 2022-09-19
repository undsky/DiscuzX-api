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

/**
 * Class TopicListAction
 */
//Mobcent::setErrors();
/**
 * Class TopicListExAction
 */
class TopicListExAction extends MobcentAction
{

    /**
     * @var null
     */
    protected $forumInfo = null;

    /**
     * @param int $boardId 板块ID
     * @param int $page 页数
     * @param int $pageSize 当页数量
     * @param string $orderby 排序字段
     * @param int $sortid 分类主题ID
     * @param string $sorts 分类主题筛选
     * @param int $circle 圈子模式
     */
    public function run($boardId, $page = 1, $pageSize = 10, $orderby = 'all',  $sortid = '', $sorts = '', $circle = 0)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_topicList($res, $boardId, $page, $pageSize, $orderby, $sortid, $sorts, $circle);
        WebUtils::outputWebApi($res);
    }


    /**
     * @param array $res
     * @param int $fid
     * @param int $page
     * @param int $pageSize
     * @param string $orderby
     * @param int $sortid
     * @param json $sorts
     * @param int $circle
     * @return array
     */
    private function _topicList($res, $fid, $page, $pageSize, $orderby, $sortid, $sorts, $circle)
    {
        $sorts = WebUtils::tarr(WebUtils::jsonDecode($sorts));
        $typeid = $sorts['type'];
        unset($sorts['type']);
        if ($fid != 0)
        {

            ForumUtils::initForum($fid);
            global $_G;
            if (empty($_G['forum']['fid']))
            {
                return $this->_makeErrorInfo($res, 'forum_nonexistence');
            }
            if ($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm']) && !$_G['forum']['allowview'])
            {
                $msg = mobcent_showmessagenoperm('viewperm', $_G['fid'], $_G['forum']['formulaperm']);
                return $this->_makeErrorInfo($res, $msg['message'], $msg['params']);
            } elseif ($_G['forum']['formulaperm'])
            {
                $msg = mobcent_formulaperm($_G['forum']['formulaperm']);
                if ($msg['message'] != '')
                {
                    return $this->_makeErrorInfo($res, $msg['message'], $msg['params']);
                }
            }
            if ($_G['forum']['password'])
            {
                return $this->_makeErrorInfo($res, 'mobcent_forum_passwd');
            }
            if ($_G['forum']['price'] && !$_G['forum']['ismoderator'])
            {
                $membercredits = C::t('common_member_forum_buylog')->get_credits($_G['uid'], $_G['fid']);
                $paycredits = $_G['forum']['price'] - $membercredits;
                if ($paycredits > 0)
                {
                    if (getuserprofile('extcredits' . $_G['setting']['creditstransextra'][1]) < $paycredits)
                    {
                        return $this->makeErrorInfo($res, lang('message', 'forum_pay_incorrect', array('paycredits' => $paycredits, 'credits' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'] . $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'])));
                    } else
                    {
                        return $this->makeErrorInfo($res, 'forum_pay_incorrect_paying', array('{paycredits}' => $paycredits, '{credits}' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['unit'] . $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title'], 'title' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]]['title']));
                    }
                }
            }
            $this->forumInfo = C::t('forum_forum')->fetch_info_by_fid($fid);;
            //获得板块信息
            $res['forumInfo'] = $this->_getForumInfo($fid);
            $res['typeInfo'] = $this->_getForumTypeInfo();
            $res['sortInfo'] = $this->__getForumSortInfo();
        }

        //获得发帖面板
        $res['newTopicPanel'] = $this->_getNewTopicPanel();
        // 获取公告列表
        $isFirstPage = $fid != 0 && $page == 1;
        $res['anno_list'] = !$isFirstPage ? array() : ForumUtils::_getAnnouncementList('new');
        $thread = new ThreadUtils();
        $threadList =  $thread->getInfo($fid,$page,$pageSize,$orderby,$typeid,$sortid,$sorts);
        $res['list'] = array();
        foreach ($threadList['thread'] as $key=>$value){
            $res['list'][] = $thread->makeThreadInfo($value);
        }
        if($isFirstPage){
            $recommend = new RecommendUtils();
            $res['recommendList'] = $recommend->getInfo('forum',$fid);
        }
        $res['topTopicList'] = $thread->getTop($fid,$page,$pageSize,1);
        $res = array_merge($res, WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $threadList['count']));
        return $res;
    }

    /**
     * @param $fid
     * @return array
     */
    private function _getForumInfo($fid)
    {
        global $_G;
        //   $forum = $_G['forum'];
        $forum = $this->forumInfo;
        require_once libfile('function/forumlist');
        $forumImage = get_forumimg($forum['icon']);
        $forumImage = (string)WebUtils::getHttpFileName($forumImage);
        $forumInfo = array();
        $forumInfo['id'] = (int)$fid;
        $forumInfo['title'] = $fid != 0 ? (string)WebUtils::emptyHtml($forum['name']) : '';
        $forumInfo['description'] = (string)WebUtils::emptyHtml($forum['description']);
        $forumInfo['icon'] = (string)$forumImage;
        $forumInfo['td_posts_num'] = $forum['todayposts'];
        $forumInfo['topic_total_num'] = $forum['threads'];
        $forumInfo['posts_total_num'] = $forum['posts'];
        $forumInfo['is_focus'] = (int)ForumUtils::getFavStatus($_G['uid'], $fid);
        return $forumInfo;
    }

    /**
     * @return array
     */
    private function _getForumTypeInfo()
    {
        $type = unserialize($this->forumInfo['threadtypes']);
        foreach ($type['types'] as $key => $value)
        {
            $return[]=array(
                'id'=>$key,
                'name'=>WebUtils::emptyHtml($value),
            );
        }
        return $return;
    }


    /**
     *
     */
    protected function __getForumSortInfo()
    {
        global $_G;
        $sort = unserialize($this->forumInfo['threadsorts']);
        //处理分类信息

        require_once libfile('function/threadsort');
        $templatearray = $sortoptionarray = array();
        $sortInfoTemp[] =  array(
            'title'=>WebUtils::t('主题分类'),
            'type'=>'type',
            'id'=>'type',
            'choices'=>$this->_getForumTypeInfo(),
        );
        $sortoptionarray[] = array(
            'name'=>WebUtils::t('全部'),
            'id'=>0,
            'list'=>$sortInfoTemp,
        );
        foreach ($sort['types'] as $stid => $sortname)
        {
            loadcache(array('threadsort_option_' . $stid, 'threadsort_template_' . $stid));
            sortthreadsortselectoption($stid);
            $sortInfo['list'] = $this->__getSortValue($_G['cache']['threadsort_option_' . $stid]);
            $sortInfo['name'] = $sortname;
            $sortInfo['id'] = intval($stid);
            $sortoptionarray[] = $sortInfo;

        }

        return $sortoptionarray;
    }

    /**
     * @param array $array
     */
    protected function __getSortValue(array $array)
    {
        $return[] = array(
            'title'=>WebUtils::t('主题分类'),
            'type'=>'type',
            'id'=>'type',
            'choices'=>$this->_getForumTypeInfo(),
        );
        foreach ($array as $key => $value)
        {
            $result = array();
            if ($value['search'] == 0||$value['type']=='image')
                continue;
            $result['title'] = $value['title'];
            $result['type'] = $value['type'];
            $result['identifier'] = $value['identifier'];
            $result['id'] = strval($key);
            switch ($result['type'])
            {
                case 'range':
                    $result['choices'] =  $value['searchtxt'];
                    break;
                case 'checkbox':
//                    $result['choices'] = $value['choices'];
//                    break;
                case 'radio':
                    $result['choices'] = $this->_getSore($value['choices']);
                    break;
                case 'select':
                    $result['choices'] = $this->__getSoreSelect($value['choices']);
                    break;
            }
            if(in_array($result['type'],array('image','calendar'))){
                continue;
            }
            $return[] = $result;
        }
        return $return;
    }

    private function _getSore(array $choices){
        foreach ($choices as $key=>$value){
            $return[]=array(
                'id'=>$key,
                'name'=>$value,
            );
        }
        return $return;
    }
    private function __getSoreSelect(array $choices)
    {

        return $this->generateTree($choices);
    }
    function generateTree($items){
        $tree = array();
        foreach($items as $key=>$item){
            unset($items[$key]['count']);
            unset($items[$key]['level']);
            $items[$key]['name']=$items[$key]['content'];
            $items[$key]['optionid']=strval($items[$key]['optionid']);
            unset($items[$key]['content']);
            if(isset($items[$item['foptionid']])){
                $items[$item['foptionid']]['sub'][] = &$items[$item['optionid']];
            }else{
                $tree[] = &$items[$item['optionid']];
            }
        }
        return $tree;
    }

    private function _makeErrorInfo($res, $message, $params = array())
    {
        $res = WebUtils::makeErrorInfo_oldVersion($res, $message, $params);
        WebUtils::outputWebApi($res);
    }

    /**
     * 获得发帖面板
     * @return array
     */
    private function _getNewTopicPanel() {
        return ForumUtils::getNewTopicPanel();
    }



}