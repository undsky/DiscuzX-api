<?php

/**
 * 用户资料类
 *
 * @author   NaiXiaoXin<nxx@yytest.cn>
 * @copyright  2012-2016, Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME'))
{
    exit('Access Denied');
}

class ProfileUtils
{


    //需要过滤的字段
    public $filterArray = array('residedist', 'birthdist', 'birthcommunity', 'birthyear', 'birthmonth', 'birthprovince', 'residecommunity', 'resideprovince');

    /**
     * 初始化 加载缓存
     */
    public static function init()
    {
        global $_G;
        loadcache('profilesetting');
        if (empty($_G['cache']['profilesetting']))
        {
            require_once libfile('function/cache');
            updatecache('profilesetting');
            loadcache('profilesetting');
        }
        include_once libfile('function/profile');
        $verify = C::t('common_member_verify')->fetch($_G['uid']);
        if (!empty($verify) && is_array($verify))
        {
            foreach ($verify as $key => $flag)
            {
                if (in_array($key, array('verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7')) && $flag == 1)
                {
                    $verifyid = intval(substr($key, -1, 1));
                    if ($_G['setting']['verify'][$verifyid]['available'])
                    {
                        foreach ($_G['setting']['verify'][$verifyid]['field'] as $field)
                        {
                            $_G['cache']['profilesetting'][$field]['unchangeable'] = 1;
                        }
                    }
                }
            }
        }
        if (empty($_G['space']))
        {
            $space = getuserbyuid($_G['uid']);
            space_merge($space, 'field_home');
            space_merge($space, 'profile');
            $_G['space'] = $space;
        }
    }

    public function checkUserInfo($userInfo)
    {
        ProfileUtils::init();
        global $_G;
        $space = $_G['space'];
        $rs = WebUtils::initWebApiResult();
        $censor = discuz_censor::instance();
        $return = array();
        foreach ($userInfo as $k => $s)
        {
            $field = $_G['cache']['profilesetting'][$k];
            if (empty($field))
            {
                continue;
            }
            //过滤+校验
            if (in_array($field['formtype'], array('text', 'textarea')))
            {
                $censor->check(WebUtils::t($s));
                if ($censor->modbanned() || $censor->modmoderated())
                {
                    $rs['errCode'] = 1;
                    $rs['errMsg'] = 'profile_censor';
                    return $rs;
                }
            }
            if ($field['unchangeable'] && !empty($space[$k]) && $space[$k] != WebUtils::t($s) && !in_array($k, array('birthcity', 'residecity', 'birthday')))
            {
                $msg = $field['title'] . WebUtils::t('无法修改!');
                $rs['errCode'] = 1;
                $rs['errMsg'] = $msg;
                return $rs;
            }
            $check = ProfileUtils::_doSpace($field, $s);
            if ($check['errCode'] != 0)
            {
                $rs['errCode'] = $check['errCode'];
                $rs['errMsg'] = $check['errMsg'];
                return $rs;
            } else
            {
                $return = array_merge($return, $check['return']);
            }
        }
        $rs['return'] = $return;
        return $rs;
    }

    public function updateUserInfo($uid, $userInfo)
    {
        return C::t('common_member_profile')->update($uid, $userInfo);
    }

    public function _getRegSetting()
    {
        $setting = DbUtils::getDzDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ', array('appbyme_config', 'app_reg')
        );
        $setting = unserialize($setting['cvalue']);
        return $setting;
    }

    /**
     * 处理出生地、居住地、生日信息 并校验是否符合DZ要求
     * $filed array() 字段信息
     * $info string/array  资料
     */
    public function _doSpace($field, $info)
    {
        global $_G;
        $rs = WebUtils::initWebApiResult();
        $reutrn = array();
        if ($field['fieldid'] == 'birthcity')
        {
            //出生地
            $_GET['birthprovince'] = $return['birthprovince'] = WebUtils::t($info['birthprovince']);
            $_GET['birthcity'] = $return['birthcity'] = WebUtils::t($info['birthcity']);
            $_GET['birthdist'] = $return['birthdist'] = WebUtils::t($info['birthdist']);
            $_GET['birthcommunity'] = $return['birthcommunity'] = WebUtils::t($info['birthcommunity']);
        } elseif ($field['fieldid'] == 'residecity')
        {
            //居住地
            $_GET['resideprovince'] = $return['resideprovince'] = WebUtils::t($info['resideprovince']);
            $_GET['residecity'] = $return['residecity'] = WebUtils::t($info['residecity']);
            $_GET['residedist'] = $return['residedist'] = WebUtils::t($info['residedist']);
            $_GET['residecommunity'] = $return['residecommunity'] = WebUtils::t($info['residecommunity']);
        } elseif ($field['fieldid'] == 'birthday')
        {
            //生日
            $space = getuserbyuid($_G['uid']);
            space_merge($space, 'field_home');
            space_merge($space, 'profile');
            if (isset($info['birthmonth']) && ($space['birthmonth'] != $info['birthmonth'] || $space['birthday'] != $info['birthday']))
            {//计算星座
                $return['constellation'] = get_constellation($info['birthmonth'], $info['birthday']);
            }
            if (isset($info['birthyear']) && $space['birthyear'] != $info['birthyear'])
            {// 计算生肖
                $return['zodiac'] = get_zodiac($info['birthyear']);
            }
            $return['birthmonth'] = $info['birthmonth'];
            $return['birthyear'] = $info['birthyear'];
            $return['birthday'] = $info['birthday'];
        } else
        {
            //其他设置项
            $check = ProfileUtils::check($field, $info);
            if ($check === false)
            {
                $rs['errCode'] = '10000';
                $rs['errMsg'] = $field['title'] . WebUtils::t('信息有误，请重试');
            } else
            {
                $return[$field['fieldid']] = $check;
            }
        }
        $rs['return'] = $return;

        return $rs;
    }

    /**
     * 调用DZFunction校验是否符合规则
     */
    public function check($field, $value)
    {
        if ($field['formtype'] == 'checkbox' || $field['formtype'] == 'list')
        {
            foreach ($value as $k => $v)
            {
                $info[] = WebUtils::t($v);
            }
            $return = implode("\n", $info);
        } elseif ($field['formtype'] == 'file')
        {
            $info = $return = str_replace(ImageUtils::getAttachUrl().'profile/','',$value);
        } else
        {
            $return = $info = WebUtils::t($value);
        }
        $check = profile_check($field['fieldid'], $info);
        if ($check)
        {
            return $return;
        } else
        {
            return false;
        }
    }


    public static function _getProFile($fields, $space)
    {
        global $_G;
        $return = array();
        foreach ($fields as $k => $v)
        {
            $temp = array();
            $set = $_G['cache']['profilesetting'][$v];
            if (!$set['available'])
            {
                continue;
            }
            $temp['fieldid'] = $set['fieldid'];
            $temp['required'] = (int)$set['required'];
            $temp['unchangeable'] = intval(!empty($space[$v]) ? $set['unchangeable'] : 0);
            $filterArray = array('residedist', 'birthdist', 'birthcommunity', 'birthyear', 'birthmonth', 'birthprovince', 'residecommunity', 'resideprovince');
            $temp['description'] = strval($set['description']);
            $temp['size'] = intval($set['size']);

            if (in_array($v, $filterArray))
            {
                continue;
            } elseif ($v == 'birthcity')
            {
                $temp['name'] = WebUtils::t('出生地');
                $temp['type'] = 'birth';
                $temp['nowSet']['birthprovince'] = strval($space['birthprovince']);
                $temp['nowSet']['birthcity'] = strval($space['birthcity']);
                $temp['nowSet']['birthdist'] = strval($space['birthdist']);
                $temp['nowSet']['birthcommunity'] = strval($space['birthcommunity']);
                $return[] = $temp;
            } elseif ($v == 'residecity')
            {
                $temp['name'] = WebUtils::t('居住地');
                $temp['type'] = 'reside';
                $temp['nowSet']['resideprovince'] = strval($space['resideprovince']);
                $temp['nowSet']['residecity'] = strval($space['residecity']);
                $temp['nowSet']['residedist'] = strval($space['residedist']);
                $temp['nowSet']['residecommunity'] = strval($space['residecommunity']);
                $return[] = $temp;
            } elseif ($v == 'birthday')
            {
                $temp['name'] = WebUtils::t('生日');
                $temp['type'] = 'birthday';
                $temp['nowSet']['birthyear'] = (int)$space['birthyear'];
                $temp['nowSet']['birthmonth'] = (int)$space['birthmonth'];
                $temp['nowSet']['birthday'] = (int)$space['birthday'];
                $return[] = $temp;
            } elseif ($v == 'gender')
            {
                $temp['name'] = WebUtils::t('性别');
                $temp['type'] = 'gender';
                $temp['nowSet'] = (int)$space['gender'];
                $return[] = $temp;
            } else
            {
                $set['size'] = (int)$set['size'];
                if ($set['formtype'] == 'file')
                {
                    if ($_GET['sdkVersion'] < '2.5.0.0')
                    {
                        continue;
                    }
                    $temp['name'] = $set['title'];
                    $temp['description'] = $set['description'];
                    $temp['type'] = $set['formtype'];
                    $temp['size'] = $set['size'];
                    $temp['nowSet'] = '';
                    if(!empty($space[$v])){
                        if(Yii::app()->params['qiniu']['app_qiniu_url']){
                            $temp['nowSet'] = $space[$v];
                        }else{
                            $temp['nowSet'] = ImageUtils::getAttachUrl() . 'profile/' . $space[$v];
                        }
                    }

                    $return[] = $temp;
                } elseif ($set['formtype'] == 'select' || $set['formtype'] == 'list' || $set['formtype'] == 'checkbox' || $set['formtype'] == 'radio')
                {
                    $temp['name'] = $set['title'];
                    $temp['type'] = $set['formtype'];
                    $temp['size'] = $set['size'];
                    $temp['choices'] = explode("\n", $set['choices']);
                    $temp['nowSet'] = explode("\n", $space[$v]);
                    $return[] = $temp;
                } elseif ($set['formtype'] == 'text' || $set['formtype'] == 'textarea')
                {
                    $temp['name'] = $set['title'];
                    $temp['type'] = $set['formtype'] == 'textarea' ? 'texta' : $set['formtype'];
                    $temp['size'] = $set['size'];
                    $temp['nowSet'] = strval($space[$v]);
                    $return[] = $temp;
                }
            }
        }
        return $return;
    }

}
