<?php
/**
 *  appbyme_share_user Model
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 *
 */

class AppbymeShareUserModel extends DiscuzAR{
    public function tableName() {
        return '{{appbyme_share_user}}';
    }
    public static function countActivity($id,$uid){
        return (int)DbUtils::getDzDbUtils(true)->queryScalar("SELECT count(*) AS count FROM %t WHERE `uid`=%d AND `activityid`=%d",array('appbyme_share_user',$uid,$id));
    }

    public static function countDayActivity($id,$uid,$starttime,$endtime){
        return (int)DbUtils::getDzDbUtils(true)->queryScalar("SELECT count(*) AS count FROM %t WHERE `uid`=%d AND `activityid`=%d AND ".DB::field('time', $starttime, '>') ."AND ".DB::field('time', $endtime, '<='),array('appbyme_share_user',$uid,$id));
    }

    public static function add($data){
        return DbUtils::getDzDbUtils(true)->insert('appbyme_share_user',$data);
    }

    public static function del($id){
        return DbUtils::getDzDbUtils(true)->delete('appbyme_share_user',array('id'=>$id));

    }

}