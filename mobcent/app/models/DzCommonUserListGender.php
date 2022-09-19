<?php
/**
 * 用户关系model
 *前人坑多，没办法必须加.别打我
 * User: 肖聪杰
 * Date: 2016/4/23
 * Time: 12:50
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class DzCommonUserListGender extends DiscuzAR{
    const TYPE_USER = 1;
    //半径查询的
    private static function _getRange($longitude, $latitude, $radius) {
        $lgRange = $radius * 180 / (EARTH_RADIUS * M_PI);
        $ltRange = $lgRange / cos($latitude * M_PI / 180);

        $range['longitude']['max'] = $longitude + $lgRange;
        $range['longitude']['min'] = $longitude - $lgRange;
        $range['latitude']['max'] = $latitude + $ltRange;
        $range['latitude']['min'] = $latitude - $ltRange;

        return $range;
    }
    private static function _getSqlDistance($longitude, $latitude) {
        return sprintf('SQRT(POW((%f-longitude)/0.012*1023,2)+POW((%f-latitude)/0.009*1001,2))', $longitude, $latitude);
    }
    // 所有用户按最新注册排序,外加男女分配
    public static function _getRecommendUsersByRegistGender($uid, $page, $pageSize,$ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT uid
            FROM %t
            WHERE uid != %d
            AND uid in $ids
            ORDER BY regdate DESC
            LIMIT %d, %d
            ",
            array('common_member', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    //获取用户通过gender分类排序
    public static function _getRecommendUsersByDefaultGender($uid, $page, $pageSize,$ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT uid
            FROM %t
            WHERE uid != %d
            AND uid in $ids
            ORDER BY credits DESC
            LIMIT %d, %d
            ",
            array('common_member', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 所有用户按最新登陆排序,按男女排序
    public static function _getRecommendUsersByLastVisitGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT m.uid
            FROM %t m INNER JOIN %t s
            ON m.uid=s.uid
            WHERE m.uid != %d
            AND m.uid in $ids
            ORDER BY s.lastvisit DESC
            LIMIT %d, %d
            ",
            array('common_member', 'common_member_status', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 所有用户按最多粉丝排序,增加男女排序
    public static function _getRecommendUsersByFollowerGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT mem.uid
            FROM %t mem INNER JOIN %t count
            ON mem.uid=count.uid
            WHERE mem.uid != %d
            AND mem.uid in $ids
            ORDER BY count.follower DESC
            LIMIT %d, %d
            ",
            array('common_member', 'common_member_count', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 所有用户按距离排序,增加男女筛选
    public static function _getRecommendUsersByRangeGender($uid, $page, $pageSize, $longitude, $latitude, $radius ,$ids) {
        $range = self::_getRange($longitude, $latitude, $radius);
        $select = '*,' . self::_getSqlDistance($longitude, $latitude) . ' AS distance';
        return DbUtils::getDzDbUtils(true)->queryAll('
            SELECT ' . $select . "
            FROM %t hsu INNER JOIN %t aus
            ON (hsu.object_id=aus.uid)
            INNER JOIN %t cm ON (aus.uid=cm.uid)
            INNER JOIN %t st ON (aus.uid=st.uid)
            WHERE hsu.type=%s
            AND hsu.object_id!=%s
            AND hsu.object_id in $ids
            AND hsu.longitude BETWEEN %s AND %s
            AND hsu.latitude BETWEEN %s AND %s
            AND aus.ukey=%s
            AND aus.uvalue=%s
            ORDER BY distance ASC
            LIMIT %d, %d
            ",
            array(
                'home_surrounding_user',
                'appbyme_user_setting',
                'common_member',
                'common_member_status',
                self::TYPE_USER,
                $uid,
                $range['longitude']['min'],
                $range['longitude']['max'],
                $range['latitude']['min'],
                $range['latitude']['max'],
                AppbymeUserSetting::KEY_GPS_LOCATION,
                AppbymeUserSetting::VALUE_GPS_LOCATION_ON,
                ($page-1)*$pageSize,
                $pageSize
            )
        );

    }
    // 获取用户粉丝默认排序,增加男女筛选
    public static function _getFollowedUsersDefaultGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT uid
            FROM %t
            WHERE followuid=%d AND status=0
            AND uid in $ids
            ORDER BY dateline DESC
            LIMIT %d, %d
            ",
            array('home_follow', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 获取用户粉丝的详细信息按最新注册排序，增加男女筛选
    public static function _getFollowedUsersByRegistGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT f.uid
            FROM %t f INNER JOIN %t m
            ON f.uid=m.uid
            WHERE f.followuid=%d
            AND f.uid in $ids
            AND f.status=0
            ORDER BY m.regdate DESC
            LIMIT %d, %d
            ",
            array('home_follow', 'common_member', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 获取用户粉丝的详细信息按最新登陆排序
    public static function _getFollowedUsersByLastVisitGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT f.uid
            FROM %t f INNER JOIN %t s
            ON f.uid=s.uid
            WHERE f.followuid=%d
            AND f.uid in $ids
            AND f.status=0
            ORDER BY s.lastvisit DESC
            LIMIT %d, %d
            ",
            array('home_follow', 'common_member_status', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 获取用户粉丝的详细信息按最多粉丝排序，增加男女筛选
    public static function _getFollowedUsersByFollowerGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT f.uid
            FROM %t f INNER JOIN %t c
            ON f.uid=c.uid
            WHERE f.followuid=%d
            AND f.uid in $ids
            AND f.status=0
            ORDER BY c.follower DESC
            LIMIT %d, %d
            ",
            array('home_follow', 'common_member_count', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 获取用户粉丝的详细信息按距离排序
    public static function _getFollowedUsersByRangeGender($uid, $page, $pageSize, $longitude, $latitude,  $sql, $radius, $ids) {
        $range = self::_getRange($longitude, $latitude, $radius);
        $select = '*,' . self::_getSqlDistance($longitude, $latitude) . ' AS distance';
        return DbUtils::getDzDbUtils(true)->queryAll('
            SELECT ' . $select . "
            FROM %t hsu INNER JOIN %t aus
            ON (hsu.object_id=aus.uid)
            INNER JOIN %t hf ON (aus.uid=hf.uid)
            INNER JOIN %t st ON (aus.uid=st.uid)
            WHERE hsu.type=%s
            AND hf.followuid=%d
            AND hf.status = 0
            AND hf.uid in $sql
            AND hf.uid in $ids
            AND hsu.longitude BETWEEN %s AND %s
            AND hsu.latitude BETWEEN %s AND %s
            AND aus.ukey=%s
            AND aus.uvalue=%s
            ORDER BY st.lastvisit DESC
            LIMIT %d, %d
            ",
            array(
                'home_surrounding_user',
                'appbyme_user_setting',
                'home_follow',
                'common_member_status',
                self::TYPE_USER,
                $uid,
                $range['longitude']['min'],
                $range['longitude']['max'],
                $range['latitude']['min'],
                $range['latitude']['max'],
                AppbymeUserSetting::KEY_GPS_LOCATION,
                AppbymeUserSetting::VALUE_GPS_LOCATION_ON,
                ($page-1)*$pageSize,
                $pageSize
            )
        );
    }

    // 用户好友列表默认排序
    public static function _getPostFuidListByDefaultGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT fuid
            FROM %t
            WHERE uid = %d
            AND fuid in $ids
            LIMIT %d, %d
            ",
            array('home_friend', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 用户好友按最新注册排序
    public static function _getPostFuidListByRegistGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT f.fuid
            FROM %t f INNER JOIN %t m
            ON f.fuid=m.uid
            WHERE f.uid=%d
            AND f.fuid in $ids
            ORDER BY m.regdate DESC
            LIMIT %d, %d
            ",
            array('home_friend', 'common_member', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 用户好友按最新登陆排序
    public static function _getPostFuidListByLastVisitGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT f.fuid
            FROM %t f INNER JOIN %t m
            ON f.fuid=m.uid
            WHERE f.uid=%d
            AND f.fuid in $ids
            ORDER BY m.lastvisit DESC
            LIMIT %d, %d
            ",
            array('home_friend', 'common_member_status', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 用户好友按最多粉丝排序
    public static function _getPostFuidListByFollowerGender($uid, $page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT f.fuid
            FROM %t f INNER JOIN %t m
            ON f.fuid=m.uid
            WHERE f.uid=%d
            AND f.fuid in $ids
            ORDER BY m.follower DESC
            LIMIT %d, %d
            ",
            array('home_friend', 'common_member_count', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 用户好友列表按距离排序
    public static function _getPostFuidListByRangeGender($uid, $page, $pageSize, $longitude, $latitude, $sql,$radius, $ids) {
        $range = self::_getRange($longitude, $latitude, $radius);
        $select = 'DISTINCT aus.uid,hsu.location,' . self::_getSqlDistance($longitude, $latitude) . ' AS distance';
        return DbUtils::getDzDbUtils(true)->queryAll("
            SELECT " . $select . "
            FROM %t hsu INNER JOIN %t aus
            ON (hsu.object_id=aus.uid)
            INNER JOIN %t hf ON (aus.uid=hf.uid)
            INNER JOIN %t st ON (aus.uid=st.uid)
            WHERE hsu.type=%s
            AND hf.uid in $sql
            AND hf.uid in $ids
            AND hsu.object_id!=%s
            AND hsu.longitude BETWEEN %s AND %s
            AND hsu.latitude BETWEEN %s AND %s
            AND aus.ukey=%s
            AND aus.uvalue=%s
            ORDER BY st.lastvisit DESC
            LIMIT %d, %d
            ",
            array(
                'home_surrounding_user',
                'appbyme_user_setting',
                'home_friend',
                'common_member_status',
                self::TYPE_USER,
                $uid,
                $range['longitude']['min'],
                $range['longitude']['max'],
                $range['latitude']['min'],
                $range['latitude']['max'],
                AppbymeUserSetting::KEY_GPS_LOCATION,
                AppbymeUserSetting::VALUE_GPS_LOCATION_ON,
                ($page-1)*$pageSize,
                $pageSize
            )
        );
    }

    // 查询用户粉丝的总数
    public static function _getFollowedUsersCountGender($uid,$ids) {
        return DbUtils::getDzDbUtils(true)->queryScalar("
            SELECT count(*) as num
            FROM %t
            WHERE followuid=%d AND status=0
            AND uid in $ids
            ",
            array('home_follow', $uid)
        );
    }

    // 用户推荐按默认排序
    public static function _getRecommendUsersSetByDefaultGender($page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT uid
            FROM %t
            WHERE status = 0
            AND uid in $ids
            ORDER BY displayorder ASC
            LIMIT %d, %d
            ",
            array('home_specialuser', $pageSize*($page-1), $pageSize)
        );
    }
    // 用户推荐按最新注册排序
    public static function _getRecommendUsersSetByRegistGender($page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT s.uid
            FROM %t s INNER JOIN %t m
            ON s.uid=m.uid
            WHERE s.status = 0
            AND s.uid in $ids
            ORDER BY m.regdate DESC
            LIMIT %d, %d
            ",
            array('home_specialuser', 'common_member', $pageSize*($page-1), $pageSize)
        );
    }
    // 用户推荐按最新登陆排序
    public static function _getRecommendUsersSetByLastVisitGender($page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT s.uid
            FROM %t s INNER JOIN %t m
            ON s.uid=m.uid
            WHERE s.status = 0
            AND s.uid in $ids
            ORDER BY m.lastvisit DESC
            LIMIT %d, %d
            ",
            array('home_specialuser', 'common_member_status', $pageSize*($page-1), $pageSize)
        );
    }
    // 用户推荐按最多粉丝排序
    public static function _getRecommendUsersSetByFollowerGender($page, $pageSize, $ids) {
        return DbUtils::getDzDbUtils(true)->queryColumn("
            SELECT s.uid
            FROM %t s INNER JOIN %t m
            ON s.uid=m.uid
            WHERE s.status = 0
            AND s.uid in $ids
            ORDER BY m.follower DESC
            LIMIT %d, %d
            ",
            array('home_specialuser', 'common_member_count', $pageSize*($page-1), $pageSize)
        );
    }
    // 用户推荐按距离排序
    public static function _getRecommendUsersSetByRangeGender($uid, $page, $pageSize, $longitude, $latitude, $radius, $ids)
    {
        $range = self::_getRange($longitude, $latitude, $radius);
        $select = '*,' . self::_getSqlDistance($longitude, $latitude) . ' AS distance';
        return DbUtils::getDzDbUtils(true)->queryAll('
            SELECT ' . $select . "
            FROM %t hsu INNER JOIN %t aus
            ON (hsu.object_id=aus.uid)
            INNER JOIN %t hs ON (aus.uid=hs.uid)
            INNER JOIN %t st ON (aus.uid=st.uid)
            WHERE hsu.type=%s
            AND hs.status = 0
            AND asu.uid in $ids
            AND hsu.object_id!=%s
            AND hsu.longitude BETWEEN %s AND %s
            AND hsu.latitude BETWEEN %s AND %s
            AND aus.ukey=%s
            AND aus.uvalue=%s
            ORDER BY st.lastvisit DESC
            LIMIT %d, %d
            ",
            array(
                'home_surrounding_user',
                'appbyme_user_setting',
                'home_specialuser',
                'common_member_status',
                self::TYPE_USER,
                $uid,
                $range['longitude']['min'],
                $range['longitude']['max'],
                $range['latitude']['min'],
                $range['latitude']['max'],
                AppbymeUserSetting::KEY_GPS_LOCATION,
                AppbymeUserSetting::VALUE_GPS_LOCATION_ON,
                ($page - 1) * $pageSize,
                $pageSize
            )
        );
    }
    // 用户设置了关注的用户数
    public static function _getRecommendUsersSetCountGender($gender) {
        $ids = DzCommonUserList::_getRecommendGenderId($gender);
        return DbUtils::getDzDbUtils(true)->queryScalar("
            SELECT COUNT(*) as num
            FROM %t
            WHERE status = 0
            AND uid in $ids
            ",
            array('home_specialuser')
        );
    }
    public static function _getRecommendUsersCountGender($uid, $gender) {
            $ids = DzCommonUserList::_getRecommendGenderId($gender);
            return DbUtils::getDzDbUtils(true)->queryScalar("
            SELECT count(*) as num
            FROM %t
            WHERE uid != %d
            AND uid in $ids
            ",
            array('common_member', $uid)
        );
    }
    public static function getPostFuidListCountGender($uid,$ids) {
        return DbUtils::getDzDbUtils(true)->queryScalar("
            SELECT count(*)
            FROM %t
            WHERE uid = %d
            AND fuid in $ids
            ",
            array('home_friend', $uid)
        );
    }

    // 查询用户关注好友的总数,增加男女筛选
    public static function _getFollowUsersCountGender($uid ,$ids) {
        $count = DbUtils::getDzDbUtils(true)->queryRow("
            SELECT count(*) as num
            FROM %t
            WHERE uid=%d AND status=0
            AND followuid in $ids
            ",
            array('home_follow', $uid)
        );
        return $count['num'];
    }
    // 查询用户关注好友的tid,增加用户男女搜索
    public static function _getFollowUsersDefaultGender($uid, $page, $pageSize, $ids) {
        if ($page==0) {
            $sql = sprintf("
            SELECT followuid
            FROM %%t
            WHERE uid=%%d AND status=0
            AND followuid in $ids
            ORDER BY dateline DESC
            ");
        }else{
            $sql = sprintf("
            SELECT followuid
            FROM %%t
            WHERE uid=%%d AND status=0
            AND followuid in $ids
            ORDER BY dateline DESC
            LIMIT %%d, %%d
            ");
        }
        return DbUtils::getDzDbUtils(true)->queryColumn(
            $sql,
            array('home_follow', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 查询用户关注好友按最新注排序,增加男女排序
    public static function _getFollowUsersByRegistGender($uid, $page, $pageSize, $ids) {
        if ($page==0) {
            $sql = sprintf("
            SELECT f.followuid
            FROM %%t f INNER JOIN %%t m
            ON f.uid=m.uid
            WHERE f.uid=%%d
            AND f.followuid in $ids
            AND f.status=0
            ORDER BY m.regdate DESC
            ");
        }else{
            $sql = sprintf("
            SELECT f.followuid
            FROM %%t f INNER JOIN %%t m
            ON f.uid=m.uid
            WHERE f.uid=%%d
            AND f.followuid in $ids
            AND f.status=0
            ORDER BY m.regdate DESC
            LIMIT %%d, %%d
            ");
        }
        return DbUtils::getDzDbUtils(true)->queryColumn(
            $sql,
            array('home_follow', 'common_member', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 查询用户关注好友按最后登陆排序
    public static function _getFollowUsersByLastVisitGender($uid, $page, $pageSize, $ids) {
        if ($page==0) {
            $sql = sprintf("
            SELECT f.followuid
            FROM %%t f INNER JOIN %%t m
            ON f.uid=m.uid
            WHERE f.uid=%%d
            AND f.followuid in $ids
            AND f.status=0
            ORDER BY m.lastvisit DESC
            ");
        }else{
            $sql = sprintf("
            SELECT f.followuid
            FROM %%t f INNER JOIN %%t m
            ON f.uid=m.uid
            WHERE f.uid=%%d
            AND f.followuid in $ids
            AND f.status=0
            ORDER BY m.lastvisit DESC
            LIMIT %%d, %%d
            ");
        }
        return DbUtils::getDzDbUtils(true)->queryColumn(
            $sql,
            array('home_follow', 'common_member_status', $uid, $pageSize*($page-1), $pageSize)
        );
    }
    // 查询用户关注好友按最多粉丝排序,增加男女搜索
    public static function _getFollowUsersByFollowerGender($uid, $page, $pageSize, $ids) {
        if ($page==0) {
            $sql = sprintf("
            SELECT f.followuid
            FROM %%t f INNER JOIN %%t m
            ON f.uid=m.uid
            WHERE f.uid=%%d
            AND f.followuid in $ids
            AND f.status=0
            ORDER BY m.follower DESC
            ");
        }else{
            $sql = sprintf("
            SELECT f.followuid
            FROM %%t f INNER JOIN %%t m
            ON f.uid=m.uid
            WHERE f.uid=%%d
            AND f.followuid in $ids
            AND f.status=0
            ORDER BY m.follower DESC
            LIMIT %%d, %%d
            ");
        }
        return DbUtils::getDzDbUtils(true)->queryColumn(
            $sql,
            array('home_follow', 'common_member_count', $uid, $pageSize*($page-1), $pageSize)
        );
    }

    public static function _getFollowUsersByRangeGender($uid, $page, $pageSize, $longitude, $latitude,  $sql, $radius ,$ids) {
        $range = self::_getRange($longitude, $latitude, $radius);
        $select = 'DISTINCT hf.uid, hsu.location,' . self::_getSqlDistance($longitude, $latitude) . ' AS distance';
        return DbUtils::getDzDbUtils(true)->queryAll("
            SELECT " . $select . "
            FROM %t hsu INNER JOIN %t aus
            ON (hsu.object_id=aus.uid)
            INNER JOIN %t hf ON (aus.uid=hf.uid)
            INNER JOIN %t st ON (aus.uid=st.uid)
            WHERE hsu.type=%s
            AND aus.uid in $sql
            AND hf.status = 0
            AND hsu.object_id!=%s
            AND aus.uid in $ids
            AND hsu.longitude BETWEEN %s AND %s
            AND hsu.latitude BETWEEN %s AND %s
            AND aus.ukey=%s
            AND aus.uvalue=%s
            ORDER BY st.lastvisit DESC
            LIMIT %d, %d
            ",
            array(
                'home_surrounding_user',
                'appbyme_user_setting',
                'home_follow',
                'common_member_status',
                self::TYPE_USER,
                $uid,
                $range['longitude']['min'],
                $range['longitude']['max'],
                $range['latitude']['min'],
                $range['latitude']['max'],
                AppbymeUserSetting::KEY_GPS_LOCATION,
                AppbymeUserSetting::VALUE_GPS_LOCATION_ON,
                ($page-1)*$pageSize,
                $pageSize
            )
        );
    }

    // 用户设置了关注的用户数
    public static function _getRecommendUsersSetCount($gender = 0) {
        return DbUtils::getDzDbUtils(true)->queryScalar('
            SELECT COUNT(a.uid) as num
            FROM %t a, %t b
            WHERE a.status = %d
            AND b.gender = %d
            AND a.uid = b.uid
            ',
            array(
                'home_specialuser',
                'common_member_profile',
                0,
                $gender
            )
        );
    }
}