<?php
/**
 * @property RepairService $model
 * User: 蒙奇·D·jie
 * Date: 16/9/26
 * Time: 下午6:02
 * Email: mqdjie@gmail.com
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class DiyRepairController extends ApiController
{
    private  $model;
    public function init()
    {
        parent::init();
        $this->model = new RepairService();
    }

    public function actionIndex()
    {
        $res = $this->model->Repair();
        if(!$res) {
            $this->error(WebUtils::u($this->model->getError()));
        }
    }
}