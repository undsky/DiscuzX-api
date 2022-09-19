<?php
/**
 * 获取验证码图片接口
 * User: 蒙奇·D·jie
 * Date: 2016/12/21
 * Time: 下午7:37
 * Email: mqdjie@gmail.com
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class GetImgCodeAction extends MobcentAction
{
    public function run($act = 'index', $key = '')
    {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->getImgCode($res, $act, $key);
        WebUtils::outputWebApi($res);
    }

    public function getImgCode($res, $act, $key)
    {
        if($act == 'getcode') {
            if($key == '') {
                return $this->makeErrorInfo($res, 'mobcent_error_params');
            }
            if($_GET['change']) {
                UserUtils::updateImgCode($key);
            }
            $vcode = UserUtils::getImgCode($key);
            if(!$vcode) {
                return $this->makeErrorInfo($res, 'mobcent_error_getpwdex_time');
            }
            WebUtils::getImg($vcode);//方法内有exit
        }
        $key = UserUtils::createKey();
        if(!$key) {
            return $this->makeErrorInfo($res, 'mobcent_error_getpwdex_error_init');
        }
        $res['body']['key'] = $key;
        $res['body']['url'] = WebUtils::createUrl_oldVersion('app/getimgcode', array('act' => 'getcode', 'key' => $key));
        return $res;
    }
}