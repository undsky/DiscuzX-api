<?php
/**
 * Created by PhpStorm.
 * 七牛上传方法
 * User: tantan
 * Date: 16/3/27
 * Time: 15:41
 */
if(!function_exists('base64_urlSafeDecode')){
    require_once DISCUZ_ROOT.'/mobcent/app/extensions/qiniu/functions.php';
}
class Qiniuup{
    /**
     * @param $data 需要上传的数据
     * @param $filename 图片保存名称
     * @param $havetime 是否加上上传时间
     * @return string 返回整个浏览地址
     */
    static public function uptoqiniu($data,$filename,$havetime = true,$isattach = true){
        $qiniuConfig = Yii::app()->params['qiniu'];
        if(empty($qiniuConfig)){
            return '';
        }
        $time = $havetime ? sprintf('%s/%s',date('Ym'),date('d')).'/' : '';
        $key = Yii::app()->params['discuz']['globals']['config']['db'][1]['dbname'].'/'.$time.$filename;
        $auth = new Auth($qiniuConfig['app_qiniu_Access_Key'],$qiniuConfig['app_qiniu_Secret_Key']);
        $taken = $auth->uploadToken($qiniuConfig['app_qiniu_Bucket_Name'],$key);
        $qiniuMager = new UploadManager();
        list($ret,$err) = $qiniuMager->put($taken,$key,$data);
        if($err === null){
            $return = $isattach ? $qiniuConfig['app_qiniu_url'].$ret['key'] : $ret['key'];
            return $return;
        }else{
            return '';
        }
    }
    static public function getqiniuurl($filename){
        $qiniuConfig = Yii::app()->params['qiniu'];
        return $qiniuConfig['app_qiniu_url'].Yii::app()->params['discuz']['globals']['config']['db'][1]['dbname'].'/'.$filename;
    }
    static public function deletefile($filename){
        $qiniuConfig = Yii::app()->params['qiniu'];
        if(empty($qiniuConfig)){
            return '';
        }
        $auth = new Auth($qiniuConfig['app_qiniu_Access_Key'],$qiniuConfig['app_qiniu_Secret_Key']);
        $bktmag = new BucketManager($auth);
        $filename = Yii::app()->params['discuz']['globals']['config']['db'][1]['dbname'].'/'.$filename;
        return $bktmag->delete($qiniuConfig['app_qiniu_Bucket_Name'],$filename);
    }
    static public function haveImg($filename){
        $qiniuConfig = Yii::app()->params['qiniu'];
        if(empty($qiniuConfig)){
            return '';
        }
        $filename = str_replace($qiniuConfig['app_qiniu_url'],'',$filename);
        $auth = new Auth($qiniuConfig['app_qiniu_Access_Key'],$qiniuConfig['app_qiniu_Secret_Key']);
        $bktmag = new BucketManager($auth);
        $qiniustat = $bktmag->stat($qiniuConfig['app_qiniu_Bucket_Name'],$filename);
        return $qiniustat[0];
    }
}