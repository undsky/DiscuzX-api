<?php

/**
 * UI Diy 控制器
 *
 * @author NaiXiaoXin
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @author HanPengyu
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

// Mobcent::setErrors();
class UIDiyController extends AdminController {

    public $navItemIconBaseUrlPath = '';
    public $componentFastpostIconBaseUrlPath = '';
    public $componentDiscoverIconBaseUrlPath = '';
    public $componentTopbarIconBaseUrlPath = '';

    public function init() {
        parent::init();

        $this->navItemIconBaseUrlPath = $this->rootUrl . '/images/admin/icon1';
        $this->componentFastpostIconBaseUrlPath = $this->rootUrl . '/images/admin/icon2';
        $this->componentDiscoverIconBaseUrlPath = $this->rootUrl . '/images/admin/icon3';
        $this->componentTopbarIconBaseUrlPath = $this->rootUrl . '/images/admin/topbar';
    }

    public function getComponentIconUrl($icon) {
        if (strpos($icon, AppbymeUIDiyModel::COMPONENT_ICON_DISCOVER_DEFAULT) !== false) {
            return $this->componentDiscoverIconBaseUrlPath . '/' . $icon . '.png';
        } else if (strpos($icon, AppbymeUIDiyModel::COMPONENT_ICON_FASTPOST) !== false) {
            return $this->componentFastpostIconBaseUrlPath . '/' . $icon . '_n.png';
        } else if (strpos($icon, AppbymeUIDiyModel::COMPONENT_ICON_TOPBAR) !== false) {
            return $this->componentTopbarIconBaseUrlPath . '/' . $icon . '_n.png';
        } else {
            return $icon;
        }
    }

    public function actionIndex($version = '2') {
        echo <<<ETO
        <center style="margin-top:100px;">当前功能已迁移,请访问[<a href="http://console.apps.xiaoyun.com" target="_blank" style="color:red;text-decoration: none;">小云控制台</a>]</center>
ETO;
        exit();
    }
    private function _gettopic(){
        $data = DbUtils::createDbUtils(true)->queryAll('SELECT `ti_id`,`ti_title` FROM %t WHERE `ti_starttime`<%d AND `ti_endtime`>%d ORDER BY `ti_id` DESC LIMIT 0,30',array('appbyme_topic_items',time(),time()));
        return $data;
    }
    public function actionSaveUidiy($navInfo,$modules,$isTemp = 0,$id = 0,$version = '1'){
        $res = WebUtils::initWebApiResult();
        $uidiyModule = new AppbymeUiDiy($version, $id);
        $navInfo = json_decode($navInfo,true);
        $modules = json_decode($modules,true);
        $uidiyModule->saveDiy(!$isTemp, $navInfo, $modules);
        echo WebUtils::outputWebApi($res, 'utf-8', false);
    }

    public function actionSaveNavInfo($navInfo, $isSync = 0, $id = 0,$version = '') {
        $res = WebUtils::initWebApiResult();

        $navInfo = WebUtils::jsonDecode($navInfo);
        if ($id >= '1') {
            AppbymeUIDiyModel::nxxSaveNavigationInfo($id, $navInfo, true);
            $isSync && AppbymeUIDiyModel::nxxSaveNavigationInfo($id, $navInfo);
        } else {
            AppbymeUIDiyModel::saveNavigationInfo($navInfo, true,$version);
            $isSync && AppbymeUIDiyModel::saveNavigationInfo($navInfo,false,$version);
        }
        echo WebUtils::outputWebApi($res, 'utf-8', false);
    }

    public function actionSaveModules($modules, $isSync = 0, $id = 0) {
        $res = WebUtils::initWebApiResult();

        $modules = WebUtils::jsonDecode($modules);
        if ($id >= '1') {
            AppbymeUIDiyModel::nxxSaveModules($id, $modules, true);
        } else {
            AppbymeUIDiyModel::saveModules($modules, true);
        }

        if ($isSync) {
            $tempModules = array();
            foreach ($modules as $module) {
                $module['leftTopbars'] = $this->_filterTopbars($module['leftTopbars']);
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

                $tempModules[] = $module;
            }
            if ($id >= '1') {
                AppbymeUIDiyModel::nxxSaveModules($id, $tempModules);
            } else {
                AppbymeUIDiyModel::SaveModules($tempModules);
            }
        }

        echo WebUtils::outputWebApi($res, 'utf-8', false);
    }

    public function actionInit() {
        $res = WebUtils::initWebApiResult();
        $id = (int) $_GET['id'];
        if ($id >= 1) {
            AppbymeUIDiyModel::nxxSaveNavigationInfo($id, AppbymeUIDiyModel::initNavigation(), true);
            AppbymeUIDiyModel::nxxSaveModules($id, AppbymeUIDiyModel::initModules(), true);
        } else {
            AppbymeUIDiyModel::saveNavigationInfo(AppbymeUIDiyModel::initNavigation(), true);
            AppbymeUIDiyModel::saveModules(AppbymeUIDiyModel::initModules(), true);
        }
        echo WebUtils::outputWebApi($res, 'utf-8', false);
    }

    // render 模块手机视图
    public function actionModulemobileui($module) {
        $module = json_decode($module,true);
        $this->renderPartial('module_mobile_ui', array(
            'module' => $module,
        ));
    }

    // render 组件手机视图
    public function actionComponentmobileui($component) {
        $component = rawurldecode($component);
        $component = WebUtils::jsonDecode($component);
        $this->renderPartial('component_mobile_ui', array(
            'component' => $component,
        ));
    }

    // 导入配置
    public function actionImportConfig() {
        $res = WebUtils::initWebApiResult();

        $errMsgs = array(
            0 => '导入配置成功',
            1 => '导入配置失败',
        );
        $res = WebUtils::makeWebApiResult($res, 1, $errMsgs[1]);
        if (!empty($_FILES) && count($_FILES) && is_uploaded_file($_FILES['file']['tmp_name']) && !$_FILES['file']['error']) {
            $config = $this->_decodeConfig(file_get_contents($_FILES['file']['tmp_name']));
            if (!empty($config)) {
                $id = (int) $_GET['id'];
                if ($id >= 1) {
                    AppbymeUIDiyModel::nxxSaveNavigationInfo($id, $config['navigation'], true);
                    AppbymeUIDiyModel::nxxSaveModules($id, $config['moduleList'], true);
                } else {
                    AppbymeUIDiyModel::saveNavigationInfo($config['navigation'], true);
                    AppbymeUIDiyModel::saveModules($config['moduleList'], true);
                }
                $res = WebUtils::makeWebApiResult($res, 0, $errMsgs[0]);
            }
        }

        WebUtils::outputWebApi($res, 'utf-8');
    }

    // 导出配置
    public function actionExportConfig() {
        header('Pragma: public');
        header('Expires: 0');
        header('Accept-Ranges: bytes');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0, max-age=0');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header(sprintf('Content-Disposition: attachment; filename="%s"', 'appbyme_uidiy.config'));

        file_put_contents('php://output', $this->_encodeConfig());
    }

    //显示配置
    public function actionConfig() {
        $config = AppbymeUIDiyModel::getConfig();
        $default = AppbymeUIDiyModel::getDefaultInfo();
        $this->renderPartial('uidiy_config', array(
            'config' => $config,
            'default' => $default,
        ));
    }

    //增加配置
    public function actionAddConfig() {
        //$id = (int) $_GET['id'];
        if ($_POST) {
            if (empty($_GET['name'])) {
                AppbymeUIDiyModel::showMsg('您未输入模块名称');
            }
            $icon = $this->iconUpload();
            $status = $_GET['open'] ? $_GET['open'] : '1';
            $id = AppbymeUIDiyModel::addConfig($_GET['name'], $icon, $status);
            $res['id'] = $id;
            AppbymeUIDiyModel::showMsg('添加成功', Yii::app()->createAbsoluteUrl('admin/uidiy/config'));
        } else {
            $this->renderPartial('configadd');
        }
    }

    //修改配置
    public function actionEditConfig() {
        $id = (int) $_GET['id'];
        if ($id >= 1) {
            $config = AppbymeUIDiyModel::getConfigByID($id);
            if (empty($config)) {
                AppbymeUIDiyModel::showMsg('ID错误请重试！', Yii::app()->createAbsoluteUrl('admin/uidiy/config'));
            }
        } else {
            $config = AppbymeUIDiyModel::getDefaultInfo();
            $config = array_diff($config, array('id' => '0'));
        }
        if ($_POST) {
            if (empty($_GET['name'])) {
                AppbymeUIDiyModel::showMsg('您未输入模块名称');
            }
            $name = WebUtils::t(daddslashes($_GET['name']));
            $icon = $this->iconUpload($config['icon']);
            $status = $_GET['status'] ? $_GET['status'] : '1';
            if ($id >= 1) {
                AppbymeUIDiyModel::editConfig($id, $name, $icon, $status);
            } else {
                AppbymeUIDiyModel::editDefauftConfig($name, $icon);
            }
            AppbymeUIDiyModel::showMsg('修改成功', Yii::app()->createAbsoluteUrl('admin/uidiy/config'));
        } else {
            $this->renderPartial('configedit', array('config' => $config));
        }
    }

    //删除配置
    public function actionDeleteConfig() {
        $res = WebUtils::initWebApiResult();
        $id = (int) $_GET['id'];
        $config = AppbymeUIDiyModel::getConfigByID($id);
        $res['errCode'] = '0';
        if (empty($config)) {
            $res['errCode'] = '-1';
            $res['errMsg'] = 'ID不存在，清重试';
            WebUtils::outputWebApi($res, 'utf-8');
        }
        AppbymeUIDiyModel::deleteConfig($id);
    }

    private function _encodeConfig() {
        $id = (int) $_GET['id'];
        if ($id >= 1) {
            $module = AppbymeUIDiyModel::nxxGetModules($id, true);
            $nav = AppbymeUIDiyModel::nxxGetNavigationInfo($id, true);
        } else {
            $module = AppbymeUIDiyModel::getModules(true);
            $nav = AppbymeUIDiyModel::getNavigationInfo(true);
        }
        $config = array(
            'version' => AppbymeUIDiyModel::CONFIG_VERSION,
            'dataChecksum' => '',
            'data' => array(
                'navigation' => $nav,
                'moduleList' => $module,
            ),
        );
        $config['dataChecksum'] = md5(WebUtils::jsonEncode($config['data'], 'utf-8'));
        $config = (string) WebUtils::jsonEncode($config, 'utf-8');
        return base64_encode($config);
    }

    private function _decodeConfig($config) {
        $res = array();

        $tmpConfig = base64_decode($config);
        $tmpConfig = WebUtils::jsonDecode($tmpConfig);
        if (isset($tmpConfig['data']) && isset($tmpConfig['dataChecksum']) &&
                $tmpConfig['dataChecksum'] == md5(WebUtils::jsonEncode($tmpConfig['data'], 'utf-8'))) {
            $res = $tmpConfig['data'];
        }
        return $res;
    }

    private function _filterTopbars($topbars) {
        $tempTopbars = array();
        foreach ($topbars as $topbar) {
            $topbar = $this->_filterComponent($topbar);
            $topbar['type'] != AppbymeUIDiyModel::COMPONENT_TYPE_EMPTY && $tempTopbars[] = $topbar;
        }
        return $tempTopbars;
    }

    private function _filterComponent($component) {
        loadcache('forums');
        global $_G;
        $forums = $_G['cache']['forums'];

        $tempComponent = $component;

        // 转换fastpostForumIds结构
        $tempFastpostForumIds = array();
        foreach ($component['extParams']['fastpostForumIds'] as $fid) {
            $tempFastpostForumIds[] = array(
                'fid' => $fid,
                'title' => WebUtils::u($forums[$fid]['name']),
            );
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

    private function _getTopicTypeSortInfos() {
        $infos = array();

        $fields = DbUtils::getDzDbUtils(true)->queryAll('
            SELECT fid, threadtypes, threadsorts
            FROM %t
            WHERE fid IN (%n)
            ', array('forum_forumfield', DzForumForum::getFids())
        );
        foreach ($fields as $field) {
            if (!empty($field)) {
                $info = array('fid' => (int) $field['fid'], 'types' => array(), 'sorts' => array());

                $types = unserialize($field['threadtypes']);
                if (!empty($types['types'])) {
                    foreach ($types['types'] as $key => $value) {
                        // 控制管理组专用
                        // if ($types['moderators'][$key] == 1) {
                        // continue;
                        // }
                        $info['types'][] = array(
                            'id' => $key,
                            'title' => WebUtils::emptyHtml($value),
                        );
                    }
                }
                $sorts = unserialize($field['threadsorts']);
                if (!empty($sorts['types'])) {
                    foreach ($sorts['types'] as $key => $value) {
                        $info['sorts'][] = array(
                            'id' => $key,
                            'title' => WebUtils::emptyHtml($value),
                        );
                    }
                }

                $infos[$info['fid']] = $info;
            }
        }
        return $infos;
    }

    /**
     * 上传图片
     * 
     * @author HanPengyu
     * @access public
     *
     * @return mixed 返回状态码和信息.
     */
    public function actionUploadIcon($type = 'uidiy') {

        $res = WebUtils::initWebApiResult();

        // 没有上传的文件
        if (empty($_FILES)) {
            self::makeResponse(0, '没有上传的文件,或者选择的文件太大！');
        }

        // 创建放置图片的文件夹
        $date = date('Ym/d', time());
        $path = MOBCENT_UPLOAD_PATH . '/' . $type . '/' . $date;

        if (UploadUtils::makeBasePath($path) == '') {
            self::makeResponse(0, '上传目录不可写！');
        }

        foreach ($_FILES as $file) {

            $file['name'] = strip_tags($file['name']);
            $ext = FileUtils::getFileExtension($file['name'], 'jpg');

            // 检测
            $imageRes = $this->checkUpload($res, $file);

            if (!$imageRes['errCode']) {
                self::makeResponse(0, $imageRes['errMsg']);
            }

            $saveName = FileUtils::getRandomUniqueFileName($path);
            $fileName = $saveName . '.' . $ext;
            $uploaded = Qiniuup::uptoqiniu(file_get_contents($file['tmp_name']),'diy_upload'.time().rand(1,500).'.'.$ext);
            if($uploaded){
                $fileName = $uploaded;
                if(file_exists($file['tmp_name'])){
                    @unlink($file['tmp_name']);
                }
            }else{
                $uploaded = move_uploaded_file($file['tmp_name'], $fileName);
                $fileName = $this->dzRootUrl . '/data/appbyme/upload/' . $type . '/' . $date . '/' . basename($fileName);
                ImageUtils::getThumbImageEx($fileName, 10, false, false, true);
            }
            if (!$uploaded) {
                self::makeResponse(0, '上传图片失败！');
            }
            self::makeResponse(1, $fileName);
        }
    }

    /**
     * 检测上传相关项
     * 
     * @param mixed $res  初始化数组.
     * @param mixed $file 上传的单个文件数组信息.
     *
     * @return mixed array.
     */
    public function checkUpload($res, $file) {

        // 文件上传失败，捕获错误代码
        if ($file['error']) {
            $res['errCode'] = 0;
            $res['errMsg'] = self::error($file['error']);
            return $res;
        }

        // 无效上传
        $file['name'] = strip_tags($file['name']);
        if (empty($file['name'])) {
            $res['errCode'] = 0;
            $res['errMsg'] = '未知上传错误！';
            return $res;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $res['errCode'] = 0;
            $res['errMsg'] = '非法上传文件';
            return $res;
        }

        // 检查文件大小
        $maxSize = 2000000;
        if ($file['size'] > $maxSize || $file['size'] == 0) {
            $res['errCode'] = 0;
            $res['errMsg'] = '上传文件大小不符！';
            return $res;
        }

        // 检查文件Mime类型
        $mime = $file['type'];
        $allowMime = array('image/png', 'image/jpeg');
        if (!in_array(strtolower($mime), $allowMime)) {
            $res['errCode'] = 0;
            $res['errMsg'] = '上传文件MIME类型不允许！';
            return $res;
        }

        // 检查文件后缀
        $ext = FileUtils::getFileExtension($file['name'], 'jpg');
        $allowExt = array('jpg', 'png', 'jpeg');
        if (!in_array(strtolower($ext), $allowExt)) {
            $res['errCode'] = 0;
            $res['errMsg'] = '上传文件后缀不允许!';
            return $res;
        }

        // 通过检测
        $res['errCode'] = 1;
        return $res;
    }

    /**
     * 通过php上传的错误码获取具体的错误信息
     * 
     * @param mixed $errNo 上传错误码.
     *
     * @return mixed string.
     */
    public static function error($errNo) {
        switch ($errNo) {
            case 1:
                $msg = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
                break;
            case 2:
                $msg = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
                break;
            case 3:
                $msg = '文件只有部分被上传！';
                break;
            case 4:
                $msg = '没有文件被上传！';
                break;
            case 6:
                $msg = '找不到临时文件夹！';
                break;
            case 7:
                $msg = '文件写入失败！';
                break;
            default:
                $msg = '未知上传错误！';
        }
        return $msg;
    }

    /**
     * 删除指定的图片
     * 
     * @param mixed $fileName 文件的名字，是带有全路径的.
     *
     * @return mixed 返回状态码和信息.
     *
     */
    public function actionDelIcon($fileName) {
        $basename = str_replace($this->dzRootUrl . '/data/appbyme/upload/', '', $fileName);
        $fileName = MOBCENT_UPLOAD_PATH . '/' . $basename;
        if (!file_exists($fileName)) {
            self::makeResponse(0, '图片不存在！');
        }

        if (!unlink($fileName)) {
            self::makeResponse(0, '图片删除失败！');
        }
        self::makeResponse(1, '图片删除成功！');
    }

    public static function makeResponse($errCode = 1, $errMsg = '') {
        $res = WebUtils::initWebApiResult();
        $res['errCode'] = $errCode;
        $res['errMsg'] = $errMsg;
        $res['data'] = array();
        WebUtils::outputWebApi($res, 'utf-8', true);
    }

    public function iconUpload($oldicon) {
        $icon = $this->rootUrl . '/images/admin/module-default.png';
        if ($_GET['icon_text']) {
            $icon = $_GET['icon_text'];
        }
        if (!empty($_FILES['icon_file']['name'])) {
            $file = $_FILES['icon_file'];
            // 创建放置图片的文件夹
            $date = date('Ym/d', time());
            $path = MOBCENT_UPLOAD_PATH . '/uidiy/' . $date;

            if (UploadUtils::makeBasePath($path) == '') {
                AppbymeUIDiyModel::showMsg('上传目录不可写！');
            }
            $file['name'] = strip_tags($file['name']);
            $ext = FileUtils::getFileExtension($file['name'], 'jpg');
            $imageRes = $this->checkUpload($res, $file);
            if (!$imageRes['errCode']) {
                AppbymeUIDiyModel::showMsg($imageRes['errMsg']);
            }
            $saveName = FileUtils::getRandomUniqueFileName($path);
            $fileName = $saveName . '.' . $ext;
            $uploaded = Qiniuup::uptoqiniu(file_get_contents($file['tmp_name']),'diy_upload'.time().rand(1,500).'.'.$ext);
            if($uploaded){
                $fileName = $uploaded;
                if(file_exists($file['tmp_name'])){
                    @unlink($file['tmp_name']);
                }
            }else{
                $uploaded = move_uploaded_file($file['tmp_name'], $fileName);
                if($uploaded){
                    $fileName = $this->dzRootUrl . '/data/appbyme/upload/uidiy/' . $date . '/' . basename($fileName);
                }
            }
            if (!$uploaded) {
                AppbymeUIDiyModel::showMsg('上传图片失败！');
            }
            $icon = $fileName;
        }
        return $icon;
    }

}
