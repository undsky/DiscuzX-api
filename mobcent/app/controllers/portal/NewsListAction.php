<?php

/**
 * 门户资讯列表
 *
 * @author NaiXiaoXin
 * @author HanPengyu
 * @copyright 2012-2015 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

//Mobcent::setErrors();
class NewsListAction extends MobcentAction {

    const HANDPAGE = 10;

    private $params = array();
    public function run($moduleId, $page = 1, $pageSize = 10, $longitude = '', $latitude = '', $radius = 100000, $isImageList = 0, $circle = '0') {
        $getKeyArray = array('mid' => $moduleId, 'page' => $page, 'pageSize' => $pageSize, 'longitude' => $longitude, 'latitude' => $latitude, 'radius' => $radius, 'isImageList' => $isImageList);
        $filter = unserialize(AppbymePoralModule::getModuleParam($moduleId));
        if (is_array($filter['other_filter']) || $filter['topic_orderby'] == 'distance') {
            $res = $this->getResult($getKeyArray);
            echo WebUtils::outputWebApi($res, '', true);
        }
        $key = CacheUtils::getNewsListKey(array($moduleId, $page, $pageSize, $isImageList, $circle));
        $this->runWithCache($key, $getKeyArray);
    }

    protected function runWithCache($key, $params = array()) {
        extract($params);

        $res = array();
        $cache = $this->getCacheInfo();
        if (!$cache['enable'] || ($res = Yii::app()->cache->get($key)) === false) {
            $res = WebUtils::outputWebApi($this->getResult($params), '', false);
            if ($page == 1 && $cache['enable']) {
                $fids = array();
                $sources = AppbymePortalModuleSource::getSources(
                    $mid, AppbymePortalModuleSource::SOURCE_TYPE_NORMAL, 0, 0, array('idtype' => array(
                        AppbymePortalModuleSource::SOURCE_TYPE_FID,
                    ))
                );
                foreach ($sources as $source) {
                    $fids[] = $source['id'];
                }
                !empty($fids) && $dep = new DiscuzDbCacheDependency('
                    SELECT MAX(lastpost)
                    FROM %t
                    WHERE fid IN (%n)
                    ', array('forum_forum', $fids)
                );

                Yii::app()->cache->set($key, $res, $cache['expire'], $dep);
            }
        }

        echo $res;
    }

    protected function getCacheInfo() {
        $cacheInfo = array('enable' => 1, 'expire' => DAY_SECONDS * 1);
        if (($cache = WebUtils::getDzPluginAppbymeAppConfig('cache_newslist')) > 0) {
            $cacheInfo['expire'] = $cache;
        } else {
            $cacheInfo['enable'] = 0;
        }
        if ($_GET['circle'] == '1') {
            $cacheInfo['enable'] = 0;
        }
        return $cacheInfo;
    }

    protected function getResult($params) {
        extract($params);
        $res = $this->initWebApiArray();
        if ($page == 1) {
            $res['piclist'] = $this->_getPicList($mid);
        } else {
            $res['piclist'] = array();
        }
        $portals = AppbymePortalModuleSource::getPortalByMid($mid);
        // 对错误mid的处理
        if (empty($portals)) {
            $res['list'] = array();
            return $res;
        }
        $hands = $this->_handCount($mid, $portals);
        $autoAdd = AppbymePortalModuleSource::getAutoAdd($mid);
        $params = unserialize(AppbymePoralModule::getModuleParam($mid));
        $params == false && $params = array();
        $this->params = $params;
        $count = 0; // 自动添加的数目
        if (!empty($autoAdd)) {
            if ($autoAdd[0]['idtype'] == 'fid') {
                // [add]板块设置用户组权限后，针对当前用户进行过滤（在列表中是否显示）Author：HanPengyu，Data：14.11.28
                require_once libfile('function/forumlist');
                foreach ($autoAdd as $auto) {
                    $forum = DzForumForum::getForumFieldByFid($auto['id']);
                    forum($forum) && $fids[] = $auto['id'];
                }
                $count = DzForumThread::getByFidCount($fids, $params, $longitude, $latitude, $radius);
            } else {
                foreach ($autoAdd as $auto) {
                    $catids[] = $auto['id'];
                }
                $count = DzPortalArticle::getByCatidCount($catids, $params);
            }
        }
        $handCount = $hands['bids'];
        $total = $handCount + $count;
        // 没有查到数据
        if ($total == 0 && $hands['count'] == 0) {
            $res['list'] = array();
            return $res;
        }
        if($page==1){
            $recommend = new RecommendUtils();
            $res['recommendList'] = $recommend->getInfo('portal',$mid);
        }

        $pageInfo = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $total);
        $res = array_merge($res, $pageInfo);
        $offset = ($page - 1) * $pageSize;
        if($handCount - $offset >= $pageSize) {
            $res['list'] = $this->_handData($mid, $offset, $pageSize);
            return $res;
        }
        //共存问题
        $isset = $handCount - $offset;
        //单独处理只有tid和aid时候的问题
        if($isset == 0 && $handCount == 0 && $hands['count'] != 0) {
            $handData = $this->_handData($mid, $offset, $pageSize);
        }
        if($isset < $pageSize && $isset > 0) {
            $handData = $this->_handData($mid, $offset, $isset);
            $offset = 0;
            $pageSize -= $isset;
        } else {
            $offset = - $isset;
        }
        if ($count != 0) {
            if ($autoAdd[0]['idtype'] == 'fid') {
                $autoData = $this->_autoFidData($fids, $offset, $pageSize, $params, $longitude, $latitude, $radius);
            } else {
                $autoData = $this->_autoCatidData($catids, $offset, $pageSize, $params);
            }
            $res['list'] = $autoData;
        } else {
            $res['list'] = array();
        }
        if(!empty($handData)) {
            $res['list'] = array_merge($handData, $res['list']);
        }

        return $res;
    }

    // 取出手动添加的数据
    private function _handData($mid, $offset, $pageSize, $params = array()) {
        $rows = array();
        $portals = AppbymePortalModuleSource::getPortalByMid($mid);
        //tid和aid只在首页返回
        if($offset < $pageSize) {
            foreach ($portals as $portal) {
                if ($portal['idtype'] == 'tid') {
                    $topicSummary = ForumUtils::getTopicSummary($portal['id'], 'portal', true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
                    $rows[] = $this->_getListField(ForumUtils::getTopicInfo($portal['id']), $topicSummary, 'topic', $portal['id']);
                } elseif ($portal['idtype'] == 'aid') {
                    $articleSummary = PortalUtils::getArticleSummary($portal['id'], true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
                    $articleInfo = $this->_getArticleByAid($portal['id']);
                    $rows[] = $this->_getListField($articleInfo, $articleSummary, 'news', $portal['id']);
                }
            }
        }
        foreach ($portals as $portal) {
            if ($portal['idtype'] == 'bid') {
                $rows = array_merge($rows, $this->getTopArtData(AppbymePortalModuleSource::getDataByBid($portal['id'], $offset, $pageSize)));
            }
        }

        return $rows;
    }

    // 获取手动添加文章或者是帖子的内容
    private function getTopArtData($handInfos) {
        $rows = array();
        if (empty($handInfos))
            return $rows;
        foreach ($handInfos as $hand) {
            //Add 兼容手动拉取 Author NaiXiaoXin Date:2015-9-25
            if ($hand['idtype'] == 'rand') {
                $temp = unserialize($hand['fields']);
                if ($temp['id'] == '0') {
                    //啥都不要操作，一般是weblink
                } elseif (!$temp['lastpost'] && $temp['id'] > '0') {
                    $hand['idtype'] = 'aid';
                    $hand['id'] = $temp['id'];
                } else {
                    $hand['idtype'] = 'tid';
                    $hand['id'] = $temp['id'];
                }
            }

            if ($hand['idtype'] == 'tid') {
                $topicSummary = ForumUtils::getTopicSummary($hand['id'], 'portal', true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
                $topicInfo = ForumUtils::getTopicInfo($hand['id']);

                //  add:在添加自定义内容的时候，手动修改的帖子标题
                $hand['title'] && $topicInfo['subject'] = $hand['title'];
                // add:DIY自定义摘要 Author:NaiXiaoXin Date:2015-9-25
                $hand['summary'] && $topicSummary['msg'] = $hand['summary'];
                $rows[] = $this->_getListField($topicInfo, $topicSummary, 'topic', $hand['id']);
            } elseif ($hand['idtype'] == 'aid') {
                $articleSummary = PortalUtils::getArticleSummary($hand['id'], true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
                $articleInfo = $this->_getArticleByAid($hand['id']);

                //  add:在添加自定义内容的时候，手动修改的文章标题
                $hand['title'] && $articleInfo['title'] = $hand['title'];
                // add:DIY自定义摘要 Author:NaiXiaoXin Date:2015-9-25
                $hand['summary'] && $articleSummary['msg'] = $hand['summary'];
                $rows[] = $this->_getListField($articleInfo, $articleSummary, 'news', $hand['id']);
            }
        }
        //debug($rows);

        return $rows;
    }

    // 通过fids来取出数据
    private function _autoFidData($fids, $offset, $pageSize, $params, $longitude, $latitude, $radius) {
        $lists = DzForumThread::getByFidData($fids, $offset, $pageSize, $params, $longitude, $latitude, $radius);
        $rows = array();
        foreach ($lists as $list) {
            $topicSummary = ForumUtils::getTopicSummary($list['tid'], 'portal', true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
            $rows[] = $this->_getListField($list, $topicSummary, 'topic', $list['tid'], $params);
        }
        return $rows;
    }

    // 通过catids来取出数据
    private function _autoCatidData($catids, $offset, $pageSize, $params) {
        $lists = DzPortalArticle::getByCatidData($catids, $offset, $pageSize, $params);
        $rows = array();
        foreach ($lists as $list) {

            // [add]考虑文章是通过帖子来发布的时候的评论数问题 Author：HanPengyu，Data：14.09.28
            if ($list['idtype'] == 'tid') {
                $topicInfo = ForumUtils::getTopicInfo($list['id']);
                $list['commentnum'] = (int) $topicInfo['replies'];
            }

            $articleSummary = PortalUtils::getArticleSummary($list['aid'], true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
            $rows[] = $this->_getListField($list, $articleSummary, 'news', $list['aid']);
        }
        return $rows;
    }

    // 通过aid来取出文章的信息
    private function _getArticleByAid($aid) {
        $articleCount = PortalUtils::getArticleCount($aid);
        $articleInfo = PortalUtils::getNewsInfo($aid);

        // [add]考虑文章是通过帖子来发布的时候的评论数问题 Author：HanPengyu，Data：14.09.27
        if ($articleInfo['idtype'] == 'tid') {
            $topicInfo = ForumUtils::getTopicInfo($articleInfo['id']);
            $articleCount['commentnum'] = (int) $topicInfo['replies'];
        }

        $articleInfo = array_merge($articleCount, $articleInfo);
        return $articleInfo;
    }

    /**
     * 列表显示需要的字段
     *
     * @param array $list      帖子或者文章的详细字段信息.
     * @param array $summary      帖子或者文章的摘要和图片.
     * @param string $source_type 源的类型.
     * @param int $source_id      源的id.
     *
     * @return array 整理好的字段.
     */
    private function _getListField($list, $summary, $source_type, $source_id, $params = array()) {
        $row = array();
        $params = $this->params;
        // 显示样式
        if ($params['topic_style'] == 2) {
            $statu = 'lastpost';
        } else {
            $statu = 'dateline';
        }

        if ($source_type == 'topic') {
            $row['special'] = (int) $list['special'];
            $row['fid'] = (int) $list['fid'];
            $row['board_id'] = (int) $list['fid'];
            $row['board_name'] = ForumUtils::getForumName($row['fid']);
            $row['board_name'] = WebUtils::emptyHtml($row['board_name']);
            if ($list['author'] == NULL) {
                global $_G;
                $list['authorid'] = (int) '0';
                $list['author'] = $_G['setting']['anonymoustext'];
            }
            if ($_GET['circle'] == '1') {
                $row['zanList'] = (array) DzForumThread::getZanList($source_id, '20', '1');
                $reply = array();
                $reply = DzForumThread::Reply($source_id);
                if (!empty($reply)) {
                    $row['reply'] = $reply;
                }
            }
            //Fix阅读数延迟更新
            $addThreadViews = C::t('forum_threadaddviews')->fetch($_G['tid']);
            if (!empty($addThreadViews)) {
                $row["views"] +=$addThreadViews["addviews"];
            }
        }

        $row['source_type'] = (string) $source_type;
        $row['source_id'] = (int) $source_id;
        $row['title'] = $source_type == 'topic' ? (string) $list['subject'] : (string) $list['title'];
        $row['title'] = WebUtils::emptyHtml($row['title']);
        $row['user_id'] = $source_type == 'topic' ? (int) $list['authorid'] : (int) $list['uid'];
        $row['last_reply_date'] = $source_type == 'topic' ? $list[$statu] . '000' : $list['dateline'] . '000';
        $row['user_nick_name'] = $source_type == 'topic' ? (string) $list['author'] : (string) $list['username'];
        $row['hits'] = $source_type == 'topic' ? (int) $list['views'] : (int) $list['viewnum'];
        $row['summary'] = (string) $summary['msg'];
        $row['replies'] = $source_type == 'topic' ? (int) $list['replies'] : (int) $list['commentnum'];
        if($list['remote'] && stripos($list['pic'],'http') !== false){
            $row['pic_path'] = $list['pic'];
            $tempRow['ratio'] = 1;
        }else{
            $tempRow = ImageUtils::getThumbImageEx($summary['image'], 15, true, true);
            $row['pic_path'] = (string) $tempRow['image'];
        }
        $row['ratio'] = $tempRow['ratio'];
        $row['redirectUrl'] = (string) $list['url'];

        $row['userAvatar'] = (string) UserUtils::getUserAvatar($row['user_id']);
        $row['gender'] = (int) UserUtils::getUserGender($row['user_id']);
        $row['recommendAdd'] = $source_type == 'topic' ? (int) ForumUtils::getRecommendAdd($row['source_id']) : 0;
        $row['isHasRecommendAdd'] = $source_type == 'topic' ? (int) ForumUtils::isHasRecommendAdd($row['source_id']) : 0;
        $row['distance'] = isset($list['distance']) ? (string) $list['distance'] : '';
        $row['location'] = isset($list['location']) ? (string) $list['location'] : '';
        $row['imageList'] = (array) $summary['imageList'];
        if($source_type=='news' && count($summary['imageList']) == 1){
            $row['imageList'] = array();
            $row['imageList'][] = $row['pic_path'] ;
        }else{
            $row['imageList'] = (array) $summary['imageList'];
        }
        $row['videoList'] = $summary['videoList'];

        $row['sourceWebUrl'] = (string) ForumUtils::getSourceWebUrl($source_id, $source_type);
        $row['verify'] = UserUtils::getVerify($row['user_id']);
        return $row;
    }

    /**
     * 获取手动添加的总数包括tid，aid，还有bid，也可以取出bids
     *
     * @param int $mid
     * @param array  $portals 门户表信息.
     * @param string $type 取出的类型，$type=count,取出总数。$type=bid，取出bids.
     *
     * @access private
     *
     * @return mixed Value.
     */
    private function _handCount($mid, $portals, $type = "count") {
        // tid、aid的数目
        $forumCount = AppbymePortalModuleSource::getHandCount($mid);
        // bid 下面内容的数目
        $portalCount = 0;
        $bids = array();
        $aids = array();
        $tids = array();
        foreach ($portals as $k => $v) {
            switch ($v['idtype']) {
                case 'bid' :
                    $bids[] = intval($v['id']);
                    $portalCount += AppbymePortalModuleSource::getCountByBid($v['id']);
                    break;
                case 'tid' :
                    $tids[] = $v['id'];
                    break;
                case 'aid' :
                    $aids[] = $v['id'];
                    break;
            }
        }
        $count = $forumCount + $portalCount;
        $res['count']   = $type == 'count' ? $count : $bids;
        $res['tids'] = $tids;
        $res['aids'] = $aids;
        $res['bids'] = $portalCount;
        return $res;
    }

    // 获取一个模块下面幻灯片的信息
    private function _getPicList($mid) {
        $portals = AppbymePortalModuleSource::getPortalByMid($mid, 2);
        $piclist = array();
        //debug($portals);
        foreach ($portals as $portal) {
            $pic_path = ImageUtils::getThumbImage(WebUtils::getHttpFileName($portal['imgurl']));
            if ($portal['idtype'] == 'tid') {
                $topicinfo = ForumUtils::getTopicInfo($portal['id']);
                $piclist[] = $this->_fieldPicList($topicinfo['fid'], 'topic', $portal['id'], $portal['title'], $pic_path);
            } elseif ($portal['idtype'] == 'aid') {
                $piclist[] = $this->_fieldPicList(0, 'news', $portal['id'], $portal['title'], $pic_path);
            } elseif ($portal['idtype'] == 'url') {
                $piclist[] = $this->_fieldPicList(0, 'weblink', $portal['id'], $portal['title'], $pic_path, $portal['url']);
            } elseif ($portal['idtype'] == 'bid') {
                $piclist = array_merge($piclist, $this->_getPicByBid($portal['id']));
            }
        }

        return $piclist;
    }

    // 幻灯片需要的字段
    private function _fieldPicList($fid, $source_type, $source_id, $title, $pic_path, $pic_toUrl = '') {
        $row = array();
        $row['fid'] = (int) $fid;
        $row['source_type'] = (string) $source_type;
        $row['source_id'] = (int) $source_id;
        $row['title'] = WebUtils::emptyHtml($title);
        $row['pic_path'] = (string) $pic_path;
        if ($source_type == 'weblink') {
            $row['pic_toUrl'] = (string) $pic_toUrl;
        }
        return $row;
    }

    // 通过bid取出幻灯片的数据
    private function _getPicByBid($bid) {
        global $_G;
        block_get($bid);
        $itemList = $_G['block'][$bid]['itemlist'];
        $list = array();
        foreach ($itemList as $item) {
            $sourceType = $item['idtype'] == 'aid' ? 'news' : 'topic';
            $sourceId = $item['id'];
            $title = $item['title'];
            //Add 兼容手动拉取 Author NaiXiaoXin Date:2015-9-25
            if ($item['idtype'] == 'rand') {
                $temp = unserialize($item['fields']);
                if ($temp['id'] == '0') {
                    $sourceType = 'weblink';
                    $sourceId = '0';
                    $url = $item['url'];
                } elseif (!$temp['lastpost'] && $temp['id'] > '0') {
                    $sourceType = 'news';
                    $sourceId = $temp['id'];
                } else {
                    $sourceType = 'topic';
                    $sourceId = $temp['id'];
                }
            }
            if ($item['makethumb'] == 1) {  // 生成缩略图成功
                if ($item['picflag'] == 1) {    // 本地
                    $picPath = $_G['setting']['attachurl'] . $item['thumbpath'];
                } elseif ($item['picflag'] == 2) {    // 远程
                    $picPath = $_G['setting']['ftp']['attachurl'] . $item['thumbpath'];
                }
            } elseif ($item['makethumb'] == 0) {    // 缩略图生成失败
                $picPath = $item['pic'];
            }

            $picPath = ImageUtils::getThumbImage(WebUtils::getHttpFileName($picPath));
            $list[] = $this->_fieldPicList(0, $sourceType, $sourceId, $title, $picPath, $url);
        }
        return $list;
    }

}
