<?php
/**
 * 我的话题
 * @author tanguanghua <18725648509@163.com>
 **/
class MytopicAction extends MobcentAction{
    //MY -----我发布的话题，CARE ------我关注的话题
    public function run($page = 1,$pageSize = 10,$type = 'MY') {
        global $_G;
        $res = WebUtils::initWebApiArray_oldVersion();
        $uid = $this->getController()->uid;
        $db = DbUtils::createDbUtils(true);
        $str = (abs($page)-1)*$pageSize;
        if($type == 'MY'){
            $data = $db->queryAll('SELECT * FROM %t WHERE `ti_authorid`=%d ORDER BY `ti_starttime` DESC LIMIT %d,%d',array('appbyme_topic_items',$uid,$str,$pageSize));
            $count = $db->queryRow('SELECT COUNT(*) FROM %t WHERE `ti_authorid`=%d',array('appbyme_topic_items',$uid));
        }else{
            $tiids = $db->queryRow('SELECT GROUP_CONCAT(ti_id) as tiids FROM %t WHERE uid=%d ORDER BY `ttid` DESC LIMIT %d,%d',array('appbyme_tpctou',$uid,$str,$pageSize));
            if($tiids['tiids']){
                $data = $db->queryAll('SELECT * FROM %t WHERE `ti_id` IN(%i)',array('appbyme_topic_items',$tiids['tiids']));
                $count = $db->queryRow('SELECT COUNT(*) FROM %t WHERE uid=%d',array('appbyme_tpctou',$uid));
            }else{
                $data = array();
                $count = 0;
            }

        }
        $finfo = $db->queryRow('SELECT ff.name,ff.fid FROM %t ac LEFT JOIN %t ff ON ff.fid=ac.cvalue WHERE ac.ckey=%s',array('appbyme_config','forum_forum','topic_bind_fid'));
        if(!empty($data)){
            foreach ($data as $k => $v){
                $data[$k]['ti_starttime'] = $v['ti_starttime'].'000';
                $data[$k]['ti_endtime'] = $v['ti_endtime'].'000';
                if($v['ti_cover']){
                    $forum = $v['ti_remote'] ? '/' : '/forum/';
                    $data[$k]['ti_cover'] = ImageUtils::getAttachUrl($v['ti_remote']).$forum.$v['ti_cover'];
                }
                $data[$k]['icon'] = UserUtils::getUserAvatar($v['ti_authorid']);
                $data[$k]['fid'] = $finfo['fid'];
                $data[$k]['fname'] = $finfo['name'];
            }
        }
        $res['list'] = $data;
        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count['COUNT(*)'],$res);
        WebUtils::outputWebApi($res);
    }
}
