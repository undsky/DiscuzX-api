<?php
/**
 * 加密
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/3/29
 * Time: 20:48
 */
class Myencrypt{
    /**
     * 获得sign值
     * @param $data  数据
     */
    static public function getSign($data){
        $signstr = '';
        $sceretkey = $data['secretkey'];
        unset($data['secretkey']);
        ksort($data);
        foreach($data as $k => $v){
            $signstr .= $k.'='.$v.'&';
        }
        $signstr .= 'secretkey='.$sceretkey;
        return md5($signstr);
    }
}