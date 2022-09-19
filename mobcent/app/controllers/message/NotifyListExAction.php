<?php

/**
 * 
 * 新版消息列表接口
 * 
 * @author  NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 GoYoo Inc.
 */
//Mobcent::setErrors();
class NotifyListExAction extends MobcentAction {

    public function run($type = 'post', $page = '1', $pageSize = '20') {
        $res = WebUtils::initWebApiArray_oldVersion();
                require_once libfile('function/friend');

        $typearr = array('post', 'system', 'at');
        $type = in_array($type, $typearr) ? $type : 'post';
        $functionname = (string) '_getNotifyList' . $type;
        $info = array(
            'count' => 0,
            'list' => array(),
        );
        global $_G;
        $uid = $_G['uid'];
        $info = $this->$functionname($info, $uid, $page, $pageSize);
        $res = array_merge($res, WebUtils::getWebApiArrayWithPage_oldVersion(
                        $page, $pageSize, $info['count']));
        $res['body']['data'] = $info['list'];
        WebUtils::outputWebApi($res);
    }

    private function _getNotifyListpost($infoList, $uid, $page, $pageSize) {
        $type = 'post';
        $count = DzHomeNotification::getCountByUid($uid, $type);
        $notifyData = DzHomeNotification::getAllNotifyByUid($uid, $type, $page, $pageSize);
        foreach ($notifyData as $data) {
            $matches = array();
            preg_match_all('/&ptid=(\d+?)&pid=(\d+?)"/i', $data['note'], $matches);
            $ptid = $matches[1][0];
            $pid = $matches[2][0];
            $postInfo = array();
            $postInfo = $this->_getPostInfo($ptid, $pid);
            if (!empty($postInfo)) {
                $postInfo['is_read'] = (int) $data['new'];
                $postInfo['type'] = $data['type'];
                $postInfo['mod'] = 'post';
                $infoList['list'][] = $postInfo;
            } else {
                preg_match_all('/&tid=(\d+?)"/i', $data['note'], $matches);
                preg_match_all('/&uid=(\d+?)"/i', $data['note'], $douser);
                if (!empty($matches[1][0]) && !empty($douser[1][0])) {
                    $postInfo = array();
                    $postInfo = self::_getThreadInfo($matches[1][0], $douser[1][0]);
                    if (!empty($postInfo)) {
                    	$postInfo['type'] = $data['type'];
                        $postInfo['replied_date'] = $data['dateline'].'000';
                        $postInfo['content'] = WebUtils::emptyHtml($data['note']);
                        $postInfo['is_read'] = (int) $data['new'];
                        $postInfo['mod'] = 'admin';
                        $infoList['list'][] = $postInfo;
                        $count++;
                    }
                }

                --$count;
            }
        }
        $infoList['count'] = $count;
        $this->_updateReadStatus($uid, 'post');
        return $infoList;
    }

    private function _getNotifyListat($infoList, $uid, $page, $pageSize) {
        $type = 'at';
        $count = DzHomeNotification::getCountByUid($uid, $type);
        $notifyData = DzHomeNotification::getAllNotifyByUid($uid, $type, $page, $pageSize);
        foreach ($notifyData as $data) {
            $matches = array();
            preg_match_all('/&ptid=(\d+?)&pid=(\d+?)"/i', $data['note'], $matches);
            $ptid = $matches[1][0];
            $pid = $matches[2][0];
            $postInfo = array();
            $postInfo = $this->_getPostInfo($ptid, $pid);
            if (!empty($postInfo)) {
                $postInfo['is_read'] = (int) $data['new'];
                $postInfo['type'] = $data['type'];
                $infoList['list'][] = $postInfo;
            } else {
                --$count;
            }
        }
        $infoList['count'] = $count;
        $this->_updateReadStatus($uid, 'at');
        return $infoList;
    }

