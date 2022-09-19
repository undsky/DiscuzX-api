<?php

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class PluginController extends AdminController {

    public function actionIndex() {
        $dir = MOBCENT_ROOT . '/app/modules/';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {

                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != ".." && $file != "admin") {
                        $darray[] = array('plugin_id' => $file, 'path' => $dir . $file);
                    }
                }
                closedir($dh);
            }
        }


        foreach ($darray as &$row) {
            $info = file_get_contents($row['path'] . '/static/appbyme_plugin.json');
            $row['info'] = json_decode($info, true);
            $row['plugin'] = AppbymePluginModel::getPlugin($row['plugin_id']);
        }
        $this->renderPartial('index', array('list' => $darray));
    }

    //安装
    public function actionInstall($plugin_id) {
        $result = AppbymePluginModel::getPlugin($plugin_id);
        if (!$result) {
            $dir = MOBCENT_ROOT . '/app/modules/' . $plugin_id;
            $file = @include($dir . '/install.inc.php');
            if (!$file) {
                $this->success('安装文件不存在!');
            } else {
                $this->saveconfig($plugin_id);
                $this->runquery($sql);
                $info = file_get_contents($dir . '/static/appbyme_plugin.json');
                $up = json_decode($info, true);
                $data = array(
                    'plugin_name' => $this->clear($up['name']),
                    'menu' => $up['menu'],
                    'version' => $up['version'],
                    'plugin_id' => $plugin_id
                );
                AppbymePluginModel::insertPlugin($data);
                $this->success('安装成功,请刷新浏览器!', Yii::app()->createAbsoluteUrl('admin/plugin/index'));
            }
        } else {
            $this->success('您已经安装过此插件!');
        }
    }

    public function actionUpdate($plugin_id) {
        $result = AppbymePluginModel::getPlugin($plugin_id);
        if ($result) {
            $dir = MOBCENT_ROOT . '/app/modules/' . $plugin_id;
            $info = file_get_contents($dir . '/static/appbyme_plugin.json');
            $up = json_decode($info, true);
            if ($up['version'] > $result['version']) {
                $file = @include($dir . '/update.inc.php');
                if (!$file) {
                    $this->success('升级文件不存在!');
                } else {
                    $this->runquery($sql);
                    $this->updatetable($sql);
                    $data = array(
                        'plugin_name' => $this->clear($up['name']),
                        'menu' => $up['menu'],
                        'version' => $up['version'],
                        'plugin_id' => $plugin_id
                    );
                    AppbymePluginModel::updatePlugin($result['id'], $data);
                    $this->success('升级成功!', Yii::app()->createAbsoluteUrl('admin/plugin/index'));
                }
            } else {
                $this->success('没有上传新版本无法升级!');
            }
        } else {
            $this->success('插件不存在无法升级!');
        }
    }

    public function saveconfig($ms = 'vote') {
        $configfile = MOBCENT_APP_ROOT . '/config/my_plugin.php';
        if (!file_exists($configfile)) {
            $str = "<?php
                   return array('" . $ms . "');
                    ?>";
            $myfile = fopen($configfile, "w") or $this->success("配置文件没有写入权限,请给mobcent/app/config/my_plugin.php写入权限");
            fwrite($myfile, $str);
            fclose($myfile);
        } else {
            $config = file_get_contents($configfile);
            preg_match("/return array\((.*)\)/", $config, $m);
            $m1 = $m[0];
            $modules = $m[1];

            $marray = str_replace("'", '', explode(',', $modules));
            if (!in_array($ms, $marray)) {
                $str = "return array(";
                foreach ($marray as $v) {
                    $str .="'" . $v . "',";
                }
                $str .= "'" . $ms . "')";
                $newconfig = str_replace($m1, $str, $config);
                $myfile = fopen($configfile, "w") or $this->success("配置文件没有写入权限,请给mobcent/app/config/my_plugin.php写入权限");
                fwrite($myfile, $newconfig);
                fclose($myfile);
            }
        }
    }

    private function clear($str) {
        global $_G;
        if ($_G['charset'] != 'utf-8') {
            return iconv("UTF-8", "GBK//IGNORE", $str);
        } else {
            return $str;
        }
    }

    public function runquery($sql) {
        global $_G;
        $tablepre = $_G['config']['db'][1]['tablepre'];
        $dbcharset = $_G['config']['db'][1]['dbcharset'];

        $sql = str_replace(array(' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'), $sql);
        $sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' `{tablepre}'), array(' ' . $tablepre, ' `' . $tablepre), $sql));

        $ret = array();
        $num = 0;
        foreach (explode(";\n", trim($sql)) as $query) {
            $queries = explode("\n", trim($query));
            foreach ($queries as $query) {
                $ret[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
            }
            $num++;
        }
        unset($sql);

        foreach ($ret as $query) {
            $query = trim($query);
            if ($query) {

                if (substr($query, 0, 12) == 'CREATE TABLE') {
                    $name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
                    DB::query($this->createtable($query, $dbcharset));
                } else {
                    DB::query($query);
                }
            }
        }
    }

    public function createtable($sql, $dbcharset) {
        $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
        $type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
        return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
                (DB::$db->version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
    }

    public function updatetable($sql) {
        global $_G;

        $config = array(
            'dbcharset' => $_G['config']['db']['1']['dbcharset'],
            'charset' => $_G['config']['output']['charset'],
            'tablepre' => $_G['config']['db']['1']['tablepre']
        );

        preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $sql, $matches);
        $newtables = empty($matches[1]) ? array() : $matches[1];
        $newsqls = empty($matches[0]) ? array() : $matches[0];
        if (empty($newtables) || empty($newsqls)) {
            return array(1);
        }

        foreach ($newtables as $i => $newtable) {
            $newcols = $this->updatetable_getcolumn($newsqls[$i]);

            if (!$query = DB::query("SHOW CREATE TABLE " . DB::table($newtable), 'SILENT')) {
                preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $newsqls[$i], $maths);

                $maths[3] = strtoupper($maths[3]);
                if ($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
                    $type = DB::result_first("SELECT VERSION()") > '4.1' ? " ENGINE=MEMORY" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]" ) : " TYPE=HEAP";
                } else {
                    $type = DB::result_first("SELECT VERSION()") > '4.1' ? " ENGINE=MYISAM" . (empty($config['dbcharset']) ? '' : " DEFAULT CHARSET=$config[dbcharset]" ) : " TYPE=MYISAM";
                }
                $usql = $maths[1] . $type;

                $usql = str_replace("CREATE TABLE IF NOT EXISTS pre_", 'CREATE TABLE IF NOT EXISTS ' . $config['tablepre'], $usql);
                $usql = str_replace("CREATE TABLE pre_", 'CREATE TABLE ' . $config['tablepre'], $usql);

                if (!DB::query($usql, 'SILENT')) {
                    return array(-1, $newtable);
                }
            } else {
                $value = DB::fetch($query);
                $oldcols = $this->updatetable_getcolumn($value['Create Table']);

                $updates = array();
                $allfileds = array_keys($newcols);
                foreach ($newcols as $key => $value) {
                    if ($key == 'PRIMARY') {
                        if ($value != $oldcols[$key]) {
                            if (!empty($oldcols[$key])) {
                                $usql = "RENAME TABLE " . DB::table($newtable) . " TO " . DB::table($newtable . '_bak');
                                if (!DB::query($usql, 'SILENT')) {
                                    return array(-1, $newtable);
                                }
                            }
                            $updates[] = "ADD PRIMARY KEY $value";
                        }
                    } elseif ($key == 'KEY') {
                        foreach ($value as $subkey => $subvalue) {
                            if (!empty($oldcols['KEY'][$subkey])) {
                                if ($subvalue != $oldcols['KEY'][$subkey]) {
                                    $updates[] = "DROP INDEX `$subkey`";
                                    $updates[] = "ADD INDEX `$subkey` $subvalue";
                                }
                            } else {
                                $updates[] = "ADD INDEX `$subkey` $subvalue";
                            }
                        }
                    } elseif ($key == 'UNIQUE') {
                        foreach ($value as $subkey => $subvalue) {
                            if (!empty($oldcols['UNIQUE'][$subkey])) {
                                if ($subvalue != $oldcols['UNIQUE'][$subkey]) {
                                    $updates[] = "DROP INDEX `$subkey`";
                                    $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                                }
                            } else {
                                $usql = "ALTER TABLE  " . DB::table($newtable) . " DROP INDEX `$subkey`";
                                DB::query($usql, 'SILENT');
                                $updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
                            }
                        }
                    } else {
                        if (!empty($oldcols[$key])) {
                            if (strtolower($value) != strtolower($oldcols[$key])) {
                                $updates[] = "CHANGE `$key` `$key` $value";
                            }
                        } else {
                            $i = array_search($key, $allfileds);
                            $fieldposition = $i > 0 ? 'AFTER `' . $allfileds[$i - 1] . '`' : 'FIRST';
                            $updates[] = "ADD `$key` $value $fieldposition";
                        }
                    }
                }

                if (!empty($updates)) {
                    $usql = "ALTER TABLE " . DB::table($newtable) . " " . implode(', ', $updates);
                    if (!DB::query($usql, 'SILENT')) {
                        return array(-1, $newtable);
                    }
                }
            }
        }
        return array(1);
    }

    public function updatetable_getcolumn($creatsql) {

        $creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
        preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

        $cols = explode("\n", $matchs[1]);
        $newcols = array();
        foreach ($cols as $value) {
            $value = trim($value);
            if (empty($value))
                continue;
            $value = $this->updatetable_remakesql($value);
            if (substr($value, -1) == ',')
                $value = substr($value, 0, -1);

            $vs = explode(' ', $value);
            $cname = $vs[0];

            if ($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

                $name_length = strlen($cname);
                if ($cname == 'UNIQUE')
                    $name_length = $name_length + 4;

                $subvalue = trim(substr($value, $name_length));
                $subvs = explode(' ', $subvalue);
                $subcname = $subvs[0];
                $newcols[$cname][$subcname] = trim(substr($value, ($name_length + 2 + strlen($subcname))));
            } elseif ($cname == 'PRIMARY') {

                $newcols[$cname] = trim(substr($value, 11));
            } else {

                $newcols[$cname] = trim(substr($value, strlen($cname)));
            }
        }
        return $newcols;
    }

    public function updatetable_remakesql($value) {
        $value = trim(preg_replace("/\s+/", ' ', $value));
        $value = str_replace(array('`', ', ', ' ,', '( ', ' )', 'mediumtext'), array('', ',', ',', '(', ')', 'text'), $value);
        return $value;
    }

    /**
     * 操作提示
     * 
     * @param mixed $msg 消息.
     * @param mixed $url 跳转地址.
     */
    public function success($msg, $url = '') {
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>提示</title>
            </head>

            <body>
            <div style="width:400px;border:1px #CCCCCC solid;height:100px; margin:0 auto; margin-top:100px; line-height:20px; text-align:center;font-size:14px;padding-top:50px;border-radius:5px">&nbsp;&nbsp;( ^_^ )
            ' . $msg;
        if ($url) {
            $html .='<meta http-equiv="refresh" content="2;URL=' . $url . '"><a  href="' . $url . '" style="text-decoration:none;font-size:14px;color:#00C">[继续]</a>';
        } else {
            $html .='<a  href="javascript:history.back()" style="text-decoration:none;font-size:14px;color:#00C">[返回]</a>';
        }
        $html .='</div></body></html>';
        echo $html;
        exit;
    }

}

?>