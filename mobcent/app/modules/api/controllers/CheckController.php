<?php
/**
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

//Mobcent::setErrors();
class CheckController extends ApiController {
    /**
     * 校验SecretKye是否准确
     */
    private $_openDownBar = 'openDownBar';

    public function actionSecretKey()
    {

    }

    public function actionCloseDown()
    {
        $openDownBar = isset($this->data['openDownBar']) ? $this->data['openDownBar'] : 1;
        $config['openDownBar'] = $openDownBar;
        AppbymeConfig::saveCvalue($this->_openDownBar, $config);
        $this->setData($config);
    }

}




