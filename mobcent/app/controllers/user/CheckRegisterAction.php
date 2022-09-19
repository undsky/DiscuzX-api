<?php

/**
 * 校验用户名和密码
 * 
 *  @author   NaiXiaoXin<nxx@yytest.cn>
 *  @datetime 2016-2-23 14:13:38
 *  @copyright  2012-2016, Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class CheckRegisterAction extends MobcentAction {

    public function run($username, $password, $email='') {
        $res = WebUtils::initWebApiArray_oldVersion();
        $username = WebUtils::t(rawurldecode($username));
        $password = rawurldecode($password);
        $email = rawurldecode($email);
        $res = $this->_check($res, $username, $password, $email);
        WebUtils::outputWebApi($res);
    }

    private function _check($res, $username, $password, $email) {
        global $_G;
        loaducenter();
        //校验
        $isCloseEmail = WebUtils::getDzPluginAppbymeAppConfig('close_email_register');
        if (empty($isCloseEmail)) {
            if (strlen($email) > 32) {
                return $this->makeErrorInfo($res, lang('message', 'profile_email_illegal'));
            }
            if ($_G['setting']['regmaildomain']) {
                $maildomainexp = '/(' . str_replace("\r\n", '|', preg_quote(trim($_G['setting']['maildomainlist']), '/')) . ')$/i';
                if ($_G['setting']['regmaildomain'] == 1 && !preg_match($maildomainexp, $email)) {
                    return $this->makeErrorInfo($res, lang('message', 'profile_email_domain_illegal'));
                } elseif ($_G['setting']['regmaildomain'] == 2 && preg_match($maildomainexp, $email)) {
                    return $this->makeErrorInfo($res, lang('message', 'profile_email_domain_illegal'));
                }
            }

            $ucresult = uc_user_checkemail($email);
            if ($ucresult == -4) {
                return $this->makeErrorInfo($res, lang('message', 'profile_email_illegal'));
            } elseif ($ucresult == -5) {
                return $this->makeErrorInfo($res, lang('message', 'profile_email_domain_illegal'));
            } elseif ($ucresult == -6) {
                return $this->makeErrorInfo($res, lang('message', 'profile_email_duplicate'));
            }
        }
        //ChekUsername
        $ucresult = '';
        $ucresult = uc_user_checkname($username);
        if ($ucresult == -1) {
            return $this->makeErrorInfo($res, lang('message', 'profile_username_illegal'));
        } elseif ($ucresult == -2) {
            return $this->makeErrorInfo($res, lang('message', 'profile_username_protect'));
        } elseif ($ucresult == -3) {
            return $this->makeErrorInfo($res, lang('message', 'register_activation'));
        }
        $usernamelen = dstrlen($username);
        if ($usernamelen < 3) {
            return $this->makeErrorInfo($res, lang('message', 'profile_username_tooshort'));
        } elseif ($usernamelen > 15) {
            return $this->makeErrorInfo($res, lang('message', 'profile_username_toolong'));
        }
        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')) . ')$/i';
        if ($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
            return $this->makeErrorInfo($res, lang('message', 'profile_username_protect'));
        }
        //CheckPwd
        if (empty($password) || $password != addslashes($password)) {
            // 抱歉，密码空或包含非法字符:新密码
            return $this->makeErrorInfo($res, lang('message', 'profile_passwd_illegal'));
        }
        global $_G;

        if ($_G['setting']['pwlength']) {
            if (strlen($password) < $_G['setting']['pwlength']) {
                return $this->makeErrorInfo($res, lang('message', 'profile_password_tooshort', array('pwlength' => $_G['setting']['pwlength'])));
            }
        }
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
        return $res;
    }

}
