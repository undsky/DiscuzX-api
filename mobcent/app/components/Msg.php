<?php
/**
 * 短信发送接口
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/3/29
 * Time: 20:22
 */
class Msg{
    /**
     * 发送短信
     * @param $phone 电话号码
     * @param $content 短信内容
     * @param array $pamas 扩展参数
     */
    static public function sendMsg($phone,$content,$pamas = array()){
        //真实地址
        $url = Yii::app()->params['msgurl'];
        $header = array('Content-Type: application/json; charset=utf-8');
        $db = DbUtils::createDbUtils(true);
        $forumkey = isset($_GET['forumKey']) ? $_GET['forumKey'] : '';
        if(!$forumkey){
            $forumkey = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s',array('appbyme_config','app_forumkey'));
            $secretkey = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s',array('appbyme_config','secretKey'));
        }else{
            $sceretkeys = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s',array('appbyme_config','ForumKey_SecretKey'));
            $sceretkeys = unserialize($sceretkeys);
            $secretkey = $sceretkeys[$forumkey];
            if(empty($secretkey)){
                $secretkey = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s',array('appbyme_config','secretKey'));
            }
        }

        $senddata = array_merge(array('phonenum'=>$phone,'content'=>$content,'forumkey'=>$forumkey,'secretkey'=>$secretkey),$pamas);
        $sign = Myencrypt::getSign($senddata);
        unset($senddata['secretkey']);
        $senddata['sign'] = $sign;
        $data = json_encode($senddata);
        $re = Http::Vsact($url,$header,$data);
        return json_decode($re,true);
    }
}