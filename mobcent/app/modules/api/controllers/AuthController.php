<?php

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
/**
 * Created by PhpStorm.
 * User: congjie
 * Date: 16/7/1
 * Time: 下午1:50
 */
class AuthController extends ApiController
{
    public $dzRootUrl;
    protected $userinfo;
    public function __construct($id,$modul)
    {
        global $_G;
        $this->dzRootUrl = $_G['siteurl'];
        parent::__construct($id,$modul);
    }


    /**
     * 认证列表
     * 用户组列表
     */
    public function actionAuthList()
    {
        global $_G;
        $array = array();
        $arr = array();
        foreach ($_G['setting']['verify'] as $key => &$value) {
            if ($value['available'] == 1) {
                $array['id'] = $key;
                $array['title'] = $value['title'];
                $arr[] = $array;
                unset($array);
            }
        }
        $sql = 'SELECT groupid,grouptitle FROM %t ORDER BY groupid';
        $data = $this->db->queryAll($sql, array('common_usergroup'));
        $this->setData(array('auth_list' => $arr, 'group_list' => $data));
        unset($data);
    }

    /**
     * 绑定认证
     * @param int $id
     */
    public function actionBindingAuth()
    {
        $arr = array();
        $arr['verify'] = isset($this->data['verify']) ? $this->data['verify'] : 0;
        $arr['group']  = is_array($this->data['group']) ? $this->data['group'] : array();
        $arr['all']    = isset($this->data['all']) ? $this->data['all'] : 0;
        $arr = array('ckey' => 'binding_auth', 'cvalue' => serialize($arr));
        $result = $this->db->insert('appbyme_config', $arr, false, true);
        if ($result == 1) {
            $this->setData(array('result' => '绑定成功'));
        }
    }

    /*
     *
     * 返回绑定cans
     */

    public function actionShowAuth()
    {
        $res = AppbymeConfig::getCvalue('binding_auth');
        if(empty($res)) {
            $res['all']    = 1;
        }
        $this->setData($res);
    }


    /**
     * 判断用户是否可以发起直播接口
     * uid去查找用户组  然后和绑定的用户组进行比对
     * 然后根据uid去查找认证列表 最后和绑定的用户列表做对比
     * @param $uid
     */
    public function actionCheckState($uid)
    {
        $bindingGroupInfo = $this->db->queryRow('select ckey,cvalue from %t where ckey = %s', array('appbyme_config', 'binding_auth'));
        //如果没有进行过配置,返回全民可以直播
        if(empty($bindingGroupInfo)) {
            return true;
        }
        $bindingGroupInfo = unserialize($bindingGroupInfo['cvalue']);
        if($bindingGroupInfo['all']) {
            return true;
        }
        if($bindingGroupInfo['group']) {
            $userInfo = UserUtils::getUserInfo($uid);
            if(in_array($userInfo['groupid'], $bindingGroupInfo['group'])) {
                return true;
            }
            $title = $this->getGroupTitle($userInfo['groupid']);
            $errcode = '000003'; $msg = $title;
        }
        //$this->error($res);
        if($bindingGroupInfo['verify']) {
            $vefity = UserUtils::getVerify($uid);
            foreach($vefity as $k => $v) {
                if($v['vid'] == $bindingGroupInfo['verify']) {
                    return true;
                }
            }
            global $_G;
            $vefname = $_G['setting']['verify'][$bindingGroupInfo['verify']]['title'];
            $errcode = '000004'; $msg = $vefname;
        }
        if($bindingGroupInfo['verify'] && $bindingGroupInfo['group'])
        {
            $errcode = '000005';
            $msg = $vefname;
        }
        if(!isset($errcode)) {
            $errcode = '000006';
            $msg     = '未绑定直播';
        }
        $this->error($errcode, WebUtils::u($msg));
    }


    public function error($errcode, $msg)
    {
        $this->out_arr['rs'] = 0;
        $this->out_arr['head']['errCode'] = $errcode;
        $this->out_arr['head']['errInfo'] = $msg;
        $this->out_json();
    }

    protected function getGroupId($uid)
    {
        $res = $this->db->queryFirst('SELECT groupid FROM %t WHERE uid = %d',array('common_member', $uid));
        return $res;
    }

    protected function isFans()
    {
        $res = $this->db->queryFirst(
            'SELECT uid FROM %t WHERE uid = %d AND followuid = %d AND status >= %d',
            array('home_follow', $this->data['uid'] , $this->data['fid'], 0)
        );
        if($res) {
            return intval($this->data['uid']);
        }
        return array();
    }

    /*
     * 获取用户组名称
     */
    protected function getGroupTitle($groupid)
    {
        $res = $this->db->queryFirst('SELECT grouptitle FROM %t WHERE groupid = %d',array('common_usergroup', $groupid));
        return $res;
    }

    public function actionCheck()
    {
        //Auths认证, groups组 fans粉丝

        switch($this->data['type']) {
            case 'auth'  :
                $res = $this->checkAuth();
                break;
            case 'group' :
                $res = $this->checkGroup();
                break;
            case 'fans'  :
                $res = $this->checkFans();
                break;
            default :
                $this->error('000004',WebUtils::u('传人参数有误'));
                break;
        }
        $this->setData($res);
    }

    private function result($In = array(), $NotIn = array())
    {
        $res['in']      = $In;
        $res['notin'] = $NotIn;
        return $res;
    }

    //通过groupid返回用户组名称
    private function getGroup($groupid)
    {
        $arr['id']   = $groupid;
        $arr['title'] = $this->getGroupTitle($groupid);
        return $arr;
    }

    //通过verifyid返回认证名称
    private function getVerify($verifyid)
    {
        global $_G;
        $verfiys = $_G['setting']['verify'];

        $arr['id']  = $verifyid;
        $arr['title'] = $verfiys[$verifyid]['title'];

        return $arr;
    }

    /**判断是否在组内
     * @return mixed
     */
    private function checkGroup()
    {
        $groups = $this->data['groups'];
        $guid   = $this->getGroupId($this->data['uid']);
        $arr    = array();
        foreach($groups as $v) {
            if($guid != $v) {
                $NotIn[] = $this->getGroup($v);
            } else {
                $arr[] = $this->getGroup($v);
            }
        }
        return $this->result($arr, $NotIn);
    }

    /**判断认证
     * @return mixed
     */
    private function checkAuth()
    {
        $auths = $this->data['auths'];
        $authInfo  = UserUtils::getVerify($this->data['uid']);
        if(empty($authInfo)) {
            foreach($auths as $k => $v) {
                $auths[$k] = $this->getVerify($v);
            }
            return $this->result(array(), $auths);
        }
        foreach($authInfo as $k => $v) {
            $authInfo[$v['vid']] = $v;
            unset($authInfo[$k]);
        }
        foreach($auths as $k => $v) {
            if(isset($authInfo[$v])) {
                $In[] = $this->getVerify($v);
            } else {
                $NotIn[] = $this->getVerify($v);
            }
        }
        $In    = isset($In) ? $In : array();
        $NotIn = isset($NotIn) ? $NotIn : array();
        return $this->result($In, $NotIn);
    }

    private function checkFans()
    {
       if($res[]['id'] = $this->isFans()) {
           return $this->result($res);
       }
        return $this->result(array(), array('id' => intval($this->data['uid'])));
    }
}