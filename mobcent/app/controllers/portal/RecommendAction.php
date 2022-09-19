<?php

/**
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * Class RecommendAction
 * @property DiscuzDbUtils db
 */
class RecommendAction extends MobcentAction
{
    /**
     * @var null
     */
    private $db = null;

    /**
     * @param $type
     * @param $ids
     */
    public function run($type, $ids)
    {
        $res         = WebUtils::initWebApiArray_oldVersion();
        $this->db    = DbUtils::createDbUtils(true);
        $res['body'] = $this->getRecommendInfo($type, explode(',', $ids));
        WebUtils::outputWebApi($res);
    }


    /**
     * @param $type
     * @param $ids
     * @return array|void
     */
    private function getRecommendInfo($type, $ids)
    {
        $info = array();
        switch ($type)
        {
            case 'topic':
                $info = $this->getTopicInfo($ids);
                break;
            case 'user':
                $info = $this->getUserInfo($ids);
                break;
            case 'forums':
                $info = $this->getForums($ids);
                break;
        }
        return $info;
    }

    /**
     * @param array $ids
     */
    private function getTopicInfo($ids)
    {
        $return = array();
        $info   = $this->db->queryAll("SELECT * FROM %t WHERE ti_id IN (%n)", array('appbyme_topic_items', $ids));
        foreach ($info as $item)
        {
            if ($item['ti_cover'])
            {
                $forum            = $item['ti_remote'] ? '/' : '/forum/';
                $item['ti_cover'] = ImageUtils::getAttachUrl($v['ti_remote']) . $forum . $v['ti_cover'];
            }
            $return[] = $item;
        }
        return $return;
    }

    /**
     * 获得用户数据
     * @param $ids
     * @return array
     */
    private function getUserInfo($ids)
    {
        $return   = array();
        $userList = C::t('common_member')->fetch_all_username_by_uid($ids);
        foreach ($userList as $uid => $username)
        {
            $return[] = array(
                'uid'      => $uid,
                'username' => $username,
                'avatar'   => UserUtils::getUserAvatar($uid),
            );
        }
        return $return;
    }

    private function getForums($ids)
    {
        global $_G;
        $return = array();
        $info = C::t('forum_forum')->fetch_all_info_by_fids($ids);
        foreach ($info as $key=>$item)
        {
            require_once libfile('function/forumlist');
            $forumImage = get_forumimg($item['icon']);
            $forumImage = (string)WebUtils::getHttpFileName($forumImage);
            $return[] = array(
                'fid'=>$item['fid'],
                'name'=>$item['name'],
                'icon'=>$forumImage,
                'td_posts_num'=>$item['todayposts'],
                'topic_total_num'=>$item['threads'],
                'posts_total_num'=>$item['posts'],
                'is_focus'=>(int)ForumUtils::getFavStatus($_G['uid'], $item['fid']),
            );
        }
        return $return;
    }
}