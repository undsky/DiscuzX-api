<?php

/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
class ForumAction extends MobcentAction
{

    public function run()
    {
        $res         = WebUtils::initWebApiArray_oldVersion();
        $res['list'] = array();
        $forumData   = AppbymeConfig::getCvalueData('topic_allow_fid', array());
        $bindId      = intval(AppbymeConfig::getCvalueData('topic_bind_fid', 0));
        if (!empty($bindId))
        {
            $res['forumList'][] = $bindId;
        }
        foreach ($forumData as $item)
        {
            $forumName = ForumUtils::getForumName($item);
            if(!$forumName){
                continue;
            }
            $res['list'][] = array(
                'fid'       => $item,
                'forumName' =>$forumName,
                'icon'      => $this->getForumImage($item),
            );
        }
        $temp = DbUtils::getDzDbUtils(true)->queryAll("SELECT distinct ti_fid FROM %t WHERE ti_starttime<%d AND ti_endtime>%d", array('appbyme_topic_items', TIMESTAMP, TIMESTAMP));
        if (!empty($temp))
        {
            foreach ($temp as $value)
            {
                if (!empty($value['ti_fid']))
                    $res['forumList'][] = intval($value['ti_fid']);
            }
        }
        WebUtils::outputWebApi($res);
    }


    protected function getForumImage($fid)
    {
        require_once libfile('function/forumlist');
        //先获得板块信息
        $tempForumInfo = C::t('forum_forum')->fetch_info_by_fid($fid);
        $forumImage    = get_forumimg($tempForumInfo['icon']);
        $forumImage    = (string)WebUtils::getHttpFileName($forumImage);

        return (string)$forumImage;
    }
}