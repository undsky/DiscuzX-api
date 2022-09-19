<!DOCTYPE html>
<html>
<head>
    <title>微生活管理</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-3.2.0.min.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-theme-3.2.0.min.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-switch-3.2.1.min.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/appbyme-admin-wshdiy.css">
    <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/appbyme-admin-uidiy/component-mobile.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $this->rootUrl; ?>/css/appbyme-admin-uidiy/module-custom.css">
    <script src="<?php echo $this->rootUrl; ?>/js/jquery-2.0.3.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/bootstrap-3.2.0.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/jquery-ui-1.11.2.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/bootstrap-switch-3.2.1.min.js"></script>
    <script src="<?php echo $this->rootUrl; ?>/js/admin/wshdiy.js"></script>
    <style type="text/css">
        .wshdiy-mobile {
            width: 350px;
            height: 700px;
            background:url("<?php echo $this->rootUrl; ?>/images/admin/mobile.png") no-repeat right top;
            background-size: 350px 700px;
            text-align: center;
            /*border: 1px solid green;*/
            position: relative;
        }
		.cleft{width:20px; height:20px; position:absolute ;top:15px;left:160px; cursor:pointer}
	    .op{background:url(<?php echo $this->rootUrl; ?>/images/admin/right.png) no-repeat;}
		.ce{background:url(<?php echo $this->rootUrl; ?>/images/admin/left.png) no-repeat;}
    </style>
</head>
<?php global $_G; ?>
<body>
        <nav class="navbar navbar-default navbar-static-top" role="navigation">
      <div class="container">
       <span  class="cleft op"></span>
        <div class="navbar-header" style="margin-right:50px">
          <a class="navbar-brand" href="http://www.appbyme.com" target="_blank" style="background:url(<?php echo $this->rootUrl; ?>/images/admin/login.png);width:140px;height:50px;"></a>
        
        
        </div>
       
          <div id="navbar" class="navbar-collapse collapse" >
          <ul class="nav navbar-nav nav-list nnn">
              <li class="active"><a href="<?php echo Yii::app()->createAbsoluteUrl('admin/index/left')?>" target="left">管理首页</a></li>
            <li><a href="<?php echo Yii::app()->createAbsoluteUrl('admin/index/left',array('act'=>1))?>" target="left">DIY自定义</a></li>
            <li><a href="<?php echo Yii::app()->createAbsoluteUrl('admin/index/left',array('act'=>2))?>"  target="left">插件应用</a></li>
            <li><a href="<?php echo Yii::app()->createAbsoluteUrl('admin/index/left',array('act'=>3))?>" target="left">权限管理</a></li>
            <li><a href="<?php echo Yii::app()->createAbsoluteUrl('admin/index/left',array('act'=>4))?>" target="left">帮助中心</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="." class="dropdown-toggle" data-toggle="dropdown"><?php echo WebUtils::u($_G['username']); ?> </a>
            </li>
            <li><a href="<?php echo Yii::app()->createAbsoluteUrl('admin/index/logout'); ?>" target="_top">退出</a></li>
          </ul>
        </div>
      </div>
    </nav>

</body>
<script>
$(function(){
    $('.nnn li').click(function(){
        $('.nnn li').removeClass('active');
        $(this).addClass('active');
		$('.cleft').addClass('op').removeClass('ce');
		window.parent.document.getElementsByTagName("frameset")[1].cols="190,*"; 
    });
    $('.cleft').click(function(){
		if($(this).hasClass('op')){
			$(this).addClass('ce').removeClass('op');
       		window.parent.document.getElementsByTagName("frameset")[1].cols="0,*"; 
		}else{
			$(this).addClass('op').removeClass('ce');
			window.parent.document.getElementsByTagName("frameset")[1].cols="190,*"; 
		}
    });
    
});
</script>