<?php
/**
 * User: 蒙奇·D·jie
 * Date: 2017/3/13
 * Time: 上午11:22
 * Email: mqdjie@gmail.com
 */

class CheckImgAction extends MobcentAction
{
    public function run($key, $code)
    {
        $res = WebUtils::initWebApiArray();
        if(!$key) {
            WebUtils::outputWebApi(WebUtils::makeErrorInfo_oldVersion($res, 'mobcent_result_error'));
        }
        $result = UserUtils::checkImgCode($key, $code);
        if(!$result) {
            WebUtils::outputWebApi(WebUtils::makeErrorInfo_oldVersion($res, 'mobcent_error_getpwdex_cerr'));
        }
        WebUtils::outputWebApi($res);
    }
}