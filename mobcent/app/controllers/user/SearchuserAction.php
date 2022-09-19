<?php

/**
 * 用户搜索.
 * User: 肖聪杰
 * Date: 2016/4/9
 * Time: 11:25
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class SearchuserAction extends MobcentAction {
    public function run($keyword, $page = 1, $pageSize = 10, $searchid = '') {
        $keyword = rawurldecode($keyword);
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_getForumData($res, $keyword, $page, $pageSize, $searchid);
        WebUtils::outputWebApi($res);
    }

    private function _getForumData($res, $keyword, $page, $pageSize, $searchid) {
        global $_G;
        //判断系统是否开启搜索的功能
        if (!$_G['setting']['search']['forum']['status']) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'search_forum_closed');
        }
        //判断当前的用户是否有搜索的权限
        if (!$_G['adminid'] && !($_G['group']['allowsearch'] & 2)) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'group_nopermission', array('{grouptitle}' => $_G['group']['grouptitle']));
        }
        if (trim($keyword) == '') {
            return WebUtils::makeErrorInfo_oldVersion($res, 'faq_keyword_empty');
        }
        $keyword = WebUtils::t(dhtmlspecialchars(trim($keyword)));


        require_once libfile('function/search');
        //普通搜索数据
        return $this->_searchData($keyword, $page, $pageSize, $res, $searchid);
    }

    //普通搜索数据
    private function _searchData($keyword, $page, $pageSize, $res, $searchid) {
        global $_G;
        //获取当前的用户信息
        $srchmod = 10;//用户搜索10
        $cachelife_time = 300;
        $cachelife_text = 3600;
        // 排序规则
        $orderby = 'uid';
        $ascdesc = 'desc';

        //判断是否有searchid
        if(empty($searchid)) {
            !($_G['group']['exempt'] & 10) && checklowerlimit('search');
            $searchstring = 'user|username|'.addslashes($keyword);
            $searchindex = array('id' => 0, 'dateline' => '0');
            //
            foreach(C::t('common_searchindex')->fetch_all_search($_G['setting']['search']['group']['searchctrl'], $_G['clientip'], $_G['uid'], $_G['timestamp'], $searchstring, $srchmod) as $index) {
                if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
                    $searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
                    break;
                } elseif($_G['adminid'] != '1' && $index['flood']) {
                    return WebUtils::makeErrorInfo_oldVersion($res, 'search_ctrl', array('searchctrl' => $_G['setting']['search']['group']['searchctrl']));
                }
            }
            if($searchindex['id']) {

                $searchid = $searchindex['id'];

            } else {
                if($_G['adminid'] != '1' && $_G['setting']['search']['group']['maxspm']) {
                    if(C::t('common_searchindex')->count_by_dateline($_G['timestamp'], $srchmod) >= $_G['setting']['search']['group']['maxspm']) {
                        return WebUtils::makeErrorInfo_oldVersion($res, 'search_toomany', array('maxspm' => $_G['setting']['search']['group']['maxspm']));

                    }
                }
                $num = $ids = 0;
                $_G['setting']['search']['group']['maxsearchresults'] = $_G['setting']['search']['group']['maxsearchresults'] ? intval($_G['setting']['search']['portal']['maxsearchresults']) : 500;
                //取得当前用户信息
                $space = getuserbyuid($_G['uid']);
                space_merge($space, 'field_home');
                //获取判断条件
                $fromarr['member'] = DB::table('common_member').' s';
                $keyword = stripsearchkey($keyword);
                $wherearr[] = 's.'.DB::field('username', '%'.$keyword.'%', 'like');//只对用户搜索

                //判断当前用户与查询用户关系
                if($wherearr) {
                    $num = $ids = 0;
                    foreach(C::t('common_member')->fetch_all_for_spacecp_search($wherearr, $fromarr, 0, 100) as $value) {
                        $ids .= ','.$value['uid'];
                        $num++;
                    }
                $keyword = str_replace('%', '+', $keyword);

                $expiration = TIMESTAMP + $cachelife_text;
                $searchid = C::t('common_searchindex')->insert(array(
                    'srchmod' => $srchmod,
                    'keywords' => $keyword,
                    'searchstring' => $searchstring,
                    'useip' => $_G['clientip'],
                    'uid' => $_G['uid'],
                    'dateline' => $_G['timestamp'],
                    'expiration' => $expiration,
                    'num' => $num,
                    'ids' => $ids
                ), true);

                !($_G['group']['exempt'] & 10) && updatecreditbyaction('search');
            }
            }
        }

        //显示搜索结果
        $page = max(1, intval($page));
        $start_limit = ($page - 1) * $pageSize;

        $index = C::t('common_searchindex')->fetch_by_searchid_srchmod($searchid, $srchmod);
        if(!$index) {
          //  showmessage('search_id_invalid');
            return WebUtils::makeErrorInfo_oldVersion($res, 'search_id_invalid');
        }

        $keyword = dhtmlspecialchars($index['keyword']);

        $index['keyword'] = rawurlencode($index['keyword']);

        //缓存如何查表、？？？
        $fromarr['member'] = DB::table('common_member').' s';
        $keyword = stripsearchkey($keyword);

        //用户自己信息
        $space['friends'] = array();
        $query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 0);
        foreach($query as $value) {
            $space['friends'][$value['fuid']] = $value['fuid'];
        }
        $followids =DzCommonUserList::_getFollowUsersByFollower($_G['uid'], 1, 100);
//拼接
    $sql ='SELECT username,uid,credits,status FROM '.DB::table('common_member').' WHERE'.DB::field('uid',explode(',',$index['ids'])).' order by '.$orderby." ".$ascdesc.' limit '.$start_limit.",".$pageSize;
        //查出数据
        $list = DB::fetch_all($sql);
        $i = 0;
         foreach($list as &$value) {
             //判断是否是好友
            $list[$i]['isfriend'] = ($value['uid']==$space['uid'] || $space['friends'][$value['uid']])?1:0;
             $list[$i++]['isfollow'] = in_array($value['uid'],$followids)?1:0;
             $res['body']['list'][] = $this->getList($value,$_G['uid']);
        }

        $res['searchid'] = intval($searchid);
        //翻页
        $pageInfo = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $index['num']);
        $res = array_merge($res, $pageInfo);
        return $res;
    }
    private function getList($list,$uid) {
        loadcache('usergroups');//引入缓存中的用户组

        $res['uid'] = intval($list['uid']);
        $res['icon'] = strval(UserUtils::getUserAvatar($list['uid']));
        $res['isFriend'] = intval($list['isfriend']);
        $res['is_black'] = intval(UserUtils::isBlacklist($list['uid'] ,$uid) ? 1 : 0);
        $res['gender'] = intval(UserUtils::getUserGender($list['uid']));
        $res['name'] = strval($list['username']);
        $res['status'] = intval($list['status']);
        $res['level'] = intval(DzCommonUserList::getUserLevel($list['uid']));
        $res['credits'] = intval($list['credits']);
        $res['isFollow'] = intval($list['isfollow']);
        //用户所在地location
        $res['dateline'] = WebUtils::t(DzCommonUserList::getUserLastVisit($list['uid'])).'000';
        $signature = WebUtils::emptyHtml(DzCommonUserList::getUserSightml($list['uid']));
        $res['signture'] = strval($signature);
        //距离
        $lat = SurroundingInfo::getUserLat($uid);
        $distant = SurroundingInfo::getSeachuser($lat['longitude'],$lat['latitude'],$list['uid']);
        if(empty($distant)) {
            $res['location'] = '';
            $res['distance'] = '';
        }else {
            $res['location'] = $distant['location'];
            $res['distance'] = $distant['distance'];
        }
        $res['userTitle'] = strval(UserUtils::getUserTitle($list['uid']));
        return $res;
    }
}