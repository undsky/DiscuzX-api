<?php

/**
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */
class UserController extends AdminController {
    public function actionIndex() {
        $array = array();
        $pageSize = 20;
        $page = max(1, $_GET['page']);
        $start = $pageSize * ($page - 1);
        $count = DB::result_first('SELECT count(*) FROM %t', array('appbyme_user_access'));
        $info = DbUtils::getDzDbUtils(true)->queryAll('SELECT pp.uid,pp.username,pp.regdate,ppp.lastvisit,pppp.mobile FROM %t p,%t pp,%t ppp,%t pppp WHERE pp.uid=p.user_id AND ppp.uid=p.user_id AND pppp.uid=p.user_id  LIMIT %d,%d', array('appbyme_user_access', 'common_member', 'common_member_status', 'common_member_profile', $start, $pageSize));
        foreach ($info as $k => $v) {
            $info[$k]['appbyme_mobile'] = $this->getAppbymeMobile($v['uid']);
            $info[$k]['qqopenid'] = $this->getQqOpenid($v['uid']);
            $wxInfo = AppbymeConnection::getUserBindInfo($v['uid']);
            $info[$k]['wxopenid'] = $wxInfo['openid'];
            $info[$k]['wxunionid'] = $wxInfo['param'];
        }
        $url = Yii::app()->createAbsoluteUrl('admin/user');

        $multi = multi($count, $pageSize, $page, $url);
        $this->renderPartial('index', array('userInfo' => $info, 'multi' => $multi));
    }


    private function getAppbymeMobile($uid) {
        return DB::result_first('
            SELECT mobile
            FROM %t
            WHERE uid=%d
            ',
            array('appbyme_sendsms', $uid)
        );
    }

    private function getQqOpenid($uid) {
        return DB::result_first('
            SELECT conopenid
            FROM %t
            WHERE uid=%s
            ', array('common_member_connect', $uid)
        );
    }
}
