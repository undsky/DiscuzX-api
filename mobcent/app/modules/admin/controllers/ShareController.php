<?php

/**
 *
 * 分享控制器
 *
 * @author  NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 GoYoo Inc.
 */
/**
 *
 *          ┌─┐       ┌─┐
 *       ┌──┘ ┴───────┘ ┴──┐
 *       │                 │
 *       │       ───       │
 *       │  ─┬┘       └┬─  │
 *       │                 │
 *       │       ─┴─       │
 *       │                 │
 *       └───┐         ┌───┘
 *           │         │
 *           │         │
 *           │         │
 *           │         └──────────────┐
 *           │                        │
 *           │                        ├─┐
 *           │                        ┌─┘
 *           │                        │
 *           └─┐  ┐  ┌───────┬──┐  ┌──┘
 *             │ ─┤ ─┤       │ ─┤ ─┤
 *             └──┴──┘       └──┴──┘
 *                 神兽保佑
 *                 代码无BUG!
 */
//Mobcent::setErrors();
class ShareController extends AdminController {


    public function actionIndex($page = '1', $pageSize = '10') {
        $info = AppbymeShareModel::getAllByPage($page, $pageSize);
        $count = AppbymeShareModel::countAll();
        $url = Yii::app()->createAbsoluteUrl('admin/share/index');
        $multi = multi($count, $pageSize, $page, $pageSize);
        $configId = AppbymeConfig::getShareActivityId();
        $this->render('index', array('info' => $info, 'multi' => $multi, 'configId' => $configId));
    }

    public function actionEdit($id = '') {
        if (!empty($id)) {
            $info = AppbymeShareModel::getInfoByID($id);
            if (empty($info)) {
                ShareUtils::showMsg('此ID不存在任何信息,请重试', Yii::app()->createAbsoluteUrl('admin/share/index'));
            }
        }
        $this->render('edit', array('info' => $info));
    }

    public function actionList($id, $page = '1', $pageSize = '20') {
        $info = AppbymeShareModel::getInfoByID($id);
        if (empty($info)) {
            ShareUtils::showMsg('此ID不存在任何信息,请重试', Yii::app()->createAbsoluteUrl('admin/share/index'));
        }
        $pageurl = Yii::app()->createAbsoluteUrl('admin/share/list', array('id' => $id, 'pageSize' => $pageSize));
        $searchRes = ShareUtils::search('appbyme_share_user', array('activityid' => $id), 'id', $pageurl, $page, $pageSize);
        $listmap = array('type' => array('1' => 'QQ', '2' => 'QQ空间', '3' => '微信好友', '4' => '朋友圈', '5' => '新浪微博', '6' => 'FaceBook'), 'form' => array('portal' => '文章', 'topic' => '帖子', 'app' => 'App分享','live'=>'直播'));
        $list = ShareUtils::int_to_string($searchRes['searchList'], $listmap);
        $this->renderPartial('list', array('lists' => $list, 'page' => $page, 'multi' => $searchRes['multi']));
    }

    public function actionAdd($id = '') {
        $res = WebUtils::initWebApiResult();
        if (!empty($_POST)) {
            if (!empty($id)) {
                $info = AppbymeShareModel::getInfoByID($id);
                if (empty($info)) {
                    ShareUtils::endWebApi($res, WebUtils::t('此ID不存在任何信息,请重试'), MOBCENT_ERROR_DEFAULT);
                }
            }
            if (!$_POST['type']) {
                ShareUtils::endWebApi($res, WebUtils::t('必须选择一种分享方式'), MOBCENT_ERROR_DEFAULT);
            }
            $updateInfo = $creditArray = $paramArray = array();
            $updateInfo['name'] = WebUtils::t($_POST['name']);
            $updateInfo['starttime'] = strtotime($_POST['starttime']);
            $updateInfo['endtime'] = strtotime($_POST['endtime']);
            $updateInfo['type'] = implode(',', $_POST['type']);
            $_POST['credit'] = (int)$_POST['credit'];
            $_POST['creditnum'] = (int)$_POST['creditnum'];
            if ($_POST['credit'] > 0 && $_POST['creditnum'] > 0) {
                $creditArray[$_POST['credit']] = $_POST['creditnum'];
            } else {
                ShareUtils::endWebApi($res, WebUtils::t('您填写的奖励设置不正确,请重新填写'), MOBCENT_ERROR_DEFAULT);
            }
            $updateInfo['credit'] = serialize($creditArray);
            if (empty($_POST['get'])) {
                ShareUtils::endWebApi($res, WebUtils::t('分享规则中四个必须需要选择一个'), MOBCENT_ERROR_DEFAULT);
            }
            if ($_POST['get']['topic']) {
                $paramArray['topic']['tid'] = $_POST['topic']['tid'];
                $paramArray['topic']['fid'] = $_POST['topic']['fid'];
            }
            if ($_POST['get']['portal']) {
                $paramArray['portal']['aid'] = $_POST['portal']['aid'];
            }
            if ($_POST['get']['app']) {
                $paramArray['app'] = true;
            }
            if ($_POST['get']['live']) {
                $paramArray['live'] = true;
            }
            $_POST['addmode'] = (int)$_POST['addmode'];
            $_POST['addnum'] = (int)$_POST['addnum'];
            if ($_POST['addmode'] > 0 && $_POST['addnum'] >= 0) {
                $paramArray['addmode'] = (int)$_POST['addmode'];
                $paramArray['addmax'] = (int)$_POST['addmax'];
            } else {
                ShareUtils::endWebApi($res, WebUtils::t('您填写的奖励模式不正确,请重新填写'), MOBCENT_ERROR_DEFAULT);
            }

            $updateInfo['param'] = serialize($paramArray);
            if ($id) {
                AppbymeShareModel::updateInfo($id, $updateInfo);
            } else {
                AppbymeShareModel::insertInfo($updateInfo);
            }
        } else {
            ShareUtils::endWebApi($res, WebUtils::t('不支持该方式请求'), MOBCENT_ERROR_DEFAULT);

        }
        WebUtils::outputWebApi($res);
    }

    public function actionDel($id) {
        $key = CacheUtils::getShareActivityKey(array('id' => $id));
        Yii::app()->cache->delete($key);
        AppbymeShareModel::delActivity($id);
        AppbymeShareUserModel::del($id);
        $this->success('删除活动成功', Yii::app()->createAbsoluteUrl('admin/share/index'));
    }

    public function actionDelConfig() {
        AppbymeConfig::delShareActivityId();
        $this->success('修改成功', Yii::app()->createAbsoluteUrl('admin/share/index'));
    }

    public function actionEditConfig($id) {
        AppbymeConfig::saveShareActivityId($id);
        $this->success('修改成功', Yii::app()->createAbsoluteUrl('admin/share/index'));
    }
}