<?php

/**
 *
 *  分享增加积分Action
 *
 * @author  NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 GoYoo Inc.
 */
/**
 *                   _ooOoo_
 *                  o8888888o
 *                  88" . "88
 *                  (| -_- |)
 *                  O\  =  /O
 *               ____/`---'\____
 *             .'  \\|     |//  `.
 *            /  \\|||  :  |||//  \
 *           /  _||||| -:- |||||-  \
 *           |   | \\\  -  /// |   |
 *           | \_|  ''\---/''  |   |
 *           \  .-\__  `-`  ___/-. /
 *         ___`. .'  /--.--\  `. . __
 *      ."" '<  `.___\_<|>_/___.'  >'"".
 *     | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *     \  \ `-.   \_ __\ /__ _/   .-` /  /
 *======`-.____`-.___\_____/___.-`____.-'======
 *                   `=---='
 *^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *         佛祖保佑       永无BUG
 */
//Mobcent::setErrors();
class ShareAction Extends MobcentAction {
    public function run($type, $form = 'topic', $tid = '', $fid = '', $aid = '') {
        $res = $this->initWebApiArray();
        $res = $this->_share($res, $type, $form, $tid, $fid, $aid);
        WebUtils::outputWebApi($res);
    }

    public function _share($res, $type, $form, $tid, $fid, $aid) {
        global $_G;
        if(!$_G['uid']){
            return $this->makeErrorInfoNoAlert($res, 'to_login');
        }
        //判断来源
        $formarr = array('topic', 'portal', 'app','live');
        if (!in_array($form, $formarr)) {
            return $this->makeErrorInfoNoAlert($res, 'mobcent_share_form_nofound');
        }
        //判断活动是否存在以及有效
        $activityId = AppbymeConfig::getShareActivityId();
        $info = AppbymeShareModel::getInfoByIDCache($activityId);
        if (empty($info)) {
            return $this->makeErrorInfoNoAlert($res, 'mobcent_share_activity_nofound');
        }
        if ($info['starttime'] > time()) {
            return $this->makeErrorInfoNoAlert($res, 'mobcent_share_activity_nostart');
        }
        if ($info['endtime'] < time()) {
            return $this->makeErrorInfoNoAlert($res, 'mobcent_share_activity_isend');
        }
        if (!in_array($type, $info['type'])) {
            return $this->makeErrorInfoNoAlert($res, 'mobcent_share_type_haveget');
        }
        $modeRs = $this->_mode($info);
        if ($modeRs['rs'] != '1') {
            return $this->makeErrorInfoNoAlert($res, $modeRs['msg'], $modeRs['value']);
        }
        //判断Form是否可用且处理
        if ($form == 'topic') {
            if (empty($info['param']['topic'])) {
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_form_isnotopen');
            }
            $res = $this->_topicShare($res,$info, $tid, $fid, $type);
        } elseif ($form == 'portal') {
            if (empty($info['param']['portal'])) {
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_form_isnotopen');
            }
            $res = $this->_portalShare($res,$info,$aid,$type);
        } elseif ($form == 'live') {
            if (empty($info['param']['live'])) {
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_form_isnotopen');
            }
            $res = $this->_liveShare($res,$info,$type);
        } else {
            if (empty($info['param']['app'])) {
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_form_isnotopen');
            }
            $res = $this->_appShare($res, $info, $type);
        }
        return $res;

    }


    private static function _mode($info) {
        global $_G;
        $rs['rs'] = '1';
        // $rs = WebUtils::initWebApiResult();
        $mode = $info['param']['addmode'];
        $max = $info['param']['addmax'];
        if (!empty($max)) {
            //判断逻辑
            if ($mode == '1') {
                $starttime = strtotime(date('Y-m-d'));
                $endtime = strtotime(date('Y-m-d', strtotime('+1 day'))) - 1;
                $count = AppbymeShareUserModel::countDayActivity($info['id'], $_G['uid'], $starttime, $endtime);
                $str =Yii::t('mobcent', 'mobcent_share_today', array());
            } else {
                $count = AppbymeShareUserModel::countActivity($info['id'], $_G['uid']);
                $str = Yii::t('mobcent', 'mobcent_share_thisact', array());
            }
            if ($max <= $count) {
                $rs['rs'] = 0;
                $rs['msg'] = 'mobcent_share_mode_addmax';
                $rs['value']['{str}'] = $str;
                $rs['value']['{num}'] = $max;
            }
        }

        return $rs;

    }

