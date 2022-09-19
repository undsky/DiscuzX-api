<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/11/29
 * Time: 下午4:12
 * Email: mqdjie@gmail.com
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class PortalModels
{
    const SOURCE_TYPE_AID = 'aid';
    const SOURCE_TYPE_TID = 'tid';
    const SOURCE_TYPE_FID = 'fid';
    const SOURCE_TYPE_CATID = 'catid';
    const SOURCE_TYPE_URL = 'url';
    const SOURCE_TYPE_BID = 'bid';
    const SOURCE_TYPE_SLIDER = 2;
    const SOURCE_TYPE_SOURCE = 1;

    private $_data;
    private $_db;
    private $offer;

    public function __construct($data)
    {
        $this->_data = $data;
        $this->_db   = DbUtils::createDbUtils(true);
        $this->offer = ($this->_data['page'] - 1) * $this->_data['pageSize'];
    }

    public function getModules()
    {
        return $this->_db->queryAll('
            SELECT mid, name, displayorder
            FROM %t
            ORDER BY displayorder ASC
            LIMIT %d,%d
            ', array('appbyme_portal_module', $this->offer, $this->_data['pageSize'])
        );
    }

    public function getSources()
    {
        return $this->_db->queryAll('
            SELECT *
            FROM %t
            WHERE mid=%d AND type=%d
            ORDER BY displayorder ASC
            LIMIT %d, %d
            ', array('appbyme_portal_module_source', $this->_data['mid'], $this->_data['type'], $this->offer, $this->_data['pageSize'])
        );
    }

    public function getParams()
    {
        $ConSources = $this->_db->queryRow('
            SELECT id, idtype
            FROM %t
            WHERE mid = %d
            AND idtype IN (%n)
        ', array('appbyme_portal_module_source', $this->_data['mid'], array('catid', 'fid'))
        );
        if(empty($ConSources)) {
            return false;
        }
        $res = $this->_db->queryScalar('
            SELECT param
            FROM %t
            WHERE mid=%d
            ', array('appbyme_portal_module', $this->_data['mid'])
        );
        $res = unserialize($res) ? unserialize($res) : array();
        if(!empty($res)) {
            if($ConSources['idtype'] == 'fid') {
                unset($res['article_picrequired']);
                unset($res['article_starttime']);
                unset($res['article_endtime']);
                unset($res['article_orderby']);
                unset($res['article_publishdateline']);
                return $res;
            } else {
                $arr['article_endtime'] = $res['article_endtime'];
                $arr['article_orderby'] = $res['article_orderby'];
                $arr['article_starttime'] = $res['article_starttime'];
                $arr['article_picrequired'] = $res['article_picrequired'];
                $arr['article_publishdateline'] = $res['article_publishdateline'];
                unset($res);
                return $arr;
            }
        }
    }

    public function updateModule()
    {
        foreach ($this->_data['displayorder'] as $k => $v) {
            $this->_db->update('appbyme_portal_module',
                array('displayorder' => $v, 'name' => $this->_data['name'][$k]),
                array('mid' => $k));
        }
        return true;
    }

    public function insertModule()
    {
        return $this->_db->insert('appbyme_portal_module', array(
            'name' => $this->_data['name'],
            'displayorder' => intval($this->_data['displayorder']),
            'param' => serialize($this->initModuleParam())
        ), true);
    }

    public function delModules() {

        $delmodule = $this->_db->delete('appbyme_portal_module', array(
            'where' => 'mid IN (%n)',
            'arg' => array($this->_data['mids'])
        ));
        $deldata = $this->_db->delete('appbyme_portal_module_source', array(
            'where' => 'mid IN (%n)',
            'arg' => array($this->_data['mids'])
        ));
        if($deldata && $delmodule) {
            return true;
        }
        return false;
    }

    public function initModuleParam() {
        return array(
            'topic_digest' => '',
            'topic_stick' => '',
            'topic_special' => '',
            'other_filter' => 0,
            'topic_picrequired' => 0,
            'topic_orderby' => 'lastpost',
            'topic_postdateline' => 0,
            'topic_lastpost' => 0,
            'topic_style' => 0,
            'article_picrequired' => 0,
            'article_starttime' => 0,
            'article_endtime' => 0,
            'article_orderby' => 'dateline',
            'article_publishdateline' => 0,
        );
    }

    public function insertSource()
    {
        $ConSources = $this->getConSources();
        if(($ConSources !== false) && $ConSources != $this->_data['new_soure_idtype']) {
            return false;
        }
        foreach ($this->_data['new_soure_ids'] as $id) {
            $source = $this->getSourceInfo($id, $this->_data['new_soure_idtype']);
            $source = array_merge($source, array(
                'mid'          => $this->_data['mid'],
                'displayorder' => intval($this->_data['new_soure_displayorder']),
                'type'         => self::SOURCE_TYPE_SOURCE,
                'param'        => '',
            ));
            $source['sid'] = $this->_db->insert('appbyme_portal_module_source', $source, true);
            $res[] = $source;
        }
        return empty($res) ? '' : $res;
    }

    public function insertSlider()
    {
        if (!empty($this->_data['new_id']) && $this->_data['new_idtype'] == self::SOURCE_TYPE_BID) {
            $this->_data['new_imgid'] = $this->_data['new_id'];
            $this->_data['new_imgtype'] = $this->_data['new_idtype'];
        }
        if (!empty($this->_data['new_id']) && !empty($this->_data['new_imgid'])) {
            $source = $this->getSourceInfo(
                $this->_data['new_id'], $this->_data['new_idtype'], $this->_data['new_imgid'], $this->_data['new_imgtype'], $this->_data['new_title']
            );
            $source = array_merge($source, array(
                'mid'          => $this->_data['mid'],
                'displayorder' => intval($this->_data['new_displayorder']),
                'type'         => self::SOURCE_TYPE_SLIDER,
                'param'        => '',
            ));
            $source['sid'] = $this->_db->insert('appbyme_portal_module_source', $source, true);
        }
        return empty($source) ? '' : $source;
    }

    public function updateParams()
    {
        $param = $this->initModuleParam();
        // 更新模块参数
        if (!empty($this->_data['param']) && is_array($this->_data['param'])) {
            $ConSources = $this->_db->queryScalar('
                SELECT idtype
                FROM %t
                WHERE mid = %d
                AND idtype IN (%n)
            ', array('appbyme_portal_module_source', $this->_data['mid'], array('catid', 'fid'))
            );
            if(!($ConSources)) {
                return false;
            }
            $param = array_merge($param, $this->_data['param']);
            if($ConSources == 'catid' && ($param['article_starttime'] > $param['article_endtime'])) {
                return false;
            }
            $res = $this->_db->update(
                'appbyme_portal_module',
                array('param' => serialize($param)),
                array('mid' => $this->_data['mid']));
            return $res ? $param : false;
        }
    }

    public function delSource()
    {
        return $this->_db->delete('appbyme_portal_module_source', array(
            'where' => 'mid = %d AND sid IN (%n) AND type = %d',
            'arg' => array($this->_data['mid'], $this->_data['del_source_sids'], self::SOURCE_TYPE_SOURCE)
        ));
    }
    public function delSlider()
    {
        return $this->_db->delete('appbyme_portal_module_source', array(
            'where' => 'mid = %d AND sid IN (%n) AND type = %d',
            'arg' => array($this->_data['mid'], $this->_data['del_slider_sids'], self::SOURCE_TYPE_SLIDER)
        ));
    }

    public function updateSource()
    {
        foreach ($this->_data['new_source_ids'] as $k => $v) {
            /*$ConSource = $this->getConSources();
            if(($ConSource != $this->_data['idtype']) && in_array($this->_data['idtype'], array('catid', 'fid'))) {
                continue;
            }*/
            $source = $this->getSourceInfo($v, $this->_data['new_soure_idtypes'][$k]);
            $source = array_merge($source, array('displayorder' => intval($this->_data['new_soure_displayorders'][$k])));
            $this->_db->update(
                'appbyme_portal_module_source',
                $source,
                array('mid' => $this->_data['mid'], 'type' => self::SOURCE_TYPE_SOURCE, 'sid' => $k));
        }
        return true;
    }

    public function updateSlider()
    {
        foreach ($this->_data['new_ids'] as $k => $v) {
            $source = $this->getSourceInfo(
                $v, $this->_data['new_idtypes'][$k], $this->_data['new_imgids'][$k], $this->_data['new_imgtypes'][$k], $this->_data['new_titles'][$k]
            );
            $source = array_merge($source, array('displayorder' => $this->_data['new_displayorders'],));
            $this->_db->update(
                'appbyme_portal_module_source',
                $source,
                array('mid' => $this->_data['mid'], 'type' => self::SOURCE_TYPE_SLIDER, 'sid' => $k));
        }
        return true;
    }

    public function getSourceInfo($id, $idtype, $imgid = 0, $imgtype = '', $title = '') {
        $source = array(
            'id' => 0, 'url' => '', 'idtype' => $idtype,
            'imgid' => 0, 'imgurl' => '', 'imgtype' => $imgtype,
            'title' => '',
        );
        switch ($idtype) {
            case self::SOURCE_TYPE_AID:
                $article = $this->_db->queryRow('
                    SELECT title
                    FROM %t
                    WHERE aid=%d
                    ', array('portal_article_title', $id)
                );
                $source['id'] = $id;
                $source['url'] = 'portal.php?mod=view&aid=' . $id;
                $source['title'] = !empty($article['title']) ? $article['title'] : '';
                break;
            case self::SOURCE_TYPE_TID:
                $topic = $this->_db->queryRow('
                    SELECT subject
                    FROM %t
                    WHERE tid=%d
                    ', array('forum_thread', $id)
                );
                $source['id'] = $id;
                $source['url'] = 'forum.php?mod=viewthread&tid=' . $id;
                $source['title'] = !empty($topic['subject']) ? $topic['subject'] : '';
                break;
            case self::SOURCE_TYPE_URL:
                $source['url'] = $id;
                break;
            case self::SOURCE_TYPE_BID:
                $block = $this->_db->queryRow('
                    SELECT name
                    FROM %t
                    WHERE bid=%d
                    ', array('common_block', $id)
                );
                $source['id'] = $id;
                $source['title'] = !empty($block['name']) ? $block['name'] : '';
                $source['imgid'] = $id;
                $source['imgtype'] = $idtype;
                break;
            case self::SOURCE_TYPE_FID:
                $forum = $this->_db->queryRow('
                    SELECT name
                    FROM %t
                    WHERE fid=%d
                    ', array('forum_forum', $id)
                );
                $source['id'] = $id;
                $source['url'] = 'forum.php?mod=forumdisplay&fid=' . $id;
                $source['title'] = !empty($forum['name']) ? $forum['name'] : '';
                break;
            case self::SOURCE_TYPE_CATID:
                $category = $this->_db->queryRow('
                    SELECT catname
                    FROM %t
                    WHERE catid=%d
                    ', array('portal_category', $id)
                );
                $source['id'] = $id;
                $source['url'] = 'portal.php?mod=list&catid=' . $id;
                $source['title'] = !empty($category['catname']) ? $category['catname'] : '';
                break;
            default:
                break;
        }
        !empty($title) && $source['title'] = $title;

        if ($imgid == 0 && $imgtype == '') {
            $source['imgid'] = $imgid = $id;
            $source['imgtype'] = $imgtype = $idtype;
        }
        switch ($imgtype) {
            case self::SOURCE_TYPE_AID:
                $article = $this->_db->queryRow('
                    SELECT pic, remote
                    FROM %t
                    WHERE aid=%d
                    ', array('portal_article_title', $imgid)
                );
                $source['imgid'] = $imgid;
                if (!empty($article)) {
                    require_once DISCUZ_ROOT . './source/function/function_home.php';
                    $source['imgurl'] = pic_get($article['pic'], '', 0, $article['remote']);
                }
                break;
            case self::SOURCE_TYPE_TID:
                $topicImage = $this->_db->queryRow('
                    SELECT *
                    FROM %t
                    WHERE tid=%d
                    ', array('forum_threadimage', $imgid)
                );
                $source['imgid'] = $imgid;
                if (!empty($topicImage)) {
                    require_once DISCUZ_ROOT . './source/function/function_home.php';
                    $source['imgurl'] = pic_get($topicImage['attachment'], 'forum', 0, $topicImage['remote']);
                }
                break;
            case self::SOURCE_TYPE_URL:
                $source['imgurl'] = $imgid;
                break;
            case self::SOURCE_TYPE_BID:
                if ($source['idtype'] != self::SOURCE_TYPE_BID) {
                    $source['imgid'] = $id;
                    $source['imgtype'] = $idtype;
                }
                break;
            default:
                break;
        }
        return $source;
    }

    public function getConSources()
    {
        return $this->_db->queryScalar('
                SELECT idtype
                FROM %t
                WHERE mid = %d
                AND idtype IN (%n)
            ', array('appbyme_portal_module_source', $this->_data['mid'], array('catid', 'fid'))
        );
    }
}