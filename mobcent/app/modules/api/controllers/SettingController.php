<?php

/**
 * Created by PhpStorm.
 * User: byd
 * Date: 16/6/12
 * Time: 上午10:53
 */
class SettingController extends ApiController
{
    public function init()
    {
        $this->out_arr = $this->out_arr();
        $this->db = DbUtils::createDbUtils(true);
    }

    /**
     * 获取基础设置数据
     */
    public function actionGetSetting()
    {
        $info = AppbymeConfig::getSetting();
        if (empty($info['search'])) {
            $info['search'][] = 'portal';
            $info['search'][] = 'topic';
            $info['search'][] = 'user';
        }
        $this->setData($info);
    }

    /**
     * 保存基础设置数据
     */
    public function actionSaveSetting()
    {
        if (strlen($this->data['reg']) == 0 || strlen($this->data['email']) == 0 || strlen($this->data['reply']) == 0 || strlen($this->data['qqsdk']) == 0) {
            $this->error('参数不能为空!');
        }
        $info['reg'] = $this->data['reg'];
        $info['email'] = $this->data['email'];
        $info['reply'] = $this->data['reply'];
        if (empty($this->data['search'])) {
            $this->data['search'][] = 'portal';
            $this->data['search'][] = 'topic';
            $this->data['search'][] = 'user';
        }
        $info['search'] = $this->data['search'];
        $info['qqsdk'] = $this->data['qqsdk'];
        AppbymeConfig::saveSetting($info);
    }

}