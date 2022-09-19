<?php
/**
 * 拉黑接口
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/3/29
 * Time: 20:22
 */
class HomeBlack{
    /**
     * 获取拉黑双列表
     * @param $uid 用户id
     */
    static public function getBothHomeBlackList($uid, $buid){
        return DbUtils::createDbUtils(true)->queryAll('SELECT `uid`,`buid` FROM %t WHERE (`uid` = %d AND `buid` = %d) OR (`uid` = %d AND `buid` = %d)',array('home_blacklist', $uid, $buid, $buid, $uid));
    }
    
    /**
     * 获取拉黑详情
     * @param $uid 用户id
     */
    static public function getHomeBlack($uid, $buid){
        return DbUtils::createDbUtils(true)->queryAll('SELECT `uid`,`buid` FROM %t WHERE `uid` = %d AND `buid` = %d',array('home_blacklist',$uid,$buid));
    }
}