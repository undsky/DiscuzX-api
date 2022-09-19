<?php

/**
 * 应用 >> 安米手机客户端 >> 开放管理 
 * 
 *  @author   NaiXiaoXin<nxx@yytest.cn>
 *  @datetime 2015-9-11 18:25:51
 *  @copyright  2012-2015, Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

require_once dirname(__FILE__) . '/appbyme.class.php';
Appbyme::init();

loadcache('plugin');
global $_G;
$setting = $_G['cache']['plugin'][Appbyme::PLUGIN_ID];
$baseUrl = rawurldecode(cpurl());
$get = DB::fetch_first("SELECT * FROM %t WHERE ckey=%s ", array('appbyme_config', 'app_open'));
$info = unserialize($get['cvalue']);
if (!submitcheck('open_submit')) {
    $formUrl = ltrim($baseUrl, 'action=');
    showtagheader('div', 'open_module', true);
    showformheader($formUrl);
    showtips(Appbyme::lang('mobcent_open_tips'));
    showtableheader(Appbyme::lang('mobcent_open_name'));
    showsetting(Appbyme::lang('mobcent_open_aboutus_open'), 'aboutus', $info['aboutus'], 'text', '', '', Appbyme::lang('mobcent_open_aboutus_tips'));
    showsetting(Appbyme::lang('mobcent_open_foundpw_open'), 'foundpw', $info['foundpw'], 'text', '', '', Appbyme::lang('mobcent_open_foundpw_tips'));
    showsetting(Appbyme::lang('mobcent_open_userinfo_open'), 'userinfo', $info['userinfo'], 'text', '', '', Appbyme::lang('mobcent_open_userinfo_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_0_name'), 'thread[0]', $info['thread']['0'], 'text', '', '',Appbyme::lang('mobcent_open_thread_0_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_1_name'), 'thread[1]', $info['thread']['1'], 'text', '', '', Appbyme::lang('mobcent_open_thread_1_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_2_name'), 'thread[2]', $info['thread']['2'], 'text', '', '', Appbyme::lang('mobcent_open_thread_2_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_3_name'), 'thread[3]', $info['thread']['3'], 'text', '', '', Appbyme::lang('mobcent_open_thread_3_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_4_name'), 'thread[4]', $info['thread']['4'], 'text', '', '', Appbyme::lang('mobcent_open_thread_4_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_5_name'), 'thread[5]', $info['thread']['5'], 'text', '', '', Appbyme::lang('mobcent_open_thread_5_tips'));
    showsetting(Appbyme::lang('mobcent_open_thread_127_name'), 'thread[127]', $info['thread']['127'], 'text', '', '', Appbyme::lang('mobcent_open_thread_127_tips'));
    showsubmit('open_submit', 'submit');
    showtablefooter();
    showformfooter();
    showtagfooter('div');
} else {
    $data = array();
    $data['aboutus'] = addslashes($_GET['aboutus']);
    $data['foundpw'] = addslashes($_GET['foundpw']);
    $data['userinfo'] = addslashes($_GET['userinfo']);
    $data['thread']['0'] = addslashes($_GET['thread']['0']);
    $data['thread']['1'] = addslashes($_GET['thread']['1']);
    $data['thread']['2'] = addslashes($_GET['thread']['2']);
    $data['thread']['3'] = addslashes($_GET['thread']['3']);
    $data['thread']['4'] = addslashes($_GET['thread']['4']);
    $data['thread']['5'] = addslashes($_GET['thread']['5']);
    $data['thread']['127'] = addslashes($_GET['thread']['127']);
    $str = serialize($data);
    Setting($str);
    cpmsg($lang['setting_update_succeed'], $baseUrl, 'succeed');
}

function Setting($str) {
    $key = 'app_open';
    $data = array(
        'ckey' => $key,
        'cvalue' => $str,
    );
    $tempData = DB::fetch_first("SELECT * FROM %t WHERE ckey=%s ", array('appbyme_config', 'app_open'));
    if (empty($tempData)) {
        DB::insert('appbyme_config', $data);
    } else {
        DB::update('appbyme_config', $data, array('ckey' => $key));
    }
    return true;
}

?>
