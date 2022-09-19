<?php

/**
 * Created by PhpStorm.
 * User: byd
 * Date: 16/6/12
 * Time: 下午2:52
 */
class WebappController extends ApiController
{
    public function init()
    {
        $this->out_arr = $this->out_arr();
        $this->db = DbUtils::createDbUtils(true);
    }

    /**
     * 获取webApp页面数据
     */
    public function actionIndex()
    {
        $config = AppbymeConfig::getWebAppInfo();
        if(empty($config)){
            $config['open'] = (int)0;
            $config['wxappid'] ='';
            $config['wxappsecret'] = '';
        }
        $this->setData($config);
    }

    /**
     * webApp修改操作
     */
    public function actionUpdateDo()
    {
        if ($_POST) {
            $config['open'] = (int)$this->data['open'];
            $config['wxappid'] = $this->data['wxappid'];
            $config['wxappsecret'] = $this->data['wxappsecret'];
            AppbymeConfig::saveWebAppInfo($config);
        }
    }
}