    private static function makeErrorInfoNoAlert($res, $message, $params = array()) {
        $params['alert'] = 0;
        return WebUtils::makeErrorInfo_oldVersion($res, $message, $params);
    }

    private  function _topicShare($res,$info,$tid,$fid,$type){
        global $_G;
        if(!empty($info['param']['topic']['tid'])){
            $tidArray = explode(',',$info['param']['topic']['tid']);
            if(!in_array($tid,$tidArray)){
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_topic_noin');
            }
        }elseif(!empty($info['param']['topic']['fid'])){
            $fidArray = explode(',',$info['param']['topic']['fid']);
            if(!in_array($fid,$fidArray)){
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_forum_noin');
            }
        }
        $add['uid'] = $_G['uid'];
        $add['activityid'] = $info['id'];
        $add['username'] = $_G['username'];
        $add['type'] = $type;
        $add['time'] = time();
        $add['form'] = 'topic';
        $add['param'] = serialize(array('tid'=>$tid,'fid'=>$fid));
        AppbymeShareUserModel::add($add);
        $str = $this->addCredit($info);
        return $this->makeErrorInfo($res, 'mobcent_share_success', array('noError' => 1,'{str}'=>$str));
    }


    private  function _portalShare($res,$info,$aid,$type){
        global $_G;
        if(!empty($info['param']['portal']['aid'])){
            $aidArray = explode(',',$info['param']['portal']['aid']);
            if(!in_array($aid,$aidArray)){
                return $this->makeErrorInfoNoAlert($res, 'mobcent_share_portal_noin');
            }
        }
        $add['uid'] = $_G['uid'];
        $add['activityid'] = $info['id'];
        $add['username'] = $_G['username'];
        $add['type'] = $type;
        $add['time'] = time();
        $add['form'] = 'portal';
        $add['param'] = serialize(array('aid'=>$aid));
        AppbymeShareUserModel::add($add);
        $str = $this->addCredit($info);
        return $this->makeErrorInfo($res, 'mobcent_share_success', array('noError' => 1,'{str}'=>$str));
    }
    private  function _appShare($res,$info,$type){
        global $_G;

        $add['uid'] = $_G['uid'];
        $add['activityid'] = $info['id'];
        $add['username'] = $_G['username'];
        $add['type'] = $type;
        $add['time'] = time();
        $add['form'] = 'app';
        AppbymeShareUserModel::add($add);
        $str = $this->addCredit($info);
        return $this->makeErrorInfo($res, 'mobcent_share_success', array('noError' => 1,'{str}'=>$str));
    }

    private  function _liveShare($res,$info,$type){
        global $_G;

        $add['uid'] = $_G['uid'];
        $add['activityid'] = $info['id'];
        $add['username'] = $_G['username'];
        $add['type'] = $type;
        $add['time'] = time();
        $add['form'] = 'live';
        AppbymeShareUserModel::add($add);
        $str = $this->addCredit($info);
        return $this->makeErrorInfo($res, 'mobcent_share_success', array('noError' => 1,'{str}'=>$str));
    }
    
    private static function addCredit($info){
        global $_G;
        $dataarr = array();
        $str = '';
        $extcredits = $_G['setting']['extcredits'];
        foreach ($info['credit'] as $k=>$v){
            $dataarr['extcredits'.$k] = $v;
            $str .= $v.$extcredits[$k]['title'].',';

        }
        updatemembercount($_G['uid'],$dataarr,true,'',0,'',Yii::t('mobcent', 'mobcent_share_discuz_credit', array()));
        DB::query('UPDATE %t SET count=count+1 WHERE id=%d', array('appbyme_share', $info['id']));

        return substr($str,0,-1);
    }
}

