<!DOCTYPE html>
<html>
    <head>
        <title>添加邀请注册</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-3.2.0.min.css">
        <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-theme-3.2.0.min.css">
        <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/bootstrap-switch-3.2.1.min.css">
        <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/jquery-ui.min.css">
        <link rel="stylesheet" href="<?php echo $this->rootUrl; ?>/css/activity/tuiguang.css">
        <script src="<?php echo $this->rootUrl; ?>/js/jquery-2.0.3.min.js"></script>
        <script src="<?php echo $this->rootUrl; ?>/js/bootstrap-3.2.0.min.js"></script>
        <script src="<?php echo $this->rootUrl; ?>/js/jquery-ui-1.11.2.min.js"></script>
        <script src="<?php echo $this->rootUrl; ?>/js/bootstrap-switch-3.2.1.min.js"></script>
    </head>
    <style type="text/css">
        .tips {
            font-weight: bold;
        }
    </style>
    <body>
        <div class="reward-edit">
            <div class="panel panel-default">
                <div class="panel-heading"><b></b></div>
                <div class="panel-body">
                    <form class="form-horizontal" id="form" action ="<?php echo Yii::app()->createAbsoluteUrl('admin/uidiy/editconfig', array('id' => $config['id'])) ?>" method="post" enctype="multipart/form-data". > 
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">模块名称：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="name" id="name" required autofocus autocomplete="off" value="<?PHP echo WebUtils::u($config['name']) ?>">                
                                <span id="helpBlock" class="help-block"><small>模块名称 必填</small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Icon：</label>
                            <div class="col-sm-10">
                                <a id="tip_upload" href="javascript:;" class="tips"onclick="nxxupload();">上传文件</a>
                                <a id="tip_text" href="javascript:;" onclick="nxxtext();">填写URL</a>
                                <input type="text" class="form-control" name="icon_text" style="display: none" id="icon_text" value="<?php echo $config['icon'] ?>" >
                                <input type="file" class="form-control" name="icon_file" style="display:done" id="icon_file">
                                <span id="helpBlock" class="help-block"><small>大小180*180 Jpg/Png文件</small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">当前图标：</label>
                            <div class="col-sm-10">
                                <img src="<?php echo $config['icon'] ?>" class="img-thumbnail" style="width: 70px;height: 70px;">
                            </div>
                        </div>  
                        <?php if (!empty($config['id'])) { ?>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">是否启用：</label>
                                <div class="col-sm-10">
                                    <select name="status" class="form-control" >
                                        <option value="1" <?php if ($config['status'] != '2') { ?>selected=""<?php } ?>>是</option>
                                        <option value="2" <?php if ($config['status'] == '2') { ?>selected=""<?php } ?> >否</option>
                                    </select>
                                    <span id="helpBlock" class="help-block"><small>默认开启。如果关闭将不会在APP中显示</small></span>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit"class="btn btn-primary">提 交</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="panel-footer"></div>
            </div>
        </div>
    </body>
    <script>
        function nxxupload() {
            document.getElementById("tip_text").className = '';
            document.getElementById("tip_upload").className = 'tips';
            document.getElementById("icon_text").style.display = 'none';
            document.getElementById("icon_file").style.display = '';
        }
        function nxxtext() {
            document.getElementById("tip_upload").className = '';
            document.getElementById("tip_text").className = 'tips';
            document.getElementById("icon_text").style.display = '';
            document.getElementById("icon_file").style.display = 'none';

        }
    </script>