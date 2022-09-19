<?php

/**
 * api接口模板
 * @author tanguanghua <18725648509@163.com>
 **/

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class ApiModule extends CWebModule
{
    public function init(){
        $this->setImport(array(
            'api.components.*',
        ));
    }
    
}
