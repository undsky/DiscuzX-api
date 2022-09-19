<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/3/30
 * Time: 12:00
 */
class Apivalidate{
    /**
     * @var string 错误信息
     */
    static private $_err = '';
    /**
     * @var array 要验证的data
     */
    static private $_data = array();
    static public function main($obj,$act){
        self::_getdata();
        $rule = $obj->getRules($act);
        foreach($rule as $k => $v){
            array_key_exists($k,self::$_data) or self::$_err[] = '不存在参数:'.$k;
            $v and call_user_func(array(self,'_vr'.$v),self::$_data[$k]);
        }
    }
    static public function geterr(){
        return self::$_err;
    }
    static public function getdata(){
        if(empty(self::$_data)){
            self::_getdata();
        }
        return self::$_data;
    }
    static private function _vrphone($data){
        $vr = preg_match('/^1[3|4|5|7|8][0-9]{9}$/',$data);
        $vr or self::$_err[] = '电话号码错误';
    }

    static private function _vremail($data){
        $res = preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$data);
        $res or self::$_err[] = '邮箱有误';
    }

    static private function _vrurl($data) {
        $res = preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$data);
        $res or self::$_err[] = 'URL地址不对';
    }

    static private function _vrstring($data) {
        $res = is_string($data);
        $res or self::$_err[] = '变量'.$data.'不是字符串';
    }

    static private function _getdata(){
        self::$_data = array_merge($_GET,$_POST);
        foreach(self::$_data as $k => $v){
            if((is_array($v)||is_object($v)||is_string($v)) && $k != 'json'){
                self::$_data[$k] = WebUtils::tarr($v);
            }
            if($k == 'json'){
                $res = json_decode($v,true);
                $res = WebUtils::tarr($res);
                self::$_data[$k] = $res;
                if($res){
                    self::$_data = array_merge(self::$_data,$res);
                }else{
                    self::$_err[] = 'json格式不正确';
                }
            }
        }
    }
}