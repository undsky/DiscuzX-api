<?php
/**
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */

define('MOBCENT_ROOT', dirname(__FILE__));
$fileName = 'http://pub-file.app.xiaoyun.com/md5check/mobcent.md5';
$fileTemp = file_get_contents($fileName);
$return = array(
    'rs' => 1,
    'errcode' => 0,
    'head' => array(
        'errInfo' => '成功'
    ),
    'body' => array()
);
$fileMd5 = unserialize($fileTemp);
if ($fileMd5 === false) {
    $return['rs'] = 0;
    $return['head']['errInfo'] = '获取远端数据失败';
    exit(json_encode($return));
}
$delCount = $changeCount = 0;
$del = $change = array();
foreach ($fileMd5 as $file => $md5) {
    $realFile = MOBCENT_ROOT . $file;
    if (file_exists($realFile)) {
        $newMd5 = md5_file($realFile);
        if ($newMd5 != $md5) {
            $changeCount++;
            $change['mobcent'.$file] = $newMd5;
        }
    } else {
        $delCount++;
        $del['mobcent'.$file] = $md5;
    }
}
$return['body']['change']['changeCount'] = $changeCount;
$return['body']['change']['change'] = $change;
$return['body']['del']['delCount'] = $delCount;
$return['body']['del']['del'] = $del;
exit(json_encode($return));
