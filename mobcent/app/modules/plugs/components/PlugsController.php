<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/6
 * Time: 18:25
 */
class PlugsController extends CController
{
    protected $uid = 0;
    protected $plugsid = '';
    protected $db = null;
    public function init()
    {
        $this->db = DbUtils::createDbUtils(true);
        $this->_ckToken();
    }
    private function _ckToken(){
        $token = Yii::app()->request->getPost('token','');
        if(!$token)
        {
            \OutPut::Error('000001');
        }
        $tokeninfo = $this->db->queryRow('SELECT * FROM %t WHERE `token`=%s LIMIT 1',array('appbyme_plugs_token',$token));
        if(!isset($tokeninfo['addtime']) || time() > $tokeninfo['addtime']+7200)
        {
            \OutPut::Error('000002');
        }
        $this->uid = $tokeninfo['uid'];
        $this->plugsid = $tokeninfo['plugsid'];
    }
    public function afterAction($action)
    {
        \OutPut::EchoJson();
    }
}