    private function _getNotifyListsystem($infoList, $uid, $page, $pageSize) {
        $count = (int) DbUtils::getDzDbUtils(true)->queryScalar('SELECT COUNT(*) FROM %t WHERE uid=%d AND type IN (%n) ', array('home_notification', $uid, array('system', 'friend', 'verify')));
        $notifyData = DbUtils::getDzDbUtils(true)->queryAll('SELECT * FROM %t WHERE uid=%d AND type IN (%n)  ORDER BY dateline DESC LIMIT %d, %d', array('home_notification', $uid, array('system', 'friend', 'verify'), ($page - 1) * $pageSize, $pageSize));
        $uids        = array();
        $authorids   = array();
        foreach ($notifyData as $data) {
            $tmpData = array();
            //debug($data);
            if ($data['type'] == 'friend') {
                $isAllowData = true;
                $actions     = array();
                //把已经成为好友的信息返回
                if(empty($data['from_idtype'])) {
                    //逗逼做法去重复
                    if(in_array($data['authorid'], $authorids) || in_array($data['uid'], $uids)) {
                        $tmpData['replied_date'] = $data['dateline'] . '000';
                        $tmpData['type'] = 'system';
                        $tmpData['icon'] = UserUtils::getUserAvatar('0');
                        $tmpData['user_name'] = WebUtils::t('系统');
                        $tmpData['user_id'] = '0';
                        $tmpData['mod'] = 'admin';
                        $tmpData['note'] = WebUtils::emptyHtml($data['note']);
                        $tmpData['is_read'] = $data['new'];
                        $infoList['list'][] = $tmpData;
                    }
                    $uids[] = $data['uid'];
                    $authorids[] = $data['authorid'];
                }
                $matches = array();
                preg_match('/<a onclick="showWindow.+?>(\S+)<\/a>/mi', $data['note'], $matches);
                if (!empty($matches)) {
                    $actions = array();
                    $action = array('redirect' => '', 'title' => $matches[1], 'type' => '');
                    // 添加好友按钮
                    $tempMatches = array();
                    preg_match('/ac=friend&op=(\w+)&uid=(\d+)/mi', $matches[0], $tempMatches);
                    if (!empty($tempMatches)) {
                        $action['redirect'] = WebUtils::createUrl_oldVersion('user/useradminview', array('act' => $tempMatches[1], 'uid' => $tempMatches[2]));
                        $action['type'] = 'firend';
                    }

                    $data['note'] = str_replace($matches[1], '', $data['note']);

                    // 暂时屏蔽已经是好友的动作
                    if (friend_check($tempMatches[2])) {
                        $isAllowData = false;
                        $count--;
                    }

                    $actions[] = $action;
                    if ($isAllowData) {
                        $tmpData['replied_date'] = $data['dateline'] . '000';
                        $tmpData['mod'] = 'firend';
		                $tmpData['note'] = WebUtils::emptyHtml($data['note']);
		               // $tmpData['fromId'] = (int) $data['from_id'];
		               // $tmpData['fromIdType'] = $data['from_idtype'];
		                $tmpData['user_name'] = $data['author'];
		                $tmpData['user_id'] = (int) $data['authorid'];
		                $tmpData['icon'] = UserUtils::getUserAvatar($data['authorid']);
		                $tmpData['actions'] = $actions;
		                $tmpData['is_read'] = $data['new'];
		                $tmpData['type'] = 'system';
                        $infoList['list'][] = $tmpData;
                    }
                }
            } else {
                $tmpData['replied_date'] = $data['dateline'] . '000';
                $tmpData['type'] = 'system';
                $tmpData['icon'] = UserUtils::getUserAvatar('0');
                $tmpData['user_name'] = WebUtils::t('系统');
                $tmpData['user_id'] = '0';
                $tmpData['mod'] = 'admin';
                $tmpData['note'] = WebUtils::emptyHtml($data['note']);
                $tmpData['is_read'] = $data['new'];
                $infoList['list'][] = $tmpData;
            }
        }
        $this->_updateReadStatus($uid, 'friend');
        $this->_updateReadStatus($uid, 'system');
        $this->_updateReadStatus($uid, 'verify');
        $infoList['count'] = $count;
        return $infoList;
    }

