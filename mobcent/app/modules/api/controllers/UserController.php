<?php
/**
 * 用户相关
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/11/29
 * Time: 12:01
 */
class UserController extends ApiController
{
    public function actionSearchUser()
    {
        $userList = $this->db->queryAll("SELECT `uid`,`username` FROM %t WHERE username LIKE %s",array('common_member',$this->data['username'].'%'));
        $this->setData($userList);
    }
}