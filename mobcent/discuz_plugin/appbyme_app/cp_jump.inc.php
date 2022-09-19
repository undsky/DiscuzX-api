<?php
/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 XiaoYun.com
 */

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP'))
{
    exit('Access Denied');
}

require_once dirname(__FILE__) . '/appbyme.class.php';
Appbyme::init();

loadcache('plugin');
global $_G;
$setting = $_G['cache']['plugin'][Appbyme::PLUGIN_ID];
$baseUrl = rawurldecode(cpurl());
$get     = DB::fetch_first("SELECT * FROM %t WHERE ckey=%s ", array('appbyme_config', 'app_jump'));
$info    = unserialize($get['cvalue']);
if (empty($info))
{
    $info = Appbyme::getJumpDefaultSetting();
}
if (!submitcheck('editsubmit'))
{
    $formUrl = ltrim($baseUrl, 'action=');
    showtagheader('div', 'jump_module', true);
    showformheader($formUrl);
    showtips(Appbyme::lang('mobcent_jump_tips'));
    showtableheader(Appbyme::lang('mobcent_jump_name'));
    showsetting(Appbyme::lang('mobcent_jump_wzurl_name'), 'wzurl', $info['wzurl'], 'text', '', '', Appbyme::lang('mobcent_jump_wzurl_tips'));
    showsetting(Appbyme::lang('mobcent_jump_inapp_name'), 'inapp', $info['inapp'], 'radio', '', '', Appbyme::lang('mobcent_jump_inapp_tips'));
    showsetting(Appbyme::lang('mobcent_jump_article_name'), 'article', $info['article'], 'radio', '', '', Appbyme::lang('mobcent_jump_article_tips'));
    showsetting(Appbyme::lang('mobcent_jump_forum_name'), 'forum', $info['forum'], 'radio', '', '', Appbyme::lang('mobcent_jump_forum_tips'));
    showsetting(Appbyme::lang('mobcent_jump_thread_name'), getThread(), $info['thread'], 'mselect', '', '', Appbyme::lang('mobcent_jump_thread_tips'));
    showsetting(Appbyme::lang('mobcent_jump_user_name'), 'user', $info['user'], 'radio', '', '', Appbyme::lang('mobcent_jump_user_tips'));
    showsetting(Appbyme::lang('mobcent_jump_plugin_name'), 'plugin', $info['plugin'], 'radio', '', '', Appbyme::lang('mobcent_jump_plugin_tips'));
    showsetting(Appbyme::lang('mobcent_jump_member_name'), 'member', $info['member'], 'radio', '', '', Appbyme::lang('mobcent_jump_member_tips'));

    showsetting(Appbyme::lang('mobcent_jump_all_name'), 'all', $info['all'], 'radio', '', '', Appbyme::lang('mobcent_jump_all_tips'));
    showsetting(Appbyme::lang('mobcent_jump_wzjump_name'), 'wzjump', $info['wzjump'], 'radio', '', '', Appbyme::lang('mobcent_jump_wzjump_tips'));
    showsubmit('editsubmit', 'submit');
    showtablefooter();
    showformfooter();
    showtagfooter('div');
}
else
{
    $data                  = array(
        'wzurl'=>daddslashes($_GET['wzurl']),
        'inapp'=>intval($_GET['inapp']),
        'article'=>intval($_GET['article']),
        'thread'=>$_GET['thread'],
        'user'=>intval($_GET['user']),
        'plugin'=>intval($_GET['plugin']),
        'all'=>intval($_GET['all']),
        'wzjump'=>intval($_GET['wzjump']),
        'forum'=>intval($_GET['forum']),
        "member"=>intval($_GET['member'])
    );
    $str                   = serialize($data);

    Setting($str);
    cpmsg($lang['setting_update_succeed'], $baseUrl, 'succeed');
}


function getThread()
{
    return array(
        'thread[]',
        array(
            array(-1, Appbyme::lang('mobcent_jump_thread_-1')),
            array(0, Appbyme::lang('mobcent_jump_thread_0')),
            array(1, Appbyme::lang('mobcent_jump_thread_1')),
            array(2, Appbyme::lang('mobcent_jump_thread_2')),
            array(3, Appbyme::lang('mobcent_jump_thread_3')),
            array(4, Appbyme::lang('mobcent_jump_thread_4')),
            array(127, Appbyme::lang('mobcent_jump_thread_127')
            )
        ),
    );
}

function Setting($str)
{
    $key      = 'app_jump';
    $data     = array(
        'ckey'   => $key,
        'cvalue' => $str,
    );
    $tempData = DB::fetch_first("SELECT * FROM %t WHERE ckey=%s ", array('appbyme_config', 'app_jump'));
    if (empty($tempData))
    {
        DB::insert('appbyme_config', $data);
    }
    else
    {
        DB::update('appbyme_config', $data, array('ckey' => $key));
    }

    return true;
}

?>
