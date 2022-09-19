<?php
/**
 * Push  view
 *
 * @author 耐小心<nxx@yytest.cn>
 * @copyright 2012-2016 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>推送</title>
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
                <div class="panel-heading"><b>推送消息</b></div>
                <div class="panel-body">
                    <form class="form-horizontal" amethod="post" id="codeform" onSubmit="return false"  >
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">推送App：</label>
                            <div class="col-sm-10">
                                <select name="forumKey" class="form-control" >
                                    <?php foreach ($info as $k => $s) { ?>
                                        <option value="<?php echo $s['forumKey']; ?>"  ><?php echo $s['name'] ?></option>
                                    <?php } ?>
                                </select>
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">推送类型：</label>
                            <div class="col-sm-10" id="pushType">
                                <input name="pushType" type="radio" value="topic"checked="checked"/>帖子
                                <input  name="pushType" type="radio" value="article"/>文章
                                <input  name="pushType" type="radio" value="url"/>URL
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">IOS标题：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="iosTitle" id="iosTitle"   autofocus autocomplete="off">
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Android标题：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="androidTitle" id="androidTitle" autofocus autocomplete="off">
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Android内容：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="content" id="content" autofocus autocomplete="off">
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group" id="topicIdDiv" >
                            <label for="" class="col-sm-2 control-label" id="topicIdTitle" >帖子ID：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="topicId" id="topicId" autofocus autocomplete="off">
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group"  style="display: none" id="pushUrlDiv">
                            <label for="" class="col-sm-2 control-label">推送URL：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="pushUrl" id="pushUrl" autofocus autocomplete="off">
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">手机类型：</label>
                            <div class="col-sm-10">
                                <input name="phoneType" type="radio" value="all" checked="checked"/>全部
                                <input name="phoneType" type="radio" value="android"/>安卓
                                <input name="phoneType" type="radio" value="ios"/>IOS
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">推送类型：</label>
                            <div class="col-sm-10">
                                <input name="isTest" type="radio" value="0" checked="checked"/>正式
                                <input name="isTest" type="radio" value="1"/>测试
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group" style="display: none" id="dzUserIdDiv">
                            <label for="" class="col-sm-2 control-label">测试UID：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="dzUserId" id="dzUserId" autofocus autocomplete="off">
                                <span id="helpBlock" class="help-block"><small></small></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-primary" onclick="doPush()">推送</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="panel-footer"></div>
            </div>
        </div>
    </body>
    <script type="text/javascript">
        //推送
        $(function () {
            $('[name=pushType]').click(function () {
                var pushType = $("input[name='pushType']:checked").val();
                if (pushType == 'topic') {
                    document.getElementById("pushUrlDiv").style.display = 'none';
                    document.getElementById("topicIdDiv").style.display = 'block';
                    document.getElementById("topicIdTitle").innerHTML = '帖子ID：';
                } else if (pushType == 'article') {
                    document.getElementById("pushUrlDiv").style.display = 'none';
                    document.getElementById("topicIdDiv").style.display = 'block';
                    document.getElementById("topicIdTitle").innerHTML = '文章ID：';
                } else {
                    document.getElementById("topicIdDiv").style.display = 'none';
                    document.getElementById("pushUrlDiv").style.display = 'block';
                }
            });
            $('[name=isTest]').click(function () {
                var isTest = $("input[name='isTest']:checked").val();
                if (isTest == '1') {
                    document.getElementById("dzUserIdDiv").style.display = 'block';
                } else {
                    document.getElementById("dzUserIdDiv").style.display = 'none';
                }
            });
        });
        function doPush() {
            var url = "<?php echo Yii::app()->createAbsoluteUrl('admin/push/pushapi') ?>";
            $.ajax({cache: false, type: "POST", url: url, data: $("#codeform").serialize(), async: false, success: function (data) {
                    var data1 = JSON.parse(data);
                    if (data1.rs === 0) {
                        alert(data1.head.errInfo);
                        
                    } else {
                        alert('推送成功');
                        window.location.reload();
                    }
                }, });
        }
    </script>