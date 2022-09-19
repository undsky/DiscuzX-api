<?php

/**
 * 找回密码
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */


if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}


class GetPwdAction extends MobcentAction{

    public function run($act='index'){
        header("Content-Type: text/html; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        header('Pragma: no-cache');
        $isApp = strpos($_SERVER['HTTP_USER_AGENT'], 'Appbyme') !== false;
        if (!$isApp) {
            echo WebUtils::lp('user_getpwd_info_app_use'); //'限制在app内使用!';
            exit;
        }
        switch ($act){
            case 'index':
                $this->_index();
            case 'step1':
                $this->_step1();
            case 'step2':
                $this->_step2();
            case 'step3':
                $this->_step3();
            case 'check':
                $this->_check();
            case 'authcode':
                $this->_authCode();
            case 'checkpwd':
                $this->_checkPwd();
        }
    }


    private function _checkPwd()
    {
        global $_G;
        if(WebUtils::getLength(trim($_GET['password'])) < $_G['setting']['pwlength']) {
            $this->_errJson(WebUtils::lp('user_getpwd_info_length_failure', 'length', $_G['setting']['pwlength']));
        }
        if(trim($_GET['password']) != trim($_GET['password1'])) {
            $this->_errJson(WebUtils::lp('user_getpwd2_change_new_tow_password_error'));
        }
        if($_G['setting']['strongpw']) {
            $strongpw_str = array();
            if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['password'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_1');
            }
            if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['password'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_2');
            }
            if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['password'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_3');
            }
            if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['password'])) {
                $strongpw_str[] = lang('member/template', 'strongpw_4');
            }
            if($strongpw_str) {
                $strongpw_str = (lang('member/template', 'password_weak').implode(',', $strongpw_str));
                $this->_errJson($strongpw_str);
            }
        }
        $data['status'] = 1;
        echo json_encode($data);
        exit();
    }

    private function _errJson($errInfo)
    {
        $data['status'] = 0;
        $data['errInfo'] = WebUtils::u($errInfo);
        echo json_encode($data);
        exit();
    }
    private function _index(){
        $this->getController()->render('getpwd');
        exit();
    }

    private function _step1() {
        if ($_POST) {
            $mobile = $_GET['mobile'];
            $authCode = $_GET['code'];
            if (!$mobile) {
                $this->_error(WebUtils::lp('user_getpwd_info_input'));
            }
            $sessionCode = Yii::app()->session['Checknum'];
            Yii::app()->session['Checknum'] = '';
            if ($authCode !== $sessionCode) {
                $this->_error(WebUtils::lp('user_getpwd_info_auth_code_error'));
            }
            $mobile = rawurldecode($mobile);
            if(UserUtils::checkMobileFormat($mobile)){
                $isRegisterValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
                if ($isRegisterValidation) {
                    $result = AppbymeSendsms::getMobileUid($mobile);
                    if ($result) {
                        $code = rand(100000, 999999);
                        Yii::app()->session['authcode'] = $code;
                        $messageUtils = new MessageUtils();
                        $res = $messageUtils->sendCode(WebUtils::initWebApiArray_oldVersion(),$mobile,$code,'getpwd');
                        if($res['rs']!=1){
                            $this->_error(WebUtils::u($res['head']['errInfo']));
                        }
                        //修改逻辑,防伪造
                        UserUtils::createPwdCode($mobile, $code);
                        $this->getController()->renderPartial('getpwd1', array('type' => 1, 'uid' => $result['uid'], 'code' => $code, 'user' => $mobile));
                        exit();
                    }else{
                        $this->_error(WebUtils::lp('user_getpwd_info_unreg'));
                    }
                }else {
                    $this->_error(WebUtils::lp('user_getpwd_info_no_open_phone'));
                }
            }else{
                global $_G;
                loaducenter();
                list($uid, $username, $email) = uc_get_user(WebUtils::t($mobile));
                if ($uid > 0) {
                    if ($email) {
                        if(strstr($email,'@appbyme.com')){
                            $this->_error('您的账号暂时不支持找回,请联系管理员');
                        }
                        $code = rand(100000, 999999);
                        Yii::app()->session['authcode'] = $code;
                        include libfile('function/mail');
                        $subject = $_G['setting']['bbname'] . WebUtils::t(WebUtils::lp('user_getpwd_info_app_goback')); //$this->clear('APP密码找回');
                        $message = WebUtils::t(WebUtils::lp('user_getpwd_info_code_message', 'code', $code, 'bbname', $_G['setting']['bbname']));
                        sendmail($email, $subject, $message, $from = '');
                        UserUtils::createPwdCode($username, $code);
                        $this->getController()->renderPartial('getpwd1', array('type' => 2, 'uid' => $uid, 'code' => $code, 'email' => $this->mailtoxxx($email), 'user' => $username));
                        exit();
                    }
                } else {
                    $this->_error(WebUtils::lp('user_getpwd_info_user_not_found'));
                    exit();
                }
            }
        }
    }



    private function _step2(){
        $code = trim(Yii::app()->request->getPost('code'));
        if ($code != Yii::app()->session['authcode']) {
            $this->_error(WebUtils::lp('user_getpwd_info_auth_code_error'));
        }
        $uid = trim(Yii::app()->request->getPost('uid'));
        $check = 0;
        $user = trim(Yii::app()->request->getPost('user'));
        if(UserUtils::checkCode($user, $code)) {
            $check = 1;
        }
        if(!$check) {
            $this->_error(WebUtils::lp('user_getpwd_info_check_failure'));
        }
        loaducenter();
        list($uid, $username, $email) = uc_get_user($uid, 1);
       // $username = WebUtils::u($username);
        Yii::app()->session['username'] = $username;
        $this->getController()->renderPartial('getpwd2', array('username' => WebUtils::u($username)));
        exit();
    }


    private function _step3(){
        $username = Yii::app()->session['username'];
        $password = trim(Yii::app()->request->getPost('password'));
        $password1 = trim(Yii::app()->request->getPost('password1'));
        if ($password1 != $password) {
            $this->_error(WebUtils::lp('user_getpwd_info_confirm_pwd_error'));
        }
        if (!$username) {
            $this->_error(WebUtils::lp('user_getpwd_info_session_timeout'));
        }
        loaducenter();
        uc_user_edit($username, '', $password, '', 1);
        unset(Yii::app()->session['authcode']);
        $this->_error(WebUtils::lp('user_getpwd_info_change_success'),100);
    }
    private function _error($msg, $status = 0) {
        $this->getController()->renderPartial('error', array('msg' => $msg, 'status' => $status));
        exit;
    }


    private function _check(){
        $code = trim(Yii::app()->request->getPost('code'));
        if ($code == Yii::app()->session['authcode']) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        echo json_encode($data);
        exit();
    }

    private function _authCode() {
        Header("Content-type: image/PNG");
        $im = imagecreate(44, 18);
        $back = ImageColorAllocate($im, 245, 245, 245);
        $vcodes = '';
        imagefill($im, 0, 0, $back); //背景
        srand((double) microtime() * 1000000);
        for ($i = 0; $i < 4; $i++) {
            $font = ImageColorAllocate($im, rand(100, 255), rand(0, 100), rand(100, 255));
            $authnum = rand(1, 9);
            $vcodes.=$authnum;
            imagestring($im, 5, 2 + $i * 10, 1, $authnum, $font);
        }
        for ($i = 0; $i < 100; $i++) { //加入干扰象素
            $randcolor = ImageColorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand(0), rand(255), $randcolor);
        }
        ImagePNG($im);
        ImageDestroy($im);
        Yii::app()->session['Checknum'] = $vcodes;
    }
    private function mailtoxxx($email) {
        $e = explode('@', $email);
        $len = strlen($e[0]);
        $slen = intval($len / 3);
        $e1 = substr($e[0], 0, $slen);
        $e2 = substr($e[0], $slen * 2, $slen);
        $xing = '';
        for ($i = 0; $i < $slen; $i++) {
            $xing.='*';
        }
        $email = $e1 . $xing . $e2 . '@' . $e[1];
        return $email;
    }
}