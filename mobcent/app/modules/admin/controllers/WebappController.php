<?php
/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
 class WebappController extends AdminController{
     public function actionIndex(){
         $config = AppbymeConfig::getWebAppInfo();
        if($_POST){
            $config['open'] = (int)$_POST['open'];
            $config['wxappid'] = $_POST['wxappid'];
            $config['wxappsecret'] = $_POST['wxappsecret'];
            AppbymeConfig::saveWebAppInfo($config);
        }
         $this->renderPartial('index',array('config'=>$config));
     }
 }