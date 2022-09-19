<?php
/**
 * 话题列表
 * @author tanguanghua <18725648509@163.com>
 **/
class TopiclistAction extends MobcentAction{
    //话题列表类型 ,所有话题ALL,未关注话题UNCARE
    public function run($page=1,$pageSize=10,$type = 'ALL',$search='') {
        global $_G;
        $where = '';
        $uid = $this->getController()->uid;
        $nowtime = $_SERVER['REQUEST_TIME'];
        $db = DbUtils::getDzDbUtils(true);
        $res = WebUtils::initWebApiArray_oldVersion();
        $str = (abs($page)-1)*$pageSize;
        if($search){
            $where .= ' AND (`ti_title` LIKE \'%'.$search.'%\' OR `ti_authorname` LIKE \'%'.$search.'%\' OR `ti_content` LIKE \'%'.$search.'%\')';
        }
        if($type == 'UNCARE'){
            $tiids = $db->queryScalar('SELECT group_concat(ti_id) FROM %t WHERE `uid`='.$uid,array('appbyme_tpctou'));
            if($tiids){
                $where .= ' AND `ti_id` NOT IN('.$tiids.')';
            }
        }
        $sql = 'SELECT * FROM %t WHERE `ti_starttime`<%d AND `ti_endtime`>%d %i ORDER BY `ti_starttime` DESC LIMIT %d,%d';
        $cntsql = 'SELECT COUNT(0) FROM %t WHERE `ti_starttime`<%d AND `ti_endtime`>%d %i';
        $data = $db->queryAll($sql,array('appbyme_topic_items',$nowtime,$nowtime,$where,$str,$pageSize));
        $count = $db->queryRow($cntsql,array('appbyme_topic_items',$nowtime,$nowtime,$where));
        $finfo = $db->queryRow('SELECT ff.name,ff.fid FROM %t ac LEFT JOIN %t ff ON ff.fid=ac.cvalue WHERE ac.ckey=%s',array('appbyme_config','forum_forum','topic_bind_fid'));
        if(!empty($data)){
            foreach ($data as $k => $v){
                $data[$k]['ti_starttime'] = $v['ti_starttime'].'000';
                $data[$k]['ti_endtime'] = $v['ti_endtime'].'000';
                if($v['ti_cover']){
                    if((stripos($v['ti_cover'], 'http') === false) && (stripos($v['ti_cover'], 'https') === false) ) {
                        $forum = $v['ti_remote'] ? '/' : '/forum/';
                        $data[$k]['ti_cover'] = ImageUtils::getAttachUrl($v['ti_remote']).$forum.$v['ti_cover'];
                    }
                }
                $data[$k]['icon'] = UserUtils::getUserAvatar($v['ti_authorid']);
                $data[$k]['fid'] = $data[$k]['ti_fid'] ? $data[$k]['ti_fid'] : $finfo['fid'];
                $data[$k]['fname'] = ForumUtils::getForumName($data[$k]['fid']);
                $data[$k]['iscare'] = $uid ? $this->_isCare($v['ti_id'],$uid) : 0;
            }
        }
        $res['list'] = $data;
        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count['COUNT(0)'],$res);
        WebUtils::outputWebApi($res);
    }

    /**
     * @param $tid 话题id
     * @param $uid 关注人id
     */
    private function _isCare($tid,$uid){
        $count = DbUtils::createDbUtils(true)->queryRow("SELECT COUNT(0) FROM %t WHERE `ti_id`=%d AND `uid`=%d",array('appbyme_tpctou',$tid,$uid));
        return $count['COUNT(0)'];
    }
}