<?php
/**
 *Webapp 管理控制器
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */
?>

<!DOCTYPE html>
<html>
<head>
    <title>设置WebApp</title>
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
        <div class="panel-heading"><b>设置WebApp</b></div>
        <div class="panel-body">
            <form class="form-horizontal" method="post">
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">是否开启：</label>

                    <div class="col-sm-10">
                        <input name="open" type="radio" value="1" <?php
                        if ($config['open'] == 1) {
                            echo 'checked';
                        }
                        ?>/>关闭
                        <input name="open" type="radio" value="2" <?php if ($config['open'] != 1) { ?> checked <?php } ?>/>开启

                        <span id="helpBlock" class="help-block"><small>开启之后分享出去都是高仿微信样式</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">微信公众号Appid：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="wxappid" id=""  autofocus autocomplete="off" value="<?php echo $config['wxappid']?>">
                        <span id="helpBlock" class="help-block"><small>用于WebApp微信登录</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">微信公众号AppSecret：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="wxappsecret" id=""  autofocus autocomplete="off" value="<?php echo $config['wxappsecret']?>">
                        <span id="helpBlock" class="help-block"><small>用于WebApp微信登录</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">提 交</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
