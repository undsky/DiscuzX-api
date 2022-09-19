<?php

/**
 *  获得地区信息
 *
 *  @author   耐小心<wangsiqi@goyoo.com>
 *  @copyright (c) 2012-2016   Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
//Mobcent::setErrors();

class GetDistrictAction extends MobcentAction {

    private $Md5Key = 'Mobcent_Distric_MD5';

    public function run($md5='') {
        $res = WebUtils::initWebApiArray_oldVersion();
        $this->runWithMd5($res,$md5);
        exit();
    }

    public function runWithMd5($res,$md5){
        $cacheMd5 = Yii::app()->cache->get($this->Md5Key);
        if($cacheMd5!==$md5){
            $this->runWithCache(array('action'=>'district'),$res);
        }else{
            $res['list'] = null;
            $res['md5']=$cacheMd5;
            WebUtils::outputWebApi($res);
        }
    }

    protected function runWithCache($key, $params=array()) {
        $cache = $this->getCacheInfo();

        $res = array();
        if (!$cache['enable'] || ($res = Yii::app()->cache->get($key)) === false) {
            $res = WebUtils::outputWebApi($this->getResult($params), '', false);
            if ($cache['enable']) {
                Yii::app()->cache->set($key, $res, $cache['expire']);
            }
        }
        $md5 = md5($res);
        Yii::app()->cache->set($this->Md5Key, $md5,DAY_SECONDS);
        $resArr = WebUtils::JsonDecode($res);

        $resArr['md5'] = $md5;
        WebUtils::outputWebApi($resArr,'utf-8');

        //   echo $res;
    }

    protected function getCacheInfo(){
        return array('enable' => 1, 'expire' => DAY_SECONDS,);

    }
    protected function getResult($res) {
        $a = DbUtils::createDbUtils(true)->queryAll('SELECT * FROM %t WHERE `level`<=3 ORDER BY displayorder ASC,level ASC,id ASC ',array('common_district'));
        $tmp = array();
        $bb = array();
        foreach($a as $v){
            switch($v['level']){
                case 1:
                    $tmp[$v['id']] =$this->_runDistrict($v);
                    break;
                case 2:
                    $tmp[$v['upid']]['pub'][$v['id']] = $this->_runDistrict($v);
                    $bb[$v['id']] = $v['upid'];
                    break;
                case 3:
                    $tmp[$bb[$v['upid']]]['pub'][$v['upid']]['pub'][$v['id']] = $this->_runDistrict($v);
                    break;
            }
        }
        foreach ($tmp as $k=>$v){
            if($a[$k]['usetype']==0){
                unset($tmp[$k]);
            }
        }
        $res['list'] = $tmp;
        return $res;
    }



    private function  _runDistrict($info){
        $return = array();
        $return['name'] = $info['name'];
        if($info['level']==1){
            $return['birth'] = in_array($info['usetype'],array('1','3'))?1:0;
            $return['reside'] = in_array($info['usetype'],array('2','3'))?1:0;
        }
        return $return;
    }

}
