<?php
$yii = dirname(__FILE__) . '/../../framework/mobcent/yii.php';
$mobcent = dirname(__FILE__) . '/../components/Mobcent.php';
$discuz = dirname(__FILE__) . '/../components/discuz/discuz.php';


require_once($yii);

require_once($mobcent);

require_once($discuz);


$dir = dirname(__FILE__) . '/../config';
$MyMainFile = $dir . '/my_main.php';
if (!file_exists($MyMainFile)) {
    $config = $dir . '/main.php';
} else {
    $config = $dir . '/my_main.php';
}
$app = Yii::createWebApplication($config);
defined('YUN_APPID') &&  $app->setId(YUN_APPID);
$app->run();