<?php
/**
 * 短信发送工具类
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}


class MessageUtils {

    public function __construct() {
    }


    public function sendCode($res,$mobile, $code, $act) {
        //根据短信渠道发送短信
        $sms = WebUtils::getDzPluginAppbymeAppConfig('sms');
        switch($sms){
            case 0:
                $res = $this->getSms($res, $mobile, $act, $code);
                break;
            case 1:
                $res = $this->getSmshy($res, $mobile, $act, $code);
                break;
            case 2:
                $res = $this->getSmsMy($res, $mobile, $act, $code);
                break;
            case 3:
                $res = $this->getSmsXiaoyun($res,$mobile,$act,$code);
                break;
            default:
                $res = $this->makeErrorInfo($res,'mobcent_yun_config_error');
                break;
        }
        return $res;
    }


    /**
     * 维新互译短信发送
     */
    private function getSmshy($res, $mobile, $act, $code) {
        $hy_account = WebUtils::getDzPluginAppbymeAppConfig('hy_account');
        $hy_password = WebUtils::getDzPluginAppbymeAppConfig('hy_password');
        $hy_template = WebUtils::u(WebUtils::getDzPluginAppbymeAppConfig('hy_template'));
        if(empty($hy_account)||empty($hy_password)||empty($hy_template)){
            return $this->makeErrorInfo($res,'mobcent_yun_config_error');
        }
        $temp = str_replace('【变量】', $code, $hy_template);
        $rest = new RestSmsSDK();
        $result = $rest->sendTemplateSMShy($hy_account, $hy_password, $mobile, $temp);
        if ($result == NULL) {
            return $this->makeErrorInfo($res, 'mobcent_result_error');
        }

        if ($result['code'] != 2) {
            return $this->makeErrorInfo($res, WebUtils::t($result['msg']));
        }
        return $res;
    }

    /**
     * 自助开发短信发送
     */
    private function getSmsMy($res, $mobile, $act, $code) {
        if (method_exists('sendSms', 'sendCode')) {
            $sendsms = new sendSms();
            $result = $sendsms->sendCode($mobile, $code, $act);
        } else {
            return $this->makeErrorInfo($res, 'mobcent_yun_config_error');
        }
        if ($result == NULL) {
            return $this->makeErrorInfo($res, 'mobcent_result_error');
        }

        if ($result['rs'] != 1) {
            $res['rs'] = 0;
            $res['head']['alert'] = 1;
            $res['errcode'] = $res['head']['errCode'] = $result['code'];
            $res['head']['errInfo'] = $result['msg'];
        }
        return $res;
    }

    /**
     * 云通讯发送
     */
    private function getSms($res, $mobile, $act, $code) {
        $accountSid = WebUtils::getDzPluginAppbymeAppConfig('yun_accountsid');
        $accountToken = WebUtils::getDzPluginAppbymeAppConfig('yun_authtoken');
        $appId = WebUtils::getDzPluginAppbymeAppConfig('appbyme_appid');
        $templateId = WebUtils::getDzPluginAppbymeAppConfig('yun_moduleid');
        if ($accountSid == '' || $accountToken == '' || $appId == '' || $templateId == '') {
            return $this->makeErrorInfo($res, 'mobcent_yun_config_error');
        }
        $serverPort = '8883';
        $serverIP = 'app.cloopen.com';
        $softVersion = '2013-12-26';

        $params = array(
            'serverIP' => $serverIP,
            'serverPort' => $serverPort,
            'softVersion' => $softVersion,
            'accountSid' => $accountSid,
            'accountToken' => $accountToken,
            'appId' => $appId,
            'action' => $act,
        );
        //手机号码，替换内容数组，模板ID
        $res = $this->sendTemplateSMS($res, $mobile, array($code, 2), $templateId, $params);
        return $res;
    }
    public function getSmsXiaoyun($res, $mobile,$act,$code) {
        $result = Msg::sendMsg($mobile,$code);
        if ($result == NULL) {
            return $this->makeErrorInfo($res, 'mobcent_result_error');
        }
        if ($result['rs'] != 1) {
            $res['rs'] = 0;
            $res['head']['alert'] = 1;
            $res['errcode'] = $res['head']['errCode'] = $result['errcode'];
            $res['head']['errInfo'] = WebUtils::t($result['message']);
        }
        return $res;
    }

    private function sendTemplateSMS($res, $to, $datas, $tempId, $params) {
        // 初始化REST SDK
        $rest = new RestSmsSDK($params['serverIP'], $params['serverPort'], $params['softVersion']);
        $rest->setAccount($params['accountSid'], $params['accountToken']);
        $rest->setAppId($params['appId']);

        $result = $rest->sendTemplateSMS($to, $datas, $tempId);
        if ($result == NULL) {
            return $this->makeErrorInfo($res, 'mobcent_result_error');
        }

        if ($result->statusCode != 0) {
            return $this->makeErrorInfo($res, $result->statusMsg);
        }
        return $res;
    }


    protected function makeErrorInfo($res, $message, $params=array()) {
        return WebUtils::makeErrorInfo_oldVersion($res, $message, $params);
    }
}