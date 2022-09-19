<?php

/**
 * 后台管理默认控制器
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class IndexController extends AdminController {

    public function actionIndex() {
        $this->renderPartial('index');
    }

    public function actionTop() {
        $this->renderPartial('top');
    }

    public function actionLeft($act = 0) {
        if ($act == 1) {
            $menu = array(
                array('name' => 'DIY自定义', 'url' => Yii::app()->createAbsoluteUrl('admin/uidiy')),
                array('name' => 'DIY高级自定义', 'url' => Yii::app()->createAbsoluteUrl('admin/uidiy/config')),
                array('name' => '微生活', 'url' => Yii::app()->createAbsoluteUrl('admin/wshdiy'))
            );
            $menu = array_merge($menu, AppbymeUIDiyModel::getLeftMenu());
        } elseif ($act == 2) {
            $menu2 = AppbymePluginModel::allPlugin();
            foreach ($menu2 as &$row) {
                $row['name'] = WebUtils::u($row['plugin_name']);
                $row['url'] = Yii::app()->createAbsoluteUrl($row['menu']);
            }
            $menu1 = array(
                array('name' => '插件管理', 'url' => Yii::app()->createAbsoluteUrl('admin/plugin/index')),
                array('name' => '邀请注册', 'url' => Yii::app()->createAbsoluteUrl('admin/reward/rewardlist'))
            );
            $menu = array_merge($menu1, $menu2);
        } elseif ($act == 3) {
            $menu = array(
                array('name' => '权限管理', 'url' => Yii::app()->createAbsoluteUrl('admin/auth/index')),
            );
        } elseif ($act == 4) {
            $menu = array(
                array('name' => '配置需求', 'url' => $this->dzRootUrl . '/mobcent/requirements/index.php'),
                array('name' => '应用下载', 'url' => $this->dzRootUrl . '/plugin.php?id=appbyme_app:download'),
                array('name' => '帮助文档', 'url' => 'http://bbs.appbyme.com/forum-57-2.html'),
                array('name' => 'VIP问题反馈', 'url' => 'http://bbs.appbyme.com/forum-58-1.html'),
            );
        } else {
            $menu = array(
                array('name' => '首页', 'url' => Yii::app()->createAbsoluteUrl('admin/index/main')),
                array('name' => 'DIY自定义', 'url' => Yii::app()->createAbsoluteUrl('admin/uidiy')),
                array('name' => '基础设置', 'url' => Yii::app()->createAbsoluteUrl('admin/setting')),
                array('name' => 'App推送', 'url' => Yii::app()->createAbsoluteUrl('admin/push')),
                array('name' => '分享管理', 'url' => Yii::app()->createAbsoluteUrl('admin/share')),
                array('name' => 'WebApp管理', 'url' => Yii::app()->createAbsoluteUrl('admin/webapp')),
                array('name' => '用户管理', 'url' => Yii::app()->createAbsoluteUrl('admin/user')),
                array('name' => '微生活', 'url' => Yii::app()->createAbsoluteUrl('admin/wshdiy')),
                array('name' => '插件管理', 'url' => Yii::app()->createAbsoluteUrl('admin/plugin/index')),
                array('name' => '话题管理', 'url' => Yii::app()->createUrl('admin/topic/tpcmag')),
                array('name' => '应用中心', 'url' => 'http://www.appbyme.com/mobcentPlugin/'),
            );
        }
        $this->renderPartial('left', array('menu' => $menu));
    }

    public function actionMain() {
        $this->renderPartial('main');
    }

    public function actionLogin() {
        if (UserUtils::isInAppbymeAdminGroup()) {
            $this->redirect(Yii::app()->createAbsoluteUrl('admin/index'));
        }

        if (!empty($_POST)) {
            
            $_POST['username'] = WebUtils::t($_POST['username']);
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $result = UserUtils::login($username, $password);

            $errorMsg = '';
            if ($result['errcode']) {
                $errorMsg = WebUtils::u($result['message']);
            } else {
                if (UserUtils::isInAppbymeAdminGroup()) {
                    $this->redirect(Yii::app()->createAbsoluteUrl('admin/index'));
                } else {
                    $errorMsg = '用户不是管理员，也不在允许登录的范围内！';
                }
            }
        }

        $this->renderPartial('login', array('errorMsg' => $errorMsg, 'username' => $username));
    }

    public function actionLogout() {
        UserUtils::logout();
        $this->redirect(Yii::app()->createAbsoluteUrl('admin/index'));
    }

    public function actionGetMobcentPlugin() {
        $html = file_get_contents('http://www.appbyme.com/mobcentPlugin/');
        $s1 = strpos($html, '<section id="block-download">');
        $len = strlen($html);
        $html = substr($html, $s1, $len - $s1);
        $s2 = strpos($html, '</section>');
        $html = substr($html, 0, $s2);
        $html = str_replace('src="', 'src="http://www.appbyme.com', $html);
        $html = str_replace('href="', 'href="http://www.appbyme.com', $html);
        $html = str_replace('http://www.appbyme.comjavascript:detail(', 'http://www.appbyme.com/mobcentPlugin/plugin/pluginDetail.do?hideDownload=true&hideTest=true&pluginId=', $html);
        $html = str_replace(');">插件详情', '">插件详情', $html);
        $html = str_replace('<section id="block-download">', '', $html);
        $html = str_replace('<input type="text" id="keyword" style="width:400px" value="">&emsp;<a class="download-btn ui-button-info"
							href="http://www.appbyme.comjavascript:pluginList();">搜索</a>
', '', $html);
        echo $html;
    }

}
