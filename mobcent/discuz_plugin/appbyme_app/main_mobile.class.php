<?php

/**
 * 页面嵌入-手机版
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @copyright 2012-2014 Appbyme
 * @license http://opensource.org/licenses/LGPL-3.0
 */

if (!defined('IN_DISCUZ'))
{
    exit('Access Denied');
}

require_once dirname(__FILE__) . '/appbyme.class.php';

class mobileplugin_appbyme_app
{

    public function global_header_mobile()
    {
        $jump = new appbymeJump();
        $jump->toDo();
    }

    public function global_footer()
    {
        if (Appbyme::isInstalled())
        {
            Appbyme::init();

            Appbyme::runCron();
        }

        return '';
    }
}

class mobileplugin_appbyme_app_forum extends mobileplugin_appbyme_app
{

    public function forumdisplay_thread_mobile_output()
    {
        $res = array();
        global $_G;
        foreach ($_G['forum_threadlist'] as $thread)
        {
            $res[] = Appbyme::getPostSign('mobile_sign_thread_mobile', 'forumdisplay_thread', $thread['status']);
        }

        return $res;
    }

    public function viewthread_posttop_mobile_output()
    {
        return $this->_getPostSignOutput('posttop');
    }

    public function viewthread_postbottom_mobile_output()
    {
        return $this->_getPostSignOutput('postbottom');
    }

    private function _getPostSignOutput($hookPosition)
    {
        $res = array();
        if ($hookPosition == Appbyme::$config['mobile_sign_position_mobile'])
        {
            global $postlist;
            foreach ($postlist as $post)
            {
                $res[] = Appbyme::getPostSign('mobile_sign_post_mobile', 'viewthread_post', $post['status']);
            }
        }

        return $res;
    }
}

class appbymeJump
{
    private $config;
    private $module;
    private $script;

    public function __construct()
    {
        $get  = DB::fetch_first("SELECT * FROM %t WHERE ckey=%s ", array('appbyme_config', 'app_jump'));
        $info = unserialize($get['cvalue']);
        if (empty($info))
        {
            $info = Appbyme::getJumpDefaultSetting();
        }
        $this->config = $info;
        $this->module = CURMODULE;
        $this->script = CURSCRIPT;
    }

    public function toDo()
    {
        /**
         * 微站地址不存在
         */
        if (empty($this->config['wzurl']))
        {
            return true;
        }
        /**
         * 客户端不跳转
         */
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Appbyme') !== false && $this->config['inapp'] == 0)
        {
            return false;
        }

        switch ($this->script)
        {
            case 'forum':
                $this->forum();
                break;
            case 'portal':
                $this->portal();
                break;
            case 'plugin':
                $this->plugin();
                break;
            case 'home':
                $this->home();
                break;
            case 'member':
                $this->member();
                break;
            case 'connect':
                $this->connect();
                break;           
            default:
                $this->all();
                break;
        }

        return true;

    }


    private function forum()
    {
        switch ($this->module)
        {
            case 'forumdisplay':
                if ($this->config['forum'] == 1)
                {
                    $this->jumpWeizhan('/forum/' . $_GET['fid']);
                }
                break;
            case 'viewthread':
                if ($this->config['thread'][0] == -1)
                {
                    break;
                }
                global $_G;
                if (in_array($_G['thread']['special'], $this->config['thread']))
                {
                    $this->jumpWeizhan('/post/' . $_GET['tid']);
                }
                break;
            //以下是不需要跳转的
            case 'ajax':
            case 'announcement':
            case 'attachment':
            case 'image';
            case 'misc';
            case 'modcp';
            case 'relatekw';
            case 'topicadmin';
            case 'post';
                break;
            default:
                $this->all();
                break;
        }
    }

    private function portal()
    {
        switch ($this->module)
        {
            case 'view':
                if ($this->config['article'] == 1)
                {
                    $this->jumpWeizhan('/article/' . $_GET['aid']);
                }
                break;
            default:
                $this->all();
                break;
        }
    }

    private function plugin()
    {
        if ($this->config['plugin'] == 1)
        {
            $this->jumpWeizhan('');
        }
    }

    private function home()
    {
        switch ($this->module)
        {
            case "space":
                if ($this->config['user'] == 1)
                {
                    $this->jumpWeizhan('/user/' . $_GET['uid']);
                }
                break;
            default;
                $this->all();
                break;
        }
    }

    private function member()
    {

        switch ($this->module){
            case "register":
            case "logging":
                if($this->config["member"]!=1){
                    break;
                }
                if($_GET["action"]=="logout"){
                    $this->jumpWeizhan("/logout");
                }
                $this->jumpWeizhan("/login",array(
                    'f'=>dreferer()
                ));
                break;
            default:
                break;
        }


    }
    private function all()
    {
        if ($this->config['all'] == 1)
        {
            $this->jumpWeizhan('');
        }
    }

    private function connect(){
        if(!defined('IN_APPBYME') && $_GET["appbyme"] == 1){
            header("HTTP/1.1 302 Moved Permanently");
            header("location: mobcent/app/web/index.php?r=user/qqlogin&" .$_SERVER["QUERY_STRING"]);
            exit();
        }
    }

    private function jumpWeizhan($url = '',$params = array())
    {
        $url .="?";
        $params["from"] = "bbsh5";
        foreach ($params as $key=>$value)
        {
            $url .= $key . "=".$value;
        }
        $url  = substr($url, 0, -1) ;
        header("HTTP/1.1 302 Moved Permanently");
        header("location: " . $this->config['wzurl'] . "/m" . $url );
        exit();
    }

}