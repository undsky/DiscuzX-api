<?php

/**
 * 获取模块配置接口
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @author NaiXiaoXin
 * @copyright 2012-2015 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class ModuleConfigAction extends MobcentAction
{

    public function run($moduleId, $configId = 0, $type = 'app')
    {
        $res                    = $this->initWebApiArray();
        $id                     = (int)$configId;
        $res['body']            = $this->_getModuleconfig($moduleId, $id, $type);
        $res['head']['errInfo'] = '';
        WebUtils::outputWebApi($res);
    }

    private function _getModuleconfig($moduleId, $id, $type)
    {
        $module     = array('padding' => '');
        $uidiyModle = new AppbymeUiDiy('', $id, $type);
        $uidiyModle->getUiDiyVersion();
        $temp = $uidiyModle->getModule(false);
        foreach ($temp as $tmpModule) {
            if ($tmpModule['id'] == $moduleId) {
                $module = AppUtils::filterModule($tmpModule);
                break;
            }
        }

        return array(
            'module' => WebUtils::tarr($module),
        );
    }

}
