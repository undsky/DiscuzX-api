<?php
/**
 * h5的接收数据接口
 * Created by PhpStorm.
 * User: xiaocongjie
 * Date: 2016/3/29
 * Time: 20:21
 */

class InsertappinfoController extends ApiController {

    private $_key = 'ForumKey_app_download_options';
    //将取得的appinfo存入数据库,app数据兼容老版本
    public function actionIndex() {
        $temRes = $this->data['json'];
        $appInfo = array(
            'appManWeb' => $temRes['appManwb'],
            'appManqq' => $temRes['appManqq'],
            'appWeb' => $temRes['appWeb'],
            'appName' => $temRes['appName'],
            'appManemail' => $temRes['appManemail'],
            'appColor' => $temRes['appColor'],
            'themeType' => $temRes['themeType'],
            'startImg' => $temRes['startImg'],
            'downId' =>$temRes['downId'],
            'downUrl' => $temRes['downUrl'],//appstore下载地址
            'downloadUrl'=>$temRes['downloadUrl'], //控制台下载地址
            'appAuthor' => $temRes['appAuthor'],
            'appDescribe' => $temRes['appDescribtion'],
            'appVersion' => $temRes['appVersion'],
            'appIcon' => $temRes['appIcon'],
            'appImage' => $temRes['appCover'],
            'appContentId' => $temRes['contentId'],
            'appDownloadUrl' => array(
                'android' => $temRes['apkUrl'],
                'apple' => $temRes['ipaUrl'],
                'appleMobile' => $temRes['plistUrl'],
            ),
            'appQRCode' => array(
                'android' => $temRes['qrcode'],
                'apple' => $temRes['qrcode'],
            )
        );
        $arr = $this->getValue($temRes['forumKey'],$this->_key,$appInfo);

        AppbymeConfig::saveForumKey_other($this->_key ,$arr);
        AppbymeConfig::saveForumkey($temRes['forumKey']);
        //兼容老板下载
        AppbymeConfig::saveDownloadOptions($appInfo);
    }

    protected function getValue($forumKey,$ckey,$appInfo) {
        $arr = AppbymeConfig::getForumKey_other($ckey);
        $arr[$forumKey] = $appInfo;
        return $arr;
    }

    protected function setRules() {
        return array(
            'index' => array(
                //'appManemail' => 'email',
                //'appWeb' => 'url',
                'forumKey' => 'string',
                'qrcode' => 'string',
                'downloadUrl'=>'url',
            )
        );
    }
}