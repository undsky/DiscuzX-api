<?php

/**
 * 插件管理基类
 *
 * @author 丹尼 <dennyw@qq.com>
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AdminpluginController extends Controller {

    public $rootUrl = '';
    public $dzRootUrl = '';

    public function init() {
        parent::init();
		loadcache('plugin');
        loadcache(MOBCENT_DZ_PLUGIN_ID);
        if (!UserUtils::isInAppbymeAdminGroup()) {
           header('location:'.Yii::app()->createAbsoluteUrl('admin/index/login'));
           exit;
        }
        global $_G;
        $_G['siteurl'] = substr($_G['siteurl'], 0, -16);
        $_G['siteroot'] = substr($_G['siteroot'], 0, -16);
        
        
        $this->rootUrl = Yii::app()->getBaseUrl(true);
        $this->dzRootUrl = substr($this->rootUrl, 0, -16);

       

        DbUtils::init(false);
        if (!$this->checkfounder()) {
            $allowUsers = ArrayUtils::explode(WebUtils::getDzPluginAppbymeAppConfig('appbyme_allow_admin_users'));
            $r = explode('/', Yii::app()->request->getParam('r'));
            if ($_G['username'] != $allowUsers[0]) {
                   $this->getUserAllow($_G['username'],$r[0]);
            }
        }
    }
    
      private function checkfounder() {
        global $_G;
        $founders = $_G['config']['admincp']['founder'];
        if (!$_G['uid'] || $_G['groupid'] != 1 || $_G['adminid'] != 1) {
            return false;
        } elseif (empty($founders)) {
            return true;
        } elseif ($this->strexists(",$founders,", ",$_G[uid],")) {
            return true;
        } elseif (!is_numeric($_G['username']) && strexists(",$founders,", ",$_G[username],")) {
            return true;
        } else {
            return FALSE;
        }
    }

    private function strexists($haystack, $needle) {
        return !(strpos($haystack, $needle) === FALSE);
    }
    
    //获取用户权限
    private function getUserAllow($username,$router){
         $sql = '
            SELECT *
            FROM %t
            WHERE username =%s
            ';
           $result = DbUtils::getDzDbUtils(true)->queryRow($sql, array('appbyme_auth',$username));
           if($result){
               $auth = explode(',',$result['allow']);
               if(!in_array($router, $auth)){
                  $this->success( '没有权限!');
               }
           }else{
                $this->success( '没有权限!');
           }
    }
    /**
     * 操作提示
     * 
     * @param mixed $msg 消息.
     * @param mixed $url 跳转地址.
     */
     public function success($msg, $url = ''){
         $html ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>提示</title>
            </head>

            <body>
            <div style="width:380px;border:1px #CCCCCC solid;height:180px; margin:0 auto; margin-top:100px; line-height:20px; text-align:center;font-size:14px;padding-top:50px;border-radius:5px">&nbsp;&nbsp;( ^_^ )
            '.$msg;
             if ($url) {
                $html .='<meta http-equiv="refresh" content="2;URL=' . $url . '"><a  href="' . $url . '" style="text-decoration:none;font-size:14px;color:#00C">[继续]</a>';
                } else {
                    $html .='<a  href="javascript:history.back()" style="text-decoration:none;font-size:14px;color:#00C">[返回]</a>';
                }   
              $html .='</div></body></html>';
        echo $html;
        exit;
     }
     /**
     * 检测上传相关项
     * 
     * @param mixed $res  初始化数组.
     * @param mixed $file 上传的单个文件数组信息.
     *
     * @return mixed array.
     */
    public function checkUpload($res, $file) {

        // 文件上传失败，捕获错误代码
        if ($file['error']) {
            $res['errCode'] = 0;
            $res['errMsg'] = '失败123';
            return $res;
        }

        // 无效上传
        $file['name'] = strip_tags($file['name']);
        if (empty($file['name'])) {
            $res['errCode'] = 0;
            $res['errMsg'] = '未知上传错误！';
            return $res;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $res['errCode'] = 0;
            $res['errMsg'] = '非法上传文件';
            return $res;
        }

        // 检查文件大小
        $maxSize = 2000000;
        if ($file['size'] > $maxSize || $file['size'] == 0) {
            $res['errCode'] = 0;
            $res['errMsg'] = '上传文件大小不符！';
            return $res;
        }

        // 检查文件Mime类型
        $mime = $file['type'];
        $allowMime = array('image/png', 'image/jpeg');
        if (!in_array(strtolower($mime), $allowMime)) {
            $res['errCode'] = 0;
            $res['errMsg'] = '上传文件MIME类型不允许！';
            return $res;
        }

        // 检查文件后缀
        $ext = FileUtils::getFileExtension($file['name'], 'jpg');
        $allowExt = array('jpg', 'png', 'jpeg');
        if (!in_array(strtolower($ext), $allowExt)) {
            $res['errCode'] = 0;
            $res['errMsg'] = '上传文件后缀不允许!';
            return $res;
        }

        // 通过检测
        $res['errCode'] = 1;
        return $res;
    }
     /**
     * 图片裁切
     * @param sting $src_file 图片地址
     * @param mixed $new_width 图片宽度
     * @param mixed $new_height 图片高度
     */
    function imagecropper($src_file, $new_width = '200', $new_height = '200') {
            $f = explode('.',$src_file);
            $dst_file =  $f[0].".thumb.".$f[1];
            $source_info = getimagesize($src_file);
            $source_mime = $source_info['mime'];
            switch ($source_mime) {
                case 'image/gif':
                    $src_img = imagecreatefromgif($src_file);
                    break;
                case 'image/jpeg':
                    $src_img = imagecreatefromjpeg($src_file);
                    break;
                case 'image/png':
                    $src_img = imagecreatefrompng($src_file);
                    break;
                default:
                    return false;
                    break;
            }
            $w = imagesx($src_img);
            $h = imagesy($src_img);
            $ratio_w = 1.0 * $new_width / $w;
            $ratio_h = 1.0 * $new_height / $h;
            $ratio = 1.0;
// 生成的图像的高宽比原来的都小，或都大 ，原则是 取大比例放大，取大比例缩小（缩小的比例就比较小了）
            if (($ratio_w < 1 && $ratio_h < 1) || ($ratio_w > 1 && $ratio_h > 1)) {
                if ($ratio_w < $ratio_h) {
                    $ratio = $ratio_h; // 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
                } else {
                    $ratio = $ratio_w;
                }
// 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
                $inter_w = (int) ($new_width / $ratio);
                $inter_h = (int) ($new_height / $ratio);
                $inter_img = imagecreatetruecolor($inter_w, $inter_h);
                imagecopy($inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h);
// 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
// 定义一个新的图像
                $new_img = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height, $inter_w, $inter_h);
                switch ($source_mime) {
                    case 'image/jpeg':
                        imagejpeg($new_img, $dst_file, 100); // 存储图像
                        break;
                    case 'image/png':
                        imagepng($new_img, $dst_file, 100);
                        break;
                    case 'image/gif':
                        imagegif($new_img, $dst_file, 100);
                        break;
                    default:
                        break;
                }
            } // end if 1
