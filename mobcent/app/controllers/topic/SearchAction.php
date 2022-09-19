<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/11/14
 * Time: 16:13
 */

class SearchAction extends MobcentAction{
    public function run($topicTitle,$fid = 0, $page = 1, $pageSize = 20)
    {
        $where = '';
        $res = WebUtils::initWebApiArray_oldVersion();
        if(!$topicTitle && !$fid)
            WebUtils::endAppWithErrorInfo(array(),'mobcent_error_topic');
        if($topicTitle)
            $where .= 'AND ti_title LIKE \''.WebUtils::t(addslashes($topicTitle)).'%\'';
        if($fid)
            $where .= ' AND ti_fid=' . $fid;

        $count = DbUtils::createDbUtils(true)->queryScalar("SELECT COUNT(`ti_id`) AS total FROM %t WHERE ti_starttime<%d AND ti_endtime>%d %i",array('appbyme_topic_items',TIMESTAMP,TIMESTAMP,$where));
        $findData = DbUtils::createDbUtils(true)->queryAll("SELECT `ti_id`,`ti_title`,`ti_fid`,`ti_cover`,`ti_remote` FROM %t WHERE ti_starttime<%d AND ti_endtime>%d %i ORDER BY ti_id DESC LIMIT %d,%d",array('appbyme_topic_items',TIMESTAMP,TIMESTAMP,$where, ($page - 1)*$pageSize, $pageSize));
        $bindFid = DbUtils::createDbUtils(true)->queryScalar("SELECT `cvalue` FROM %t WHERE ckey=%s",array('appbyme_config','topic_bind_fid'));
        foreach($findData as $key => $item)
        {
            if($item['ti_fid'] == 0)
                $item['ti_fid'] = $bindFid;
            if($item['ti_cover']){
                $forum = $item['ti_remote'] ? '/' : '/forum/';
                $item['ti_cover'] = ImageUtils::getAttachUrl($item['ti_remote']).$forum.$item['ti_cover'];
            }

            $findData[$key] = $item;
        }
        $res['list'] = $findData;
        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count, $res);
        WebUtils::outputWebApi($res);
    }
}