<?php
/**
 * 获取用户信息
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/16
 * Time: 11:23
 */
class GetuserinfoController extends PlugsController
{
    public function actionIndex()
    {
        $openid = $this->db->queryScalar('SELECT `openid` FROM %t WHERE `uid`=%d AND `plugsid`=%s LIMIT 1',array('appbyme_user_openid',$this->uid,$this->plugsid));
        if(!$openid)
        {
            $openid = sha1($this->uid.$this->plugsid.rand(1,20000).time());
            $this->db->insert('appbyme_user_openid',array(
                'uid' => $this->uid,
                'plugsid' => $this->plugsid,
                'openid' => $openid
            ));
        }
        $userInfo['username'] = UserUtils::getUserName($this->uid);
        $userInfo['avatar'] = UserUtils::getUserAvatar($this->uid);
        $userInfo['gender'] = UserUtils::getUserGender($this->uid);
        $userInfo['openid'] = $openid;
        $userInfo['userid'] = $this->uid;
        \OutPut::SetData($userInfo);
    }
}