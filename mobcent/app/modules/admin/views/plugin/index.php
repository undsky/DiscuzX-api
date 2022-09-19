<!DOCTYPE html>
<html>
<head>
    <title>插件列表</title>
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
    <div class="panel-heading"><b>插件列表</b></div>
    <div class="panel-body">
 
    <div class="panel panel-default">
    <!-- Default panel contents -->
        <div class="panel-body">
             <button type="button" class="btn btn-primary" style="float:left;margin-right:20px" onclick="　window.open ('http://www.appbyme.com/mobcentPlugin')">安米应用中心</button>
        </div>
    <!-- Table -->
    <table class="table table-hover">
        <tr>
             <td></td>
            <td>插件名称</td>
            <td>简介</td>
            <td>作者</td>
            <td>版本</td>
            <td>状态</td>
        </tr>
        <?php $num=1; ?>
        <?php foreach($list as $list):
            if($list['info']){
            ?>
        <tr >
            <td> <img src="../modules/<?php echo $list['plugin_id']?>/static/appbyme_plugin.png" width="40" height="40" /></td>
            <td>
               
                <b><?php echo $list['info']['name']?></b>
                <?php if($list['plugin']['menu']){?>
                <br><a href="<?php echo Yii::app()->createAbsoluteUrl($list['plugin']['menu'])?>" style="color: #0066ff">管理</a>
                <?php }?>
            </td>
            <td><?php echo $list['info']['description']?></td>
            <td><?php echo $list['info']['author']?></td>
            <td>
                  <?php if($list['plugin']['version'] && $list['info']['version'] >$list['plugin']['version'] ){?>
                <?php echo $list['plugin']['version']?>(<font color="#ff6600">新<?php echo $list['info']['version']?></font>)
                <?php }else{?>
                <?php echo $list['info']['version']?>
                 <?php }?>
            </td>
            <td>
                 <?php if($list['plugin']['menu']){?>
                <?php if($list['plugin']['version'] && $list['info']['version'] >$list['plugin']['version'] ){?>
                 <button class="btn btn-primary btn-xs flag-btn "  onclick="document.location='<?php echo Yii::app()->createAbsoluteUrl('admin/plugin/update',array('plugin_id'=>$list['plugin_id']))?>'" >升级</button>
                <?php }else{?>
                已安装
                <?php }?>
                 <?php }else{?>
                <button class="btn btn-primary btn-xs flag-btn "  onclick="document.location='<?php echo Yii::app()->createAbsoluteUrl('admin/plugin/install',array('plugin_id'=>$list['plugin_id']))?>'">安装</button>
             <?php }?>
            </td>
        </tr>
            <?php }
            endforeach; ?>
    </table>

  

    </div>

    </div>
    <div class="panel-footer">
    </div>
</div>
</body>