    private function _getPostInfo($tid, $pid) {
        $info = array();

        $post = ForumUtils::getPostInfo($tid, $pid);
        if (!empty($post)) {
            $forumName = ForumUtils::getForumName($post['fid']);
            $threadPost = ForumUtils::getTopicPostInfo($tid);

            $topicContent = ForumUtils::getTopicContent($tid, $threadPost);
            $postContent = ForumUtils::getPostContent($tid, $pid, $post);

            $content = $this->_getContent($postContent, $topicContent);

            $info['board_name'] = $forumName;
            $info['board_id'] = (int) $post['fid'];

            $info['topic_id'] = (int) $tid;
            $info['topic_subject'] = $threadPost['subject'];
            $info['topic_content'] = $content['topic'];
            $info['topic_url'] = '';

            $info['reply_content'] = $content['reply'];
            $info['reply_url'] = '';
            $info['reply_remind_id'] = (int) $pid;
            $info['user_name'] = $post['author'];
            $info['user_id'] = (int) $post['authorid'];
            $info['icon'] = UserUtils::getUserAvatar($post['authorid']);
            $info['is_read'] = 1;
            $info['replied_date'] = $post['dateline'] . '000';
        }

        return $info;
    }

    private function _getThreadInfo($tid, $uid) {
        $info = array();
        $threadInfo = ForumUtils::getTopicPostInfo($tid);
        if (!empty($threadInfo)) {
            $info['board_name'] = ForumUtils::getForumName($threadInfo['fid']);
            $info['board_id'] = (int) $threadInfo['fid'];
            $info['topic_id'] = (int) $tid;
            $info['topic_subject'] = $threadInfo['subject'];

            $info['user_name'] = UserUtils::getUserName($uid);
            $info['user_id'] = (int) $uid;
            $info['icon'] = UserUtils::getUserAvatar($uid);
        }
        return $info;
        //   debug($threadInfo);
    }

    /**
     * copy from Discuz
     */
    private function _updateReadStatus($uid, $type) {
        //采用新的刷新机制
        C::t('home_notification')->ignore($uid, $type, '', true, true);
        $this->update_newprompt($uid,$type);
        //helper_notification::update_newprompt($uid, $type);
    }

    private function _getContent($postContent, $topicContent) {
        $content = array('topic' => '', 'reply' => '');
        if (!empty($postContent['main'])) {
            $content['reply'] = $this->_transContent($postContent['main']);
        }
        if (!empty($postContent['quote'])) {
            $content['topic'] = WebUtils::subString($postContent['quote']['msg'], 0, Yii::app()->params['mobcent']['forum']['post']['summaryLength']);
        } else {
            $content['topic'] = $this->_transContent($topicContent['main']);
        }

        return $content;
    }

    private function _transContent($content) {
        $msg = '';
        if (!empty($content)) {
            foreach ($content as $line) {
                if ($line['type'] == 'text') {
                    $msg .= $line['content'] . "\r\n";
                }elseif ($line['type'] == 'url'){
                    $msg .= $line['content'] . "\r\n";
                }
            }
            $msg = preg_replace('/\[mobcent_phiz=.+?\]/', '', $msg);
            $length = Yii::app()->params['mobcent']['forum']['post']['summaryLength'];
            $msg = WebUtils::subString($msg, 0, $length);
        }
        return $msg;
    }


    private function update_newprompt($uid, $type) {
        global $_G;
        if($_G['member']['newprompt_num']) {
            $tmpprompt = $_G['member']['newprompt_num'];
            $num = 0;
            $updateprompt = 0;
            if(!empty($tmpprompt[$type])) {
                unset($tmpprompt[$type]);
                $updateprompt = true;
            }
            foreach($tmpprompt as $key => $val) {
                $num += $val;
            }
            if($num) {
                if($updateprompt) {
                    C::t('common_member_newprompt')->update($uid, array('data' => serialize($tmpprompt)));
                    C::t('common_member')->update($uid, array('newprompt'=>$num));
                }
            } else {
                C::t('common_member_newprompt')->delete($_G['uid']);
                C::t('common_member')->update($_G['uid'], array('newprompt'=>0));
            }
        }
    }
}
