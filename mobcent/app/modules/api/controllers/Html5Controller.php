<?php
/**
 * Created by PhpStorm.
 * User: onmylifejie
 * Date: 2016/6/2
 * Time: 18:32
 */

class Html5Controller extends ApiController
{

    private $_openDownBar = 'openDownBar';
    private $_BaiConfig = 'BaiId_config';


    public function actionShowBar()
    {
        $forumKey = isset($this->data['forumKey']) ? $this->data['forumKey'] : 0;

        $config = AppbymeConfig::getCvalue($this->_openDownBar);
        if(!isset($config['openDownBar'])) {
            $config['openDownBar'] = 1;
        }
        $this->setData($config);
    }

    public function actionCensus()
    {
        $config = AppbymeConfig::getCvalue($this->_BaiConfig);
        $config['census'] = isset($config['census']) ? $config['census'] : '';//JAVA第一次掉接口时没数据时返回空给客户端，避免什么都不返
        $forumKey = isset($this->data['forumKey']) ? $this->data['forumKey'] : 0;
        $Census = isset($this->data['census']) ? $this->data['census'] : 0;
        $isOpen = isset($this->data['isOpen']) ? $this->data['isOpen'] : 1;
        if ($Census) {
            if ($Census !== $config['census']) {
                $config['census'] = $Census;
                $config['isOpen'] = $isOpen;
                AppbymeConfig::saveCvalue($this->_BaiConfig, $config);
            }
        }
        $this->setData($config);
    }

    public function actionSendCode()
    {
        if(!$this->_isMobile()) {
            $this->error('未开启注册手机验证');
        }
        $messageUtils = new MessageUtils();
        $res = WebUtils::initWebApiArray_oldVersion();
        if($this->data['xiaoyun']) {
            $messageUtils->sendCode($res,$this->data['phone'],$this->data['code'],'xiaoyun');
        } else {
            $messageUtils->sendCode($res,$this->data['phone'],$this->data['code'],'register');
        }
    }


    // 是否开启注册手机验证
    private function _isMobile() {
        $config = WebUtils::getDzPluginAppbymeAppConfig('mobcent_register_validation');
        return !($config !== false && $config == 0);
    }
}