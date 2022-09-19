<!DOCTYPE html>
<html>
<head>
    <title>话题管理主页</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-3.2.0.min.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-theme-3.2.0.min.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-switch-3.2.1.min.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/activity/tuiguang.css">
    <script src="<?php echo $this->rootUrl; ?>/js/jquery-2.0.3.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/bootstrap-3.2.0.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/jquery-ui-1.11.2.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/bootstrap-switch-3.2.1.min.js"></script>
</head>
<body>

<div class="panel panel-default">
    <div class="panel-heading"><b>话题管理</b></div>
    <div class="panel-body">
        <div class="activ-list">
            <div class="activ text-center">
                <a href="<?php echo Yii::app()->createUrl('admin/topic/tpcmag');?>" target="main"><img src="<?php echo $this->rootUrl.'/images/admin/topic/topic.jpg'; ?>" class="img-rounded"></a>
                <div><small>话题管理</small></div>
            </div>
            <div class="activ text-center">
                <a href="<?php echo Yii::app()->createUrl('admin/topic/puttpcmag');?>" target="main"><img src="<?php echo $this->rootUrl.'/images/admin/topic/topicman.png'; ?>" class="img-rounded"></a>
                <div><small>话题发布人管理</small></div>
            </div>
            <div class="activ text-center">
                <a href="<?php echo Yii::app()->createUrl('admin/topic/tpctmag');?>" target="main"><img src="<?php echo $this->rootUrl.'/images/admin/topic/tiezi.jpg'; ?>" class="img-rounded"></a>
                <div><small>话题帖子管理</small></div>
            </div>
            
        </div>

    </div>
    <div class="panel-footer"></div>
</div>
</body>
</html>
