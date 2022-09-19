<?php
/**
 * 插件基类
 *
 * @author 丹尼 <dennyww@qq.com>
 * @copyright 2012-2014 Appbyme,denny
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class PluginController extends Controller
{
    public $rootUrl = '';
    public $dzRootUrl = '';

    public function init()
    {
        parent::init();
      
        global $_G;
        $_G['siteurl'] = substr($_G['siteurl'], 0, -16);
        $_G['siteroot'] = substr($_G['siteroot'], 0, -16);

        $this->rootUrl = Yii::app()->getBaseUrl(true);
        $this->dzRootUrl = substr($this->rootUrl, 0, -16);

        loadcache('plugin');
        loadcache(MOBCENT_DZ_PLUGIN_ID);

        DbUtils::init(false);
    }
    public function error($msg){
        echo $msg;exit;
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
            $res['errMsg'] = '失败';
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
        $allowMime = array('image/png', 'image/jpeg','application/octet-stream');
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

}
