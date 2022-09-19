<?php

/**
 *
 *
 * @author 耐小心<i@naixiaoxin.com>
 * @copyright 2003-2017 耐小心
 */
class RecommendUtils
{

    /**
     * @var DiscuzDbUtils
     */
    private $db;

    private $url = 'http://pub-file.app.xiaoyun.com';

    public function __construct()
    {
        $this->db = DbUtils::createDbUtils(true);
    }


    public function getInfo($type, $moduleId)
    {
        $result = array();
        $sql    = "SELECT l.id,l.ext,l.title,m.position,l.type
          FROM %t  l 
          LEFT JOIN %t m ON l.id=m.listId
          LEFT  JOIN %t b ON b.module=m.id
          WHERE  b.type=%s AND b.moduleId=%s";
        $info   = $this->db->queryAll($sql, array('appbyme_recommend_list', 'appbyme_recommend_module', 'appbyme_recommend_bind', $type, $moduleId));
        foreach ($info as $item)
        {
            $item['ext'] = unserialize($item['ext']);
            $result[]    = $this->makeInfo($item);
        }

        return $result;
    }


    private function makeInfo($item)
    {
        $result             = array();
        $result['position'] = intval($item['position']);
        $result['type']     = $item['type'];
        switch ($result['type'])
        {
            case 'topic':
                $result['title'] = Yii::t('mobcent','mobcent_recommend_topic_'.$item['ext']['type']);
                $result['data'] = $this->makeTopic($item['ext']);
                break;
            case 'forum':
                $result['title'] = Yii::t('mobcent','mobcent_recommend_forums');
                $result['data'] = $this->makeForum($item['ext']);
                break;
            case 'activity':
                $result['title'] = $item['ext']['name'];
                $result['data'] = $this->makeActivity($item['ext']);
                break;
            case 'user':
                $result['title'] = Yii::t('mobcent','mobcent_recommend_user');
                $result['data'] = $this->makeUser($item['ext']);
                break;
            case 'live':
                $result['title'] = Yii::t('mobcent','mobcent_recommend_live');
                break;
        }

        return $result;
    }


    /**
     * 获得话题相关信息
     * @param $ext
     * @return array
     */
    private function makeTopic($ext)
    {
        $result = array();
        switch ($ext['type'])
        {
            case 'custom':
                $topicId = explode(',',$ext['topicId']);
                $info = $this->db->queryAll("SELECT * FROM %t WHERE  ti_id IN (%n)", array('appbyme_topic_items', $topicId));
                break;
            case 'new':
                $info = $this->db->queryAll("SELECT * FROM %t  WHERE `ti_starttime`<%d AND `ti_endtime`>%d  ORDER BY ti_id DESC LIMIT 0,5", array('appbyme_topic_items', time(), time()));
                break;
            case 'hot':
                $info = $this->db->queryAll("SELECT * FROM %t  WHERE `ti_starttime`<%d AND `ti_endtime`>%d  ORDER BY ti_topiccount DESC LIMIT 0,5", array('appbyme_topic_items', time(), time()));
                break;
            default:
                $info = array();
        }
        foreach ($info as $item)
        {
            $items = array(
                'ti_id'    => intval($item['ti_id']),
                'ti_title' => $item['ti_title'],
                'ti_cover' => $item['ti_cover']
            );
            if ($item['ti_cover'])
            {
                if ((stripos($item['ti_cover'], 'http') === false) && (stripos($item['ti_cover'], 'https') === false))
                {
                    $forum             = $item['ti_remote'] ? '/' : '/forum/';
                    $items['ti_cover'] = ImageUtils::getAttachUrl($item['ti_remote']) . $forum . $item['ti_cover'];
                }
            }
            $result[] = $items;
        }

        return $result;
    }

    private function makeForum($ext)
    {
        global $_G;
        $result = array();
        require_once libfile('function/forumlist');

        $forumInfos = C::t('forum_forum')->fetch_all_info_by_fids(explode(',', $ext['boardId']));
        foreach ($forumInfos as $forum)
        {
            $forumInfo['fid']             =  intval($forum['fid']);
            $forumImage                   = get_forumimg($forum['icon']);
            $forumImage                   = (string)WebUtils::getHttpFileName($forumImage);
            $forumInfo['icon']            = (string)$forumImage;
            $forumInfo['td_posts_num']    = intval($forum['todayposts']);
            $forumInfo['topic_total_num'] = intval($forum['threads']);
            $forumInfo['posts_total_num'] = intval($forum['posts']);
            $forumInfo['title']           = (string)WebUtils::emptyHtml($forum['name']);
            $forumInfo['description']     = (string)WebUtils::emptyHtml($forum['description']);
            $forumInfo['is_focus'] = (int)ForumUtils::getFavStatus($_G['uid'], $forum['fid']);
            $result[]                     = $forumInfo;
        }

        return $result;
    }

    private function makeActivity($ext)
    {
        return array(array(
            'name'    => $ext['name'],
            'pic'     => $this->url . $ext['pic'],
            'type'    => $ext['type'],
            'topicId' => intval($ext['topicId']),
            'url'     => strval($ext['url'])
        ));
    }

    private function makeUser($ext)
    {
        switch ($ext['type'])
        {
            case 'custom':
                //fetch_all_username_by_uid
                $userName = C::t('common_member')->fetch_all_username_by_uid(explode(',', $ext['userId']));
                foreach (explode(',', $ext['userId']) as $item)
                {
                    $result[] = array(
                        'uid'  => intval($item),
                        'name' => $userName[$item],
                        'icon' => UserUtils::getUserAvatar($item)
                    );
                }

                return $result;
                break;
            default:
                $info = WebUtils::httpRequestAppAPI('user/userlist', array(
                    'type'      => $ext['type'],
                    'orderBy'   => $ext['orderBy'],
                    'longitude' => $_GET['longitude'],
                    'latitude'  => $_GET['latitude']
                ));
                $res  = WebUtils::tarr(WebUtils::jsonDecode($info));

                return $res['list'];
        }
    }

}