<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/5/27
 * Time: 17:20
 */
class UpdateController extends ApiController{
    //修改用户注册时间
    public function actionUsertime($uid = 0,$time = 0){
        if($uid && $time){
            $this->db->update('common_member',array(
                'regdate' => $time
            ),array(
                'uid' => $uid
            ));
        }else{
            $this->error('参数错误');
        }
    }
    //修改帖子创建时间
    public function actionTopictime($tid = 0,$time = 0){
        if($tid && $time){
            $this->db->update('forum_thread',array(
                'dateline' => $time
            ),array(
                'tid' => $tid
            ));
        }else{
            $this->error('参数错误');
        }
    }
    //修改文章创建时间
    public function actionArticletime($aid = 0, $time = 0){
        if($aid && $time){
            $this->db->update('portal_article_title',array(
                'dateline' => $time
            ),array(
                'tid' => $aid
            ));
        }else{
            $this->error('参数错误');
        }
    }
    //修改帖子回复时间
    public function actionReplaytime($pid = 0,$time = 0){
        if($pid && $time){
            $this->db->update('forum_post',array(
                'dateline' => $time
            ),array(
                'pid' => $pid
            ));
        }else{
            $this->error('参数错误');
        }
    }
}