<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/12/5
 * Time: 下午7:20
 * Email: mqdjie@gmail.com
 */

class GetFavoriteInfoAction extends MobcentAction
{
    public function run($idType = 'aid', $orderBy = 'dateline', $page = 1, $pageSize = 10)
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        switch ($idType)
        {
            case 'aid' :
                $res = $this->getResultByAid($res, $idType, $page, $pageSize);
                break;
            default :
                break;
        }
        WebUtils::outputWebApi($res);
    }

    private function getResultByAid($res, $idType, $page, $pageSize)
    {
        global $_G;
        if($count = $this->CountFavoriteByAid($_G['uid']))
        {
            $portals = $this->getUserfavoriteByAid($_G['uid'], $idType, $page, $pageSize);
            foreach($portals as $v)
            {
                $articleSummary = PortalUtils::getArticleSummary($v, true, array('imageList' => $_GET['isImageList'], 'imageListLen' => 9, 'imageListThumb' => 1));
                $articleInfo = $this->_getArticleByAid($v);
                $rows[] = $this->_getListField($articleInfo, $articleSummary, 'news', $v);
            }
            $res['body']['list'] = $rows;
        }
        $pageInfo = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count);
        $res['body'] = array_merge($res['body'], $pageInfo);
        return $res;
    }

    // 获取用户收藏文章
    private function getUserfavoriteByAid($uid, $idType, $page, $pageSize)
    {
        return DbUtils::getDzDbUtils(true)->queryColumn('
            SELECT article.aid
            FROM %t favorite INNER JOIN %t article
            ON favorite.id = article.aid
            WHERE favorite.uid = %d
            AND favorite.idtype = %s
            ORDER BY favorite.dateline DESC
            LIMIT %d, %d
            ',
            array('home_favorite', 'portal_article_content', $uid, $idType, $pageSize * ($page - 1), $pageSize)
        );
    }

    private function CountFavoriteByAid($uid)
    {
        return DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(*)
            FROM %t favorite INNER JOIN %t article
            ON favorite.id = article.aid
            WHERE favorite.uid = %d
            AND favorite.idtype = %s
            ',
            array('home_favorite', 'portal_article_content', $uid, 'aid')
        );
    }

    // 通过aid来取出文章的信息
    private function _getArticleByAid($aid) {
        $articleCount = PortalUtils::getArticleCount($aid);
        $articleInfo = PortalUtils::getNewsInfo($aid);
        $articleInfo = array_merge($articleCount, $articleInfo);
        return $articleInfo;
    }

    private function _getListField($list, $summary, $source_type, $source_id, $params = array()) {
        $row = array();

        $row['aid']   = $list['aid'];
        $row['catid'] = $list['catid'];
        $row['title'] = $source_type == 'topic' ? (string) $list['subject'] : (string) $list['title'];
        $row['title'] = WebUtils::emptyHtml($row['title']);
        $row['user_id'] = $list['uid'] ;
        $row['last_reply_date'] =  $list['dateline'] . '000';
        $row['user_nick_name'] = $source_type == 'topic' ? (string) $list['author'] : (string) $list['username'];
        $row['hits'] = $source_type == 'topic' ? (int) $list['views'] : (int) $list['viewnum'];
        $row['summary'] = (string) $summary['msg'];
        $row['replies'] = $source_type == 'topic' ? (int) $list['replies'] : (int) $list['commentnum'];
        if($list['remote'] && stripos($list['pic'],'http') !== false){
            $row['pic_path'] = $list['pic'];
        }else{
            $tempRow = ImageUtils::getThumbImageEx($summary['image'], 15, true, true);
            $row['pic_path'] = (string) $tempRow['image'];
        }

        $row['userAvatar'] = (string) UserUtils::getUserAvatar($row['user_id']);
        $row['gender'] = (int) UserUtils::getUserGender($row['user_id']);
        $row['distance'] = isset($list['distance']) ? (string) $list['distance'] : '';
        $row['location'] = isset($list['location']) ? (string) $list['location'] : '';
        /*$row['imageList'] = (array) $summary['imageList'];
        if($source_type=='news' && count($summary['imageList']) == 1){
            $row['imageList'] = array();
            $row['imageList'][] = $row['pic_path'] ;
        }else{
            $row['imageList'] = (array) $summary['imageList'];

        }*/
        $row['sourceWebUrl'] = (string) ForumUtils::getSourceWebUrl($source_id, $source_type);
        return $row;
    }
}