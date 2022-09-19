<?php

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AuthController extends AdminController {

    public function actionIndex() {
        $allowUsers = ArrayUtils::explode(WebUtils::getDzPluginAppbymeAppConfig('appbyme_allow_admin_users'));
        $admin = $allowUsers[0];
        $pluginarray = array();
        $plugin = AppbymePluginModel::allPlugin();
        foreach ($plugin as $v) {
            $p['name'] = WebUtils::u($v['plugin_name']);
            $p['id'] = $v['plugin_id'];
            $pluginarray[] = $p;
        }
        $sys = array(
            array('name' => 'DIY自定义', 'id' => 'uidiy'),
            array('name' => '基础设置', 'id' => 'setting'),
            array('name' => '微生活', 'id' => 'wshdiy'),
            array('name' => '邀请注册', 'id' => 'reward'),
            array('name' => 'App推送', 'id' => 'push'),
            array('name' => '分享管理', 'id' => 'share'),
            array('name' => 'WebApp管理', 'id' => 'webapp'),
            array('name' => '话题管理', 'id' => 'topic'),
            array('name' => '用户管理', 'id' => 'user'),
        );
        foreach ($allowUsers as $v) {
            $a['name'] = $v;
            $a['allow'] = $this->getallow($v);
            $allowUser[] = $a;
        }
        unset($allowUser[0]);
        $auth = array_merge($sys, $pluginarray);
        $this->renderPartial('index', array('admin' => $admin, 'allowUsers' => $allowUser, 'auth' => $auth));
    }

    public function actionUpdate() {
        if ($_POST) {
            DbUtils::getDzDbUtils(true)->delete('appbyme_auth', 'id>0');
            $allowUsers = ArrayUtils::explode(WebUtils::getDzPluginAppbymeAppConfig('appbyme_allow_admin_users'));
            unset($allowUsers[0]);
            foreach ($allowUsers as $key => $v) {
                // echo $v . '---' . implode(',', $_POST['auth' . $key]).'---'.$key.'<br>';
                $this->saveauth($v, $_POST['auth' . $key]);
            }
            $this->success('保存成功', Yii::app()->createAbsoluteUrl('admin/auth/index'));
        } else {
            echo '数据未提交';
        }
    }

    private function saveauth($v, $allow) {
        $data = array('username' => $v, 'allow' => implode(',', $allow));
        return DbUtils::getDzDbUtils(true)->insert('appbyme_auth', $data);
    }

    private function getallow($v) {
        $sql = '
            SELECT *
            FROM %t
            WHERE username =%s
            ';
        $result = DbUtils::getDzDbUtils(true)->queryRow($sql, array('appbyme_auth', $v));
        if ($result) {
            return $result['allow'];
        } else {
            return '';
        }
    }

}

?>