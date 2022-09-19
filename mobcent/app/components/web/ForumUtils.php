<?php

/**
 * 论坛相关工具类
 *
 * @author NaiXiaoXin
 * @author 谢建平 <xiejianping@mobcent.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME'))
{
    exit('Access Denied');
}

class ForumUtils
{

    static $postStatus = array(
        '1' => 'apple',
        '2' => 'android',
        '3' => 'weizhan',
        '4' => 'weapp'
    );

    /**
     * 旧的Status值
     * @var array
     */
    static $postStatusOld = array(
        'apple'   => 15,
        'android' => 16,
    );


    static $platType = array(
        APP_TYPE_APPLE   => 1,
        APP_TYPE_ANDROID => 2,
        APP_TYPE_WZ      => 3,
        APP_TYPE_WEAPP   => 4
    );

    /**
     * 获取通告列表
     *
     * @return array
     */
    public static function getAnnouncementList()
    {
        return DzForumAnnouncement::getAnnouncements();
    }

    /**
     * 版块相关
     */

    /**
     * 获取客户端能显示的fids
     */
    public static function getForumShowFids()
    {
        return self::_getForumFids('forum_show');
    }

    /**
     * 获取客户端能显示版块图片的fids
     */
    public static function getForumImageShowFids()
    {
        return self::_getForumFids('forum_show_image');
    }

    /**
     * 获取客户端图库能显示的fids
     */
    public static function getForumPhotoGalleryShowFids()
    {
        return self::_getForumFids('forum_photo_show');
    }

    private static function _getForumFids($key)
    {
        $fids   = array();
        $config = WebUtils::getDzPluginAppbymeAppConfig($key);
        if ($config && ($config = unserialize($config)))
        {
            $config[0] != '' && $fids = $config;
        }
        else
        {
            $fids = DzForumForum::getFids();
        }

        return $fids;
    }

    public static function getForumName($fid)
    {
        return DzForumForum::getNameByFid($fid);
    }

    /**
     * 获取版块信息
     *
     * @param string $fids 版块信息列表 空或者0则为全部版块
     * @return array
     */
    public static function getForumInfos($fids = '')
    {
        if ($fids == 'all')
        {
            $fids = DzForumForum::getAllFid();
        }
        elseif ($fids == '' || $fids == '0')
        {
            $fids = ForumUtils::getForumShowFids();
        }
        else
        {
            $fids = ArrayUtils::explode($fids);
        }

        return DzForumForum::getForumInfos($fids);
    }

    public static function getForumGroupList()
    {
        return DzForumForum::getForumGroups();
    }

    public static function getForumList($gid)
    {
        return DzForumForum::getForumsByGid($gid);
    }

    /**
     * 获取子版块列表
     *
     * @param int $fid 版块id
     * @return array
     */
    public static function getForumSubList($fid)
    {
        return DzForumForum::getSubForumsByFid($fid);
    }

    /**
     * 获取所有版块列表
     *
     * @return array
     */
    public static function getForumListForHtml()
    {
        global $_G;
        loadcache('forums');
        // $settings[0] = lang('portalcp', 'block_all_forum');
        foreach ($_G['cache']['forums'] as $fid => $forum)
        {
            $forumValue     = ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')) . $forum['name'];
            $settings[$fid] = $forumValue;
        }

        return $settings;
    }

    /**
     * 主题相关
     */

    /**
     * 初始化加载版块
     */
    public static function initForum($fid, $tid = 0)
    {
        $app = Yii::app()->getController()->mobcentDiscuzApp;
        $app->loadForum($fid, $tid);
    }

    /**
     * 获取发帖面板
     */
    public static function getNewTopicPanel($fid = 0)
    {
        $fid > 0 && ForumUtils::initForum($fid);

        $panel = array();

        global $_G;

        if (($_G['forum']['simple'] & 1) || $_G['forum']['redirect'])
        {
            return $panel;
        }

        $_G['group']['allowpostpoll'] = $_G['group']['allowpost'] && $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);

        if (!$_G['forum']['threadsorts']['required'] && !$_G['forum']['allowspecialonly'])
        {
            $panel[] = array(
                'type'   => 'normal',
                'action' => '',
                'title'  => Yii::t('mobcent', 'mobcent_topic_publish', array()), // WebUtils::t('发表帖子'),
            );
        }
        if (is_array($_G['forum']['threadsorts']['types']))
        {
            foreach ($_G['forum']['threadsorts']['types'] as $tsortid => $name)
            {
                $panel[] = array(
                    'type'     => 'sort',
                    'actionId' => $tsortid,
                    'action'   => '',
                    'title'    => WebUtils::emptyHtml($name),
                );
            }
        }
        if ($_G['group']['allowpostpoll'])
        {
            $panel[] = array(
                'type'   => 'vote',
                'action' => '',
                'title'  => Yii::t('mobcent', 'mobcent_vote_start', array()), // WebUtils::t('发起投票'),
            );
        }

        return $panel;
    }

    /**
     * 获取主题分类，分类信息
     */
    public static function getTopicClassificationInfos($fid = 0)
    {
        $fid > 0 && ForumUtils::initForum($fid);

        $infos = array('types' => array(), 'sorts' => array(), 'requireTypes' => false);

        // $field = DbUtils::getDzDbUtils(true)->queryRow('
        //     SELECT threadtypes, threadsorts
        //     FROM %t
        //     WHERE fid=%d
        //     ',
        //     array('forum_forumfield', $fid)
        // );
        global $_G;
        $field = $_G['forum'];
        if (!empty($field))
        {
            // $types = unserialize($field['threadtypes']);
            $types = $field['threadtypes'];
            if (!empty($types['types']))
            {
                $infos['requireTypes'] = $types['required'];
                foreach ($types['types'] as $key => $value)
                {
                    // 控制管理组专用@xushaowei
                    if ($types['moderators'][$key] == 1)
                    {
                        continue;
                    }
                    $infos['types'][] = array(
                        'classificationType_id'   => $key,
                        'classificationType_name' => WebUtils::emptyHtml($value),
                    );
                }
            }
            // $sorts = unserialize($field['threadsorts']);
            $sorts = $field['threadsorts'];
            if (!empty($sorts['types']))
            {
                foreach ($sorts['types'] as $key => $value)
                {
                    $infos['sorts'][] = array(
                        'classificationTop_id'   => $key,
                        'classificationTop_name' => WebUtils::emptyHtml($value),
                    );
                }
            }
        }

        return $infos;
    }

    /**
     * 取得主题个数
     *
     * @param int $fid 版块id,0则代表全部版块
     * @param array $params
     * @return array
     */
    public static function getTopicCount($fid = 0, $params = array())
    {
        return DzForumThread::getCountByFid($fid, $params);
    }

    /**
     * 获取帖子列表
     *
     * @param int $fid 版块id,0则代表全部版块
     * @param int $page Description.
     * @param int $pageSize Description.
     * @param array $params Description.
     *
     * @return array.
     */
    public static function getTopicList($fid = 0, $page = 1, $pageSize = 10, $params = array())
    {
        return DzForumThread::getTopicsByFid($fid, $page, $pageSize, $params);
    }

    public static function getTopicInfo($tid)
    {
        return DzForumThread::getTopicByTid($tid);
    }

    /**
     * 获取主题类型
     *
     * @param int|array $tid int为主题id, array为主题info
     *
     * @return array
     */
    public static function getTopicType($tid)
    {
        return DzForumThread::getTopicTypeByTid($tid);
    }

    /**
     * 获取主题摘要(内容摘要以及图片)
     *
     * @param int $tid 帖子id
     * @param string $type forum为论坛模块，portal为门户模块
     * @param bool $transBr 是否要转换换行
     * @param array $options 参数选项, 可选值: array('imageList' => 1, 'imageListLen' => 9, 'imageListThumb' => 1)
     * @return array array('msg' => '', 'image' => '', 'imageList' => array())
     */
    public static function getTopicSummary($tid, $type = 'forum', $transBr = true, $options = array())
    {
        $summary = array('msg' => '', 'image' => '', 'imageList' => array(), 'videoList' => array());


        $summaryLength = WebUtils::getDzPluginAppbymeAppConfig($type == 'forum' ? 'forum_summary_length' : 'portal_summary_length');
        $allowImage    = WebUtils::getDzPluginAppbymeAppConfig($type == 'forum' ? 'forum_allow_image' : 'portal_allow_image');
        $allowImage    = !($allowImage === '0');
        if ($summaryLength === '0' && !$allowImage)
        {
            return $summary;
        }

        $content = self::getTopicContent($tid);
        if (!empty($content['main']))
        {
            $msg             = '';
            $isFindImage     = false;
            $isFindImageList = false;
            $getImageList    = isset($options['imageList']) ? $options['imageList'] : 0;
            $imageListLen    = isset($options['imageListLen']) ? $options['imageListLen'] : 9;
            $imageListThumb  = isset($options['imageListThumb']) ? $options['imageListThumb'] : 1;
            $imageListCount  = 0;
//            debug($content);
            foreach ($content['main'] as $line)
            {
                if ($line['type'] == 'image' && !$isFindImageList && !strpos($line['content'], 'mobcent'))
                {

                    $imageListCount++;
                    if ($allowImage)
                    {
                        !$isFindImage && $summary['image'] = $line['content'];
                        if ($getImageList && !$isFindImageList)
                        {
                            $imageListThumb && $line['content'] = ImageUtils::getThumbImage($line['content']);
                            $summary['imageList'][] = $line['content'];
                        }
                    }
                    $isFindImage = true;

                    !$getImageList && $isFindImageList = true;
                    $imageListCount == $imageListLen && $isFindImageList = true;
                }
                if ($line['type'] == 'video' && !empty($line['picurl']) && $allowImage)
                {
                    $summary['videoList'][] = array(
                        'videoUrl' => $line['content'],
                        'picUrl'   => $line['picurl']
                    );
                }
                if ($line['type'] == 'text')
                {
                    $msg .= $line['content'] . "\r\n";
                }
            }
            $msg = preg_replace('/\[mobcent_phiz=.+?\]/', '', $msg);
            $msg = preg_replace(WebUtils::t('/本帖最后由 .*? 于 .*? 编辑/'), '', $msg);
            $transBr && $msg = WebUtils::emptyReturnLine($msg, ' ');
            $msg = trim($msg);
            $summaryLength === false && $summaryLength = 40;
            $summary['msg'] = (string)WebUtils::subString($msg, 0, $summaryLength);
            $summary['msg'] = preg_replace('/\[.{0,8}[^\]]$/', '', $summary['msg']);
        }

        return $summary;
    }

    /**
     * 获取主题封面
     *
     * @param int $tid
     */
    public static function getTopicCover($tid)
    {
        $image      = '';
        $topicImage = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE tid=%d
            ', array('forum_threadimage', $tid)
        );
        if (!empty($topicImage))
        {
            require_once DISCUZ_ROOT . './source/function/function_home.php';
            $image = pic_get($topicImage['attachment'], 'forum', 0, $topicImage['remote']);
        }

        return WebUtils::getHttpFileName($image);
    }

    public static function getTopicContent($tid, $post = array())
    {
        global $_G;
        $k = 'POST_INFO_' . $tid . '_' . md5(serialize($post));
        //游客不允许看图
        if (!$_G['group']['allowgetimage'])
        {
            $k = 'POST_INFO_' . $tid . '_' . md5(serialize($post)) . '_noimg';
        }
        if (strpos($post, '[/hide]') !== FALSE)
        {
            if (!$_G['forum']['ismoderator'])
            {
                if ($_G['uid'])
                {
                    $authorreplyexist = $post['tid'] == $_G['tid'] ? C::t('forum_post')->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']) : FALSE;
                }
            }
            else
            {
                $authorreplyexist = true;
            }
            if (!$authorreplyexist)
            {
                $k = 'POST_INFO_' . $tid . '_' . md5(serialize($post)) . '_no';
            }
        }
        $res = Yii::app()->cache->get($k);
        if ($res)
        {
            $res = unserialize($res);
            foreach ($res['main'] as $k => $v)
            {
                if (strstr($v['content'], WebUtils::t('如果您要查看本帖隐藏内容请回复')))
                {
                    $exp = explode(WebUtils::t('，'), $v['content']);
                    foreach ($exp as $key => $value)
                    {
                        if (strstr($value, WebUtils::t('如果您要查看本帖隐藏内容请回复')))
                        {
                            $key -= 1;
                            if ($_G['uid'])
                            {
                                $res['main'][$k]['content'] = str_replace($exp[$key], $_G['username'], $res['main'][$k]['content']);
                            }
                        }
                    }
                }
            }

            return ($res);
        }
        else
        {
            if (empty($post))
            {
                $post = self::getTopicPostInfo($tid);
            }
            $res = self::getContentByPost($post);
            Yii::app()->cache->set($k, serialize($res), 600);

            return $res;
        }
    }

    public static function getHtmlContent($tid, $post = array())
    {
        if (empty($post))
        {
            $post = self::getTopicPostInfo($tid);
        }

        return self::getContentByBbcode($post);
    }

    public static function getWebContent($tid, $post = array())
    {
        if (empty($post))
        {
            $post = self::getTopicPostInfo($tid);
        }

        return self::getWebContentByPost($post);
    }

    public static function getWebContentByPost($post)
    {
        return $post;
    }

    /**
     * 获取投票主题数据
     *
     * @param int|array $tid int为主题id, array为主题info
     *
     * @return array
     */
    public static function getTopicVoteInfo($tid)
    {
        return DzForumThread::getTopicVoteInfoByTid($tid);
    }

    public static function getTopicActivityInfo($tid)
    {
        return DzForumThread::getTopicActivityInfoByTid($tid);
    }

    /**
     * 测试是否投票主题
     *
     * @param int|array $tid int为主题id, array为主题info
     *
     * @return bool
     */
    public static function isVoteTopic($tid)
    {
        return DzForumThread::isVote($tid);
    }

    public static function isActivityTopic($tid)
    {
        return DzForumThread::isActivity($tid);
    }

    public static function isHotTopic($tid)
    {
        return DzForumThread::isHot($tid);
    }

    public static function isMarrowTopic($tid)
    {
        return DzForumThread::isMarrow($tid);
    }

    public static function isTopTopic($tid)
    {
        return DzForumThread::isTop($tid);
    }

    public static function isFavoriteTopic($uid, $tid)
    {
        return DzForumThread::isFavorite($uid, $tid);
    }

    // 是否赞过
    public static function isHasRecommendAdd($tid)
    {
        global $_G;
        $recommendAdd = DzSupportInfo::getSupportTopicByUidAndTid($_G['uid'], $tid);

        return !empty($recommendAdd) ? 1 : 0;
    }

    // 赞的总数
    public static function getRecommendAdd($tid)
    {
        return DzSupportInfo::getSupportTopicCount($tid);
    }

    /**
     * 测试是否此主帖仅作者可见
     */
    public static function isOnlyAuthorTopic($tid)
    {
        return DzForumThread::isOnlyAuthor($tid);
    }

    /**
     * 帖子相关
     */

    /**
     * 获取主题帖信息
     */
    public static function getTopicPostInfo($tid)
    {
        return DzForumPost::getFirstPostByTid($tid);
    }

    public static function getPostInfo($tid, $pid)
    {
        return DzForumPost::getPostByTidAndPid($tid, $pid);
    }

    /**
     * 获取回帖列表
     *
     * @param int $tid Description.
     * @param int $page Description.
     * @param int $pageSize Description.
     * @param array $params Description.
     *
     * @return array
     */
    public static function getPostList($tid, $page = 1, $pageSize = 10, $params = array())
    {
        return DzForumPost::getPostsByTid($tid, $page, $pageSize, $params);
    }

    /**置顶回帖返回
     * @param $tid
     * @return mixed
     */
    public static function getPostStick($tid)
    {
        return DzForumPost::_getPostStick($tid);
    }

    /**
     * 获取回帖个数
     *
     */
    public static function getPostCount($tid, $params = array())
    {
        return DzForumPost::getCountByTid($tid, $params);
    }

    public static function getPostContent($tid, $pid, $post = array())
    {
        if (empty($post))
        {
            $post = self::getPostInfo($tid, $pid);
        }

        return self::getContentByPost($post);
    }

    public static function getPostBBCodeContent($tid, $pid, $post = array())
    {
        if (empty($post))
        {
            $post = self::getPostInfo($tid, $pid);
        }

        return self::getContentByBbcode($post);
    }

    public static function getContentByBbcode($post)
    {
        $content = array();
        $post    = self::_transHtmlMessage($post);
        if (!empty($post))
        {
            $content = self::_filterHtmlMessage($post['message']);
        }

//        debug($content);
        return $content;
    }

    public static function getContentByPost($post)
    {
        $content = array();
        if (!empty($post))
        {
            // 兼容有些用户自己添加了帖子标题，表情
            // if (!empty($post['face'])) {
            //     $post['smileyoff'] = 0;
            //     $post['message'] .= $post['face'];
            // }
            // $post['message'] .= $post['subject'];
            $post    = self::_transPostMessage($post);
            $content = self::_filterPostMessage($post['message'], $post);
        }

        return $content;
    }

    public static function getTopicLocation($tid)
    {
        return SurroundingInfo::getLocationById($tid, SurroundingInfo::TYPE_TOPIC);
    }

    public static function getPostLocation($pid)
    {
        return SurroundingInfo::getLocationById($pid, SurroundingInfo::TYPE_POST);
    }

    /**
     * 测试是否投票主题
     *
     * @param int $tid int为主题id
     * @param int|array $pid int为帖子id, array为帖子info
     *
     * @return bool
     */
    public static function isAnonymousPost($tid, $pid)
    {
        return DzForumPost::isAnonymous($tid, $pid);
    }

    /**
     * 获取帖子管理面板权限信息
     *
     * @param array $params 获得权限所需要的信息.
     * @return array  管理面板数据
     *
     */
    public static function getPostManagePanel($params = array())
    {
        extract($params);
        global $_G;

        // 从DISCUZ_ROOT/source/module/forum/forum_viewthread.php复制而来
        $rushreply = getstatus($_G['forum_thread']['status'], 3);

        $allowblockrecommend = $_G['group']['allowdiy'] || getstatus($_G['member']['allowadmincp'], 4) || getstatus($_G['member']['allowadmincp'], 5) || getstatus($_G['member']['allowadmincp'], 6);
        if ($_G['setting']['portalstatus'])
        {
            $allowpostarticle = $_G['group']['allowmanagearticle'] || $_G['group']['allowpostarticle'] || getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3);
            $allowpusharticle = empty($_G['forum_thread']['special']) && empty($_G['forum_thread']['sortid']) && !$_G['forum_thread']['pushedaid'];
        }
        else
        {
            $allowpostarticle = $allowpusharticle = false;
        }
        if ($_G['forum_thread']['displayorder'] != -4)
        {
            $modmenu = array(
                'thread' => $_G['forum']['ismoderator'] || $allowblockrecommend || $allowpusharticle && $allowpostarticle,
                'post'   => $_G['forum']['ismoderator'] && ($_G['group']['allowwarnpost'] || $_G['group']['allowbanpost'] || $_G['group']['allowdelpost'] || $_G['group']['allowstickreply']) || $_G['forum_thread']['pushedaid'] && $allowpostarticle || $_G['forum_thread']['authorid'] == $_G['uid']
            );
        }
        else
        {
            $modmenu = array();
        }

        if (empty($modmenu))
        {
            $modmenu['thread'] = false;
            $modmenu['post']   = false;
        }

        $manageItems = array('topic' => array(), 'post' => array());

        if ($modmenu['thread'])
        {
            if ($_G['forum']['ismoderator'])
            {
                // 删除主题
                if ($_G['group']['allowdelpost'])
                {
                    $manageItems['topic'][] = array('action' => 'delete', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_delete_theme'))); //删除主题
                }
                // 置顶
                if ($_G['group']['allowstickthread'] && ($_G['forum_thread']['displayorder'] <= 3 || $_G['adminid'] == 1) && !$_G['forum_thread']['is_archived'])
                {
                    $manageItems['topic'][] = array('action' => 'top', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_stick'))); //置顶
                }
                // 精华
                if ($_G['group']['allowdigestthread'] && !$_G['forum_thread']['is_archived'])
                {
                    $manageItems['topic'][] = array('action' => 'marrow', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_elite'))); //精华
                }
                //  打开/关闭
                if ($_G['group']['allowclosethread'] && !$_G['forum_thread']['is_archived'] && $_G['forum']['status'] != 3)
                {
                    $manageItems['topic'][] = array(
                        'action' => !$_G['forum_thread']['closed'] ? 'close' : 'open',
                        'title'  => WebUtils::t(!$_G['forum_thread']['closed'] ? WebUtils::lp('post_manager_panel_close') : WebUtils::lp('post_manager_panel_open')), //关闭,//打开
                    );
                }
                // 移动
                if ($_G['group']['allowmovethread'] && !$_G['forum_thread']['is_archived'] && $_G['forum']['status'] != 3)
                {
                    $manageItems['topic'][] = array('action' => 'move', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_move'))); //移动
                }
                // 屏蔽
                if ($_G['group']['allowbanpost'])
                {
                    $manageItems['topic'][] = array('action' => 'band', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_shield'))); //屏蔽
                }
            }
        }

        // 编辑权限 author:HanPengyu |start
        $post['adminid']      = $userMember['adminid'];
        $post['authorid']     = $userMember['authorid'];
        $post['dbdateline']   = $userMember['dateline'];
        $alloweditpost_status = $editPerm['alloweditpost_status'];
        $edittimelimit        = $editPerm['edittimelimit'];

        if ((($_G['forum']['ismoderator'] && $_G['group']['alloweditpost'] && (!in_array($post['adminid'], array(1, 2, 3)) || $_G['adminid'] <= $post['adminid'])) || ($_G['forum']['alloweditpost'] && $_G['uid'] && ($post['authorid'] == $_G['uid'] && $_G['forum_thread']['closed'] == 0) && !(!$alloweditpost_status && $edittimelimit && TIMESTAMP - $post['dbdateline'] > $edittimelimit))))
        {
            if ($_G['forum_thread']['special'] == 2 && !$post['message'])
            {
                $manageItems['topic'][] = array('action' => 'edit', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_add_bar_introduce'))); //添加柜台介绍
            }
            else
            {
                $manageItems['topic'][] = array('action' => 'edit', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_edit'))); //编辑
            }
            $manageItems['post'][] = array('action' => 'edit', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_edit'))); //编辑
        }
        // end

        if ($modmenu['post'])
        {
            if ($_G['forum']['ismoderator'])
            {
                // 删除
                if ($_G['group']['allowdelpost'] && !$rushreply)
                {
                    $manageItems['post'][] = array('action' => 'delete', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_delete'))); //删除
                }
                // 屏蔽
                if ($_G['group']['allowbanpost'])
                {
                    $manageItems['post'][] = array('action' => 'band', 'title' => WebUtils::t(WebUtils::lp('post_manager_panel_shield'))); //屏蔽
                }
            }
        }

        return $manageItems;
    }

    // 获取帖子附加的面板信息
    public static function getPostExtraPanel()
    {
        $panels = array('topic' => array(), 'post' => array());
        global $_G;

        // 评分的权限控制

        $ratePlugConfig = (int)WebUtils::getDzPluginAppbymeAppConfig('forum_allow_topic_rate');
        //   if ($ratePlugConfig && $_G['group']['raterange']) {
        //暂时去掉对于用户组权限判断 Nxx
        if ($ratePlugConfig)
        {
            $panels['topic'][] = array('action' => 'rate', 'title' => WebUtils::t(WebUtils::lp('post_extra_panel_grade'))); //评分
            // $panels['post'][] = array('action' => 'rate', 'title' => WebUtils::t('评分'));
        }
        // 赞
        $topicConfig = (int)WebUtils::getDzPluginAppbymeAppConfig('forum_allow_topic_recommend');
        $postConfig  = (int)WebUtils::getDzPluginAppbymeAppConfig('forum_allow_post_recommend');
        // $topicConfig = $postConfig = 1;

        $support = $_G['setting']['recommendthread'];
        if ($support['status'] && $topicConfig == 1)
        {
            $panels['topic'][] = array('action' => 'support', 'title' => WebUtils::emptyHtml($support['addtext']));
        }

        $supportPost = $_G['setting']['repliesrank'];
        if ((MobcentDiscuz::getMobcentDiscuzVersion() > 'x25') && $supportPost && $postConfig == 1)
        {
            $panels['post'][] = array('action' => 'support', 'title' => WebUtils::emptyHtml(WebUtils::t(WebUtils::lp('post_extra_panel_support'))), 'recommendAdd' => ''); //支持
        }

        return $panels;
    }

    /**转换信息给H5
     * @param $post
     * @return mixed
     */
    private static function _transHtmlMessage($post)
    {
        if ($post['bbcodeoff'] != 1)
        {
            // 转换视音频，为了不走 discuz 原先的流程
            $post['message'] = preg_replace_callback(
                '/\[(audio|media|flash|code)(.*?)\](.*?)\[\/\1\]/s', create_function('$matches', '
                    $string = "";
                    $posterString = "";
                    switch ($matches[1]) {
                        case "audio":
                            $string = "<audio src={$matches[3]}></audio>";
                            break;
                        case "media":
                            if($matches[2]){
                                $poster = explode(",",$matches[2]);
                                if(!empty($poster))
                                {
                                    if(trim($poster[0],"=") == "aid" && $poster[1])
                                    {
                                        $data = DbUtils::createDbUtils(true)->queryRow("SELECT * FROM %t WHERE aid=%d",array("forum_attachment_0",$poster[1]));
                                        if(!empty($data))
                                        {
                                               $forum = $data["remote"] ? "/" : "/forum/";
                                                 $attach_url = ImageUtils::getAttachUrl($data["remote"]).$forum.$data["attachment"];
                                            $posterString = "poster=\'".$attach_url."\'";
                                        }
                                        $videoUrl = ForumUtils::transVideoUrl($matches[3]);
                                        $string = "<embed $posterString src={$videoUrl} data-svideo=\'1\'>";
                                        break;
                                    }
                                }
                            }
                        case "flash":
                            $videoUrl = ForumUtils::transVideoUrl($matches[3]);
                            $string = "<embed $posterString src={$videoUrl}>";
                            break;
                        case "code":
                            $videoUrl = ForumUtils::transVideoUrl($matches[3]);
                            $string = "<code> {$videoUrl}</code>";
                            break;
                        default:
                            break;
                    }
                    return "$string";
                '), $post['message']
            );
        }

        return DzForumPost::transPostContentToHtml($post);
    }

    private static function _transPostMessage($post)
    {
        if ($post['bbcodeoff'] != 1)
        {
            // 转换视音频，为了不走 discuz 原先的流程
            $post['message'] = preg_replace_callback(
                '/\[(audio|media|flash|code)(.*?)\](.*?)\[\/\1\]/s', create_function('$matches', '
                    global $_G;
                    $string = "";
                    switch ($matches[1]) {
                        case "audio":
                            $string = "[mobcent_audio={$matches[3]}]";
                            break;
                        case "media":
                                if($matches[2]){
                                    $poster = explode(",",$matches[2]);
                                    if(!empty($poster))
                                    {
                                        if(trim($poster[0],"=") == "aid" && $poster[1])
                                        {
                                            $data = DbUtils::createDbUtils(true)->queryRow("SELECT * FROM %t WHERE aid=%d",array("forum_attachment_0",$poster[1]));
                                            if(!empty($data))
                                            {       
                                                   $forum = $data["remote"] ? "/" : "/forum/";
                                                  $attach_url = ImageUtils::getAttachUrl($data["remote"]).$forum.$data["attachment"];
                                            }
                                        }
                                    }
                                }
                        case "flash":
                            $videoUrl = ForumUtils::transVideoUrl($matches[3]);
                            $string = "[mobcent_video={$videoUrl} mobcent_picurl={$attach_url}]";
                            break;
                        default:
                            break;
                    }
                    return "[mobcent_br]\r\n" . $string . "\r\n";
                '), $post['message']
            );
        }

        // 转换超链接
        $post['message'] = preg_replace_callback('/\[url=?(.*?)\](.*?)\[\\/url\]/', create_function('$matches', '
                $matches[1] || $matches[1] = $matches[2];
                $matches[1] = WebUtils::getHttpFileName($matches[1]);
                return "[mobcent_br]\r\n[mobcent_url={$matches[1]}(title={$matches[2]})]\r\n";
            '), $post['message']
        );

        return DzForumPost::transPostContentToHtml($post);
    }

    private static function _filterHtmlMessage($postMsg)
    {
        $postMsg = WebUtils::emptyReturnLine($postMsg);
        $matches = array();
        preg_match_all('/<blockquote>.*?<font.*?>.*?<font.*?>(.*?)<\/font>.*?<br \/>(.*?)<\/blockquote>/', $postMsg, $matches);
        $postMsg = preg_replace('/<blockquote>.*?<\/blockquote>/', '', $postMsg);
        $postMsg = preg_replace('/<div class="quote">.*?<\/div>/', '', $postMsg);
        // 处理图片缩略图
        $postMsg = preg_replace('/<style>.*?<\/style>/', '', $postMsg);
        if ($postMsg)
        {
            // 处理附件下载
            $postMsg = preg_replace_callback('/<ignore_js_op>.*?<\/ignore_js_op>/', create_function('$matches', '
                $attachment = "";
                $tempAttachUrlMatches = $tempAttachTitleMatches = $tempAttachDescMatches = $tempImgMatches = array();

                preg_match("/<a.*?href=\"(.+?)\".*?>(.+?)<\/a>/", $matches[0], $tempAttachUrlMatches);
                if (!empty($tempAttachUrlMatches)) {
                    $url = WebUtils::getHttpFileName($tempAttachUrlMatches[1]);
                    preg_match("/<em.*?>(.+?)<\/em>/", $matches[0], $tempAttachDescMatches);
                    $desc = !empty($tempAttachDescMatches) ? $tempAttachDescMatches[1] : "";
                    $title = $tempAttachUrlMatches[2];
                    if ($desc == "" || $title == WebUtils::t("下载附件")) {
                        preg_match("/<div class=\"tip_horn\">.*?<p>(.+?)<\/p>/", $matches[0], $tempAttachDescMatches);
                        $desc = !empty($tempAttachDescMatches) ? $tempAttachDescMatches[1] : "";
                    }
                    if(strpos($tempAttachUrlMatches[1], "nothumb=yes") === false) {
                        $desc != "" && $attachment .= "<a href={$url}>{$title}</a>{$tempAttachDescMatches[1]}";
                    }
                }

                // 处理图片附件
                $imgPattern = "/<img.*?id=\".*?\".*?aid=\"(\d+)\".*?(zoomfile|makefile)=\"(.+?)\".*?\/>/";
                preg_match($imgPattern, $matches[0], $tempImgMatches);
                if (!empty($tempImgMatches)) {
                    $image = WebUtils::getHttpFileName($tempImgMatches[3]);
                    $attachment .= "<img src={$image}>";
                }
                return $attachment;
            '), $postMsg
            );
            // 处理网络图片和客户端自带表情
            $postMsg = preg_replace_callback('/<img[^>]*?id=[^>]*?(src|file)="(.+?)".*?\/>/', create_function('$matches', '
                $image = WebUtils::getHttpFileName($matches[2]);
                // 处理给手机客户端的表情图片
                if (strpos($image, "/app/data/phiz/") !== false) {
                    if ((array_search(basename($image), WebUtils::getMobcentPhizMaps())) !== false) {
                        return "<img class=\'icon-emoji\' src={$image} style=\'width: 20px; height: auto;\'>";
                    }
                }
                return "<img src={$image}>";
            '), $postMsg
            );
            // 处理表情
            $postMsg = preg_replace_callback('/<img src="(.+?)".*?smilieid.*?\/>/', create_function('$matches', '
                $image = WebUtils::getHttpFileName($matches[1]);
                return "<img class=\'icon-emoji\' src={$image} style=\'width: 20px; height: auto;\'>";
            '), $postMsg
            );
            $postMsg = self::__trimPostContent($postMsg);
            $postMsg = htmlspecialchars_decode($postMsg);
        }

        return $postMsg;
    }

    private static function _filterPostMessage($postMsg, $post)
    {
        $msgArr = array('main' => array(), 'quote' => array('who' => '', 'msg' => ''));

        $postMsg = WebUtils::emptyReturnLine($postMsg);
        $postMsg .= '[mobcent_br]<br />';
        // 处理引用内容
        $matches = array();
        preg_match_all('/<blockquote>.*?<font.*?>.*?<font.*?>(.*?)<\/font>.*?<br \/>(.*?)<\/blockquote>/', $postMsg, $matches);
        if (!empty($matches[1]))
        {
            $msgArr['quote']['who'] = $matches[1][0];
        }
        if (!empty($matches[2]))
        {
            $tmpMsg                 = preg_replace('/<br \/>/s', "\r\n", $matches[2][0]);
            $tmpMsg                 = preg_replace('/\[mobcent_audio=(.*?)\]/', '${1}', $tmpMsg);
            $tmpMsg                 = preg_replace('/\[mobcent_url=.*?\]/', '', $tmpMsg);
            $tmpMsg                 = preg_replace('/\[mobcent_br\]/', '', $tmpMsg);
            $tmpMsg                 = self::_emptyPostHtmlContent($tmpMsg);
            $tmpMsg                 = self::__trimPostContent($tmpMsg);
            $msgArr['quote']['msg'] = $tmpMsg;
        }
        if (empty($matches[1]))
        {
            unset($msgArr['quote']);
        }
        $postMsg = preg_replace('/<blockquote>.*?<\/blockquote>/', '', $postMsg);
        $postMsg = preg_replace('/<div class="quote">.*?<\/div>/', '', $postMsg);
        // 处理图片缩略图
        $postMsg = preg_replace('/<style>.*?<\/style>/', '', $postMsg);
        // 处理附件下载
        $postMsg = preg_replace_callback('/<ignore_js_op>.*?<\/ignore_js_op>/', create_function('$matches', '
                $attachment = "";
                $tempAttachUrlMatches = $tempAttachTitleMatches = $tempAttachDescMatches = $tempImgMatches = array();

                // 处理附件下载
                // preg_match("/<strong>(.+?)<\/strong>/", $matches[0], $tempAttachTitleMatches);
                // $title = !empty($tempAttachTitleMatches) ? $tempAttachTitleMatches[1] : "";

                preg_match("/<a.*?href=\"(.+?)\".*?>(.+?)<\/a>/", $matches[0], $tempAttachUrlMatches);
                if (!empty($tempAttachUrlMatches)) {
                    $url = WebUtils::getHttpFileName($tempAttachUrlMatches[1]);
                    preg_match("/<em.*?>(.+?)<\/em>/", $matches[0], $tempAttachDescMatches);
                    $desc = !empty($tempAttachDescMatches) ? $tempAttachDescMatches[1] : "";
                    $title = $tempAttachUrlMatches[2];
                    // if ($desc == "" ) {
                    if ($desc == "" || $title == WebUtils::t("下载附件")) {
                        preg_match("/<div class=\"tip_horn\">.*?<p>(.+?)<\/p>/", $matches[0], $tempAttachDescMatches);
                        $desc = !empty($tempAttachDescMatches) ? $tempAttachDescMatches[1] : "";
                    }
                    $desc != "" && $attachment .= "[mobcent_br]<br />[mobcent_attachment={$url}(title={$title})(desc={$tempAttachDescMatches[1]})]<br />";
                }

                // 处理图片附件
                $imgPattern = "/<img.*?id=\".*?\".*?aid=\"(\d+)\".*?(zoomfile|makefile)=\"(.+?)\".*?\/>/";
                preg_match($imgPattern, $matches[0], $tempImgMatches);
                if (!empty($tempImgMatches)) {
                    $image = WebUtils::getHttpFileName($tempImgMatches[3]);
                    $attachment .= "[mobcent_br]<br />[mobcent_image={$image}(aid={$tempImgMatches[1]})]<br />";
                }

                return $attachment;
            '), $postMsg
        );
        // 处理网络图片
        $postMsg = preg_replace_callback('/<img.*?id[^>]*?(src|file)="(.+?)".*?\/>/', create_function('$matches', '
                $image = WebUtils::getHttpFileName($matches[2]);
                // 处理给手机客户端的表情图片
                if (strpos($image, "/app/data/phiz/") !== false) {
                    if (($key = array_search(basename($image), WebUtils::getMobcentPhizMaps())) !== false) {
                        return $key;
                    }
                }
                return "[mobcent_br]<br />[mobcent_image={$image}]<br />";
            '), $postMsg
        );

        // 处理表情
        $postMsg = preg_replace_callback('/<img src="(.+?)".*?smilieid.*?\/>/', create_function('$matches', '
                $image = WebUtils::getHttpFileName($matches[1]);
                return "[mobcent_phiz={$image}]";
            '), $postMsg
        );

        // 处理文字排版
        $postMsg = str_replace('</div>', '<br />', $postMsg);
        $postMsg = str_replace('<br>', '<br />', $postMsg);
        // 转换格式
        $matches = array();
        preg_match_all('/(.*?)<br \/>/', $postMsg, $matches);
        if (!empty($matches[1]))
        {
            $lastLineIndex = count($matches[1]) - 1;
            $tempMsg       = array('type' => 'text', 'content' => '');
            foreach ($matches[1] as $i => $line)
            {
                $line = self::_emptyPostHtmlContent($line);
                $msg  = array('content' => $line, 'type' => 'text');

                $tempMatches = array();
                preg_match('/\[mobcent_(audio|video|image|url|attachment)=(.*?)\]/', $line, $tempMatches);
                if (!empty($tempMatches))
                {
                    $msg['type'] = $tempMatches[1];
                    switch ($msg['type'])
                    {
                        case 'audio':
                        case 'video':
                            preg_match('/(.+?)mobcent_picurl=(.*)/', $tempMatches[2], $video);
                            if ($video)
                            {
                                $msg['picurl']  = $video[2];
                                $tempMatches[2] = trim($video[1]);
                            }
                        case 'image':
                            $tempMatches2 = array();
                            if (strpos($tempMatches[2], "data:image") === false)
                            {
                                preg_match('/(.+)\(aid=(\d+)\)/', $tempMatches[2], $tempMatches2);
                            }
                            if (!empty($tempMatches2))
                            {
                                $msg['content']          = $tempMatches2[1];
                                $msg['extraInfo']['aid'] = (int)$tempMatches2[2];
                            }
                            else
                            {
                                $msg['content'] = $tempMatches[2];
                            }
                            if ($msg['type'] == 'audio' && strpos($msg['content'], '.mobcent.mp3') !== false)
                            {
                                $msg['content'] = rtrim($msg['content'], '.mobcent.mp3');
                            }
                            // 处理有些用户用了远程附件插件的路径问题
                            if (strpos($msg['content'], '/mobcent/app/web/forum.php') !== false)
                            {
                                $msg['content'] = str_replace('/mobcent/app/web/', '/', $msg['content']);
                            }
                            break;
                        case 'url':
                            $msg['content'] = $msg['extraInfo']['url'] = '';
                            $tempUrlMatches = array();
                            preg_match('/(.+?)\(title=(.+?)\)/', $tempMatches[2], $tempUrlMatches);
                            if (!empty($tempUrlMatches))
                            {
                                $msg['content']          = $tempUrlMatches[2];
                                $msg['extraInfo']['url'] = $tempUrlMatches[1];
                            }
                            break;
                        case 'attachment':
                            $msg['content']    = $msg['extraInfo']['url'] = $msg['extraInfo']['desc'] = '';
                            $tempAttachMatches = array();
                            preg_match('/(.+?)\(title=(.+?)\)\(desc=(.+)\)/', $tempMatches[2], $tempAttachMatches);
                            if (!empty($tempAttachMatches))
                            {
                                $msg['content']           = $tempAttachMatches[2];
                                $msg['extraInfo']['url']  = $tempAttachMatches[1];
                                $msg['extraInfo']['desc'] = $tempAttachMatches[3];
                            }
                            break;
                        default:
                            $msg['content'] = '';
                            break;
                    }
                }
                // 拼接text类型
                if ($msg['type'] == 'text')
                {
                    $line != '[mobcent_br]' && ($tempMsg['content'] .= $line . "\r\n");
                    $tempMsg['content'] = str_replace('[mobcent_br]', '', $tempMsg['content']);
                }
                if ($msg['type'] != 'text' || $i == $lastLineIndex)
                {
                    $tempMsg['content'] = self::__trimPostContent($tempMsg['content']);
                    $tempMsg['content'] != '' && $msgArr['main'][] = $tempMsg;
                    $tempMsg['content'] = '';
                    $msg['type'] != 'text' && $msgArr['main'][] = $msg;
                }
            }
        }

        return $msgArr;
    }

    private static function _emptyPostHtmlContent($postMsg)
    {
        $newPostMsg = $postMsg;

        // 处理引用内容
        $newPostMsg = preg_replace('/<blockquote>.*?<\/blockquote>/', '', $newPostMsg);

        // 处理隐藏内容
        // $newPostMsg = preg_replace('/<div class="showhide">.*?<\/div>/', '', $newPostMsg);
        // $newPostMsg = preg_replace('/<div class="locked">.*?<\/div>/', '', $newPostMsg);
        // 处理附件
        $newPostMsg = preg_replace('/<ignore_js_op>.*?<\/ignore_js_op>/', ' ', $newPostMsg);

        // 处理其他
        $newPostMsg = preg_replace('/<script.*?<\/script>/', ' ', $newPostMsg);
        $newPostMsg = preg_replace('/<!--.*?-->/', '', $newPostMsg);

        $newPostMsg = WebUtils::emptyHtml($newPostMsg);

        return $newPostMsg;
    }

    private static function __trimPostContent($str)
    {
        return trim($str, "\t\n\r\0\x0B");
    }

    public static function getPostSendStatus($type = 'topic', $platType = APP_TYPE_ANDROID)
    {
        // return $platType == APP_TYPE_APPLE ? DzForumPost::STATUS_SEND_FROM_APP_APPLE : DzForumPost::STATUS_SEND_FROM_APP_ANDROID;
        // topic 的15位的apple status代码有冲突，故改为与apple相同
        if ($type == 'topic')
        {
            return setstatus(16, 1);
        }
//        $status = !self::$platType[$platType]?self::$platType[$platType]:self::$platType[APP_TYPE_ANDROID];
        $status = self::$platType[$platType];
        if (empty($status))
        {
            $status = self::$platType[$platType];
        }
        $status     = base_convert($status, 10, 2);
        $status     = sprintf('%03d', $status);
        $poststatus = 0;
        for ($i = 0; $i < 3; $i++)
        {
            $poststatus = setstatus(14 + $i, $status{$i}, $poststatus);
        }

        return $poststatus;
    }

    /**
     * 获取获取设置客户端尾巴
     */
    public static function getMobileSign($status)
    {
        if (!WebUtils::getDzPluginAppbymeAppConfig('mobile_allow_app_sign'))
        {
            return '';
        }

        $postSigns    = array(
            'android' => WebUtils::t(WebUtils::lp('mobile_sign_android')), //来自安卓手机客户端
            'apple'   => WebUtils::t(WebUtils::lp('mobile_sign_ios')), //来自苹果手机客户端
            'weizhan' => WebUtils::t(WebUtils::lp('mobile_sign_wz')),
            'weapp'   => WebUtils::t(WebUtils::lp('mobile_sign_weapp')),
        );
        $postSignText = WebUtils::getDzPluginAppbymeAppConfig('mobile_sign_post');
        $postSignText = WebUtils::emptyReturnLine($postSignText);
        $matches      = array();
        preg_match_all('/\[title_(all|android|apple|weizhan|weapp)\](.*?)\[\/title_\1\]/', $postSignText, $matches, PREG_SET_ORDER);
        foreach ($matches as $matche)
        {
            if ($matche[1] == 'all')
            {
                $postSigns['android'] = $postSigns['apple'] = $postSigns['weizhan'] = $postSignsp['weapp'] = $matche[2];
                break;
            }
            else if ($matche[1] == 'android' || $matche[1] == 'apple' || $matche[1] == 'weizhan' || $matche[1] == 'weapp')
            {
                $postSigns[$matche[1]] = $matche[2];
            }
        }

        $postSign   = '';
        $postStatus = getstatus($status, 14) . getstatus($status, 15) . getstatus($status, 16);
        $postStatus = base_convert($postStatus, 2, 10);
        if ($postStatus == '000')
        {
            foreach (self::$postStatusOld as $key => $postStatus)
            {
                if (getstatus($status, $postStatus))
                {
                    $postSign = $postSigns[$key];
                    break;
                }
            }
        }
        else
        {
            $postStatus = self::$postStatus[$postStatus];
            $postSign   = $postSigns[$postStatus];
        }


        return WebUtils::emptyHtml($postSign);
    }

    /**
     * 转换视频地址为html5地址
     * @param string $video 视频地址
     * @return string
     */
    public function transVideoUrl($url)
    {
        // 转换优酷 .swf
        preg_match('/\:\/\/player\.youku\.com\/player\.php[0-9a-z\/_-]*\/sid\/([0-9a-z_-]+)/i', $url, $tempMatches);
        if (empty($tempMatches))
        {
            preg_match('/^http\:\/\/v\.youku\.com\/v_show\/id_([0-9a-z_-]+)/i', $url, $tempMatches);
        }
        if (!empty($tempMatches))
        {
            return 'http://player.youku.com/embed/' . $tempMatches[1];
        }
        // 转换爱奇艺 .swf
        preg_match("#^http://player\.video\.qiyi\.com/.+?_(\w+?)\.swf#s", $url, $tempMatches);
        if (!empty($tempMatches))
        {
            return "http://www.iqiyi.com/v_{$tempMatches[1]}.html";
        }
        //土豆
        if (stripos($url, 'tudou') !== false)
        {
            $todouApi = 'http://api.tudou.com/v6/tool/repaste?app_key=da7bee320651afb7&format=xml&url=' . $url;

            return (string)simplexml_load_file($todouApi)->itemInfo->outerGPlayerUrl;
        }

//        https://imgcache.qq.com/tencentvideo_v1/playerv3/TPout.swf?max_age=86400&v=20161117&vid=n00221isgwn&auto=0
        //腾讯视频
        preg_match("#^http://static\.video\.qq\.com/TPout\.swf\?vid=(\w+)#s", $url, $tempMatches);
        if (!empty($tempMatches))
        {
            return 'http://v.qq.com/iframe/player.html?vid=' . $tempMatches[1] . '&auto=0';
        }
        //腾讯视频Https
        preg_match("#^https://imgcache\.qq\.com/tencentvideo_v1/playerv3/TPout\.swf\?max_age=(\w+)&v=(\w+)&vid=(\w+)#s", $url, $tempMatches);
        if (!empty($tempMatches))
        {
            return 'http://v.qq.com/iframe/player.html?vid=' . $tempMatches[3] . '&auto=0';
        }

        //56
        preg_match('/^http\:\/\/share\.vrs\.sohu\.com\/my\/v\.swf.*&id=(\d+)/i', $url, $tempMatches);
        if (!empty($tempMatches))
        {
            $url      = 'http://my.tv.sohu.com/play/videonew.do?vid=' . $tempMatches[1];
            $videonew = json_decode(file_get_contents($url), true);

            return 'http://tv.sohu.com/upload/static/share/share_play.html#' . $tempMatches[1] . '_' . $videonew['pid'] . '_0_9001_0';
        }

        return $url;
    }

    /**
     * 解析视频html5地址
     *
     * @param string $video 视频地址
     *
     * @access public
     * @static
     *
     * @return array 解析后的信息
     */
    public static function parseVideoUrl($video)
    {
        $videoInfo = array('type' => 'unkown', 'vid' => '');
        //土豆
        if (strpos($video, 'http://www.tudou.com/programs/view/html5embed.action?') !== false)
        {
            $videoInfo = array('type' => 'tudou', 'vid' => '');
        }
        //搜狐
        if (strpos($video, 'http://tv.sohu.com/upload/static/share/share_play.html#') !== false)
        {
            $videoInfo = array('type' => 'sohu', 'vid' => '');
        }
        //腾讯
        if (strpos($video, 'http://v.qq.com/iframe/player.html?vid=') !== false)
        {
            $videoInfo = array('type' => 'tx', 'vid' => '');
        }
        //优酷
        if (strpos($video, 'http://player.youku.com/embed/') !== false)
        {
            $videoInfo = array('type' => 'youku', 'vid' => '');
        }

        return $videoInfo;
    }

    /*     *
     * 帖子内容页需要返回的数据
     *
     * @param int $pid    帖子id.
     * @param int $column 评分栏目显示几个.
     * @param int $row    显示几行评分数据.
     *
     * @return array $tabList 帖子内容页需要返回的数据.
     */

    public static function topicRateList($pid, $column = 2, $row = 3, $forumKey = '')
    {
        global $_G;
        $postlist = $postcache = array();

        // 2.5是引用传值，3.1是直接返回。
        // $ratelogs = C::t('forum_ratelog')->fetch_postrate_by_pid(array($pid), $postlist, $postcache, $_G['setting']['ratelogrecord']);

        list($ratelogs, $postlist, $postcache) = self::fetch_postrate_by_pid(array($pid), $postlist, $postcache, $_G['setting']['ratelogrecord']);

        if (empty($postlist))
        {
            return array(
                'padding' => '',
            );
        }

        // 评分人数和评分栏目的控制
        $totalRate = count($postlist[$pid]['totalrate']);
        $rateItem  = $postlist[$pid]['ratelogextcredits'];
        ksort($rateItem);

        $i            = 1;
        $t["field$i"] = WebUtils::t(WebUtils::lp('topic_rate_list_join_num')); //参与人数
        $i++;
        $rateItem = array_slice($rateItem, 0, $column, true);
        foreach ($rateItem as $id => $score)
        {
            $t["field$i"] = (string)$_G['setting']['extcredits'][$id]['title'];
            $i++;
        }
        if ($i == 3)
        {
            $t['field3'] = '';
            $i++;
        }
        // $t["field$i"] = (string)WebUtils::t('理由');
        // 评分具体内容
        $rate                      = array();
        $postlist[$pid]['ratelog'] = array_slice($postlist[$pid]['ratelog'], 0, $row, true);
        foreach ($postlist[$pid]['ratelog'] as $uid => $ratelog)
        {
            $i               = 1;
            $temp["field$i"] = (string)$ratelog['username'];
            $i++;
            foreach ($rateItem as $id => $score)
            {
                $temp["field$i"] = isset($ratelog['score'][$id]) ?
                    ($ratelog['score'][$id] < 0 ? (string)$ratelog['score'][$id] : '+' . $ratelog['score'][$id]) :
                    '';
                $i++;
            }
            if ($i == 3)
            {
                $temp['field3'] = '';
                $i++;
            }
            // $temp["field$i"] = (string)$ratelog['reason'];
            $rate[] = $temp;
        }

        // 评分总数
        $i                = 1;
        $total            = array();
        $total["field$i"] = (string)$totalRate;
        $i++;
        foreach ($rateItem as $id => $score)
        {
            $total["field$i"] = (string)$score;
            $i++;
        }
        if ($i == 3)
        {
            $total['field3'] = '';
            $i++;
        }
        // $total["field$i"] = '';

        $tabList               = array();
        $tabList['head']       = $t;
        $tabList['total']      = $total;
        $tabList['body']       = $rate;
        $tabList['showAllUrl'] = WebUtils::createUrl_oldVersion('forum/ratelistview', array('tid' => $tid, 'pid' => $pid, 'fourmKey' => $forumKey));

        return $tabList;
    }

    // 全部评分列表
    public static function rateList($tid, $pid, $page = 1, $pageSize = 3)
    {
        global $_G;
        $loglist = $logcount = $res = array();
        $post    = C::t('forum_post')->fetch('tid:' . $tid, $pid);
        if ($post['invisible'] != 0)
        {
            $post = array();
        }

        if ($post)
        {
            $loglist = C::t('forum_ratelog')->fetch_all_by_pid($pid);
        }

        if ($_G['setting']['bannedmessages'])
        {
            $postmember      = getuserbyuid($post['authorid']);
            $post['groupid'] = $postmember['groupid'];
        }

        foreach ($loglist as $k => $log)
        {
            $logcount[$log['extcredits']] += $log['score'];
            // $log['dateline'] = dgmdate($log['dateline'], 'u');
            $log['dateline'] = $log['dateline'];
            $log['score']    = $log['score'] > 0 ? '+' . $log['score'] : $log['score'];
            $log['reason']   = dhtmlspecialchars($log['reason']);
            $loglist[$k]     = $log;
        }

        $res['loglist']  = $loglist;
        $res['logcount'] = $logcount;

        return $res;
    }

    // 因为版本的差异，所以使用3.1的方式来取出数据　Copy file from《table_forum_ratelog.php》
    public static function fetch_postrate_by_pid($pids, $postlist, $postcache, $ratelogrecord)
    {
        $pids     = array_map('intval', (array)$pids);
        $query    = DB::query("SELECT * FROM " . DB::table('forum_ratelog') . " WHERE pid IN (" . dimplode($pids) . ") ORDER BY dateline DESC");
        $ratelogs = array();
        while ($ratelog = DB::fetch($query))
        {
            if (count($postlist[$ratelog['pid']]['ratelog']) < $ratelogrecord)
            {
                $ratelogs[$ratelog['pid']][$ratelog['uid']]['username'] = $ratelog['username'];
                //加上评论者id
                $ratelogs[$ratelog['pid']][$ratelog['uid']]['uid']                           = $ratelog['uid'];
                $ratelogs[$ratelog['pid']][$ratelog['uid']]['score'][$ratelog['extcredits']] += $ratelog['score'];
                empty($ratelogs[$ratelog['pid']][$ratelog['uid']]['reason']) && $ratelogs[$ratelog['pid']][$ratelog['uid']]['reason'] = dhtmlspecialchars($ratelog['reason']);
                $postlist[$ratelog['pid']]['ratelog'][$ratelog['uid']] = $ratelogs[$ratelog['pid']][$ratelog['uid']];
            }

            $postcache[$ratelog['pid']]['rate']['ratelogs']                           = $postlist[$ratelog['pid']]['ratelog'];
            $postcache[$ratelog['pid']]['rate']['extcredits'][$ratelog['extcredits']] = $postlist[$ratelog['pid']]['ratelogextcredits'][$ratelog['extcredits']] += $ratelog['score'];
            if (!$postlist[$ratelog['pid']]['totalrate'] || !in_array($ratelog['uid'], $postlist[$ratelog['pid']]['totalrate']))
            {
                $postlist[$ratelog['pid']]['totalrate'][] = $ratelog['uid'];
            }
            $postcache[$ratelog['pid']]['rate']['totalrate'] = $postlist[$ratelog['pid']]['totalrate'];
        }

        return array($ratelogs, $postlist, $postcache);
    }

    /**
     * 获得在pc访问的url
     *
     * @param mixed $souceId 源的ID
     * @param string $sourceType 源的类型（topic|news）
     *
     * @return string 访问地址
     */
    public static function getSourceWebUrl($souceId, $sourceType = 'topic')
    {
        $sourceWebUrl = '';
        switch ($sourceType)
        {
            case 'topic':
                $sourceWebUrl = WebUtils::getHttpFileName('forum.php?mod=viewthread&tid=' . $souceId);
                break;
            case 'news':
                $sourceWebUrl = WebUtils::getHttpFileName('portal.php?mod=view&aid=' . $souceId);
                break;
            default:
                break;
        }

        return $sourceWebUrl;
    }

    /**
     * 更新用户名至点赞表
     *
     * @author NaiXiaoXin
     * @param int $tid 帖子ID
     * @return boolean
     */
    public static function updateUsernameByRecommend($tid = '')
    {
        if (empty($tid))
        {
            return false;
        }
        global $_G;
        DB::query(" UPDATE %t p,%t  pp  SET pp.username = p.username WHERE pp.username = '1' AND p.uid=pp.recommenduid AND pp.tid=%d", array('common_member', 'forum_memberrecommend', $tid));
        if ($_G['setting']['membersplit'] == '1')
        {
            DB::query(" UPDATE %t p,%t  pp  SET pp.username = p.username WHERE pp.username = '1' AND p.uid=pp.recommenduid AND pp.tid=%d", array('common_member_archive', 'forum_memberrecommend', $tid));
        }

        return true;
    }

    /**
     *    打开 url
     *
     *    1 参数 url 地址
     *    2 参数 header 引用
     *
     *    返回值 字符串
     * */
    function url($url = '', &$header = array())
    {
        //$timeout = $this->timeout;
        $timeout = '10';
        $accept  = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1478.0 Safari/537.36';

        $content = '';


        if (function_exists('curl_init'))
        {
            // curl 的
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 86400);
            curl_setopt($curl, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
            curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, $accept);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            $content = curl_exec($curl);
            curl_close($curl);
        }
        elseif (function_exists('file_get_contents'))
        {

            // file_get_contents
            $head[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
            $head[] = "User-Agent: $accept";
            $head[] = "Accept-Language: zh-CN,zh;q=0.5";
            $head   = implode("\r\n", $head) . "\r\n\r\n";

            $context['http'] = array(
                'method'  => "GET",
                'header'  => $head,
                'timeout' => $timeout,
            );

            $content = @file_get_contents($url, false, stream_context_create($context));
            if ($gzip = $this->gzip($content))
            {
                $content = $gzip;
            }
            $content = implode("\r\n", $http_response_header) . "\r\n\r\n" . $content;
        }
        elseif (function_exists('fsockopen') || function_exists('pfsockopen'))
        {
            // fsockopen or pfsockopen
            $url = parse_url($url);
            if (empty($url['host']))
            {
                return false;
            }
            $url['port'] = empty($url['port']) ? 80 : $url['port'];

            $host = $url['host'];
            $host .= $url['port'] == 80 ? '' : ':' . $port;

            $get = '';
            $get .= empty($url['path']) ? '/' : $url['path'];
            $get .= empty($url['query']) ? '' : '?' . $url['query'];


            $head[] = "GET $get HTTP/1.1";
            $head[] = "Host: $host";
            $head[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
            $head[] = "User-Agent: $accept";
            $head[] = "Accept-Language: zh-CN,zh;q=0.5";
            $head[] = "Connection: Close";
            $head   = implode("\r\n", $head) . "\r\n\r\n";

            $function = function_exists('fsockopen') ? 'fsockopen' : 'pfsockopen';
            if (!$fp = @$function($url['host'], $url['port'], $errno, $errstr, $timeout))
            {
                return false;
            }

            if (!fputs($fp, $head))
            {
                return false;
            }

            while (!feof($fp))
            {
                $content .= fgets($fp, 1024);
            }
            fclose($fp);

            if ($gzip = $this->gzip($content))
            {
                $content = $gzip;
            }

            $content = str_replace("\r\n", "\n", $content);
            $content = explode("\n\n", $content, 2);

            if (!empty($content[1]) && !strpos($content[0], "\nContent-Length:"))
            {
                $content[1] = preg_replace('/^[0-9a-z\r\n]*(.+?)[0-9\r\n]*$/i', '$1', $content[1]);
            }
            $content = implode("\n\n", $content);
        }

        // 分割 header  body
        $content = str_replace("\r\n", "\n", $content);
        $content = explode("\n\n", $content, 2);

        // 解析 header
        $header = array();
        foreach (explode("\n", $content[0]) as $k => $v)
        {
            if ($v)
            {
                $v = explode(':', $v, 2);
                if (isset($v[1]))
                {
                    if (substr($v[1], 0, 1) == ' ')
                    {
                        $v[1] = substr($v[1], 1);
                    }
                    $header[trim($v[0])] = $v[1];
                }
                elseif (empty($r['status']) && preg_match('/^(HTTP|GET|POST)/', $v[0]))
                {
                    $header['status'] = $v[0];
                }
                else
                {
                    $header[] = $v[0];
                }
            }
        }


        $body = empty($content[1]) ? '' : $content[1];

        return $body;
    }

    /**
     *    解析数组
     *
     *    1 参数 str
     *    2 参数 arr 引用
     *
     *    返回值 无
     * */
    function parse_str($str, &$arr)
    {
        parse_str($str, $arr);
        if (get_magic_quotes_gpc())
        {
            $arr = self::stripslashes_array($arr);
        }
    }

    /**
     *    stripslashes 取消转义 数组
     *
     *    1 参数 输入数组
     *
     *    返回值 处理后的数组
     * */
    function stripslashes_array($value)
    {
        if (is_array($value))
        {
            $value = array_map(array($this, __FUNCTION__), $value);
        }
        elseif (is_object($value))
        {
            $vars = get_object_vars($value);
            foreach ($vars as $key => $data)
            {
                $value->{$key} = stripslashes_array($data);
            }
        }
        else
        {
            $value = stripslashes($value);
        }

        return $value;
    }

    public static function nxxTopicRateList($pid, $forumKey = '')
    {
        global $_G;
        $postlist = $postcache = array();

        list($ratelogs, $postlist, $postcache) = self::fetch_postrate_by_pid(array($pid), $postlist, $postcache, $_G['setting']['ratelogrecord']);
        if (empty($postlist))
        {
            return '';
        }
        $totalRate = count($postlist[$pid]['totalrate']);  //人数
        $rateItem  = $postlist[$pid]['ratelogextcredits'];
        ksort($rateItem);
        foreach ($rateItem as $id => $score)
        {
            $temp              = array();
            $temp['info']      = $_G['setting']['extcredits'][$id]['title'];
            $temp['value']     = $score;
            $return['score'][] = $temp;
        }
        foreach ($postlist[$pid]['ratelog'] as $uid => $ratelog)
        {
            $temp                 = array();
            $temp['uid']          = $uid;
            $temp['userName']     = $ratelog['username'];
            $temp['userIcon']     = UserUtils::getUserAvatar($uid);
            $return['userList'][] = $temp;
        }
        $return['userNumber'] = $totalRate;
        $return['showAllUrl'] = WebUtils::createUrl_oldVersion('forum/ratelistview', array('tid' => $tid, 'pid' => $pid, 'fourmKey' => $forumKey));

        return $return;
    }

    /**
     * 校验用户是否有可编辑权限
     *
     * @param array $postInfo 从数据库里取出的帖子信息
     * @param bool $show 是否显示消息
     */
    public function checkEdit($postInfo = array(), $show = true)
    {
        global $_G;
        $res['rs']    = 1;
        $isfirstpost  = $postInfo['first'] ? 1 : 0;
        $isorigauthor = $_G['uid'] && $_G['uid'] == $postInfo['authorid'];
        if (empty($postInfo))
        {
            if ($show)
            {
                $res['rs']  = 0;
                $res['msg'] = 'post_not_found';

                return $res;
            }
            else
            {
                return false;
            }
        }
        elseif (!(($_G['forum']['alloweditpost'] || $postInfo['invisible'] == -3) && $isorigauthor))
        {
            if ($show)
            {
                $res['rs']    = 0;
                $res['msg']   = 'post_not_my_thread';
                $res['value'] = array('{grouptitle}' => $_G['group']['grouptitle'],);

                return $res;
            }
            else
            {
                return false;
            }
        }
        elseif ($isorigauthor && !$_G['forum']['ismoderator'] && $orig['invisible'] != -3)
        {
            $alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $special + 1); //note 指定类型主题是否受编辑时间限制
            if (!$alloweditpost_status && $_G['group']['edittimelimit'] && TIMESTAMP - $postInfo['dateline'] > $_G['group']['edittimelimit'] * 60)
            {
                if ($show)
                {
                    $res['rs']    = 0;
                    $res['msg']   = 'post_edit_timelimit';
                    $res['value'] = array('{edittimelimit}' => $_G['group']['edittimelimit']);

                    return $res;
                }
                else
                {
                    return false;
                }
            }
        }

        return $show ? $res : true;
    }

    //获得是否关注对应的板块
    public function getFavStatus($uid, $fid)
    {
        if (empty($uid))
        {
            return 0;
        }
        $temp = C::t('home_favorite')->fetch_by_id_idtype($fid, 'fid', $uid);

        return $temp ? 1 : 0;
    }

    // 获取公告列表
    public static function _getAnnouncementList($sort)
    {
        $announcementShow = WebUtils::getDzPluginAppbymeAppConfig('forum_announcement_show');
        $announcementShow = dunserialize($announcementShow);
        !is_array($announcementShow) && $announcementShow = array();

        $sortMaps = array('new' => 2, 'marrow' => 3, 'top' => 4);
        $index    = isset($sortMaps[$sort]) ? $sortMaps[$sort] : 1;
        if (in_array(0, $announcementShow) || !in_array($index, $announcementShow))
        {
            return array();
        }

        $list          = array();
        $announcements = ForumUtils::getAnnouncementList();
        foreach ($announcements as $announcement)
        {
            $list[] = array(
                'announce_id' => $announcement['id'],
                'author'      => $announcement['author'],
                'board_id'    => 0,
                'forum_id'    => 0,
                'start_date'  => $announcement['starttime'] . '000',
                'title'       => WebUtils::emptyHtml($announcement['subject']),
                'redirect'    => $announcement['type'] == DzForumAnnouncement::TYPE_URL ? $announcement['message'] : '',
            );
        }

        return $list;
    }

    //是否是话题贴
    public static function TopicPost($tid)
    {
        $topic = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT pid, ti_id
            FROM %t
            WHERE pid=%d
            ', array('appbyme_tpctopost', $tid)
        );

        return $topic;
    }

    //话题贴标题
    public static function getTopicTitle($ti_id)
    {
        static $topicList;
        if (!$topicList[$ti_id])
        {
            $topicList[$ti_id] = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE ti_id = %d
        ', array('appbyme_topic_items', $ti_id));

        }

        return $topicList[$ti_id]['ti_title'];
    }

    //回帖仅作者可见
    public static function getOnlyAuthor()
    {
        return "<div class='only-author'>" . WebUtils::t('此帖仅作者可见') . "</div>";
    }
}
