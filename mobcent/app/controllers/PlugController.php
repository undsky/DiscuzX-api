<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/16
 * Time: 20:45
 */
class PlugController extends MobcentController
{
    public function actionToken($plugsid)
    {
        $uid = $this->uid;
        $time = time();
        $res = WebUtils::initWebApiArray_oldVersion();
        if(!$plugsid){
            $res['rs'] = 0;
            $res['head']['errInfo'] = '参数错误!';
        }
        $token = sha1($time.$uid.$plugsid.rand(1,2000));
        $re = DbUtils::createDbUtils(true)->insert('appbyme_plugs_token',array(
            'uid' => $uid,
            'token' => $token,
            'plugsid' => $plugsid,
            'addtime' => $time
        ));
        if($re){
            $res['body']['token'] = $token;
        }else{
            $res['rs'] = 0;
            $res['head']['errInfo'] = '创建失败!';
        }
        WebUtils::outputWebApi($res);
    }
}