<?php
/**
 * 判断site是否正确.与SecretKey验证
 * User: 肖聪杰
 * Date: 2016/4/25
 * Time: 10:20
 */
class SiteController extends ApiController{
    public function init()
    {
        $this->out_arr = $this->out_arr();
        $this->db = DbUtils::createDbUtils(true);
    }
    public function actionSiteInfo() {
        global $_G;
        $setting = $_G['setting'];
        $tmpPassword = trim($this->data['install_password']);
        $tmpsecretKey = trim($this->data['secretKey']);
        $tmpforumKey = trim($this->data['forumKey']);
        $password = WebUtils::subString(WebUtils::getDzPluginAppbymeAppConfig('install_password'), 0, 10);
        if (!empty($password) && $password == $tmpPassword) {
            //如果有传forumKey和secretKey进行操作
            if($tmpforumKey !='' && $tmpsecretKey !=''){
                $arr = AppbymeConfig::getForumKeySecretKey('ForumKey_SecretKey');
                $arr[$tmpforumKey] = $tmpsecretKey;
                $searr = serialize($arr);
                $inarr = array('ckey' => 'ForumKey_SecretKey','cvalue' => $searr);
                AppbymeConfig::saveCkey($inarr);
                AppbymeConfig::saveCkey(array('ckey' => 'app_forumkey','cvalue' => $tmpforumKey));
                AppbymeConfig::saveCkey(array('ckey' => 'secretKey','cvalue' => $tmpsecretKey));
            }
            //返回参数设置
            $res = array(
                'setting_basic_bbname' => $setting['bbname'],
                'setting_basic_sitename' => $setting['sitename'],
                'setting_basic_siteurl' => $setting['siteurl'],
                'setting_basic_adminemail' => $setting['adminemail'],
                'setting_basic_icp' => $setting['icp'],
                'setting_basic_boardlicensed' => $setting['boardlicensed'],

                'onlineinfo' => 0,
                'thread_num' => 0,
                'post_num' => 0,
                'person_num' => 0,
                'setting_basic_stat' => '',
            );
            $this->setData($res);
        } else {
            $this->error('01010000');
        }
    }
}