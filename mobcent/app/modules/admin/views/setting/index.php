<?php
/**
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */

?>
<!DOCTYPE html>
<html>
<head>
    <title>基础设置</title>
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
        <div class="panel-heading"><b>基础设置</b></div>
        <div class="panel-body">
            <form class="form-horizontal" action="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/setting/index" method="post">
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">隐私保护协议：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="reg" id=""  autofocus autocomplete="off" value="<?php echo $info['reg']?>">
                        <span id="helpBlock" class="help-block"><small>请填写连接,在客户端注册页面显示</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">注册协议：</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="registration" id=""  autofocus autocomplete="off" value="<?php echo $info['registration']?>">
                        <span id="helpBlock" class="help-block"><small>请填写连接,在客户端注册页面显示</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">是否开启邮箱验证：</label>
                    <div class="col-sm-10">
                        <input name="email" type="radio" value="1" <?php
                        if ($info['email'] == 1) {
                            echo 'checked';
                        }
                        ?>/>开启
                        <input name="email" type="radio" value="0" <?php if ($info['email'] != 1) { ?> checked <?php } ?>/>关闭
                        <span id="helpBlock" class="help-block"><small>当同时开启邮箱注册和本选项之后,客户端注册需要邮箱验证</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">是否回帖楼主可见：</label>
                    <div class="col-sm-10">
                        <input name="reply" type="radio" value="1" <?php
                        if ($info['reply'] == 1) {
                            echo 'checked';
                        }
                        ?>/>开启
                        <input name="reply" type="radio" value="0" <?php if ($info['reply'] != 1) { ?> checked <?php } ?>/>关闭
                        <span id="helpBlock" class="help-block"><small>开启之后,发帖时可选是否回帖楼主可见</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">搜索:</label>
                    <div class="col-sm-10">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="search[]"
                                   <?php if (in_array('topic', $info['search'])){ ?>checked <?php } ?> value="topic"> 帖子
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="search[]"
                                   <?php if (in_array('portal', $info['search'])){ ?>checked <?php } ?> value="portal"> 文章
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="search[]"
                                   <?php if (in_array('user', $info['search'])){ ?>checked <?php } ?> value="user">用户
                        </label>
                        <span id="helpBlock" class="help-block"><small>选择之后,可搜索对应的内容.不选即选择全部</small></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-sm-2 control-label">是否使用QQSDK登录：</label>
                    <div class="col-sm-10">
                        <input name="qqsdk" type="radio" value="1" <?php
                        if ($info['qqsdk'] == 1) {
                            echo 'checked';
                        }
                        ?>/>开启
                        <input name="qqsdk" type="radio" value="0" <?php if ($info['qqsdk'] != 1) { ?> checked <?php } ?>/>关闭
                        <span id="helpBlock" class="help-block"><small>当开启之后,QQ登录将通过直接唤起APP.将于PC的QQ登录不互通绑定关系</small></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary">提 交</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="panel-footer"></div>
    </div>
</div>
</body>
