<?php

/**
 *  基于Discuz二开Model_Forum_Post
 * 
 *  @author   耐小心<nxx@yytest.cn>
 *  @copyright (c) 2012-2016   Appbyme
 */
class Mobcent_model_forum_post extends model_forum_post {

    public function __construct($tid,$pid) {
        parent::__construct($tid,$pid);
    }

    /**
     *  基于Discuz的DeletePost二次开发
     *  @author 耐小心<nxx@yytest.cn>
     */
    public function Mobcent_deletepost($res,$parameters) {

        $this->_init_parameters($parameters);
        //note 后台开关
        if (!$this->setting['editperdel']) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'post_edit_thread_ban_del');
        }

        $isfirstpost = $this->post['first'] ? 1 : 0;

        if ($isfirstpost && $this->thread['replies'] > 0) {
            return WebUtils::makeErrorInfo_oldVersion($res,($this->thread['special'] == 3 ? 'post_edit_reward_already_reply' : 'post_edit_thread_already_reply'), NULL);
            /* showmessage('post_edit_reward_already_reply'); */
            /* showmessage('post_edit_thread_already_reply'); */
        }

        //===========================================================
        //note 更新积分
        if ($this->thread['displayorder'] >= 0) {
            updatepostcredits('-', $this->post['authorid'], ($isfirstpost ? 'post' : 'reply'), $this->forum['fid']);
        }

        //===========================================================

        if (!$this->param['handlereplycredit']) {//note 如果还没有处理回复积分
            if (!$isfirstpost && !$this->param['isanonymous']) {
                //noteX 删除回复操作积分记录规则：系统销毁获得积分，但获得的积分记录保存
                //			if($postreplycredit = DB::result_first("SELECT replycredit FROM ".DB::table($posttable)." WHERE pid = '$pid' LIMIT 1")) {
                //				DB::query("UPDATE ".DB::table($posttable)." SET replycredit = 0 WHERE pid = '$pid' LIMIT 1");
                //				updatemembercount($orig['authorid'], array($replycredit_rule['extcreditstype'] => '-'.$postreplycredit));
                //			}
                $postreplycredit = C::t('forum_post')->fetch('tid:' . $this->thread['tid'], $this->post['pid']);
                $postreplycredit = $postreplycredit['replycredit'];
                if ($postreplycredit) {
                    C::t('forum_post')->update('tid:' . $this->thread['tid'], $this->post['pid'], array('replycredit' => 0));
                    updatemembercount($this->post['authorid'], array($replycredit_rule['extcreditstype'] => '-' . $postreplycredit));
                }
            }
        }

        //=========================================================
        //DB::query("DELETE FROM ".DB::table($posttable)." WHERE pid='$pid'");
        C::t('forum_post')->delete('tid:' . $this->thread['tid'], $this->post['pid']);

        //=========================================================

        $forumcounter = array();
        if ($isfirstpost) {
            //$forumadd = 'threads=threads-\'1\', posts=posts-\'1\'';
            $forumcounter['threads'] = $forumcounter['posts'] = -1;
            $tablearray = array('forum_relatedthread', 'forum_debate', 'forum_debatepost', 'forum_polloption', 'forum_poll');
            foreach ($tablearray as $table) {
                //DB::query("DELETE FROM ".DB::table($table)." WHERE tid='".$this->thread['tid']."'", 'UNBUFFERED');
                C::t($table)->delete_by_tid($this->thread['tid']);
            }
            C::t('forum_thread')->delete_by_tid($this->thread['tid']); //note 从上面foreach移到这里处理
            C::t('common_moderate')->delete($this->thread['tid'], 'tid');
            C::t('forum_threadmod')->delete_by_tid($this->thread['tid']); //note 从上面foreach移到这里处理
            if ($this->setting['globalstick'] && in_array($this->thread['displayorder'], array(2, 3))) {
                require_once libfile('function/cache');
                updatecache('globalstick');
            }
        } else {
// 			$savepostposition && DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE pid='$pid'");
            //$savepostposition && C::t('forum_postposition')->delete_by_tid_or_pid('pid', $pid);
            //$forumadd = 'posts=posts-\'1\'';
            $forumcounter['posts'] = -1;
//			$query = DB::query("SELECT author, dateline, anonymous FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline DESC LIMIT 1");
//			$lastpost = DB::fetch($query);
            $lastpost = C::t('forum_post')->fetch_visiblepost_by_tid('tid:' . $this->thread['tid'], $this->thread['tid'], 0, 1);
            $lastpost['author'] = !$lastpost['anonymous'] ? addslashes($lastpost['author']) : '';
//			DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies-'1', attachment='$thread_attachment', lastposter='$lastpost[author]', lastpost='$lastpost[dateline]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
//			$updatefieldarr = array(
//				'replies' => -1,
//				'attachment' => array($thread_attachment),
//				'lastposter' => array($lastpost['author']),
//				'lastpost' => array($lastpost['dateline'])
//			);
            $this->param['updatefieldarr']['replies'] = -1;
            $this->param['updatefieldarr']['lastposter'] = array($lastpost['author']);
            $this->param['updatefieldarr']['lastpost'] = array($lastpost['dateline']);

            C::t('forum_thread')->increase($this->thread['tid'], $this->param['updatefieldarr']);
        }

        $this->forum['lastpost'] = explode("\t", $this->forum['lastpost']);
        if ($this->post['dateline'] == $this->forum['lastpost'][2] && ($this->post['author'] == $this->forum['lastpost'][3] || ($this->forum['lastpost'][3] == '' && $this->post['anonymous']))) {
//			$lastthread = DB::fetch_first("SELECT tid, subject, lastpost, lastposter FROM ".DB::table('forum_thread')."
//				WHERE fid='$_G[fid]' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");
            $lastthread = C::t('forum_thread')->fetch_by_fid_displayorder($this->forum['fid']);
            //$forumadd .= ", lastpost='$lastthread[tid]\t$lastthread[subject]\t$lastthread[lastpost]\t$lastthread[lastposter]'";
            C::t('forum_forum')->update($this->forum['fid'], array('lastpost' => "$lastthread[tid]\t$lastthread[subject]\t$lastthread[lastpost]\t$lastthread[lastposter]"));
        }
        //DB::query("UPDATE ".DB::table('forum_forum')." SET $forumadd WHERE fid='$_G[fid]'", 'UNBUFFERED');
        C::t('forum_forum')->update_forum_counter($this->forum['fid'], $forumcounter['threads'], $forumcounter['posts']);
        return $res;
    }

}
