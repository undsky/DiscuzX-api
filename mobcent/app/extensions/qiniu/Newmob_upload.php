<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/3/27
 * Time: 16:44
 */
class Newmob_upload extends discuz_upload{
    function save($ignore = 0) {
        if($ignore) {
            if(!$this->save_to_local($this->attach['tmp_name'], $this->attach['target'])) {
                $this->errorcode = -103;
                return false;
            } else {
                $this->errorcode = 0;
                return true;
            }
        }
        $uploaded = Qiniuup::uptoqiniu(file_get_contents($this->attach['tmp_name']),$this->attach['name']);
        if($uploaded){
            $this->attach['imageinfo'] = $this->get_image_info($this->attach['tmp_name'], true);
            //删除临时文件
            if(file_exists($this->attach['tmp_name'])){
                @unlink($this->attach['tmp_name']);
            }
            $this->attach['target'] = $uploaded;
            $this->attach['attachment'] = $uploaded;
        }else{
            $uploaded = $this->save_to_local($this->attach['tmp_name'], $this->attach['target']);
            if($uploaded){
                $this->attach['imageinfo'] = $this->get_image_info($this->attach['target'], true);
            }
        }
        if(empty($this->attach) || empty($this->attach['tmp_name']) || empty($this->attach['target'])) {
            $this->errorcode = -101;
        } elseif(in_array($this->type, array('group', 'album', 'category')) && !$this->attach['isimage']) {
            $this->errorcode = -102;
        } elseif(in_array($this->type, array('common')) && (!$this->attach['isimage'] && $this->attach['ext'] != 'ext')) {
            $this->errorcode = -102;
        } elseif(!$uploaded) {
            $this->errorcode = -103;
        } elseif(($this->attach['isimage'] || $this->attach['ext'] == 'swf') && !$this->attach['imageinfo']) {
            $this->errorcode = -104;
            @unlink($this->attach['target']);
        } else {
            $this->errorcode = 0;
            return true;
        }

        return false;
    }
    function get_image_info($target, $allowswf = false) {
        $ext = $this->attach['ext'];
        $isimage = $this->attach['isimage'];
        if(!$isimage && ($ext != 'swf' || !$allowswf)) {
            return false;
        } elseif(!is_readable($target)) {
            return false;
        } elseif($imageinfo = @getimagesize($target)) {
            list($width, $height, $type) = !empty($imageinfo) ? $imageinfo : array('', '', '');
            $size = $width * $height;
            if($size > 16777216 || $size < 16 ) {
                return false;
            } elseif($ext == 'swf' && $type != 4 && $type != 13) {
                return false;
            } elseif($isimage && !in_array($type, array(1,2,3,6,13))) {
                return false;
            } elseif(!$allowswf && ($ext == 'swf' || $type == 4 || $type == 13)) {
                return false;
            }
            return $imageinfo;
        } else {
            return false;
        }
    }
}