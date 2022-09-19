<?php
/**
 * 微信快速登录api
 * Created by PhpStorm.
 * User: onmylifejie
 * Date: 2016/6/21
 * Time: 19:50
 **/
class ConfigController extends ApiController{
    private $_wxLogin = 'wxLogin_config';
    private $_H5Post = 'h5post';

    public function actionWxLogin()
    {
        $config = AppbymeConfig::getCvalue($this->_wxLogin);

        $forumKey = isset($this->data['forumKey']) ? $this->data['forumKey'] : 0;
        if(!isset($config['wxLogin'])) {
            $config['wxLogin'] = isset($this->data['wxLogin']) ? intval($this->data['wxLogin']) : 1;
        }
        if(isset($this->data['wxLogin'])) {
            $config['wxLogin'] = $this->data['wxLogin'];
            AppbymeConfig::saveCvalue($this->_wxLogin, $config);
        }
        $this->setData($config);
    }

    public function actionPost()
    {
        if($_POST){
            AppbymeConfig::saveCvalue($this->_H5Post,$this->data['post']);
        }
        $config = intval(AppbymeConfig::getCvalueData($this->_H5Post,1));
        $this->setData(array('post'=>$config));
    }


}