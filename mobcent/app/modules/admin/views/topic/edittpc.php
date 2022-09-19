<!DOCTYPE html>
<html>
<head>
    <title>编辑话题</title>
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
<body>
    <div class="reward-edit">
        <div class="panel panel-default">
            <div class="panel-heading"><b>新增(编辑)话题</b></div>
            <div class="panel-body">
                <form class="form-horizontal" action="<?php echo Yii::app()->createUrl('admin/topic/tpcsub');?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="tiid" value="<?php echo $data['ti_id'];?>"/>
                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label">话题标题：</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="title" id="" required autofocus autocomplete="off" <?php if($data['ti_title']){echo 'value="'.WebUtils::u($data['ti_title']).'"';}?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label">内容：</label>

                        <div class="col-sm-10">
                            <textarea class="form-control" rows="4" style="width: 600px;" name="content"><?php if(isset($data['ti_content'])){echo WebUtils::u($data['ti_content']);} ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label">话题开始时间：</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="startTime" id="startTime" required autocomplete="off" <?php if($data['ti_starttime']){echo 'value="'.date('m/d/Y',$data['ti_starttime']).'"';}?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label">话题结束时间：</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="endtime" id="stopTime" required autocomplete="off" <?php if($data['ti_endtime']){echo 'value="'.date('m/d/Y',$data['ti_endtime']).'"';}?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label">话题封面：</label>
                        <div class="col-sm-10">
                            <input type="file" id="inputfile" name="uploadFile[]">
                        </div>
                        <p class="help-block col-sm-2">上传话题封面将会覆盖以前的封面。</p>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">提交</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="panel-footer"></div>
        </div>
    </div>
</body>
<script>
    $( "#startTime" ).datepicker();
    $( "#stopTime" ).datepicker();
</script>