<?php
/**
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */


define('MOBCENT_ROOT', dirname(__FILE__));

exit();
global $md5data;
checkfiles('/','\.php|\.js');
$configfile = MOBCENT_ROOT . '/../mobcent.md5';
$str = serialize($md5data);
$myfile = fopen($configfile, "w");
fwrite($myfile, $str);
fclose($myfile);
echo 'Success';

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
    global $md5data;

    $dir = @opendir(MOBCENT_ROOT . $currentdir);
    $exts = '/(' . $ext . ')$/i';
    while ($entry = @readdir($dir)) {
        $file = $currentdir . $entry;
        $relalName = MOBCENT_ROOT . $file;
        if ($entry != '.' && $entry != '..' && (($ext && preg_match($exts, $entry) || !$ext) || $sub && is_dir($relalName))) {
            if (($currentdir == '/app/modules/' && !in_array($entry, array('api', 'admin'))) || $currentdir == '/app/web/ueditor/'||$currentdir=='/discuz_plugin/') {
                continue;
            }
            $newexts = '/(\.DS_Store|\.svn|\.git|\my_(.*)|\mobcent\/mobcent.md5|\FileOut.php)$/i';
            if(preg_match ($newexts, $file)){
                continue;
            }
            if ($sub && is_dir($relalName)) {
                checkfiles($file . '/', $ext, $sub, $skip);
            } else {
                if($ext && !preg_match($exts, $entry)){
                    continue;
                }
                if (is_dir($relalName)) {
                    $md5data[$file] = md5($relalName);
                } else {
                    echo $relalName.'<br />';
                    $md5data[$file] = md5_file($relalName);
                }
            }
        }
    }
}
