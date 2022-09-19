<?php

/**
 * 帖子管理Api 
 * 
 *  @author   耐小心<nxx@yytest.cn>
 *  @copyright (c) 2015-2016   NaiXiaoXin
 */

//Mobcent::setErrors();
class TopicAdminExAction extends MobcentAction {
    public function run( $topicId = '', $postId = '') {
        $this->_del($topicId, $postId);
    }

    public function _del($tid, $pid) {
        header("Content-Type: text/html; charset=utf-8");

        $res = WebUtils::initWebApiArray_oldVersion();
        global $_G;
        require_once libfile('function/forum');
        cknewuser();

        //note 引用头文件
        require_once libfile('class/credit');
        require_once libfile('function/post');
        //getPostInfo
        $orig = $postInfo = get_post_by_tid_pid($tid, $pid);
        if(empty($postInfo)){
            $this->_exitWithHtmlAlert(WebUtils::t('帖子未找到,请重试'));
        }
        if ($postInfo && $_POST['bansubmit']=='yes') {

            $app = Yii::app()->getController()->mobcentDiscuzApp;
            $app->loadForum($postInfo['fid'], $postInfo['tid']);
            //Check 是否有权限编辑
            $check = ForumUtils::checkEdit($postInfo, false);
            if (!$check) {
                $this->_exitWithHtmlAlert(WebUtils::t('您无权编辑此帖子'));
            }
            //初始化变量
            $thread = $_G['thread'];
            $special = $postInfo['special'];
            $isorigauthor = $_G['uid'] && $_G['uid'] == $orig['authorid'];
            $isanonymous = ($_G['group']['allowanonymous'] || $orig['anonymous']) && getgpc('isanonymous') ? 1 : 0;
            $isfirstpost = $orig['first'] ? 1 : 0;


            if ($special == 5 && !$isfirstpost) {
                $this->_exitWithHtmlAlert(WebUtils::t('无法删除已经有回复的悬赏帖'));
            }
            Yii::import('application.components.discuz.source.class.model.model_forum_post', true);
            $modpost = new Mobcent_model_forum_post($tid, $pid);
            if ($thread['special'] == 3) {
                $modpost->attach_before_method('deletepost', array('class' => 'extend_thread_reward', 'method' => 'before_deletepost'));
            }
            if ($thread['replycredit'] && $isfirstpost) {
                $modpost->attach_before_method('deletepost', array('class' => 'extend_thread_replycredit', 'method' => 'before_deletepost'));
            }
            $modpost->attach_before_method('deletepost', array('class' => 'extend_thread_image', 'method' => 'before_deletepost'));

            if ($thread['special'] == 2) {
                $modpost->attach_after_method('deletepost', array('class' => 'extend_thread_trade', 'method' => 'after_deletepost'));
            }
            if ($isfirstpost) {
                $modpost->attach_after_method('deletepost', array('class' => 'extend_thread_sort', 'method' => 'after_deletepost'));
            }

            $modpost->attach_after_method('deletepost', array('class' => 'extend_thread_filter', 'method' => 'after_deletepost'));

            $param = array(
                'special' => $special,
                'isanonymous' => $isanonymous,
            );
            $res = $modpost->Mobcent_deletepost($res, $param);
            if ($res['rs'] == '1') {
                if ($_G['forum']['threadcaches']) {
                    deletethreadcaches($_G['tid']);
                }
                dsetcookie('clearUserdata', 'forum');
            }else{
                $this->_exitWithHtmlAlert($res['errcode']);
            }
            Yii::app()->cache->flush();
            if($postInfo['first']){
                DbUtils::createDbUtils(true)->query('DELETE FROM %t WHERE `pid`=%d',array('appbyme_tpctopost',$tid));
            }
            $this->_exitWithHtmlAlert(WebUtils::t('删帖成功'));
        }else{
            $this->getController()->renderPartial('delTopic');
        }
    }

    private function _exitWithHtmlAlert($message) {
        $message = WebUtils::u(lang('message', $message));
        $location = WebUtils::createUrl_oldVersion('index/returnmobileview');
        $htmlString = '
            <script>
                alert("' . $message . '");
                location.href = "' . $location . '";
            </script>';
        echo $htmlString;
        exit;
    }
}
