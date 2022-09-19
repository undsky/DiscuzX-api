<?php
/**
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
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
$formUrl = ltrim($baseUrl, 'action=');

//取出字段列表
$list = array();
$get = DB::fetch_first("SELECT * FROM %t WHERE ckey=%s ", array('appbyme_config', 'app_reg'));
$info = unserialize($get['cvalue']);
foreach (C::t('common_member_profile_setting')->range() as $fieldid => $value) {
    $list[$fieldid] = array(
        'title' => $value['title'],
        'available' => $value['available'],
        'showinregister' => $info[$fieldid]['show'],
    );
}

// 隐藏字段：birthyear, birthmonth, resideprovince, birthprovince
unset($list['birthyear']);
unset($list['birthmonth']);
unset($list['birthprovince']);
unset($list['birthdist']);
unset($list['birthcommunity']);
unset($list['resideprovince']);
unset($list['residedist']);
unset($list['residecommunity']);
//unset($list['idcardtype']);

//debug($list);
if (!submitcheck('regsubmit')) {
    showtips(Appbyme::lang('mobcent_reg_tips'));
    showformheader($formUrl);
    showtableheader('', '', 'id="profiletable_header"');
    $tdstyle = array('class="td22"', 'class="td28" width="100"', 'class="td28" width="100"', 'class="td28" width="100"', 'class="td28" width="100"', 'class="td28"', 'class="td28"');
    showsubtitle(array('members_profile_edit_name', 'members_profile_edit_available', 'members_profile_edit_reg_view',  ''), 'header tbm', $tdstyle);
    showtablefooter();
    echo '<script type="text/javascript">floatbottom(\'profiletable_header\');</script>';
    showtableheader('members_profile', 'nobottom', 'id="porfiletable"');
//名称 启用 注册页显示 是否必填
    showsubtitle(array('members_profile_edit_name', 'members_profile_edit_available', 'members_profile_edit_reg_view', ''), 'header', $tdstyle);
    foreach ($list as $fieldid => $value) {
        $value['available'] = '<input type="checkbox" class="checkbox" name="available[' . $fieldid . ']" ' . ($value['available'] ? 'checked="checked" ' : '') . 'value="1" disabled="true">';
        $value['showinregister'] = '<input type="checkbox" class="checkbox" name="showinregister[' . $fieldid . ']" ' . ($value['showinregister'] ? 'checked="checked" ' : '') . 'value="1">';
        $value['edit'] = '<a href="' . ADMINSCRIPT . '?action=members&operation=profile&fieldid=' . $fieldid . '" title="" class="act">' . $lang[edit] . '</a>';
        showtablerow('', array(), $value);
    }
    showsubmit('regsubmit');
    showtablefooter();
    showformfooter();
} else {
    //   debug($_GET);
    $count = count($_GET['showinregister']);
    if ($count > '5') {
        cpmsg(Appbyme::lang('mobcent_reg_only'), $baseUrl, 'error');
    }
    foreach ($_GET['showinregister'] as $key => $value) {
        $set[$key]['show'] = '1';
        $set[$key]['must'] = $_GET['must'][$key];
    }
    Appbyme::setAppbymeConfig('app_reg', serialize($set));
    cpmsg(Appbyme::lang('mobcent_seo_edit_succeed'), $baseUrl, 'succeed');
}

