<?php

/**
 * @author  谢建平 <jianping_xie@aliyun.com>  
 * @copyright 2012-2014 Appbyme
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class IndexController extends Controller {

	public function actionIndex() {        
		echo 'welcome mobcent';
	}

	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			echo $error['message'];
		}
	}

    public function actionReturnMobileView() {
		if(stripos(strtolower(Yii::app()->request->getUserAgent()),'appbyme') === false)
		{
			header("Content-Type: text/html; charset=utf-8");
			$str = <<<ETO
			<!DOCTYPE html>
			<html lang="zh">
			<head>
				<meta charset="UTF-8">
				<title>操作成功</title>
			</head>
			<body>
				<script type="text/javascript">alert("操作成功!");window.history.go(-2)</script>
			</body>
			</html>
ETO;
			exit($str);
		}
    }
}