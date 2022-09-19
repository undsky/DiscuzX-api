<?php
/**
 * 关注话题
 * @author tanguanghua <18725648509@163.com>
 **/
class CaretpcAction extends MobcentAction{
    //QX----取消关注，CARE-----关注
    public function run($ti_id = 0,$type = 'CARE'){
        $uid = $this->getController()->uid;
        $res = WebUtils::initWebApiArray_oldVersion();
        $db = DbUtils::createDbUtils(true);
        if($ti_id){
            $isttest = $db->queryScalar("SELECT `ti_id` FROM %t WHERE `ti_id`=%d AND `uid`=%d",array('appbyme_tpctou',$ti_id,$uid));
            if($type == 'CARE' && !$isttest){
                $db->insert('appbyme_tpctou', array('ti_id'=>$ti_id,'uid'=>$uid));
            }else if($type == 'QX' && $isttest){
                $db->delete('appbyme_tpctou', array('ti_id'=>$ti_id,'uid'=>$uid));
            }
        }else{
            $res['rs'] = 0;
            $res['head']['errInfo'] = WebUtils::t('参数传入错误');
        }
        WebUtils::outputWebApi($res);
    }
}
