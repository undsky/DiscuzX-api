<?php
/**
 * 话题详情
 * @author tanguanghua <18725648509@163.com>
 **/
class TopicdtlAction extends MobcentAction{
    public function run($ti_id=0,$tid=0,$ti_tile='',$page=1,$pageSize=10,$orderby='NEW'){
        $orderarr = array(
            'NEW' => ' ORDER BY tt.tpid DESC ',
            'HOT' => ' ORDER BY ft.heats DESC '
        );
        $str = (abs($page)-1)*$pageSize;
        if(!$ti_id && !$tid&&!$ti_tile){
            WebUtils::endAppWithErrorInfo(array(),'mobcent_error_params');
        }
        $db = DbUtils::createDbUtils(true);
        $res = WebUtils::initWebApiArray_oldVersion();
        if(!$ti_id&&$tid){
            $topicInfoByTid = $db->queryRow("SELECT * FROM %t WHERE `pid`=%d",array('appbyme_tpctopost',$tid));
            if(empty($topicInfoByTid)){
                WebUtils::endAppWithErrorInfo(array(),'mobcent_error_topic_no');
            }
            $ti_id = $topicInfoByTid['ti_id'];
        }
        if(!$ti_id&&$ti_tile){
            $topicInfoByName = $db->queryRow("SELECT * FROM %t WHERE `ti_title`=%s",array('appbyme_topic_items',WebUtils::t($ti_tile)));
            if(empty($topicInfoByName)){
                WebUtils::endAppWithErrorInfo(array(),'mobcent_error_topic_no_tag');
            }
            $ti_id = $topicInfoByName['ti_id'];
        }
        $topicList = $this->_getTopiclist($ti_id,$orderarr[$orderby],$str,$pageSize);
        $count = $db->queryRow('SELECT COUNT(0) FROM %t WHERE `ti_id`=%d',array('appbyme_tpctopost',$ti_id));
        if($page == 1){
            $finfo = $db->queryRow('SELECT ff.name,ff.fid FROM %t ac LEFT JOIN %t ff ON ff.fid=ac.cvalue WHERE ac.ckey=%s',array('appbyme_config','forum_forum','topic_bind_fid'));
            $topicinfo = $db->queryRow('SELECT * FROM %t WHERE `ti_id`=%d LIMIT 1',array('appbyme_topic_items',$ti_id));
            $tmpcount = DbUtils::createDbUtils(true)->queryRow('SELECT SUM(ft.replies),COUNT(0) FROM %t ft LEFT JOIN %t tt ON tt.`pid`=ft.`tid` WHERE tt.`ti_id`=%d',array('forum_thread','appbyme_tpctopost',$ti_id));
            $res['tpcinfo'] = $this->_dealTpcinfo($topicinfo,$finfo);
            $res['topUser'] = $this->_getTopuser($ti_id);
            $res['partinNum'] = $tmpcount['SUM(ft.replies)'] + $tmpcount['COUNT(0)'];
        }
        $res['list'] = $this->_getListField($topicList);
        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count['COUNT(0)'],$res);
        WebUtils::outputWebApi($res);
    }
    private function _getListField($topicList) {
        global $_G;
        $forum = $_G['forum'];

        $isImageList = isset($_GET['isImageList']) ? $_GET['isImageList'] : 0;

        $list = array();
        foreach ($topicList as $topic) {
            $isTopicMoved = false;
            $movedTitle = '';
            if ($topic['closed'] > 1) {
                $movedTitle = WebUtils::t('移动: ');
                $isTopicMoved = true;
                $topic['tid'] = $topic['closed'];
            }

            $tid = (int)$topic['tid'];
            $topicFid = (int)$topic['fid'];
            $typeTitle = '';
            if (WebUtils::getDzPluginAppbymeAppConfig('forum_allow_topictype_prefix')) {
                if (isset($forum['threadtypes']['prefix']) &&
                    $forum['threadtypes']['prefix'] == 1 &&
                    isset($forum['threadtypes']['types'][$topic['typeid']])
                ) {
                    $typeTitle = '[' . $forum['threadtypes']['types'][$topic['typeid']] . ']';
                }
            }
            $sortTitle = '';
            if (WebUtils::getDzPluginAppbymeAppConfig('forum_allow_topicsort_prefix')) {
                if (!empty($forum['threadsorts']['prefix']) &&
                    isset($forum['threadsorts']['types'][$topic['sortid']])
                ) {
                    $sortTitle = '[' . $forum['threadsorts']['types'][$topic['sortid']] . ']';
                }
            }
            $isTopicMoved && $typeTitle = $sortTitle = '';
            $topicInfo = array();
            $topicInfo['board_id'] = $topicFid;
            $topicInfo['board_name'] = ForumUtils::getForumName($topicFid);
            $topicInfo['board_name'] = WebUtils::emptyHtml($topicInfo['board_name']);

            $topicInfo['topic_id'] = $tid;
            $topicInfo['type'] = ForumUtils::getTopicType($topic);
            $topicInfo['title'] = $movedTitle . $typeTitle . $sortTitle . $topic['subject'];
            $topicInfo['title'] = WebUtils::emptyHtml($topicInfo['title']);
            if (isset($_G['forum_thread']['views']) &&
                $_G['forum_thread']['tid'] == $topic['tid'] &&
                $_G['forum_thread']['views'] > $topic['views']
            ) {
                $topic['views'] = $_G['forum_thread']['views'];
            }

            if ($topic['author'] == NULL) {
                global $_G;
                $topicInfo['user_id'] = (int)'0';
                $topicInfo['user_nick_name'] = $_G['setting']['anonymoustext'];
                $topicInfo['userAvatar'] = UserUtils::getUserAvatar('0');
            } else {
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
            $key = sprintf('mobcentTopicSummary_%s_%s_%s', $tid, $_G['groupid'], $isImageList);
            if (!$cache['enable'] || ($topicSummary = Yii::app()->cache->get($key)) === false) {
                $topicSummary = ForumUtils::getTopicSummary($tid, 'forum', true, array('imageList' => $isImageList, 'imageListLen' => 9, 'imageListThumb' => 1));
                if ($cache['enable']) {
                    Yii::app()->cache->set($key, $topicSummary, $cache['expire']);
                }
            }
            $topicInfo['subject'] = $topicSummary['msg'];
            $tempTopicInfo = ImageUtils::getThumbImageEx($topicSummary['image'], 15, true, false);
            $topicInfo['pic_path'] = $tempTopicInfo['image'];
            $topicInfo['ratio'] = $tempTopicInfo['ratio'];
            $topicInfo['gender'] = (int)UserUtils::getUserGender($topic['authorid']);
            $topicInfo['recommendAdd'] = (int)ForumUtils::getRecommendAdd($tid);
            $topicInfo['special'] = (int)$topic['special'];
            $topicInfo['isHasRecommendAdd'] = ForumUtils::isHasRecommendAdd($tid);
            $topicInfo['imageList'] = (array)$topicSummary['imageList'];
            $topicInfo['sourceWebUrl'] = (string)ForumUtils::getSourceWebUrl($tid, 'topic');
            $topicInfo['videoList'] = $topicSummary['videoList'];

            $topicInfo['verify'] = UserUtils::getVerify($topicInfo['user_id']);
            if ($_GET['circle'] == '1') {
                $topicInfo['zanList'] = (array)DzForumThread::getZanList($tid, '20', '1');
                $reply = DzForumThread::Reply($tid);
                if (!empty($reply)) {
                    $topicInfo['reply'] = $reply;
                }
            }
            $list[] = $topicInfo;
        }
        return $list;
    }

    /**
     * @param $tid 话题id
     * @param $uid 关注人id
     */
    private function _isCare($tid,$uid){
        $count = DbUtils::createDbUtils(true)->queryRow("SELECT COUNT(0) FROM %t WHERE `ti_id`=%d AND `uid`=%d",array('appbyme_tpctou',$tid,$uid));
        return $count['COUNT(0)'];
    }

    /**
     * @param array $data 处理的数据
     */
    private function _dealTpcinfo($topicinfo,$finfo){
        $uid = $this->getController()->uid;
        $topicinfo['ti_starttime'] = $topicinfo['ti_starttime'].'000';
        $topicinfo['ti_endtime'] = $topicinfo['ti_endtime'].'000';
        if($topicinfo['ti_cover']){
            if((stripos($topicinfo['ti_cover'], 'http') === false) && (stripos($topicinfo['ti_cover'], 'https') === false) ) {
                $forum                 = $topicinfo['ti_remote'] ? '/' : '/forum/';
                $topicinfo['ti_cover'] = ImageUtils::getAttachUrl($topicinfo['ti_remote']) . $forum . $topicinfo['ti_cover'];
            }
        }
        $topicinfo['icon'] = UserUtils::getUserAvatar($topicinfo['ti_authorid']);
        $topicinfo['fid'] = $topicinfo['ti_fid'] ? $topicinfo['ti_fid'] : $finfo['fid'];
        $topicinfo['fname'] = ForumUtils::getForumName($topicinfo['fid']);
        $topicinfo['iscare'] = $uid ? $this->_isCare($topicinfo['ti_id'],$uid) : 0;
        $topicinfo['ti_fid'] = $topicinfo['ti_fid'] ? $topicinfo['ti_fid'] : AppbymeConfig::getCvalueData('topic_bind_fid');
        return $topicinfo;
    }

    /**
     * 获得十个比较活跃帖子的用户名和id
     */
    private function _getTopuser($ti_id){
        $return = array();
        $topUser = DbUtils::createDbUtils(true)->queryAll('SELECT distinct ft.authorid FROM %t as ft LEFT JOIN %t as tt ON tt.`pid`=ft.`tid` WHERE tt.`ti_id`=%d ORDER BY ft.`heats` LIMIT %d,%d',array('forum_thread','appbyme_tpctopost',$ti_id,0,10));
        foreach($topUser as $v){
            $tmp['uid'] = $v['authorid'];
            $tmp['avatar'] = UserUtils::getUserAvatar($v['authorid']);
            $return[] = $tmp;
        }
        return $return;
    }
    /**
     * 获得话题帖子列表
     */
    private function _getTopiclist($ti_id,$orderby,$str,$pageSize){
        $del = '';
        $data = DbUtils::createDbUtils(true)->queryAll('SELECT ft.*,tt.`pid` FROM %t as tt LEFT JOIN %t as ft ON tt.`pid`=ft.`tid` WHERE tt.`ti_id`=%d AND ft.`displayorder`>=0'.$orderby.'LIMIT %d,%d',array('appbyme_tpctopost','forum_thread',$ti_id,$str,$pageSize));
        foreach($data as $k => $v){
            if($v['tid'] == null){
                $del .= $v['pid'].',';
            }
            unset($data[$k]['pid']);
        }
        $del = trim($del,',');
        if($del){
            DbUtils::createDbUtils(true)->query('DELETE FROM %t WHERE `pid` IN(%i)',array('appbyme_tpctopost',$del));
            $data = $this->_getTopiclist($ti_id,$orderby,$str,$pageSize);
        }
        return $data;
    }
}