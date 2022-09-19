<?php

/**
 *  获得UI配置
 * 
 *  @author   NaiXiaoXin<nxx@yytest.cn>
 *  @copyright (c) 2012-2015, Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class GetUIConfigAction extends MobcentAction {

    public function run() {
        $res = $this->initWebApiArray();
        $res['body'] = $this->getUIconfig();
        $res['head']['errInfo'] = '';
        echo WebUtils::outputWebApi($res, 'utf-8', false);
    }

    public function getUIconfig() {
        $return = array();
        $defauft = AppbymeUIDiyModel::getDefaultInfo();
        global $_G;
        $detemp['configId'] = '0';
        $detemp['name'] = $defauft['name'] ? WebUtils::u($defauft['name']) : '自定义页面';
        $detemp['icon'] = $defauft['icon'] ? $defauft['icon'] : $_G['siteurl'] . '/images/admin/module-default.png';
        $return[] = $detemp;
        $temp = AppbymeUIDiyModel::getOpenUIDiyConfig();
        if (empty($temp)) {
            return $return;
        }
        foreach ($temp as $k => $s) {
            $array = array();
            $array['configId'] = $s['id'];
            $array['name'] = WebUtils::u($s['name']);
            $array['icon'] = $s['icon'];
            $return[] = $array;
        }
        return $return;
    }

}