// 2 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
// =if( ($ratio_w < 1 && $ratio_h > 1) || ($ratio_w >1 && $ratio_h <1) )
            else {
                $ratio = $ratio_h > $ratio_w ? $ratio_h : $ratio_w; //取比例大的那个值
// 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
                $inter_w = (int) ($w * $ratio);
                $inter_h = (int) ($h * $ratio);
                $inter_img = imagecreatetruecolor($inter_w, $inter_h);
//将原图缩放比例后裁剪
                imagecopyresampled($inter_img, $src_img, 0, 0, 0, 0, $inter_w, $inter_h, $w, $h);
// 定义一个新的图像
                $new_img = imagecreatetruecolor($new_width, $new_height);
                imagecopy($new_img, $inter_img, 0, 0, 0, 0, $new_width, $new_height);
                switch ($source_mime) {
                    case 'image/jpeg':
                        imagejpeg($new_img, $dst_file, 100); // 存储图像
                        break;
                    case 'image/png':
                        imagepng($new_img, $dst_file, 100);
                        break;
                    case 'image/gif':
                        imagegif($new_img, $dst_file, 100);
                        break;
                    default:
                        break;
                }
            }
        }
        /*
         * 图片上传
         * @param $p 路径
         */
        public function Uploadimg($p) {
        $res = WebUtils::initWebApiResult();
        $path = MOBCENT_UPLOAD_PATH . $p;
        if (UploadUtils::makeBasePath($path) == '') {
            // $this->output('上传目录不可写！');
            return array('success' => false, 'msg' => '上传目录不可写');
        }
        foreach ($_FILES as $file) {

            $file['name'] = strip_tags($file['name']);
            $ext = strtolower(FileUtils::getFileExtension($file['name'], 'jpg'));

            // 检测
            $imageRes = $this->checkUpload($res, $file);

            if (!$imageRes['errCode']) {
                // $this->output($imageRes['errMsg']);
                return array('success' => false, 'msg' => $imageRes['errMsg']);
            }

            $saveName = FileUtils::getRandomUniqueFileName($path);
            $fileName = $saveName . '.' . $ext;

            if (!move_uploaded_file($file['tmp_name'], $fileName)) {
                //$this->output( '上传图片失败！');
                return array('success' => false, 'msg' => '上传图片失败');
            }
            $fileName = $this->dzRootUrl . '/data/appbyme/upload' . $p . '/' . basename($fileName);
            return array('success' => true, 'msg' => $fileName);
        }
    }
}
