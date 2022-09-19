<?php
/**
 * 编辑/新增视图
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 *
 */
global $_G;
$extcredits = $_G['setting']['extcredits'];
if (!empty($info)) {
    $str = '编辑';
    $sub = '编 辑';
} else {
    $str = '添加';
    $sub = '添 加';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $str ?>分享推广</title>
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
        <div class="panel-heading"><b><?php echo $str ?>分享推广</b></div>
        <div class="panel-body">
            <form class="form-horizontal" onSubmit="return false" id="editForm" method="post">
                <input type="hidden" name="apphash" value="<?php echo MobcentDiscuz::getAppHashValue() ?>">
                <input type="hidden" name="id" value="<?php echo $info['id'] ?>">
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">名称：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="name" id=""
                               value="<?php echo WebUtils::u($info['name']) ?>" required autofocus autocomplete="off">
                        <span id="helpBlock" class="help-block"><small>用于后台标注</small></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">活动开始时间：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="starttime" id="startTime"
                               value="<?php if ($info) {
                                   echo date('m/d/Y', $info['starttime']);
                               } ?>" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">活动结束时间：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="endtime" id="stopTime" value="<?php if ($info) {
                            echo date('m/d/Y', $info['endtime']);
                        } ?>" placeholder="" required
                               autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">分享平台:</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="type[]"
                                   <?php if (in_array('1', $info['type'])){ ?>checked <?php } ?> value="1"> QQ
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="type[]"
                                   <?php if (in_array('2', $info['type'])){ ?>checked <?php } ?> value="2"> 空间
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="type[]"
                                   <?php if (in_array('3', $info['type'])){ ?>checked <?php } ?> value="3"> 微信好友
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="type[]"
                                   <?php if (in_array('4', $info['type'])){ ?>checked <?php } ?> value="4"> 微信朋友圈
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="type[]"
                                   <?php if (in_array('5', $info['type'])){ ?>checked <?php } ?> value="5"> 新浪微博
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="type[]"
                                   <?php if (in_array('6', $info['type'])){ ?>checked <?php } ?> value="6"> Facebook
                        </label>
                        <span id="helpBlock" class="help-block"><small>打钩分享的平台才可以获得对应积分,打包的时候需要填写对应的Appid</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">奖励积分：</label>
                    <div class="col-sm-10">
                        <select name="credit" class="form-control">
                            <?php foreach ($extcredits as $k => $s) { ?>
                                <option <?php if (!empty($info['credit'][$k])) {
                                        $creditId = $k; ?>selected=""<?php } ?>
                                        value="<?php echo $k; ?>"><?php echo WebUtils::u($s['title']) ?></option>
                            <?php } ?>
                        </select>
                        <span id="helpBlock" class="help-block"><small></small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">奖励积分数量：</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="creditnum"
                               value="<?php echo $info['credit'][$creditId] ?>" required autocomplete="off">
                        <span id="helpBlock" class="help-block">每分享一次可获得积分数量</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">奖励模式：</label>
                    <div class="col-sm-10">
                        <select name="addmode" class="form-control">
                            <option <?php if ($info['param']['addmode'] == '1'){ ?>checked <?php } ?> value="1">限制当天最多
                            </option>
                            <option <?php if ($info['param']['addmode'] == '2'){ ?>checked <?php } ?> value="2">
                                限制当次活动最多
                            </option>
                        </select>
                        <span id="helpBlock" class="help-block"><small>可设置当天或者当次最多获得.</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">最多可获得次数：</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="addmax"
                               value="<?php echo $info['param']['addmax'] ?>" required autocomplete="off">
                        <span id="helpBlock" class="help-block">和奖励模式联动,如果需要不限制留空填0,开启之后会比较跑MySQL资源</span>
                    </div>
                </div>


                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">分享规则:</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="get[portal]"
                                   <?php if ($info['param']['portal']){ ?>checked <?php } ?> value="1" id="potral"> 门户文章
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="get[topic]"
                                   <?php if ($info['param']['topic']){ ?>checked <?php } ?> value="1" id="topic"> 帖子分享
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="get[app]"
                                   <?php if ($info['param']['app']){ ?>checked <?php } ?> value="1"> 设置中分享APP
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="get[live]"
                                   <?php if ($info['param']['live']){ ?>checked <?php } ?> value="1"> 直播
                        </label>
                        <span id="helpBlock" class="help-block">如果没勾选对应的选项,分享之后将无法获得积分</span>

                    </div>
                </div>
                <div class="form-group" id="tid"
                     <?php if (!$info['param']['topic']){ ?>style="display: none" <?php } ?> >
                    <label for="" class="col-sm-2 control-label">帖子ID：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="topic[tid]"
                               value="<?php echo $info['param']['topic']['tid'] ?>" autocomplete="off">
                        <span id="helpBlock"
                              class="help-block">若设置了帖子ID,那么只有对应的帖子分享之后可以获得积分.多个帖子请用','分割.如果需要不限制留空填0</span>
                    </div>

                </div>
                <div class="form-group" id="fid"
                     <?php if (!$info['param']['topic']){ ?>style="display: none" <?php } ?>>
                    <label for="" class="col-sm-2 control-label">版块ID：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="topic[fid]"
                               value="<?php echo $info['param']['topic']['fid'] ?>" autocomplete="off">
                        <span id="helpBlock" class="help-block">若设置了版块ID,那么只有对应的版块分享之后可以获得积分.多个板块请用','分割.如果需要不限制留空填0,若版块和帖子同时填写且不为0,以帖子为准</span>
                    </div>
                </div>
                <div class="form-group" id="aid"
                     <?php if (!$info['param']['portal']){ ?>style="display: none" <?php } ?>>
                    <label for="" class="col-sm-2 control-label">文章ID：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="portal[aid]"
                               value="<?php echo $info['param']['portal']['aid'] ?>" autocomplete="off">
                        <span id="helpBlock"
                              class="help-block">若设置了文章,那么只有对应的文章分享之后可以获得积分.多个板块请用','分割.如果需要不限制留空填0</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary" onclick="toEdit()"><?php echo $sub ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="panel-footer"></div>
    </div>
</div>
</body>
<script>
    $("#startTime").datepicker();
    $("#stopTime").datepicker();
    $(function () {
        $('#topic').click(function () {
            if ($('input[name="get[topic]"]').prop("checked")) {
                $("#fid").css('display', 'block');
                $("#tid").css('display', 'block');
            } else {
                $("#fid").css('display', 'none');
                $("#tid").css('display', 'none');
            }
        });
        $('#potral').click(function () {
            if ($('input[name="get[portal]"]').prop("checked")) {
                $("#aid").css('display', 'block');
            } else {
                $("#aid").css('display', 'none');
            }
        });
    });

    function toEdit() {
        var url = "<?php echo Yii::app()->createAbsoluteUrl('admin/share/add') ?>";
        $.ajax({
            cache: false,
            type: "POST",
            url: url,
            data: $("#editForm").serialize(),
            async: false,
            success: function (data) {
                var data1 = JSON.parse(data);
                if (data1.errCode !== 0) {
                    alert(data1.errMsg);
                    
                } else {
                    <?php if($info){?>
                    alert('修改成功');
                    <?php }else{?>
                    alert('新增成功');
                    <?php } ?>
                    window.location.href = "<?php echo Yii::app()->createAbsoluteUrl('admin/share/index') ?>";
                }
            },
        });
    }

</script>