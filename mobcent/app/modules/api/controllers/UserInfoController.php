<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/10/31
 * Time: 下午12:28
 * Email: mqdjie@gmail.com
 */

class UserInfoController extends ApiController
{
    public function actionGetUserInfo()
    {
        $uids = ($this->data['uids']);
        if(!is_array($uids)) {
            $this->error('id error');
        }
        $this->setData($this->getUserInfo($uids));
    }
    //组装用户新
    protected function getUserInfo($uids)
    {
        $arr = array();
        foreach($uids as $v) {
            $tmparr = array();
            $userInfo = getuserbyuid($v);
            if($userInfo['uid']) {
                $tmparr['uid']      = $userInfo['uid'];
                $tmparr['username'] = $userInfo['username'];
                $tmparr['avatar']   = UserUtils::getUserAvatar($userInfo['uid']);
                $arr[] = $tmparr;
            }
        }
        unset($tmparr);
        unset($userInfo);
        return $arr;
    }
}