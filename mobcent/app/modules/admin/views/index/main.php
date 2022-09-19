<!DOCTYPE html>
<html>
<head>
    <title>管理首页</title>
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
    <style type="text/css">
        body{
            font-size: 12px;
        }
		.page-header{ margin-top:10px}
		.topic li{ line-height:25px;}
		.topic li a{ font-size:14px}
    </style>
</head>
<body>

<div style="width:100%; height:auto; float:left">


<div style="float:left; width:400px">

<div class="panel panel-default">
    <div class="panel-heading"><b>官方公告</b></div>
    <div class="panel-body topic">
 
    <script type="text/javascript" src="http://bbs.appbyme.com/api.php?mod=js&bid=4"></script>

    </div>
    <div class="panel-footer">
    </div>
</div>

</div>
<div style="float:left; width:400px; margin-left:15px">

<div class="panel panel-default">
    <div class="panel-heading"><b>帮助中心</b></div>
    <div class="panel-body topic">
 
<script type="text/javascript" src="http://bbs.appbyme.com/api.php?mod=js&bid=3"></script>

    </div>
    <div class="panel-footer">
    </div>
</div>

</div>

</div>
<div style="width:100%; height:auto; float:left">

<div class="panel panel-default" style="width:815px">
    <div class="panel-heading"><b>最新插件</b></div>
    <div class="panel-body" id="plugin" style="margin-top:0px; height:400px; overflow:scroll; overflow-x:hidden;">


    </div>
    <div class="panel-footer">
    </div>
</div></div>
<script>
$(function(){
	  $.ajax({
        url:'<?php echo Yii::app()->createAbsoluteUrl('admin/index/getmobcentplugin')?>',
        type:'get',
        data:'',
        success:function(data){
            $('#plugin').html(data);
        }
    });
	});
</script>
</body>
