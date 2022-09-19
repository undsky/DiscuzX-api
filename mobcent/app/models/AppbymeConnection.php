<?php

/**
 * WX/FB绑定model类
 *
 * @author HanPengyu
 * @author 耐小心
 * @copyright 2012-2015 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AppbymeConnection extends DiscuzAR {

    const WECHAT_BIND = 1;  // 微信绑定登录
    const WECHAT_TYPE = 1;  // 微信已修改密码
    const WECHAT_PASSWORD = 2; //未修改密码
    const FaceBook_TYPE = 2; //FB类型
    const BING_STATUS = 1; //已绑定类型
    const BIND_CHANGE_STATUS = 2;
    const QqSdk_TYPE=3;

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{appbyme_connection}}';
    }

    public function rules() {
        return array(
        );
    }

    // WX 自定义表
    public static function getMobcentWxinfoByOpenId($openId) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE openid=%s
            AND type=%n
            ', array('appbyme_connection', $openId, self::WECHAT_TYPE)
        );
    }
	//使用联合ID获得用户信息
	public static function getMobcentWxinfoByUnionId($unionId) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE param=%s
            AND type=%n
            ', array('appbyme_connection', $unionId, self::WECHAT_TYPE)
        );
    }
    // 插件自定义WX表
    public static function insertMobcentWx($data) {
        return DbUtils::getDzDbUtils(true)->insert('appbyme_connection', $data);
    }

    // WX 3.2微信表
    public static function getWXinfoByOpenId($openId) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE openid=%s
            AND status=%n
            ', array('common_member_wechatmp', $openId, 1)
        );
    }

    // 检测是否有微信登录插件
    public static function isWechat($identifier = 'wechat') {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE identifier=%s
            ', array('common_plugin', $identifier)
        );
    }

    // 检测用户是否绑定
    public static function getUserBindInfo($uid, $type = self::WECHAT_TYPE) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE uid=%d
            AND type=%d
            ', array('appbyme_connection', $uid, $type)
        );
    }

    //获得FaceBook绑定
    public static function getFBInfoByOpenId($openId) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE openid=%s
            AND status=%n
            AND type=%n
            ', array('appbyme_connection', $openId, self::BING_STATUS, self::FaceBook_TYPE)
        );
    }
    //获得QQSdk绑定
    public static function getQqSdkInfoByOpenId($openId) {
        return DbUtils::getDzDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE openid=%s
            AND status=%n
            AND type=%n
            ', array('appbyme_connection', $openId, self::BING_STATUS, self::QqSdk_TYPE)
        );
    }

    //同步头像 传入UID和URL即可
    public static function syncAvatar($uid = '', $avatar = '') {
        $member = getuserbyuid($uid);
        if (!$member || !$avatar) {
            return '';
        }
        if (!$content = dfsockopen($avatar)) {
            return false;
        }
        $tmpFile = DISCUZ_ROOT . './data/avatar/' . TIMESTAMP . random(6);
        file_put_contents($tmpFile, $content);
        if (!is_file($tmpFile)) {
            return false;
        }
        $result = self::upload($uid, $tmpFile);
        @unlink($tmpFile);
        DB::update('common_member', array('avatarstatus' => '1'), array('uid' => $uid));
        return $result;
    }

    private static function upload($uid, $localFile) { //???
        global $_G;
        if (!$uid || !$localFile) {
            return false;
        }
        list($width, $height, $type, $attr) = getimagesize($localFile);
        if (!$width) {
            return false;
        }
        if ($width < 10 || $height < 10 || $type == 4) {
            return false;
        }
        $imageType = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
        $fileType = $imgType[$type];
        if (!$fileType) {
            $fileType = '.jpg';
        }
        $avatarPath = $_G['setting']['attachdir'];
        $tmpAvatar = $avatarPath . './temp/upload' . $uid . $fileType;
        file_exists($tmpAvatar) && @unlink($tmpAvatar);
        file_put_contents($tmpAvatar, file_get_contents($localFile));
        if (!is_file($tmpAvatar)) {
            return false;
        }
        $tmpAvatarBig = './temp/upload' . $uid . 'big' . $fileType;
        $tmpAvatarMiddle = './temp/upload' . $uid . 'middle' . $fileType;
        $tmpAvatarSmall = './temp/upload' . $uid . 'small' . $fileType;
        require_once libfile('class/image');
        $image = new image();
        if ($image->Thumb($tmpAvatar, $tmpAvatarBig, 200, 250, 1) <= 0) {
            return false;
        }
        if ($image->Thumb($tmpAvatar, $tmpAvatarMiddle, 120, 120, 1) <= 0) {
            return false;
        }
        if ($image->Thumb($tmpAvatar, $tmpAvatarSmall, 48, 48, 2) <= 0) {
            return false;
        }
        $tmpAvatarBig = $avatarPath . $tmpAvatarBig;
        $tmpAvatarMiddle = $avatarPath . $tmpAvatarMiddle;
        $tmpAvatarSmall = $avatarPath . $tmpAvatarSmall;
        if(!empty(Yii::app()->params['qiniu'])){
            $upbig = Qiniuup::uptoqiniu(file_get_contents($tmpAvatarBig),'upload'.$uid.'big'.$fileType,false);
            $upmiddle = Qiniuup::uptoqiniu(file_get_contents($tmpAvatarMiddle),'upload'.$uid.'middle'.$fileType,false);
            $upsmall = Qiniuup::uptoqiniu(file_get_contents($tmpAvatarSmall),'upload'.$uid.'small'.$fileType,false);
            if($upbig && $upmiddle && $upsmall){
                $result = array('qiniu_upload' => 1);
            }else{
                $result = array('qiniu_upload' => 0);
            }
        }else{
            $avatar1 = self::byte2hex(file_get_contents($tmpAvatarBig));
            $avatar2 = self::byte2hex(file_get_contents($tmpAvatarMiddle));
            $avatar3 = self::byte2hex(file_get_contents($tmpAvatarSmall));
            $extra = '&avatar1=' . $avatar1 . '&avatar2=' . $avatar2 . '&avatar3=' . $avatar3;
            $result = self::uc_api_post_ex('user', 'rectavatar', array('uid' => $uid), $extra);
        }
        @unlink($tmpAvatar);
        @unlink($tmpAvatarBig);
        @unlink($tmpAvatarMiddle);
        @unlink($tmpAvatarSmall);
        return $result;
    }

    private static function byte2hex($string) {
        $buffer = '';
        $value = unpack('H*', $string);
        $value = str_split($value[1], 2);
        $b = '';
        foreach ($value as $k => $v) {
            $b .= strtoupper($v);
        }
        return $b;
    }

    private static function uc_api_post_ex($module, $action, $arg = array(), $extra = '') {
        loaducenter();
        $s = $sep = '';
        foreach ($arg as $k => $v) {
            $k = urlencode($k);
            if (is_array($v)) {
                $s2 = $sep2 = '';
                foreach ($v as $k2 => $v2) {
                    $k2 = urlencode($k2);
                    $s2 .= "$sep2{$k}[$k2]=" . urlencode(uc_stripslashes($v2));
                    $sep2 = '&';
                }
                $s .= $sep . $s2;
            } else {
                $s .= "$sep$k=" . urlencode(uc_stripslashes($v));
            }
            $sep = '&';
        }
        $postdata = uc_api_requestdata($module, $action, $s, $extra);
        return uc_fopen2(UC_API . '/index.php', 500000, $postdata, '', TRUE, UC_IP, 20);
    }
	public static  function getUserInfoFromWeiXin($openId,$accessToken){
		$result = WebUtils::httpRequest("https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openId");
		$json = WebUtils::jsonDecode($result, true);
		return $json;
	}
	
	public  static function updateWeiXinUserInfo($data,$id){
		return DB::update('appbyme_connection',$data,array('id'=>$id));
	}
}
