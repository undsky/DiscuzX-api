<?php
/**
 * Share Index Views
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
        body {
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="panel panel-default">
    <div class="panel-heading"><b>分享列表</b></div>
    <div class="panel-body">
        <button type="button" class="btn btn-primary" style="margin-bottom:10px"
                onclick="document.location='<?php echo Yii::app()->createAbsoluteUrl('admin/share/edit') ?>'">添加分享
        </button>
        <div class="panel panel-default">
            <table class="table table-hover">
                <tr>
                    <td>序号</td>
                    <td>标题</td>
                    <td>分享平台</td>
                    <td>分享内容</td>
                    <td>开始时间</td>
                    <td>结束时间</td>
                    <td>当前参加次数总和</td>
                    <td>操作</td>
                </tr>
                <?php $num = 1; ?>
                <?php foreach ($info as $list): ?>
                    <tr>
                        <td><?php echo $list['id']; ?></td>
                        <td><?php echo WebUtils::u($list['name']); ?></td>
                        <td><?php echo ShareUtils::getCountShareType($list['type']) ?></td>
                        <td><?php echo ShareUtils::getShowType($list['param']) ?></td>
                        <td><?php echo WebUtils::u(dgmdate($list['starttime'], 'd')) ?></td>
                        <td><?php echo WebUtils::u(dgmdate($list['endtime'], 'd')) ?></td>
                        <td><?php echo $list['count'] ?></td>
                        <td>
                            <a href="<?php echo Yii::app()->createAbsoluteUrl('admin/share/list', array('id' => $list['id'])) ?>">记录</a>&nbsp;&nbsp;
                            <a href="<?php echo Yii::app()->createAbsoluteUrl('admin/share/edit', array('id' => $list['id'])) ?>">编辑</a>&nbsp;&nbsp;
                            <a class="del" href="<?php echo Yii::app()->createAbsoluteUrl('admin/share/del', array('id' => $list['id'])) ?>">删除</a>
                            <?php if ($list['id'] == $configId) { ?>
                                <a  href="<?php echo Yii::app()->createAbsoluteUrl('admin/share/delConfig', array('id' => $list['id'])) ?>">取消默认</a>
                            <?php } else { ?>
                                <a  href="<?php echo Yii::app()->createAbsoluteUrl('admin/share/editconfig', array('id' => $list['id'])) ?>">设为默认</a>
                            <?php } ?>
                        </td>
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
<script type="text/javascript">

    $(function(){
        $('.del').click(function(){
            if(!confirm("删除活动将会清空所有的关于这个活动数据")){
                return false;
            }
        });
    })
 </script>
</body>
