<?php

/**
 * 初始化 App UI接口
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @author NaiXiaoXin
 * @copyright 2012-2015 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class InitUIAction extends MobcentAction
{

    private $custom;
    private $configId;
    private $diyVersion;
    private $type;

    public function run($custom = 0, $configId = 0, $md5 = '', $type = 'app')
    {
        $res              = $this->initWebApiArray();
        $this->configId   = intval($configId);
        $this->custom     = $custom;
        $this->diyVersion = $this->getDiyVersion();
        $this->type       = $type;
        $this->runWithMd5($res, $md5, $custom, $this->configId, $this->diyVersion, $type);
    }


    protected function runWithMd5($res, $md5, $custom, $configId, $diyVersion, $type)
    {
        $params   = array($configId, $diyVersion, $type);
        $cacheMd5 = Yii::app()->cache->get(CacheUtils::getUiDiyMd5Key($params));
        if ($cacheMd5 !== $md5) {
            $this->runWithCache(CacheUtils::getUiDiyKey($params), $res);
        } else {
            //Md5一致的情况下
            $res['md5'] = $cacheMd5;
            WebUtils::outputWebApi($res);
        }
    }


    protected function runWithCache($key)
    {
        $cache = $this->getCacheInfo();

        //    $res = array();
        if (!$cache['enable'] || ($res = Yii::app()->cache->get($key)) === false) {
            $res = WebUtils::outputWebApi($this->getResult($this->custom, $this->configId, $this->diyVersion,$this->type), '', false);
            if ($cache['enable']) {
                Yii::app()->cache->set($key, $res, $cache['expire']);
            }
        }
        exit($res);
    }

    protected function getCacheInfo()
    {
        return array('enable' => 1, 'expire' => HOUR_SECONDS,);
    }


    protected function getResult($custom, $id, $version = 1, $type)
    {
        $res        = $this->initWebApiArray();
        $moduleList = array();
        $uidiyModle = new AppbymeUiDiy($version, $id, $type);
        $result = $uidiyModle->getInfo(false);
        $temp   = WebUtils::tarr($result['modules']);
        $nav    = WebUtils::tarr($result['navInfo']);
        foreach ($temp as $module) {
            if (!$custom && $module['type'] == AppbymeUIDiyModel::MODULE_TYPE_CUSTOM) {
                $module['componentList'] = array();
            }
            $moduleList[] = AppUtils::filterModule($module);
        }
        $res['body']['navigation'] = $nav;
        $res['body']['moduleList'] = $moduleList;
        $res['head']['errInfo']    = '';
        $res['md5']                = md5(WebUtils::outputWebApi($res, '', false));
        Yii::app()->cache->set(CacheUtils::getUiDiyMd5Key(array($this->configId, $this->diyVersion)), $res['md5'], HOUR_SECONDS);

        return $res;
    }


    private function getDiyVersion()
    {
        return isset($_GET['egnVersion']) ? end(explode('.', $_GET['egnVersion'])) : '1';
    }
}
