<?php
/**
 *
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 *
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>分享列表</title>
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
    <div class="panel-heading"><b>分享列表</b></div>
    <div class="panel-body">
        <div class="panel panel-default">
            <table class="table table-hover">
                <tr>
                    <td>序号</td>
                    <td>UID</td>
                    <td>用户名</td>
                    <td>分享时间</td>
                    <td>分享平台</td>
                    <td>分享内容关联ID</td>
                    <td>分享来源</td>
                </tr>
                <?php $num=1; ?>
                <?php foreach($lists as $list): ?>
                    <tr >
                        <td><?php echo $list['id']; ?></td>
                        <td><?php echo $list['uid'];?></td>
                        <td><?php echo WebUtils::u($list['username']);?></td>
                        <td><?php echo dgmdate($list['time']);?></td>
                        <td><?php echo $list['type_text'];?></td>
                        <td><?php echo ShareUtils::getShowFxMsg($list['param'],$list['form']);?></td>
                        <td><?php echo $list['form_text'];?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <nav>
                <ul class="pager">
                    <?php echo WebUtils::u($multi); ?>
                </ul>
            </nav>

        </div>

    </div>
</div>
</body>

