<?php 

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class ActivityController extends AdminController{

    public function actionIndex() {
        $this->renderPartial('index');
    }
}
?>