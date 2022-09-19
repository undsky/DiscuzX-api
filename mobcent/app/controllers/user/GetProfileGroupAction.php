<?php

/**
 *  获得用户栏目
 *
 * @author   耐小心<nxx@yytest.cn>
 * @copyright (c) 2012-2016   Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

//Mobcent::setErrors();

class GetProfileGroupAction extends MobcentAction {

    public function run($type = 'userinfo') {
        $res = WebUtils::initWebApiArray_oldVersion();
        $res = $this->_getProfileGroup($res, $type);
        WebUtils::outputWebApi($res);
    }

    private function _getProfileGroup($res, $type) {
        global $_G;
        $profilegroup = C::t('common_setting')->fetch('profilegroup', true);
        foreach ($profilegroup as $key => $value) {
            if (!$value['available']) {
                unset($profilegroup[$key]);
            }
        }

        $space = getuserbyuid($_G['uid']);
        space_merge($space, 'field_home');
        space_merge($space, 'profile');
        // 获取 profile setting
        loadcache('profilesetting');
        if (empty($_G['cache']['profilesetting'])) {
            require_once libfile('function/cache');
            updatecache('profilesetting');
            loadcache('profilesetting');
        };
        $verify = C::t('common_member_verify')->fetch($_G['uid']);
        if (!empty($verify) && is_array($verify)) {
            foreach ($verify as $key => $flag) {
                if (in_array($key, array('verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7')) && $flag == 1) {
                    $verifyid = intval(substr($key, -1, 1));
                    if ($_G['setting']['verify'][$verifyid]['available']) {
                        foreach ($_G['setting']['verify'][$verifyid]['field'] as $field) {
                            $_G['cache']['profilesetting'][$field]['unchangeable'] = 1;
                        }
                    }
                }
            }
        }
        if ($type == 'reg') {
            ProfileUtils::init();
            $old = $profilegroup;
            $set = $this->_getRegSetting();
            unset($profilegroup);
            $profilegroup['reg']['title'] = WebUtils::t('注册');
            foreach ($set as $k => $s) {
                //去除DZ中未开启未开启部分
                if (!$old[$k]) {
                    unset($set[$k]);
                }
                $profilegroup['reg']['field'][$k] = $k;
//                $_G['cache']['profilesetting'][$k]['required'] = $s['must'];
            }
        }
        $temp = array();
        $temp['name'] = 'default';
        $temp['field'] = $this->getDefault();
        $res['list'][] = $temp;


        foreach ($profilegroup as $k => $v) {
            $temp = array();
            $temp['name'] = $v['title'];
            $temp['field'] = ProfileUtils::_getProFile($v['field'], $space);
            $res['list'][] = $temp;
        }
        return $res;
    }


    private function _getRegSetting() {
        $setting = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ', array('appbyme_config', 'app_reg')
        );
        $setting = unserialize($setting['cvalue']);
        return $setting;
    }


    private function getDefault() {
        global $_G;
        $space = UserUtils::getUserInfo($_G['uid']);
        space_merge($space, 'field_forum');
        $return = array();
        $userAvatar['name'] = WebUtils::t('头像');
        $userAvatar['fieldid'] = 'avatar';
        $userAvatar['required'] = 0;
        $userAvatar['unchangeable'] = 0;
        $userAvatar['description'] = '';
        $userAvatar['type'] = 'avatar';
        $userAvatar['size'] = 0;
        $userAvatar['nowSet'] = UserUtils::getUserAvatar($_G['uid']);
        $return[] = $userAvatar;
        $sign['name'] = WebUtils::t('签名');
        $sign['fieldid'] = 'sign';
        $sign['required'] = 0;
        $sign['unchangeable'] = 0;
        $sign['description'] = '';
        $sign['type'] = 'sign';
        $sign['size'] = (int)$_G['group']['maxsigsize'];
        $sign['nowSet'] = WebUtils::emptyHtml($space['sightml']);
        $return[] = $sign;
        return $return;
    }
}
