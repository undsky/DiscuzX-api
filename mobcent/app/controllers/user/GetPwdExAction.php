<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/12/6
 * Time: 上午11:09
 * Email: mqdjie@gmail.com
 */

class GetPwdExAction extends MobcentAction
{
    const ImgCodeTime = 1800;
    const CodeTime = 600;
    const CodeRate = 3;
    const Rate = 0;
    public function run($act = 'index', $key, $imgcode, $code = '', $pwd = '', $pwdcm = '', $user = '')
    {
        $res = WebUtils::initWebApiArray();
        $user = rawurldecode($user);
        $res = $this->getPwd($act, $key, $res, $imgcode, $code, $pwd, $pwdcm, $user);
        WebUtils::outputWebApi($res);
    }

    public function getPwd($act, $key, $res, $imgcode, $code, $pwd, $pwdcm, $user)
    {
        switch($act)
        {
            case 'send':
                if(($result = $this->checkImgCode($key, $res, $imgcode)) !== true) {
                    return $result;
                }
                $res = $this->sendCode($res, $user);
                break;
            case 'check' :
                if(($result = $this->checkcode($res, $user, $code)) !== true) {
                    return $result;
                }
                break;
            case 'new' :
                if(($result = $this->checkcode($res, $user, $code, $act)) !== true) {
                    return $result;
                }
                if(($result = $this->_checkPwd($res, $pwd, $pwdcm)) !== true) {
                    return $result;
                }
                if(($result = $this->newPwd($res, $pwd, $pwdcm, $user, $key)) !== true) {
                    return $result;
                }
                break;
            default :
                $res = $this->makeErrorInfo($res, 'mobcent_result_error');
                break;
        }
        return $res;
    }
    private function newPwd($res, $pwd, $pwdcm, $user, $key)
    {
        $username = $user;

        if(UserUtils::checkMobileFormat($user)) {
            $info = AppbymeSendsms::getMobileUid($user);
            $username = UserUtils::getUCUserName($info['uid']);
        }
        if ($pwd != $pwdcm) {
            return $this->makeErrorInfo($res, ('mobcent_error_getpwdex_confirm_pwd_error'));
        }
        
        loaducenter();
        uc_user_edit($username, '', $pwd, '', 1);
        DbUtils::createDbUtils(true)->delete('appbyme_getpwd', array('pwdkey' => $user));
        DbUtils::createDbUtils(true)->delete('appbyme_getpwd', array('pwdkey' => $key));
        return true;
    }

    private function sendCode($res, $user)
    {
        $user = rawurldecode($user);
        if(UserUtils::checkMobileFormat($user)){
            $isRegisterValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
            if ($isRegisterValidation) {
                $result = AppbymeSendsms::getMobileUid($user);
                if ($result) {
                    $code = rand(100000, 999999);
                    $messageUtils = new MessageUtils();
                    $res = $messageUtils->sendCode(WebUtils::initWebApiArray_oldVersion(),$user,$code,'getpwd');
                    if($res['rs']!=1){
                        return $this->makeErrorInfo($res, ($res['head']['errInfo']));
                    }
                    DbUtils::createDbUtils(true)->update('appbyme_getpwd', array('rate' => 1), array('pwdkey' => $user));
                    DbUtils::createDbUtils(true)->insert('appbyme_getpwd', array('pwdkey' => $user, 'code' => $code, 'time' => time()), false, true);
                    $res['body']['type'] = 'phone';
                    return $res;
                }else{
                   return $this->makeErrorInfo($res, ('mobcent_error_getpwdex_info_unreg'));
                }
            }else {
                return $this->makeErrorInfo($res, ('mobcent_error_getpwdex_no_open_phone'));
            }
        }else{
            global $_G;
            loaducenter();
            list($uid, $username, $email) = uc_get_user(WebUtils::t($user));
            if ($uid > 0) {
                if ($email) {
                    if(strstr($email,'@appbyme.com')){
                        return $this->makeErrorInfo($res, 'mobcent_error_getpwdex_no');
                    }
                    $code = rand(100000, 999999);
                    include libfile('function/mail');
                    $subject = $_G['setting']['bbname'] . WebUtils::t($res, ('user_getpwd_info_app_goback')); //$this->clear('APP密码找回');
                    $message = WebUtils::t(WebUtils::lp('user_getpwd_info_code_message', 'code', $code, 'bbname', $_G['setting']['bbname']));
                    sendmail($email, $subject, $message, $from = '');
                    DbUtils::createDbUtils(true)->update('appbyme_getpwd', array('rate' => 1), array('pwdkey' => $user));
                    DbUtils::createDbUtils(true)->insert('appbyme_getpwd', array('pwdkey' => $user, 'code' => $code, 'time' => time()), false, true);
                    $res['body']['type'] = 'email';
                    $res['body']['email'] = $this->mailtoxxx($email);
                    return $res;
                }
            } else {
                return $this->makeErrorInfo($res, ('mobcent_error_getpwdex_error_user'));
            }
        }
    }


    private function checkImgCode($key, $res, $code)
    {
        $result = UserUtils::checkImgCode($key, $code);
        if($result) {
            return true;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_getpwdex_cerr');
    }

    //效验密码强度
    private function _checkPwd($res, $pwd, $pwdex)
    {
        global $_G;
        if(WebUtils::getLength(trim($pwd)) < $_G['setting']['pwlength']) {
            return $this->makeErrorInfo($res,WebUtils::lp('user_getpwd_info_length_failure', 'length', $_G['setting']['pwlength']));
        }
        if(trim($pwd) != trim($pwdex)) {
            return $this->makeErrorInfo($res,WebUtils::lp('user_getpwd2_change_new_tow_password_error'));
        }
        if($_G['setting']['strongpw']) {
            $strongpw_str = array();
            if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $pwd)) {
                $strongpw_str[] = lang('member/template', 'strongpw_1');
            }
            if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $pwd)) {
                $strongpw_str[] = lang('member/template', 'strongpw_2');
            }
            if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $pwd)) {
                $strongpw_str[] = lang('member/template', 'strongpw_3');
            }
            if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $pwd)) {
                $strongpw_str[] = lang('member/template', 'strongpw_4');
            }
            if($strongpw_str) {
                $strongpw_str = (lang('member/template', 'password_weak').implode(',', $strongpw_str));
                return $this->makeErrorInfo($res, $strongpw_str);
            }
        }
        return true;
    }
    private function checkcode($res, $key, $code, $act = '')
    {
        $result = UserUtils::checkCode($key, $code);
        if($act == 'new') {
            if($result['coderate'] > 3) {
                return $this->makeErrorInfo($res, 'mobcent_error_getpwdex_code_more');
            }
        }
        if($result) {
            return true;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_getpwdex_error_code');
    }

    private function mailtoxxx($email) {
        $e = explode('@', $email);
        $len = strlen($e[0]);
        $slen = intval($len / 3);
        $e1 = substr($e[0], 0, $slen);
        $e2 = substr($e[0], $slen * 3, $slen);
        $xing = '';
        for ($i = 0; $i < $slen; $i++) {
            $xing.='*';
        }
        $email = $e1 . $xing . $e2 . '@' . $e[1];
        return $email;
    }
}