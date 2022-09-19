<!DOCTYPE html>
<html>
<head>
    <title>权限设置</title>
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
    </style>
</head>
<body>
<div class="panel panel-default">
    <div class="panel-heading"><b>权限设置</b></div>
    <div class="panel-body">
 
    <div class="panel panel-default">
    <!-- Default panel contents -->
        <div class="panel-body">
            <font color="#FF0000"><?php echo WebUtils::u($admin)?></font> 为总管理权限，插件后台设置管理员时，第一个为总管理员,网站创始人也具有最高权限!
        </div>
    <!-- Table -->
   
  <form id="form1" name="form1" method="post"  action="<?php echo Yii::app()->createAbsoluteUrl('admin/auth/update')?>">
<table class="table table-hover">
        <tr>
             <td width="15%">管理员</td>
            <td width="85%">权限</td>
        </tr>
        <?php
        foreach($allowUsers as $key=>$v){
        ?>
         <tr>
             <td width="15%"><?php echo WebUtils::u($v['name'])?>
            </td>
            <td width="85%"><?php
            $allow = explode(',',$v['allow'] );
            foreach($auth as $avalue){?> 
            
              <input
                  <?php foreach($allow as $vvv){?>
                  <?php if($vvv==$avalue['id']){?> checked="checked"<?php }?>
                  <?php }?>
                  type="checkbox" name="auth<?php echo $key?>[]" value="<?php echo $avalue['id']?>" id="auth_<?php echo $key?>_<?php echo $avalue['id']?>">
               <?php echo $avalue['name']?>&nbsp;
                <?php
                
            }
              unset($allow);   
            ?>
           
           </td>
        </tr>
        <?php }?>
        <tr>
             <td colspan="2">
                 <?php if(count($allowUsers)>0){?>
                 <button type="submit" class="btn btn-primary">保 存</button>
                 <?php }?>
             </td>
        </tr>
    </table>
   </form>
  

    </div>

    </div>
    <div class="panel-footer">
    </div>
</div>
</body>
