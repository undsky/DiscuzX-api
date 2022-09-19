<?php
/**
 * 新UiDiyModule类
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AppbymeUiDiy
{


    public    $version     = '1';
    public    $id          = 0;
    public    $now_ver     = '';
    public    $now_ver_tmp = '';
    protected $version_pre = array(
        'app'   => 'app_uidiy_',
        'weapp' => 'weapp_uidiy_',
    );
    protected $module_pre  = array(
        'app'   => 'app_uidiy_modules',
        'weapp' => 'weapp_uidiy_modules'
    );
    protected $nav_pre     = array(
        'app'   => 'app_uidiy_nav_info',
        'weapp' => 'weapp_uidiy_nav_info',
    );
    protected $type        = 'app';
    protected $type_array  = array('app', 'weapp');


    public function __construct($version = '1', $id = 0, $type = 'app')
    {
        $this->version     = $version;
        $this->id          = $id;
        $this->now_ver     = $this->getDiyVersion(FALSE);
        $this->now_ver_tmp = $this->getDiyVersion(TRUE);
        $this->type        = in_array($type, $this->type_array) ? $type : 'app';
//        $this->type        = $type;

    }

    public function getInfo($isTemp)
    {
        $navInfo     = $this->getNavInfo($isTemp);
        $tempModules = $this->getModule($isTemp);
        // 初始化默认配置1
        if (empty($navInfo) || empty($navInfo['navItemList'])) {
            $navInfo = AppbymeUIDiyModel::initNavigation();
        }
        //初始化默认配置2
        if (empty($tempModules)) {
            $tempModules = AppbymeUIDiyModel::initModules();
        }
        // 必须存在发现和快发模块
        $isFindDiscover = $isFindFastpost = FALSE;
        $modules        = array();
        foreach ($tempModules as $module) {
            switch ($module['id']) {
                case AppbymeUIDiyModel::MODULE_ID_DISCOVER:
                    $isFindDiscover = TRUE;
                    break;
                case AppbymeUIDiyModel::MODULE_ID_FASTPOST:
                    $isFindFastpost = TRUE;
                    break;
            }
            $modules[] = $module;
        }
        if (!$isFindFastpost) {
            $fastpostModule = AppbymeUIDiyModel::initFastpostModule();
            array_unshift($modules, $fastpostModule);
        }
        if (!$isFindDiscover) {
            $discoverModule = AppbymeUIDiyModel::initDiscoverModule();
            array_unshift($modules, $discoverModule);
        }

        return array('navInfo' => $navInfo, 'modules' => $modules);
    }


    public function getNavInfo($isTemp)
    {
        $navInfo = $this->getNavigationInfo($isTemp, $this->version);

        return $navInfo;
    }


    public function getModule($isTemp)
    {
        $tempModules = $this->getModules($isTemp, $this->version);

        return $tempModules;

    }

    public function saveDiy($isTemp, $navInfo, $modules)
    {
        $this->saveModules($modules, $isTemp);
        $this->saveNatinfo($navInfo, $isTemp);
        if (!$isTemp) {
            $this->saveModules($modules, TRUE);
            $this->saveNatinfo($navInfo, TRUE);
            //删除Yii缓存
            //$configId,$diyVersion
        }
        Yii::app()->cache->delete(CacheUtils::getUiDiyMd5Key(array($this->id, $this->version, $this->type)));
        Yii::app()->cache->delete(CacheUtils::getUiDiyKey(array($this->id, $this->version, $this->type)));

        return TRUE;
    }



    /**
     * ----------------------------DB层-------------------------------------------
     */

    /**
     * 获得DIY版本号
     */
    private function getDiyVersion($isTemp)
    {
        $data = DbUtils::createDbUtils(TRUE)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ',
            array('appbyme_config', $this->getDiyVersionKey($isTemp))
        );

        return $data ? $data : '1';
    }

    private function getDiyVersionKey($isTemp)
    {
        $data = $this->version_pre[$this->type];
        $data .= $isTemp ? 'tmp' : 'stable';
        $data .= '_' . $this->id . '_version';

        return $data;
    }

    protected function getNavKey($isTemp)
    {
        $data = $this->nav_pre[$this->type];
        $data .= $isTemp ? '_temp' : '';

        return $data;
    }


    protected function getModuleKey($isTemp)
    {
        $data = $this->module_pre[$this->type];
        $data .= $isTemp ? '_temp' : '';

        return $data;
    }

    private function getNavigationInfo($isTemp = FALSE, $version = '')
    {
        $tmpkey    = $this->getNavKey($isTemp);
        $dbVersion = $isTemp ? $this->now_ver_tmp : $this->now_ver;
        if ($version > $dbVersion) {
            $version = $dbVersion;
        }
        while ($version > 0) {
            $key = $tmpkey . '_' . $this->id . '_' . $version;
            if (DbUtils::createDbUtils(TRUE)->queryScalar('SELECT COUNT(0) FROM %t WHERE `ckey`=%s', array('appbyme_config', $key))) {
                break;
            }
            $version--;
        }
        $keys = array(
            $key,
            $tmpkey . '1.0',
            $tmpkey,
        );
        for ($i = 0; $i < 3; $i++) {
            $data = DbUtils::createDbUtils(TRUE)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ', array('appbyme_config', $keys[$i])
            );
            if ($data) {
                break;
            }
        }

        if ($data) {
            $data   = WebUtils::u($data);
            $return = unserialize($data);
        } else {
            $return = array(
                'type'        => AppbymeUIDiyModel::NAV_TYPE_BOTTOM,
                'navItemList' => array()
            );
        }

        return $return;
    }

    public function getModules($isTemp = FALSE, $version = '')
    {
        $tmpkey    = $this->getModuleKey($isTemp);
        $dbVersion = $isTemp ? $this->now_ver_tmp : $this->now_ver;
        if ($version > $dbVersion) {
            $version = $dbVersion;
        }
        while ($version > 0) {
            $key = $tmpkey . '_' . $this->id . '_' . $version;
            if (DbUtils::createDbUtils(TRUE)->queryScalar('SELECT COUNT(0) FROM %t WHERE `ckey`=%s', array('appbyme_config', $key))) {
                break;
            }
            $version--;
        }
        $keys = array(
            $key,
            $tmpkey,
        );
        for ($i = 0; $i < 2; $i++) {
            $data = DbUtils::createDbUtils(TRUE)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ', array('appbyme_config', $keys[$i])
            );
            if ($data) {
                break;
            }
        }
        if ($data) {
            $data   = WebUtils::u($data);
            $return = unserialize($data);
        } else {
            $return = array();
        }

        return $return;
    }


    private function saveNatinfo($data, $isTemp)
    {
        $key = $this->getNavKey($isTemp);
        $key .= '_' . $this->id . '_' . $this->version;
        $appUIDiyNavInfo = array(
            'ckey'   => $key,
            'cvalue' => strtoupper(Yii::app()->charset) == 'GBK' ? WebUtils::t(serialize($this->_filterGbkChar($data))) : WebUtils::t(serialize($data)),
        );
        DB::insert('appbyme_config', $appUIDiyNavInfo, FALSE, TRUE);
        $version     = $this->getDiyVersion($isTemp);
        $version     = $version > $this->version ? $version : $this->version;
        $versionInfo = array(
            'ckey'   => $this->getDiyVersionKey($isTemp),
            'cvalue' => $version,
        );
        DB::insert('appbyme_config', $versionInfo, FALSE, TRUE);

        return TRUE;
    }

    private function saveModules($modules, $isTemp)
    {
        $key = $this->getModuleKey($isTemp);
        $key .= '_' . $this->id . '_' . $this->version;
        if (!$isTemp) {
            $tempModules = array();
            foreach ($modules as $module) {
                $module['leftTopbars']  = $this->_filterTopbars($module['leftTopbars']);
                $module['rightTopbars'] = $this->_filterTopbars($module['rightTopbars']);

                $tempComponentList = array();
                foreach ($module['componentList'] as $component) {
                    $component = $this->_filterComponent($component);
                    if ($module['type'] == AppbymeUIDiyModel::MODULE_TYPE_SUBNAV) {
                        if ($component['title'] != '') {
                            $tempComponentList[] = $component;
                        }
                    } else {
                        $tempComponentList[] = $component;
                    }
                }
                $module['componentList'] = $tempComponentList;
                $tempModules[]           = $module;
            }
        } else {
            $tempModules = $modules;
        }
        $appUIDiyNavInfo = array(
            'ckey'   => $key,
            'cvalue' => strtoupper(Yii::app()->charset) == 'GBK' ? WebUtils::t(serialize($this->_filterGbkChar($tempModules))) : WebUtils::t(serialize($tempModules)),
        );
        DB::insert('appbyme_config', $appUIDiyNavInfo, FALSE, TRUE);
        $version     = $this->getDiyVersion($isTemp);
        $version     = $version > $this->version ? $version : $this->version;
        $versionInfo = array(
            'ckey'   => $this->getDiyVersionKey($isTemp),
            'cvalue' => $version,
        );
        DB::insert('appbyme_config', $versionInfo, FALSE, TRUE);

        return TRUE;
    }

    private function _filterGbkChar(array $arr)
    {
        $string = json_encode($arr);
        $string = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return iconv("UCS-2BE","GBK//IGNORE",pack("H*", $matches[1]));'
            ),
            $string);
        $string = iconv("GBK", "UTF-8", $string);

        return json_decode($string, TRUE);
    }

    private function _filterComponent($component)
    {
        loadcache('forums');
        global $_G;
        $forums = $_G['cache']['forums'];

        $tempComponent = $component;

        // 转换fastpostForumIds结构
        $tempFastpostForumIds = array();
        foreach ($component['extParams']['fastpostForumIds'] as $fid) {
            if (is_array($fid)) {
                $tempFastpostForumIds[] = $fid;
            } else {
                $tempFastpostForumIds[] = array(
                    'fid'   => $fid,
                    'title' => WebUtils::u($forums[$fid]['name']),
                );
            }
        }
        $tempComponent['extParams']['fastpostForumIds'] = $tempFastpostForumIds;

        // 转换componentList结构
        $tempComponentList = array();
        foreach ($component['componentList'] as $subComponent) {
            if (!$subComponent['extParams']['isHidden']) {
                $tempComponentList[] = $this->_filterComponent($subComponent);
            }
        }
        $tempComponent['componentList'] = $tempComponentList;

        return $tempComponent;
    }

    private function _filterTopbars($topbars)
    {
        $tempTopbars = array();
        foreach ($topbars as $topbar) {
            $topbar = $this->_filterComponent($topbar);
            if ($topbar['type'] != AppbymeUIDiyModel::COMPONENT_TYPE_EMPTY && $topbar) {
                $tempTopbars[] = $topbar;
            }
        }

        return $tempTopbars;
    }


    //获得配置信息通过ID
    public function getConfigByID($id)
    {
        return DbUtils::createDbUtils(TRUE)->queryRow('
            SELECT *
            FROM %t
            WHERE `id` = %s
            ', array('appbyme_uidiyconfig', $id)
        );
    }


    public function getUiDiyVersion()
    {
        $this->version     = isset($_GET['egnVersion']) ? end(explode('.', $_GET['egnVersion'])) : '1';
        $this->now_ver     = $this->getDiyVersion(FALSE);
        $this->now_ver_tmp = $this->getDiyVersion(TRUE);

        return;
    }

}