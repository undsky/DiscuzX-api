<?php
/**
 *
 *
 * @author 耐小心<i@naixiaoxin.com>
 * @copyright 2003-2017 耐小心
 */

class UpdatePwdAction extends MobcentAction{

    /**
     * @var $db DiscuzDbUtils
     */
    private $db=null;

    /**
     * @var int
     */
    private $uid = 0;

    private $username = '';

    /**
     *
     * @param string $oldpassword
     * @param string $newpassword
     * @param string $type
     */
    public function run($oldpassword='',$newpassword='',$type='GET')
    {
        $res = $this->initWebApiArray();
        $this->db = DbUtils::createDbUtils(true);
        global $_G;
        $this->uid = $_G['uid'];
        $this->username = $_G['username'];
        switch ($type){
            case "GET":
               $res  = $this->getStatus($res);
               break;
            default:
                $res = $this->editPwd($res,$type,$oldpassword,$newpassword);
        }
        WebUtils::outputWebApi($res);
    }

    private function getStatus($res)
    {
        $result = $this->db->queryRow("SELECT * FROM %t WHERE type=%d AND uid=%d",array('appbyme_connection',AppbymeConnection::WECHAT_TYPE,$this->uid));
//        debug($result);
        $res['wechat'] = intval($result['status']);
        return $res;
    }

    private function editPwd($res,$type,$oldpassword,$newpassword)
    {
        loaducenter();
        $ignoreoldpw = 0;
        if($type=='WECHAT'){
            $result = $this->db->queryRow("SELECT * FROM %t WHERE type=%d AND uid=%d",array('appbyme_connection',AppbymeConnection::WECHAT_TYPE,$this->uid));
            if($result['status']!=AppbymeConnection::BIND_CHANGE_STATUS){
                return $this->makeErrorInfo($res,'请勿越权');
            }
            $ignoreoldpw = 1;
        }
        global $_G;
        //基于Discuz判断密码强度
        if (!empty($newpassword) && $_G['setting']['strongpw']) {
            $strongpw_str = array();
            if (in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_1');
            }
            if (in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_2');
            }
            if (in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_3');
            }
            if (in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $newpassword)) {
                $strongpw_str[] = lang('member/template', 'strongpw_4');
            }
            if ($strongpw_str) {
                $msg = lang('member/template', 'password_weak') . implode(',', $strongpw_str);
                return $this->makeErrorInfo($res, $msg);
            }
        }
        $ucresult = uc_user_edit(addslashes($_G['username']), $oldpassword, $newpassword,"",$ignoreoldpw);
        if ($ucresult == -1) {
            // 原密码不正确，您不能修改密码或 Email 或安全提问
            return $this->makeErrorInfo($res, lang('message', 'profile_passwd_wrong'));
        }

        $setarr['password'] = md5(random(10));
        C::t('common_member')->update($_G['uid'], $setarr);
        $secretStr = AppbymeUserAccess::getSecretStr($_G['uid'], $newpassword);
        $newAccessSecret = $secretStr['accessSecret'];
        $data = array('user_access_secret' => $newAccessSecret);
        AppbymeUserAccess::updateUserAccess($data, $_G['uid']);
        $res['token'] = $secretStr['accessToken'];
        $res['secret'] = $newAccessSecret;
//        UserUtils::updateCookie(getuserinfo($_G['uid']),$_G['uid']);
        if(in_array($type,array("WECHAT"))){
//            $this->db->update()
            AppbymeConnection::updateWeiXinUserInfo(array(
                'status'=>AppbymeConnection::BING_STATUS,
            ),$result['id']);
        }
        return $res;
    }
}