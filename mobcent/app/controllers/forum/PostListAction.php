<?php

/**
 * 主题详情接口
 *
 * @author NaiXiaoXin
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME'))
{
    exit('Access Denied');
}

//Mobcent::setErrors();

class PostListAction extends MobcentAction
{

    public function run($topicId, $authorId = 0, $order = 0, $page = 1, $pageSize = 10, $bbcode = 0)
    {
        $tid = (int)$topicId;
        $page = intval($page); //解决全等问题
        global $_G;
        if (!empty($_G['uid']))
        {
            $res = $this->getResult(array('tid' => $tid, 'page' => $page, 'pageSize' => $pageSize, 'order' => $order, 'authorId' => $authorId, 'bbcode' => $bbcode));
            echo WebUtils::outputWebApi($res, '', true);
        }
        $key = "{$this->id}_{$_G['groupid']}_{$tid}_{$authorId}_{$order}_{$page}_{$pageSize}";

        $this->runWithCache($key, array('tid' => $tid, 'page' => $page, 'pageSize' => $pageSize, 'order' => $order, 'authorId' => $authorId, 'bbcode' => $bbcode));

        // Mobcent::dumpSql();
    }

    protected function getCacheInfo()
    {
        return Yii::app()->params['mobcent']['cache']['postList'];
    }

    protected function getResult($params = array())
    {
        Mobcent::import(sprintf(
            '%s/forum_viewthread_%s.php', MOBCENT_APP_ROOT . '/components/discuz/forum', MobcentDiscuz::getMobcentDiscuzVersion()
        ));
        extract($params);
        $res = WebUtils::initWebApiArray();
        $res = array_merge(array('rs' => 1, 'errcode' => ''), $res);
        $app = Yii::app()->getController()->mobcentDiscuzApp;



        $app->loadForum(null, $tid);
        global $_G;
        $forumInfo = $_G['forum'];
        $topic = $_G['forum_thread'];


        if (empty($topic))
        {
            return $this->_makeErrorInfo($res, 'thread_nonexistence');
        }

        // 该主题是由别的版块移动过来的
        if ($topic['closed'] > 1)
        {
            $tid = $topic['closed'];
            $topic['tid'] = $tid;
        }

        // 检查权限
        global $_G;
        if (empty($_G['forum']['allowview']))
        {
            if (!$_G['forum']['viewperm'] && !$_G['group']['readaccess'])
            {
                return $this->_makeErrorInfo($res, 'group_nopermission', array(
                    '{grouptitle}' => $_G['group']['grouptitle'],
                ));
            } elseif ($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm']))
            {
                $msg = mobcent_showmessagenoperm('viewperm', $_G['fid']);
                return $this->_makeErrorInfo($res, $msg['message'], $msg['params']);
            }
        } elseif ($_G['forum']['allowview'] == -1)
        {
            return $this->_makeErrorInfo($res, 'forum_access_view_disallow');
        }

        if ($_G['forum']['formulaperm'])
        {
            $msg = mobcent_formulaperm($_G['forum']['formulaperm']);
            if ($msg['message'] != '')
            {
                return $this->_makeErrorInfo($res, $msg['message'], $msg['params']);
            }
        }

        // if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
        // dheader("Location: $_G[siteurl]forum.php?mod=forumdisplay&fid=$_G[fid]");
        // }
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
                // dheader("Location: $_G[siteurl]forum.php?mod=forumdisplay&fid=$_G[fid]");
            }
        }

        if ($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid'])
        {
            return $this->_makeErrorInfo($res, 'thread_nopermission', array(
                '{readperm}' => $_G['forum_thread']['readperm'],
            ));
        }

        // 编辑权限相关 start
        if ($_G['forum']['alloweditpost'] && $_G['uid'])
        {
            $alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $_G['forum_thread']['special'] + 1);
            if (!$alloweditpost_status)
            {
                $edittimelimit = $_G['group']['edittimelimit'] * 60;
            }
        }
        $editPerm = array();
        $editPerm['alloweditpost_status'] = $alloweditpost_status;
        $editPerm['edittimelimit'] = $edittimelimit;
        $editPerm['bbcode'] = $bbcode;
        // edit end

        $params = array('editPerm' => $editPerm);
        if ($page <= 1 && ($authorId == 0 || $authorId == $topic['authorid']))
        {
            $res['topic'] = $this->_getTopicInfo($topic, $params);
            if (empty($res['topic']))
            {
                return $this->_makeErrorInfo($res, 'post_not_found');
            }
        }

        $res = $this->_getPostInfos($res, $topic, $page, $pageSize, $order, $authorId, $params);

        $res['forumName'] = WebUtils::emptyHtml($forumInfo['name']);
        $res['boardId'] = (int)$forumInfo['fid'];
        //$res['forumTopicUrl'] = Yii::app()->getController()->dzRootUrl . "/forum.php?mod=viewthread&tid=" . $tid;
        //$res['forumTopicUrl'] =  Yii::app()->createAbsoluteUrl('webapp/share',array('tid'=>$tid));
        $res['forumTopicUrl'] = (string)ForumUtils::getSourceWebUrl($tid, 'topic');
        $res['img_url'] = '';
        $res['icon_url'] = '';
        viewthread_updateviews($_G['forum_thread']['threadtableid']);
        // print_r($res);die;
        return $res;
    }

    private function _makeErrorInfo($res, $message, $params = array())
    {
        return WebUtils::makeErrorInfo_oldVersion($res, $message, $params);
    }

    private function _getTopicInfo($topic, $params = array())
    {
        extract($params);
        $topicInfo = array();
        $tid = (int)$topic['tid'];
        if (!empty($topic))
        {
            $post = ForumUtils::getTopicPostInfo($tid);
            if (!empty($post))
            {
                if ($editPerm['bbcode'])
                {
                    $message = ForumUtils::getHtmlContent($tid, $post);
                    $topicInfo['html-content'] = $message;
                } else {
                    $content = ForumUtils::getTopicContent($tid, $post);
                }
                global $_G;
                $topicInfo['topic_id'] = $tid;
                $topicInfo['title'] = $topic['subject'];
                $topicInfo['title'] = WebUtils::emptyHtml($topicInfo['title']);

                // 修正帖子查看数
                if (isset($_G['forum_thread']['views']) &&
                    $_G['forum_thread']['tid'] == $topic['tid'] &&
                    $_G['forum_thread']['views'] > $topic['views']
                )
                {
                    $topic['views'] = $_G['forum_thread']['views'];
                }

                $topicInfo['type'] = ForumUtils::getTopicType($topic);
                $topicInfo['special'] = intval($topic['special']);
                $topicInfo['sortId'] = (int)$topic['sortid'];
                $topicInfo['user_id'] = ForumUtils::isAnonymousPost($tid, $post) ? 0 : (int)$topic['authorid'];
                $topicInfo['user_nick_name'] = ForumUtils::isAnonymousPost($tid, $post) ? $this->_getAnonymoustext() : $topic['author'];
                $topicInfo['replies'] = (int)$topic['replies'];
                $topicInfo['hits'] = (int)($topic['replies'] > $topic['views'] ? $topic['replies'] : $topic['views']);
                $topicInfo['essence'] = ForumUtils::isMarrowTopic($topic) ? 1 : 0;
                $topicInfo['vote'] = ForumUtils::isVoteTopic($topic) ? 1 : 0;
                $topicInfo['hot'] = ForumUtils::isHotTopic($topic) ? 1 : 0;
                $topicInfo['price'] = $_G['forum_thread']['price'] ? 1 : 0;
                $topicInfo['top'] = ForumUtils::isTopTopic($topic) ? 1 : 0;
                $topicInfo['is_favor'] = ForumUtils::isFavoriteTopic($_G['uid'], $tid) ? 1 : 0;
                $topicInfo['create_date'] = $topic['dateline'] . '000';
                $topicInfo['icon'] = UserUtils::getUserAvatar($topicInfo['user_id']);
                $topicInfo['level'] = $this->_getUserLevel($topicInfo['user_id']);
                $topicInfo['userTitle'] = UserUtils::getUserTitle($topicInfo['user_id']);
                $topicInfo['userColor'] = UserUtils::getUserColor($topicInfo['user_id']);
                $topicInfo['isFollow'] = UserUtils::isFollow($_G['uid'], $topicInfo['user_id']) ? 1 : 0;
                $topicInfo['zanList'] = (array)DzForumThread::getZanList($tid);
                //话题贴返回
                $isTopic = ForumUtils::TopicPost($tid);
                if(empty($isTopic)) {
                    $topicInfo['isTopic']    = 0;
                } else {
                    $topicInfo['isTopic']    = 1;
                    $topicInfo['topicInfo']['topicTitle'] = ForumUtils::getTopicTitle($isTopic['ti_id']);
                }
                $postContent = $this->_getPostContent($content);
                // 处理屏蔽
                if ($_G['forum']['ismoderator'] || !getstatus($post['status'] ,1))
                {
                    $sort = $this->_getSortInfo($topic);
                    if (!empty($sort['summary']))
                    {
                        array_unshift($postContent, $this->_transSortInfo($sort));
                        $topicInfo['sortContent'] = $this->_getSortInfoForJson($topic);
                    }
                }

                $topicInfo['content'] = $postContent;
                if ($topicInfo['type'] == 'normal')
                {
                    $this->_isComplexContent($topicInfo['content']) && $topicInfo['type'] = 'normal_complex';
                }

                $topicInfo['poll_info'] = ForumUtils::isVoteTopic($topic) ? $this->_getVoteInfo($topic) : WebUtils::getWebApiResPadding();
                $topicInfo['activityInfo'] = ForumUtils::isActivityTopic($topic) ? TopicUtils::getActivityInfo($topic) : WebUtils::getWebApiResPadding();
                $topicInfo['location'] = ForumUtils::getTopicLocation($tid);

                // 主题管理面板编辑 start
                $userMember = $this->_getUserInfoByAuthorid($post['authorid']);
                $userMember = array_merge($userMember, $post);
                $params = array('editPerm' => $editPerm, 'userMember' => $userMember);
                // end
                $topicInfo['delThread'] = ForumUtils::checkEdit(array_merge($post, array('adminid' => $userMember['adminid'])), false) ? true : false;

                $manageItems = ForumUtils::getPostManagePanel($params);

                foreach ($manageItems['topic'] as $key => $item)
                {
                    $item['type'] = $item['action'];
                    if ($item['action'] == 'edit')
                    {
                        $item['action'] = WebUtils::getHttpFileName("forum.php?mod=post&action=edit&fid=$post[fid]&tid=$tid&pid=$post[pid]");
                    } else
                    {
                        $item['action'] = WebUtils::createUrl_oldVersion('forum/topicadminview', array('fid' => $post['fid'], 'tid' => $tid, 'pid' => $post['pid'], 'act' => $item['action'], 'type' => 'topic'));
                    }
                    $manageItems['topic'][$key] = $item;
                }
                global $_G;
                if ($_G['group']['allowbanuser'] || $_G['group']['allowbanvisituser'])
                {
                    $temp = array();
                    $temp['type'] = 'edituser';
                    $temp['action'] = WebUtils::createUrl_oldVersion('user/useradmincp', array('uid' => $post['authorid']));
                    $temp['title'] = WebUtils::t('禁言/禁止访问');
                    $manageItems['topic'][] = $temp;
                }
                if ($topicInfo['delThread'])
                {
                    $temp = array();
                    $temp['type'] = 'delthread';
                    $temp['action'] = WebUtils::createUrl_oldVersion('forum/topicadminex', array('topicId' => $post['tid'], 'postId' => $post['pid']));
                    $temp['title'] = WebUtils::t('删帖');
                    $manageItems['topic'][] = $temp;
                }
                $extraItems = ForumUtils::getPostExtraPanel();
                foreach ($extraItems['topic'] as $key => $item)
                {
                    $item['extParams'] = array('beforeAction' => '');
                    $item['type'] = $item['action'];
                    $item['action'] = '';
                    if ($item['type'] == 'rate')
                    {
                        $item['action'] = WebUtils::createUrl_oldVersion('forum/topicrate', array('tid' => $tid, 'pid' => $post['pid'], 'type' => 'view', 'forumKey' => $_GET['forumKey']));
                        $item['extParams']['beforeAction'] = WebUtils::createUrl_oldVersion('forum/topicrate', array('tid' => $tid, 'pid' => $post['pid'], 'type' => 'check', 'forumKey' => $_GET['forumKey']));
                    } elseif ($item['type'] == 'support')
                    {
                        $item['action'] = WebUtils::createUrl_oldVersion('forum/support', array('tid' => $tid, 'pid' => $post['pid'], 'type' => 'thread'));
                        $item['extParams']['recommendAdd'] = (int)$_G['thread']['recommend_add'];
                        $recommendAdd = DzSupportInfo::getSupportTopicByUidAndTid($_G['uid'], $tid);
                        $item['extParams']['isHasRecommendAdd'] = !empty($recommendAdd) ? 1 : 0;
                    }
                    $extraItems['topic'][$key] = $item;
                }

                $topicInfo['managePanel'] = $manageItems['topic'];
                $topicInfo['extraPanel'] = $extraItems['topic'];
                $topicInfo['mobileSign'] = ForumUtils::getMobileSign($post['status']);

                // if (empty($topic['author']) && !empty($topic['authorid'])){
                //     $topicInfo['reply_status'] = -1;
                // } else if(empty($topic['author']) && empty($topic['authorid'])){
                //     $topicInfo['reply_status'] = 0;
                // } else {
                //     $topicInfo['reply_status'] = 1;
                // }
                $topicInfo['status'] = 1;
                $topicInfo['reply_status'] = 1;
                $topicInfo['flag'] = 0;
                $topicInfo['gender'] = UserUtils::getUserGender($topicInfo['user_id']);;
                $topicInfo['reply_posts_id'] = (int)$post['pid'];
                $topicInfo['rateList'] = ForumUtils::topicRateList($post['pid'], 2, 3, $_GET['forumKey']);
                $tempnxx = array();
                $tempnxx = ForumUtils::nxxTopicRateList($post['pid'], $_GET['forumKey']);
                if (!empty($tempnxx))
                {
                    $topicInfo['reward'] = $tempnxx;
                }
                global $_G;
                $topicInfo['relateItem'] = $this->getrelateitem($post['tags'], $post['tid'], $_G['setting']['relatenum'], $_G['setting']['relatetime']);

                // $topicInfo['reward'] = ForumUtils::nxxTopicRateList($post['pid']);
            }
        }
        return $topicInfo;
    }
    // 如果内容有复杂类型，要让客户端不能编辑
    private function _isComplexContent($content)
    {
        $complex = false;
        foreach ($content as $line)
        {
            // if ($line['type'] != 0 && !($line['type'] == 1 && $line['aid'] == 0)) {
            // 暂时也把有图片附件屏蔽
            if ($line['type'] != 0)
            {
                $complex = true;
                break;
            }
        }
        return $complex;
    }

    private function _getVoteInfo($tid)
    {
        $vote = array();

        $voteInfo = ForumUtils::getTopicVoteInfo($tid);
        $vote['deadline'] = $voteInfo['expiration'];
         $vote['is_visible'] = $voteInfo['visiblepoll'] ? 1 : 0;
        // 投票后暂时永远结果可见xushaowei，老大让改的，

        //$vote['is_visible'] = 1;
        $vote['voters'] = (int)$voteInfo['voterscount'];
        $vote['type'] = (int)$voteInfo['maxchoices'];
        $vote['poll_status'] = $voteInfo['status'];
        $vote['poll_id'] = array(0);
        $vote['isimage'] = $voteInfo['isimage'];

        foreach ($voteInfo['options'] as $option)
        {
            $voteItem['name'] = $option['polloption'];
            $voteItem['poll_item_id'] = (int)$option['polloptionid'];
            $voteItem['total_num'] = (int)$option['votes'];
            $voteItem['percent'] = $option['percent'] . '%';
            if($vote['isimage']) {
                $voteItem['image'] = WebUtils::getHttpFileName($option['imginfo']['small']);
            }
            $vote['poll_item_list'][] = $voteItem;
        }
        return $vote;
    }

    // 获取分类信息
    private function _getSortInfo($thread)
    {
        // from forum_viewthread template
        $sort = array('title' => '', 'summary' => '');

        global $_G;
        $threadsort = $thread['sortid'] && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ? 1 : 0;
        if ($threadsort)
        {
            require_once libfile('function/threadsort');
            $threadsortshow = threadsortshow($thread['sortid'], $_G['tid']);
        }

        if ($threadsort && $threadsortshow)
        {
            if ($threadsortshow['typetemplate'])
            {
                //echo $threadsortshow['typetemplate'];
                $sort = $this->_getSortByTemplate($threadsortshow);
            } elseif ($threadsortshow['optionlist'])
            {
                if ($threadsortshow['optionlist'] == 'expire')
                {
                    $sort['summary'] = WebUtils::t("该信息已经过期\n");
                } else
                {
                    $sort['title'] = $_G['forum']['threadsorts']['types'][$_G['forum_thread']['sortid']];
                    if (is_array($threadsortshow['optionlist']))
                    {
                        foreach ($threadsortshow['optionlist'] as $option)
                        {
                            if ($option['type'] != 'info')
                            {
                                $sort['summary'] .= sprintf("%s :\t", $option['title']);
                                if ($option['value'] || ($option['type'] == 'number' && $option['value'] !== ''))
                                {
                                    $matches = array();
                                    preg_match('/action=protectsort.+?optionid=(\d+)/', $option['value'], $matches);
                                    if (!empty($matches))
                                    {
                                        $option['value'] = $this->_getProtectSortOptionListValue($_G['tid'], $matches[1]);
                                    } else
                                    {
                                        $option['value'] = WebUtils::emptyHtml($option['value']);
                                    }
                                    $sort['summary'] .= $option['value'] . $option['unit'];
                                }
                                $sort['summary'] .= "\n";
                            }
                        }
                    }
                }
            }
        }

        return $sort;
    }

    // 获取分类信息
    private function _getSortInfoForJson($thread)
    {
        // from forum_viewthread template
        $sort = array('title' => '', 'summary' => array());

        global $_G;
        $threadsort = $thread['sortid'] && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ? 1 : 0;
        if ($threadsort)
        {
            require_once libfile('function/threadsort');
            $threadsortshow = threadsortshow($thread['sortid'], $_G['tid']);
        }
        if ($threadsort && $threadsortshow)
        {
            if ($threadsortshow['optionlist'])
            {
                if ($threadsortshow['optionlist'] == 'expire')
                {
                    $sort['summary'] = WebUtils::t("该信息已经过期\n");
                } else
                {
                    $sort['title'] = $_G['forum']['threadsorts']['types'][$_G['forum_thread']['sortid']];
                    if (is_array($threadsortshow['optionlist']))
                    {
                        foreach ($threadsortshow['optionlist'] as $option)
                        {
                            //                            var_dump($option);
                            if ($option['type'] != 'info')
                            {
                                //                                $sort['summary'] .= sprintf("%s :\t", $option['title']);
                                //                                if ($option['value'] || ($option['type'] == 'number' && $option['value'] !== '')) {
                                //                                    $matches = array();
                                //                                    preg_match('/action=protectsort.+?optionid=(\d+)/', $option['value'], $matches);
                                //                                    if (!empty($matches)) {
                                //                                        $option['value'] = $this->_getProtectSortOptionListValue($_G['tid'], $matches[1]);
                                //                                    } else {
                                //                                        $option['value'] = WebUtils::emptyHtml($option['value']);
                                //                                    }
                                //                                    $sort['summary'][] = $option;
                                //                                }
                                if ($option['type'] == 'image')
                                {
                                    $pattern = '/<img.*src="(.*?)"/';
                                    preg_match($pattern, $option['value'], $matches);
                                    if (!empty($matches))
                                    {

                                        $option['value'] = "<img src=\"" . WebUtils::getHttpFileName($matches[1]) . "\">";
                                    }
                                } else
                                {
                                    preg_match('/action=protectsort.+?optionid=(\d+)/', $option['value'], $matches);
                                    if (!empty($matches))
                                    {
                                        $option['value'] = $this->_getProtectSortOptionListValue($_G['tid'], $matches[1]);
                                    }
                                }
                                $sort['summary'][] = $option;
                                //                                $sort['summary'] .= "\n";
                            }
                        }
                    }
                }
            }
        }
        //        debug($sort);
        return $sort;
    }

    // 获取分类信息保护字段的值
    private function _getProtectSortOptionListValue($tid, $optionid)
    {
        $value = '';

        $typeoptionvarvalue = C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($tid, $optionid);
        $typeoptionvarvalue[0]['expiration'] = $typeoptionvarvalue[0]['expiration'] && $typeoptionvarvalue[0]['expiration'] <= TIMESTAMP ? 1 : 0;
        $option = C::t('forum_typeoption')->fetch($optionid);

        if (($option['expiration'] && !$typeoptionvarvalue[0]['expiration']) || empty($option['expiration']))
        {
            $protect = dunserialize($option['protect']);
            include_once libfile('function/threadsort');
            if (protectguard($protect))
            {
                if (empty($option['permprompt']))
                {
                    $value = lang('forum/misc', 'view_noperm');
                } else
                {
                    $value = $option['permprompt'];
                }
            } else
            {
                $value = nl2br($typeoptionvarvalue[0]['value']);
            }
        } else
        {
            $value = lang('forum/misc', 'has_expired');
        }
        return $value;
    }

    private function _getSortByTemplate($threadsortshow)
    {
        Mobcent::import(MOBCENT_APP_ROOT . '/components/discuz/template/SortTemplate.php');
        $template = new SortTemplate;
        return $template->getTopicSort($threadsortshow);
    }

    private function _transSortInfo($sort)
    {
        if ($sort['summary'] != '')
        {
            $sort = array('infor' => $sort['summary'], 'type' => 0);
        } else
        {
            $sort = array();
        }
        return $sort;
    }

    private function _getPostInfos($res, $topic, $page, $pageSize, $order, $authorId, $params = array())
    {
        extract($params);
        $postInfos = array();

        $tid = (int)$topic['tid'];
        $postCount = ForumUtils::getPostCount($tid, array('authorId' => $authorId));

        if (!empty($topic))
        {
            global $_G;
            $isOnlyAuthorTopic = ForumUtils::isOnlyAuthorTopic($topic);
            $postList = ForumUtils::getPostList($tid, $page, $pageSize, array(
                    'order' => $order ? 'dateline DESC' : 'dateline ASC',
                    'authorId' => $authorId
                )
            );
            /**
             *  返回置顶回帖信息
             */
            if ($_G['forum_thread']['stickreply'] && $page === 1)
            {
                $postStick = ForumUtils::getPostStick($tid);
                foreach ($postList as $k => $v)
                {
                    $postList[$v['pid']] = $v;
                    $postList[$v['pid']]['poststick'] = 0;
                    unset($postList[$k]);
                }
                foreach ($postStick as $k => $value)
                {
                    $postStick[$k]['poststick'] = 1;
                    if (isset($postList[$value['pid']]))
                    {
                        unset($postList[$value['pid']]);
                    }
                }
                $postList = array_merge($postStick, $postList);
            }
            $position = $order ?
                $postCount + 1 - $pageSize * ($page - 1) :
                $pageSize * ($page - 1) + 2;

            if ((MobcentDiscuz::getMobcentDiscuzVersion() > 'x25') && $_G['setting']['repliesrank'] && $postList)
            {
                if ($postList)
                {
                    $tempPids = array();
                    $tempPostRecommends = array();
                    foreach ($postList as $post)
                    {
                        $tempPids[] = (int)$post['pid'];
                    }
                    foreach (C::t('forum_hotreply_number')->fetch_all_by_pids(array_values($tempPids)) as $pid => $post)
                    {
                        $tempPostRecommends[$pid]['support'] = dintval($post['support']);
                        // $tempPostRecommends[$pid]['against'] = dintval($post['against']);
                    }
                    $isSupport = DzSupportInfo::getSupportPostsByUidAndTid($_G['uid'], $tid);
                }
            }

            foreach ($postList as $post)
            {
                $pid = (int)$post['pid'];
                if ($editPerm['bbcode'])
                {
                    $htmlcontent = ForumUtils::getPostBBCodeContent($tid, $pid, $post);
                }
                $content = ForumUtils::getPostContent($tid, $pid, $post);
                $isOnlyAuthorPost = $isOnlyAuthorTopic && $_G['uid'] != $post['authorid'] && $_G['uid'] != $_G['forum_thread']['authorid'] && !$post['first'] && !$_G['forum']['ismoderator'];
                $postInfo['reply_id'] = ForumUtils::isAnonymousPost($tid, $post) ? 0 : (int)$post['authorid'];

                if ($editPerm['bbcode'])
                {
                    if (!$isOnlyAuthorPost)
                    {
                        $postInfo['html_reply_content'] = $htmlcontent;
                    } else {
                        $postInfo['html_reply_content'] = ForumUtils::getOnlyAuthor();
                    }
                } else {
                    $postInfo['reply_content'] = $isOnlyAuthorPost ? $this->_filterContent(array(array('content' => WebUtils::t('此帖仅作者可见'), 'type' => 'text'))) : $this->_getPostContent($content);
                }
                $postInfo['reply_type'] = 'normal';
                $this->_isComplexContent($postInfo['reply_content']) && $postInfo['reply_type'] = 'normal_complex';

                $postInfo['reply_name'] = ForumUtils::isAnonymousPost($tid, $post) ? $this->_getAnonymoustext() : $post['author'];
                $postInfo['reply_posts_id'] = $pid;
                //是否是置顶回帖
                $postInfo['poststick'] = $post['poststick'] ? $post['poststick'] : 0;
                if ($_G['group']['allowstickreply'])
                {
                    $type = 'add';
                    $postInfo['sticktitle'] = WebUtils::t('回帖置顶');
                    if ($post['poststick'])
                    {
                        $postInfo['sticktitle'] = WebUtils::t('取消回帖置顶');
                        $type = 'del';
                    }
                    $postInfo['stickurl'] = WebUtils::createUrl_oldVersion('forum/updateforum', array('topicId' => $post['tid'], 'postId' => $post['pid'], 'position' => $post['position'], 'type' => $type));
                }
                // 抢楼帖时采用原楼层
                if (getstatus($topic['status'], 3))
                {
                    $postInfo['position'] = $post['position'];
                } else
                {
                    $postInfo['position'] = $order ? $position-- : $position++;
                }

                $postInfo['posts_date'] = $post['dateline'] . '000';

                $postInfo['icon'] = UserUtils::getUserAvatar($postInfo['reply_id']);

                $postInfo['level'] = $this->_getUserLevel($postInfo['reply_id']);
                $postInfo['userTitle'] = UserUtils::getUserTitle($postInfo['reply_id']);
                $postInfo['userColor'] = UserUtils::getUserColor($postInfo['reply_id']);

                $postInfo['location'] = ForumUtils::getPostLocation($pid);
                $postInfo['mobileSign'] = ForumUtils::getMobileSign($post['status']);

                // if(empty($post['author']) && isset($post['authorid']) && !empty($post['authorid'])){
                //     $postInfo['reply_status'] = -1;
                // }elseif(empty($post ['author']) && empty($post ['authorid'])){
                //     $postInfo['reply_status'] = 0;
                // }else{
                //     $postInfo['reply_status'] = 1;
                // }
                $postInfo['reply_status'] = 1;
                $postInfo['status'] = 1;
                $postInfo['role_num'] = 1;
                $postInfo['title'] = '';
                $postInfo['gender'] = UserUtils::getUserGender($postInfo['reply_id']);
                $postInfo = array_merge($postInfo, $this->_getPostQuoteInfo($content, $isOnlyAuthorPost));

                // 回帖管理面板编辑 start
                $userMember = $this->_getUserInfoByAuthorid($post['authorid']);
                $userMember = array_merge($userMember, $post);
                $params = array('editPerm' => $editPerm, 'userMember' => $userMember);
                // end
                $postInfo['delThread'] = ForumUtils::checkEdit(array_merge($post, array('adminid' => $userMember['adminid'])), false) ? true : false;

                $manageItems = ForumUtils::getPostManagePanel($params);
                foreach ($manageItems['post'] as $key => $item)
                {
                    if ($item['action'] == 'edit')
                    {
                        $item['action'] = WebUtils::getHttpFileName("forum.php?mod=post&action=edit&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]");
                    } else
                    {
                        $item['action'] = WebUtils::createUrl_oldVersion('forum/topicadminview', array('fid' => $post['fid'], 'tid' => $tid, 'pid' => $post['pid'], 'act' => $item['action'], 'type' => 'post'));
                    }
                    $manageItems['post'][$key] = $item;
                }
                global $_G;
                if ($_G['group']['allowbanuser'] || $_G['group']['allowbanvisituser'])
                {
                    $temp = array();
                    $temp['type'] = 'edituser';
                    $temp['action'] = WebUtils::createUrl_oldVersion('user/useradmincp', array('uid' => $post['authorid']));
                    $temp['title'] = WebUtils::t('禁言/禁止访问');
                    $manageItems['post'][] = $temp;
                }
                if ($postInfo['delThread'])
                {
                    $temp = array();
                    $temp['type'] = 'delthread';
                    $temp['action'] = WebUtils::createUrl_oldVersion('forum/topicadminex', array('topicId' => $post['tid'], 'postId' => $post['pid']));
                    $temp['title'] = WebUtils::t('删帖');
                    $manageItems['post'][] = $temp;
                }
                $count = mb_strlen($postInfo['reply_content'][0]['infor'], $_G['charset']);
                if ($count < $_G['setting']['threadfilternum'])
                {
                    $isWater = true;
                }

                $isWater = false;
                $count = mb_strlen($postInfo['reply_content'][0]['infor'], $_G['charset']);
                if ($_G['setting']['filterednovote'] && $count < $_G['setting']['threadfilternum'])
                {
                    $isWater = true;
                }

                $extraItems = array();
                $tempExtraItems = ForumUtils::getPostExtraPanel();
                foreach ($tempExtraItems['post'] as $key => $item)
                {
                    $item['extParams'] = array('beforeAction' => '');
                    $item['type'] = $item['action'];
                    $item['action'] = '';
                    if ($item['type'] == 'support' && !$isWater)
                    {
                        $item['action'] = WebUtils::createUrl_oldVersion('forum/support', array('tid' => $tid, 'pid' => $post['pid'], 'type' => 'post'));
                        $item['extParams']['recommendAdd'] = (int)$tempPostRecommends[$post['pid']]['support'];
                        $isRecommendAdd = in_array($post['pid'], $isSupport) ? 1 : 0;
                        $item['extParams']['isHasRecommendAdd'] = (int)$isRecommendAdd;
                    }
                    if ($item['type'] != 'support' || !$isWater)
                    {
                        $extraItems[] = $item;
                    }
                }

                $postInfo['managePanel'] = $manageItems['post'];
                $postInfo['extraPanel'] = $extraItems;
                $postInfos[] = $postInfo;
            }
        }

        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $postCount, $res);
        $res['list'] = $postInfos;

        return $res;
    }

    private function chage($arr)
    {
        foreach ($arr as $k => $v)
        {
            if (is_array($v))
            {
                $arr[$k] = $this->chage($arr[$k]);
            }
            $arr[$k] = WebUtils::u($v);
        }
        return $arr;
    }

    private function _getAnonymoustext()
    {
        return MobcentDiscuz::getDiscuzCommonSetting('anonymoustext');
    }

    private function _getPostContent($content)
    {
        return !empty($content['main']) ? $this->_filterContent($content['main']) : array();
    }

    private function _getPostQuoteInfo($content, $isOnlyAuthorPost = false)
    {
        $postInfo['is_quote'] = !empty($content['quote']) ? 1 : 0;
        $postInfo['quote_pid'] = 0;
        $postInfo['quote_content'] = !empty($content['quote']) && !$isOnlyAuthorPost ? $content['quote']['who'] . "\n" . $content['quote']['msg'] : '';
        $postInfo['quote_user_name'] = '';
        return $postInfo;
    }

    private function _filterContent($content)
    {
        $typeMaps = array('text' => 0, 'image' => 1, 'video' => 2, 'audio' => 3, 'url' => 4, 'attachment' => 5,);
        $newContent = array();
        foreach ($content as $value)
        {
            $tempContent['infor'] = $value['content'];
            $tempContent['type'] = $typeMaps[$value['type']];
            switch ($value['type'])
            {
                case 'image':
                    $tempContent['originalInfo'] = $value['content'];
                    $tempContent['aid'] = isset($value['extraInfo']['aid']) ? $value['extraInfo']['aid'] : 0;
                    $tempContent['infor'] = ImageUtils::getThumbImage($value['content']);
                    break;
                case 'url':
                    $tempContent['infor'] = $value['content'];
                    $tempContent['url'] = $value['extraInfo']['url'];
                    break;
                case 'attachment':
                    $tempContent['infor'] = $value['content'];
                    $tempContent['url'] = $value['extraInfo']['url'];
                    $tempContent['desc'] = $value['extraInfo']['desc'];
                    break;
                case 'video':
                    $videoInfo = ForumUtils::parseVideoUrl($value['content']);
                    $tempContent['extParams'] = array(
                        'videoType' => $videoInfo['type'],
                        'videoId' => $videoInfo['vid'],
                    );
                    if($value['picurl']){
                        $tempContent['extParams']['picurl'] =$value['picurl'];
                    }
                    break;
                default:
                    break;
            }
            // 判定是否开启附件下载
            if (!WebUtils::getDzPluginAppbymeAppConfig('forum_allow_attachment') && $tempContent['type'] == 5)
            {
                $tempContent = array();
            }
            !empty($tempContent) && $newContent[] = $tempContent;
        }
        return $newContent;
    }

    private function _getUserLevel($uid)
    {
        $icon = UserUtils::getUserLevelIcon($uid);
        return $icon['sun'] * 4 + $icon['moon'] * 2 + $icon['star'] * 1;
    }

    // 通过 authorid 获取用户信息
    private function _getUserInfoByAuthorid($authorid)
    {
        $userMember = array();
        $userMember = DzCommonMember::getInfoByAuthorid($authorid);
        return $userMember;
    }

    private function getrelateitem($tag, $tid, $relatenum, $relatetime, $relatecache = '', $type = 'tid')
    {
        $tagarray = explode("\t", $tag);

        $tagidarray = $relatearray = $relateitem = array();
        $updatecache = 0;
        $limit = $relatenum;
        if (!$limit)
        {
            return '';
        }
        foreach ($tagarray as $var)
        {
            $tagidarray[] = $var['0'];
        }
        if (!$tagidarray)
        {
            return '';
        }
        if (empty($relatecache))
        {
            $thread = C::t('forum_thread')->fetch($tid);
            $relatecache = $thread['relatebytag'];
        }
        if ($relatecache)
        {
            $relatecache = explode("\t", $relatecache);
            if (TIMESTAMP > $relatecache[0] + $relatetime * 60)
            {
                $updatecache = 1;
            } else
            {
                if (!empty($relatecache[1]))
                {
                    $relatearray = explode(',', $relatecache[1]);
                }
            }
        } else
        {
            $updatecache = 1;
        }
        if ($updatecache)
        {
            $query = C::t('common_tagitem')->select($tagidarray, $tid, $type, 'itemid', 'DESC', $limit, 0, '<>');
            foreach ($query as $result)
            {
                if ($result['itemid'])
                {
                    $relatearray[] = $result['itemid'];
                }
            }
            if ($relatearray)
            {
                $relatebytag = implode(',', $relatearray);
            }
            C::t('forum_thread')->update($tid, array('relatebytag' => TIMESTAMP . "\t" . $relatebytag));
        }


        if (!empty($relatearray))
        {
            rsort($relatearray);
            foreach (C::t('forum_thread')->fetch_all_by_tid($relatearray) as $result)
            {
                if ($result['displayorder'] >= 0)
                {
                    $topicSummary = ForumUtils::getTopicSummary($result['tid']);
                    $relateitem[] = array(
                        'tid' => $result['tid'],
                        'subject' => $result['subject'],
                        'image' => $topicSummary['image'],
                        'msg' => $topicSummary['msg'],
                        'lastReplyTime' => $result['lastpost'] . '000',
                        'postTime' => $result['dateline'] . '000',
                        'author' => $result['author']
                    );
                }
            }
        }
        return $relateitem;
    }
    //}
}




