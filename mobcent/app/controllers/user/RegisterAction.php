<?php

/**
 * Registe Interface
 *
 * @author HanPengyu,NaiXiaoXin
 * @author XiaoCongjj<xiaocongjie@goyoo.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

// Mobcent::setErrors();
class RegisterAction extends MobcentAction {

    public function run($username, $password, $email, $mobile = '', $code = '', $isValidation = 1, $isCloseEmail = 0) {
        $username = WebUtils::t(rawurldecode($username));
        $password = rawurldecode($password);
        $email = rawurldecode($email);
        $res = $this->initWebApiArray();
        $res = $this->_register($res, $username, $password, $email, $mobile, $code, $isValidation);
        echo WebUtils::outputWebApi($res, '', false);
    }

    private function _register($res, $username, $password, $email, $mobile, $code, $isValidation) {
        $FastRegister = isset($_GET['fastRegister']) ? $_GET['fastRegister'] : '';
        if ($isValidation && !$FastRegister) {
            // 是否开启注册手机验证
            $isRegisterValidation = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
            if ($isRegisterValidation) {
                $checkInfo = UserUtils::checkMobileCode($res, $mobile, $code);
                if ($checkInfo['rs'] == 0) {
                    return $this->makeErrorInfo($res, $checkInfo['errcode']);
                }
            }
        }

        $device = isset($_GET['device']) ? $_GET['device'] : '';   //获取用户设备号
        //判断用户是正常注册还是快速注册

        if ($FastRegister && empty($device)) {
            return WebUtils::makeErrorInfo_oldVersion($res, 'fastregister_failed');
        }
        //是否开启快速注册
        $isFastRegister = WebUtils::getDzPluginAppbymeAppConfig('fast_register');
        if ($isFastRegister && $FastRegister && !empty($device)) {
            global $_G;
            $url = $_G['siteurl'];
            $fastUserInfo = DbUtils::getDzDbUtils(true)->queryAll('
                SELECT ID,username,passwd
                FROM %t
                WHERE DEVICE = %s
                ', array('appbyme_fastregister', $device)
            );
            if(empty($fastUserInfo)&&!empty($_GET['oldDevice'])){
                $fastUserInfo = DbUtils::getDzDbUtils(true)->queryAll('
                SELECT ID,username,passwd
                FROM %t
                WHERE DEVICE = %s
                ', array('appbyme_fastregister', $_GET['oldDevice'])
                );
                if(!empty($fastUserInfo)){
                    DbUtils::getDzDbUtils(true)->update('appbyme_fastregister',array('DEVICE'=>$device),array('ID'=>$fastUserInfo[0]['ID']));
                }
            }
            if ($fastUserInfo) { //判断此设备是否已经注册过
                $username = $fastUserInfo[0]['username'];
                $password = $fastUserInfo[0]['passwd'];
                if (empty($fastUserInfo[0]['passwd'])) {
                    $password = $fastUserInfo[0]['username'];
                }
                $loginUrl = $url . "mobcent/app/web/index.php?r=user/login&username=" . $username . "&password=" . $password;
                header("Location:" . $loginUrl . ""); //跳转到登录页面
                exit();
            } else {
                $randChars = self::random_str('10');
                $password = $randChars . '@';
                $username = $randChars;
                $password = $password;
                $Reg = '1';
            }
        }
        /**
         * Fix 无法注册
         *  @author NaiXiaoXin
         */
//        } else {
//            $username = WebUtils::t(rawurldecode($username));
//            $password = rawurldecode($password);
//            $email = rawurldecode($email);
//        }
        //是否关闭邮箱注册
        $isCloseEmail = WebUtils::getDzPluginAppbymeAppConfig('close_email_register');
        if ($isCloseEmail) {
            global $_G;
//            $url = $_G['siteurl'];
//            $arr1 = explode('/', $url);
//            $arr2 = explode('.', $arr1[2]);
//            $email = UserUtils::_generateChars($randChars);
//            $email = $email . "@" . $arr2[1] . "." . $arr2[2];
        } else {
            $email = rawurldecode($email);
        }
        $regInfo = UserUtils::register($username, $password, $email, 'general', $FastRegister);
        if ($regInfo['errcode']) {
            return $this->makeErrorInfo($res, $regInfo['message']);
        }
        /**
         * 快速注册成功后 再写入注册表
         *  @author NaiXiaoXin
         */
        if ($Reg == '1') {
            $data = array('username' => $username, 'device' => $device, 'passwd' => $password);
            DbUtils::getDzDbUtils(true)->insert('appbyme_fastregister', $data);
            $res['avatar'] = UserUtils::getUserAvatar($_G['uid']);
        }
        if ($isValidation) {
            if ($isRegisterValidation) {
                // 注册完毕之后更新手机验证信息
                $updataArr = array('uid' => $regInfo['info']['uid']);
                AppbymeSendsms::updateMobile($mobile, $updataArr);
            }
        }
        $userInfo = AppbymeUserAccess::registerProcess($regInfo['info']['uid'], $password);
        $res['token'] = (string) $userInfo['token'];
        $res['secret'] = (string) $userInfo['secret'];
        $user = UserUtils::getUserInfomation($regInfo['info']['uid']);
        $res = array_merge($res,$user);
        return $res;
    }

    private function random_str($length) {
        //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
        $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

        $str = '';
        $arr_len = count($arr);
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $arr_len - 1);
            $str.=$arr[$rand];
        }

        return $str;
    }

}
