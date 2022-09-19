<?php

/**
 * 用户关注好友、粉丝和推荐接口
 *
 * @author 徐少伟 <xushaowei@mobcent.com>
 * @copyright 2012-2014 Appbyme
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
// Mobcent::setErrors();
class UserListAction extends CAction {

    public function run($type='follow', $page=1, $pageSize=10, $orderBy='dateline', $longitude='', $latitude='', $radius=100000,$gender = '') {
        $res = WebUtils::initWebApiArray_oldVersion();
        switch ($orderBy) {
            case 'register': $sortType = 'regdate';break;
            case 'login': $sortType = 'lastvisit';break;
            case 'followed': $sortType = 'follower';break;
            case 'distance': $sortType = 'range';break;
            case 'dateline': $sortType = 'default';break;
            case 'at':$sortType = 'at';break;
            default:break;
        }

        global $_G;
        $uid = $_G['uid'];
        $viewUid = isset($_GET['uid']) ? $_GET['uid'] : $uid;
        $data = HomeBlack::getHomeBlack($uid, $viewUid);
		if ($uid <= 0 || count($data) > 0) {
			$res = WebUtils::makeErrorInfo_oldVersion($res, 'mobcent_error_user_index_err');
		} else {
        	$res = $this->_getUserInfoList($res, $type, $uid, $viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
		}
        echo WebUtils::outputWebApi($res, '', false);
    }

    private function _getUserInfoList($res, $type, $uid, $viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender) {

        $res['list'] = $this->_getUserList($type, $uid, $viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
        $count = $this->_getUserListCount($type, $uid, $viewUid, $page, $pageSize, $gender);
        $res = WebUtils::getWebApiArrayWithPage_oldVersion($page, $pageSize, $count, $res);
        return $res;
    }

    // 用户关注、粉丝和推荐详细信息列表
    private function _getUserList($type, $uid, $viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender) {
        if($gender == '') {
            switch ($type) {
                case 'follow':
                    $users = $this->_getFollowUsers($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius);
                    break;
                case 'followed':
                    $users = $this->_getFollowedUsers($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius);
                    break;
                case 'recommend':
                    $users = $this->_getRecommendUserList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
                    $users = $users['users'];
                    break;
                case 'friend':
                    $users = $this->_getPostFuidList($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius);
                    break;
                case 'all':
                    $users = $this->_getAllUidList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
                    break;
                default:
                    break;
            }
        }else {
            $ids = DzCommonUserList::_getRecommendGenderId($gender);
            if($ids == '') return '';
            switch ($type) {
                case 'follow':
                    $users = $this->_getFollowUsersGender($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $ids);
                    break;
                case 'followed':
                    $users = $this->_getFollowedUsersGender($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $ids);
                    break;
                case 'recommend':
                    $users = $this->_getRecommendUserList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
                    $users = $users['users'];
                    break;
                case 'friend':
                    $users = $this->_getPostFuidListGender($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $ids);
                    break;
                case 'all':
                    $users = $this->_getAllUidList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
                    break;
                default:
                    break;
            }
        }
        $list = $this->_transUserList($users, $viewUid, $longitude, $latitude, $radius, $page, $pageSize, $sortType);
        return $list;
    }

    // 用户关注、粉丝和推荐详细信息
    private function _transUserList($users, $viewUid, $longitude, $latitude, $radius, $page, $pageSize, $sortType) {
        loadcache('usergroups');

        $list = array();
        foreach ($users as $user) {
            $tmpUserInfo = array();
            if ($sortType == 'range') {
                $tmpUserInfo['distance'] = (string)$user['distance'];
                $tmpUserInfo['location'] = (string)WebUtils::t($user['location']);
                $uid = $user['uid'];
            } else {
                $tmpUserInfo['distance'] = '';
                $tmpUserInfo['location'] = '';
                $uid = $user;
            }
            $tmpUserInfo['is_friend'] = UserUtils::isFollow($viewUid, $uid) ? 1 : 0;
            $tmpUserInfo['isFriend'] = UserUtils::isFriend($viewUid, $uid) ? 1 : 0;
            $tmpUserInfo['isFollow'] = UserUtils::isFollow($viewUid, $uid) ? 1 : 0;
            $tmpUserInfo['uid'] = (int)$uid;
            $tmpUserInfo['name'] = UserUtils::getUserName($uid);
            $tmpUserInfo['name'] = WebUtils::emptyHtml($tmpUserInfo['name']);
            $tmpUserInfo['status'] = (int)UserUtils::getUserLoginStatus($uid);
            $tmpUserInfo['is_black'] = UserUtils::isBlacklist($viewUid , $uid) ? 1 : 0;
            $tmpUserInfo['gender'] = (int)UserUtils::getUserGender($uid);
            $tmpUserInfo['icon'] = UserUtils::getUserAvatar($uid);
            $tmpUserInfo['level'] = (int)DzCommonUserList::getUserLevel($uid);
            $tmpUserInfo['userTitle'] = strval(UserUtils::getUserTitle($uid));
            $tmpUserInfo['verify'] = UserUtils::getVerify($uid);
            $lastLogin = WebUtils::t(DzCommonUserList::getUserLastVisit($uid));
            $tmpUserInfo['lastLogin'] = $lastLogin . '000';
            if ($sortType == 'regdate') {
                $lastRegdate = DzCommonUserList::getUserLastRegdate($uid);
                $tmpUserInfo['dateline'] = $lastRegdate . '000';
            } else {
                $tmpUserInfo['dateline'] = $lastLogin . '000';
            }
            $signature = WebUtils::emptyHtml(DzCommonUserList::getUserSightml($uid));
            $tmpUserInfo['signature'] = $signature;
            $userInfo = UserUtils::getUserInfo($uid);
            $tmpUserInfo['credits'] = (int)$userInfo['credits'];
            
            $list[] = $tmpUserInfo;
        }
        return $list;
    }

    // 用户关注、粉丝和推荐的总数
    private function _getUserListCount($type, $uid, $viewUid, $page, $pageSize, $gender) {
        if($gender == '') {
            switch ($type) {
                case 'follow':
                    $count = DzCommonUserList::_getFollowUsersCount($viewUid);
                    break;
                case 'followed':
                    $count = DzCommonUserList::_getFollowedUsersCount($viewUid);
                    break;
                case 'recommend':
                    $count = $this->_getRecommendUserList($uid, $page, $pageSize);
                    $count = $count['count'];
                    break;
                case 'friend':
                    $count = DzCommonUserList::getPostFuidListCount($viewUid);
                    break;
                case 'all':
                    $count = DzCommonUserList::_getRecommendUsersCount($uid);
                    break;
                default:
                    break;
            }
        }else {
            $ids = DzCommonUserList::_getRecommendGenderId($gender);
            if($ids == '') return '';
            switch ($type) {
                case 'follow':
                    $count = DzCommonUserListGender::_getFollowUsersCountGender($viewUid,$ids);
                    break;
                case 'followed':
                    $count = DzCommonUserListGender::_getFollowedUsersCountGender($viewUid,$ids);
                    break;
                case 'recommend':
                    $count = DzCommonUserListGender::_getRecommendUsersSetCount($gender);
                    break;
                case 'friend':
                    $count = DzCommonUserListGender::getPostFuidListCountGender($viewUid,$ids);
                    break;
                case 'all':
                    $count = DzCommonUserListGender::_getRecommendUsersCountGender($uid,$gender);
                    break;
                default:
                    break;
            }
        }
        return $count;
    }

    // 设置用户关注
    private function _getRecommendUserList($uid, $page, $pageSize, $sortType = 'default', $longitude = 0, $latitude = 0, $radius = 0, $gender = '') {
        $users = DzCommonUserList::_getRecommendUsersSet($page, $pageSize);
        if (!empty($users)) {
            $userListInfo = array();
            $userListInfo['users'] = $this->_getRecommendUsers($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
            if($gender == '') {
                $userListInfo['count'] = DzCommonUserList::_getRecommendUsersSetCount();
            }else {
                $userListInfo['count'] = DzCommonUserListGender::_getRecommendUsersSetCount($gender);
            }
            return $userListInfo;
        } else {
            $userListInfo = array();
            $userListInfo['users'] = $this->_getAllUidList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender);
            $userListInfo['count'] = DzCommonUserList::_getRecommendUsersCount($uid);
            return $userListInfo;
        }
    }
    //用户关注排序，增加男女筛选
    private function _getFollowUsersGender($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $ids) {
        switch ($sortType) {
            case 'default':
                return DzCommonUserListGender::_getFollowUsersDefaultGender($viewUid, $page, $pageSize,$ids);
                break;
            case 'regdate':
                return DzCommonUserListGender::_getFollowUsersByRegistGender($viewUid, $page, $pageSize,$ids);
                break;
            case 'lastvisit':
                return DzCommonUserListGender::_getFollowUsersByLastVisitGender($viewUid, $page, $pageSize,$ids);
                break;
            case 'follower':
                return DzCommonUserListGender::_getFollowUsersByFollowerGender($viewUid, $page, $pageSize,$ids);
                break;
            case 'range':
                $sql = DzCommonUserList::_getFollowuid($viewUid,$page,$pageSize);
                if($sql == '') {
                    return '';
                }
                return DzCommonUserListGender::_getFollowUsersByRangeGender($viewUid, $page, $pageSize, $longitude, $latitude, $sql, $radius,$ids);
                break;
            default:
                break;
        }
    }
    // 用户关注排序
    private function _getFollowUsers($viewUid, $page, $pageSize, $sortType, $longitude, $latitude, $radius) {
        switch ($sortType) {
            case 'default':
                return DzCommonUserList::_getFollowUsersDefault($viewUid, $page, $pageSize);
                break;
            case 'regdate':
                return DzCommonUserList::_getFollowUsersByRegist($viewUid, $page, $pageSize);
                break;
            case 'lastvisit':
                return DzCommonUserList::_getFollowUsersByLastVisit($viewUid, $page, $pageSize);
                break;
            case 'follower':
                return DzCommonUserList::_getFollowUsersByFollower($viewUid, $page, $pageSize);
                break;
            case 'range':
                $sql = DzCommonUserList::_getFollowuid($viewUid,$page,$pageSize);
                if($sql == '') {
                    return '';
                }
                return DzCommonUserList::_getFollowUsersByRange($viewUid, $page, $pageSize, $longitude, $latitude, $sql, $radius);
                break;
            default:
                break;
        }
    }
    // 用户粉丝排序选择,增加男女排序的
    private function _getFollowedUsersGender($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $ids) {
        switch ($sortType) {
            case 'default':
                return DzCommonUserListGender::_getFollowedUsersDefaultGender($uid, $page, $pageSize, $ids);
                break;
            case 'regdate':
                return DzCommonUserListGender::_getFollowedUsersByRegistGender($uid, $page, $pageSize, $ids);
                break;
            case 'lastvisit':
                return DzCommonUserListGender::_getFollowedUsersByLastVisitGender($uid, $page, $pageSize, $ids);
                break;
            case 'follower':
                return DzCommonUserListGender::_getFollowedUsersByFollowerGender($uid, $page, $pageSize, $ids);
                break;
            case 'range':
                $sql = DzCommonUserList::_getFolloweduid($uid,$page,$pageSize);
                if($sql == '') {
                    return "";
                }
                return DzCommonUserListGender::_getFollowedUsersByRangeGender($uid, $page, $pageSize, $longitude, $latitude, $sql, $radius, $ids);
                break;
            default:
                break;
        }
    }
    // 用户粉丝排序选择
    private function _getFollowedUsers($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius) {
        switch ($sortType) {
            case 'default':
                return DzCommonUserList::_getFollowedUsersDefault($uid, $page, $pageSize);
                break;
            case 'regdate':
                return DzCommonUserList::_getFollowedUsersByRegist($uid, $page, $pageSize);
                break;
            case 'lastvisit':
                return DzCommonUserList::_getFollowedUsersByLastVisit($uid, $page, $pageSize);
                break;
            case 'follower':
                return DzCommonUserList::_getFollowedUsersByFollower($uid, $page, $pageSize);
                break;
            case 'range':
                $sql = DzCommonUserList::_getFolloweduid($uid,$page,$pageSize);
                if($sql == '') {
                    return "";
                }
                return DzCommonUserList::_getFollowedUsersByRange($uid, $page, $pageSize, $longitude, $latitude, $sql, $radius);
                break;
            default:
                break;
        }
    }

    // 用户推荐关注排序选择
    private function _getRecommendUsers($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender) {
        global $_G;
        if($gender == '') {
            switch ($sortType) {
                case 'default':
                    return DzCommonUserList::_getRecommendUsersSetByDefault($page, $pageSize);
                    break;
                case 'regdate':
                    return DzCommonUserList::_getRecommendUsersSetByRegist($page, $pageSize);
                    break;
                case 'lastvisit':
                    return DzCommonUserList::_getRecommendUsersSetByLastVisit($page, $pageSize);
                    break;
                case 'follower':
                    return DzCommonUserList::_getRecommendUsersSetByFollower($page, $pageSize);
                    break;
                case 'range':
                    return DzCommonUserList::_getRecommendUsersSetByRange($_G['uid'], $page, $pageSize, $longitude, $latitude, $radius);
                    break;
                default:
                    break;
            }
        }else {
            $ids = DzCommonUserList::_getRecommendGenderId($gender);
            if($ids == '') return '';
            switch ($sortType) {
                case 'default':
                    return DzCommonUserListGender::_getRecommendUsersSetByDefaultGender($page, $pageSize, $ids);
                    break;
                case 'regdate':
                    return DzCommonUserListGender::_getRecommendUsersSetByRegistGender($page, $pageSize, $ids);
                    break;
                case 'lastvisit':
                    return DzCommonUserListGender::_getRecommendUsersSetByLastVisitGender($page, $pageSize, $ids);
                    break;
                case 'follower':
                    return DzCommonUserListGender::_getRecommendUsersSetByFollowerGender($page, $pageSize, $ids);
                    break;
                case 'range':
                    return DzCommonUserListGender::_getRecommendUsersSetByRangeGender($_G['uid'], $page, $pageSize, $longitude, $latitude, $radius, $ids);
                    break;
                default:
                    break;
            }
        }
    }

    // 用户好友排序选择
    private function _getPostFuidList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius) {
        switch ($sortType) {
            case 'default':
                return DzCommonUserList::_getPostFuidListByDefault($uid, $page, $pageSize);
                break;
            case 'regdate':
                return DzCommonUserList::_getPostFuidListByRegist($uid, $page, $pageSize);
                break;
            case 'lastvisit':
                return DzCommonUserList::_getPostFuidListByLastVisit($uid, $page, $pageSize);
                break;
            case 'follower':
                return DzCommonUserList::_getPostFuidListByFollower($uid, $page, $pageSize);
                break;
            case 'range':
                $sql = DzCommonUserList::_getFrienduid($uid,$page,$pageSize);
                if($sql == '') {
                    return '';
                }
                return DzCommonUserList::_getPostFuidListByRange($uid, $page, $pageSize, $longitude, $latitude, $sql, $radius);
                break;
            case 'at':
                return DzCommonUserList::_getPostFuidListByAT($uid);
                break;
            default:
                break;
        }
    }
    // 用户好友排序选择，增加男女筛选
    private function _getPostFuidListGender($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $ids) {
        switch ($sortType) {
            case 'default':
                return DzCommonUserListGender::_getPostFuidListByDefaultGender($uid, $page, $pageSize, $ids);
                break;
            case 'regdate':
                return DzCommonUserListGender::_getPostFuidListByRegistGender($uid, $page, $pageSize, $ids);
                break;
            case 'lastvisit':
                return DzCommonUserListGender::_getPostFuidListByLastVisitGender($uid, $page, $pageSize, $ids);
                break;
            case 'follower':
                return DzCommonUserListGender::_getPostFuidListByFollowerGender($uid, $page, $pageSize, $ids);
                break;
            case 'range':
                $sql = DzCommonUserList::_getFrienduid($uid,$page,$pageSize);
                if($sql == '') {
                    return '';
                }
                return DzCommonUserListGender::_getPostFuidListByRangeGender($uid, $page, $pageSize, $longitude, $latitude, $sql, $radius, $ids);
                break;
            case 'at':
                return DzCommonUserList::_getPostFuidListByAT($uid);
                break;
            default:
                break;
        }
    }

    // 全部用户排序选择
    private function _getAllUidList($uid, $page, $pageSize, $sortType, $longitude, $latitude, $radius, $gender) {
        if($gender == '') {
            switch ($sortType) {
                case 'default':
                    return DzCommonUserList::_getRecommendUsersByDefault($uid, $page, $pageSize);
                    break;
                case 'regdate':
                    return DzCommonUserList::_getRecommendUsersByRegist($uid, $page, $pageSize);
                    break;
                case 'lastvisit':
                    return DzCommonUserList::_getRecommendUsersByLastVisit($uid, $page, $pageSize);
                    break;
                case 'follower':
                    return DzCommonUserList::_getRecommendUsersByFollower($uid, $page, $pageSize);
                    break;
                case 'range':
                    return DzCommonUserList::_getRecommendUsersByRange($uid, $page, $pageSize, $longitude, $latitude, $radius);
                    break;
                default:
                    break;
            }
        }else {
            $ids = DzCommonUserList::_getRecommendGenderId($gender);
            if($ids == '') return '';
            switch ($sortType) {
                case 'default':
                    return DzCommonUserListGender::_getRecommendUsersByDefaultGender($uid, $page, $pageSize, $ids);
                    break;
                case 'regdate':
                    return DzCommonUserListGender::_getRecommendUsersByRegistGender($uid, $page, $pageSize, $ids);
                    break;
                case 'lastvisit':
                    return DzCommonUserListGender::_getRecommendUsersByLastVisitGender($uid, $page, $pageSize, $ids);
                    break;
                case 'follower':
                    return DzCommonUserListGender::_getRecommendUsersByFollowerGender($uid, $page, $pageSize, $ids);
                    break;
                case 'range':
                    return DzCommonUserListGender::_getRecommendUsersByRangeGender($uid, $page, $pageSize, $longitude, $latitude, $radius, $ids);
                    break;
                default:
                    break;
            }
        }

    }
}