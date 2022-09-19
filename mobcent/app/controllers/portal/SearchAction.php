<?php
/**
 *
 * 搜索文章接口
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class SearchAction extends MobcentAction {
    public function run($keyword, $searchid = '', $page = 1, $pageSize = 20) {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_seachPortal($res, $keyword, $searchid, $page, $pageSize);
        WebUtils::outputWebApi($res);
    }

    private function _seachPortal($res, $kw, $searchid, $page, $pageSize) {
        global $_G;
        if (!$_G['setting']['search']['portal']['status']) {
            return $this->makeErrorInfo($res, 'search_portal_closed');
        }
        if ($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 1)) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'group_nopermission', array('{grouptitle}' => $_G['group']['grouptitle']));
        }
        $keyword = WebUtils::t(dhtmlspecialchars(trim($kw)));

        require_once libfile('function/home');
        require_once libfile('function/portal');
        require_once libfile('function/search');

        $srchmod = 1;

        $cachelife_time = 300;
        $cachelife_text = 3600;
        //note 排序规则
        $orderby = 'aid';
        $ascdesc = 'desc';

        if (empty($searchid)) {
            !($_G['group']['exempt'] & 2) && checklowerlimit('search');
            $searchstring = 'portal|title|' . addslashes($keyword);
            $searchindex = array('id' => 0, 'dateline' => '0');
            foreach (C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['portal']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
                if ($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
                    $searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
                    break;
                } elseif ($_G['adminid'] != '1' && $index['flood']) {
                    return WebUtils::makeErrorInfo_oldVersion($res, 'search_ctrl', array('searchctrl' => $_G['setting']['search']['portal']['searchctrl']));
                }
            }
            if ($searchindex['id']) {
                $searchid = $searchindex['id'];
            } else {
                if ($_G['adminid'] != '1' && $_G['setting']['search']['portal']['maxspm']) {
                    if (C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['portal']['maxspm']) {
                        return WebUtils::makeErrorInfo_oldVersion($res, 'search_toomany', array('maxspm' => $_G['setting']['search']['portal']['maxspm']));
                    }
                }
                $num = $ids = 0;
                $_G['setting']['search']['portal']['maxsearchresults'] = $_G['setting']['search']['portal']['maxsearchresults'] ? intval($_G['setting']['search']['portal']['maxsearchresults']) : 500;
                list($keyword, $srchtxtsql) = searchkey($keyword, "title LIKE '%{text}%'", true);
                $query = C::t('portal_article_title')->fetch_all_by_sql(' 1 ' . $srchtxtsql, 'ORDER BY aid DESC ', 0, $_G['setting']['search']['portal']['maxsearchresults']);
                foreach ($query as $article) {
                    $ids .= ',' . $article['aid'];
                    $num++;
                }
                $keywords = str_replace('%', '+', $keyword);
                $expiration = TIMESTAMP + $cachelife_text;
                $searchid = C::t('common_searchindex')->insert(array(
                    'srchmod' => $srchmod,
                    'keywords' => $keywords,
                    'searchstring' => $searchstring,
                    'useip' => $_G['clientip'],
                    'uid' => $_G['uid'],
                    'dateline' => $_G['timestamp'],
                    'expiration' => $expiration,
                    'num' => $num,
                    'ids' => $ids
                ), true);
                !($_G['portal']['exempt'] & 2) && updatecreditbyaction('search');
            }
        }
        $page = max(1, intval($page));
        $start_limit = ($page - 1) * $pageSize;
        $index = C::t('common_searchindex')->fetch_by_searchid_srchmod($searchid, $srchmod);
        if (!$index) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'search_id_invalid');
        }
        $keyword = dhtmlspecialchars($index['keywords']);
        $keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';
        $index['keywords'] = rawurlencode($index['keywords']);
        $articlelist = array();
        $query = C::t('portal_article_title')->fetch_all_for_search(explode(',', $index['ids']), $orderby, $ascdesc, $start_limit, $pageSize);
        foreach ($query as $article) {
            $articlelist[] = $this->_fieldInfo($article);
        }
        $pageInfo = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $index['num']);
        $res = array_merge($res, $pageInfo);
        $res['searchid'] = (int)$searchid;
        $res['list'] = $articlelist;
        return $res;

    }

    private function _fieldInfo($articleInfo) {
        $articleSummary = PortalUtils::getArticleSummary($articleInfo['aid'], true);
        $row['aid'] = (int)$articleInfo['aid'];
        $row['title'] = (string)$articleInfo['title'];
        $row['title'] = WebUtils::emptyHtml($row['title']);
        $row['user_id'] = (int)$articleInfo['uid'];
        $row['dateline'] = $articleInfo['dateline'] . '000';
        $row['user_nick_name'] = (string)$articleInfo['username'];
        $row['hits'] = (int)$articleInfo['viewnum'];
        $row['summary'] = (string)$articleSummary['msg'];
        $tempRow = ImageUtils::getThumbImageEx($articleSummary['image'], 15, true, true);
        $row['pic_path'] = (string)$tempRow['image'];
        $row['userAvatar'] = (string)UserUtils::getUserAvatar($row['user_id']);
        $row['gender'] = (int)UserUtils::getUserGender($row['user_id']);
        return $row;
    }
}