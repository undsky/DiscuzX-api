<!DOCTYPE html>
<html>
    <head>
        <title>多UIDIY管理</title>
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
    </head>
    <style type="text/css">
        .activ {
            width: 100px;
            height: 120px;
            float: left;
        }
    </style>
    <body>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="activ-list">
                    <div class="activ text-center">
                        <a href="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/uidiy/index"  target="main"><img src="<?php if($default['icon']){echo $default['icon'];} else{ echo $this->rootUrl . '/images/admin/module-default.png';} ?>" class="img-thumbnail"></a>
                        <div><small><?php if($default['name']){echo WebUtils::u($default['name']);}else{echo  '自定义管理';}?></small></div>
                                 <div>
                                    <a href="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/uidiy/editconfig&id=0" target="main"><small>编辑</small></a>
                                </div>
                    </div>
                    <?php if (!empty($config)): ?>
                        <?php foreach ($config as $k): ?>
                            <div class="activ text-center"> 
                                <a href="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/uidiy/index&id=<?php echo $k['id'] ?>" target="main"><img src="<?php echo $k['icon']; ?>" class="img-thumbnail"></a>
                                <div><small><?php echo WebUtils::u($k['name']) ?></small></div>
                                <div>
                                    <a href="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/uidiy/editconfig&id=<?php echo $k['id'] ?>" target="main"><small>编辑</small></a>
                                    <a href="javascript:;" onclick="delete_config('<?php echo $k['id'] ?>')"><small>删除</small></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="activ text-center">
                        <a href="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/uidiy/addconfig" target="main"><img src="<?php echo $this->rootUrl . '/images/admin/module-add.png'; ?>" class="img-rounded"></a>
                        <div><small>添加多DIY模块</small></div>
                    </div>

                </div>

            </div>
        </div>
        <script>
            function delete_config(nxxid) {

                if (nxxid == '') {
                    alert('ID有误');
                } else {
                    if (confirm('是否确定删除?此操作不可逆，删除之后将无法恢复该配置！')) {
                        //alert('选择了是');
                    } else {
                        return ;
                       // alert('选择了否');
                    }
                    var url = "<?php echo Yii::app()->createAbsoluteUrl('admin/uidiy/deleteconfig') ?>";
                    var url = url + '&id=' + nxxid;
                    $.ajax({cache: false, type: "GET", url: url, async: false, success: function (data) {
                            var data = JSON.parse(data);
                            if (data.errCode === '0') {
                                alert('删除成功');
                                var url1 = "<?php echo Yii::app()->createAbsoluteUrl('admin/uidiy/config') ?>";
                                setTimeout(function () {
                                    location.href = url1
                                }, 1)
                            } else {
                                alert('删除失败。请重试');
                            }
                        }, })
                }
            }


        </script>
    </body>