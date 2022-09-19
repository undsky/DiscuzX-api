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
         .leftnav li{
            border-top: 1px solid #fcfcfc;
            border-bottom: 1px solid #e5e5e5; 
        }
        .leftnav li a{color:#585858; font-size:13px}
        .leftnav .active{background:url(<?php echo $this->rootUrl; ?>/images/admin/menubg.png)}
        .leftnav .active a{color:#2b7dbc;font-weight: bold;}
        body{background:#f2f2f2}
    </style>
</head>
<body >
    <ul class="nav  leftnav">
        <?php
        foreach($menu as $v){
        ?>
        </li>
           <li >
            <a href="<?php echo $v['url']?>"   target="main"><?php echo $v['name']?></a>
        </li>
        <?php }?>
    </ul>

</body>
</html>
<script>
$(function(){
    $('.leftnav li').click(function(){
        $('.leftnav li').removeClass('active');
        $(this).addClass('active');
    });
});
</script>