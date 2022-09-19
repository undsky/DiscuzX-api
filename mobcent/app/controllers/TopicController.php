<?php
/**
 * topic控制器
 * @author tanguanghua <18725648509@163.com>
 **/
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME'))
{
    exit('Access Denied');
}

class TopicController extends MobcentController
{

    public function actions()
    {
        return array(
            'topiclist' => 'application.controllers.topic.TopiclistAction',
            'caretpc'   => 'application.controllers.topic.CaretpcAction',
            'topicdtl'  => 'application.controllers.topic.TopicdtlAction',
            'mytopic'   => 'application.controllers.topic.MytopicAction',
            'subtopic'  => 'application.controllers.topic.SubtopicAction',
            'search'    => 'application.controllers.topic.SearchAction',
            'forum'     => 'application.controllers.topic.ForumAction'
        );
    }

    protected function mobcentAccessRules()
    {
        return array(
            'topiclist' => false,
            'caretpc'   => true,
            'topicdtl'  => false,
            'mytopic'   => true,
            'subtopic'  => false,
            'search'    => true,
            'forum'     => false
        );
    }
}