<?php

/**
 * 新版上传头像接口
 *
 * @author  HanPengyu
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class UploadAvatarExAction extends MobcentAction {

    public function run() {
        $res = $this->initWebApiArray();
        $uid = $this->getController()->uid;
        $res = $this->_runAction($res, $uid);
        echo WebUtils::outputWebApi($res, '', false);
    }

    private function _runAction($res, $uid) {
        if (empty($_FILES['userAvatar']['tmp_name'])) {
            return WebUtils::makeErrorInfo_oldVersion($res, WebUtils::t('请选择上传的文件'));
        }

        if ($_FILES['userAvatar']['error'] > 0) {
            return WebUtils::makeErrorInfo_oldVersion($res, WebUtils::t('上传文件失败'));
        }

        if ($_FILES['userAvatar']['size'] > 2000000) {
            return WebUtils::makeErrorInfo_oldVersion($res, WebUtils::t('上传文件太大'));
        }

        $savePath = UploadUtils::getTempAvatarPath();
        $fileName = sprintf('%s/avatar_%s.jpg', $savePath, $uid);
        if (move_uploaded_file($_FILES['userAvatar']['tmp_name'], $fileName)) {
            $image = $this->_UploadAvatarToUcenter($uid, $fileName);
            FileUtils::safeDeleteFile($fileName);
            if ((isset($image['qiniu_upload']) && $image['qiniu_upload']) || $image['face']['@attributes']['success'] == '1') {
                $url = UserUtils::getUserAvatar($uid);
                DB::update('common_member', array('avatarstatus' => '1'), array('uid' => $uid));
                return array_merge($res, array('icon_url' => '', 'pic_path' => $url));
            }
        }
        return WebUtils::makeErrorInfo_oldVersion($res, WebUtils::t('修改头像失败'));
    }



    private function _UploadAvatarToUcenter($uid, $fileNmae) {
        if (empty($uid) || empty($fileNmae)) {
            return false;
        }
        $result = self::upload($uid, $fileNmae);
        if(!is_array($result))
            $result = $this->xmlToArray($result);
        return $result;
    }

    public function xmlToArray($xml) {
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
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
        /*$imageType = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
        $fileType = $imageType[$type];
        if (!$fileType) {
            $fileType = '.jpg';
        }*/
        $fileType = '.jpg';
        $avatarPath = $_G['setting']['attachdir'];
        $tmpAvatar =$avatarPath . './temp/upload' . $uid . $fileType;
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

}
