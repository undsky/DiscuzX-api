<!DOCTYPE html>
<html>
<head>
    <title>发布人管理</title>
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
    <div class="panel-heading"><b>发布人管理</b></div>
    <div class="panel-body">
        <form class="form-inline" action="<?php echo Yii::app()->createUrl('admin/topic/puttpcmag')?>" method="post">
                <div class="form-group">
                    <label class="radio-inline">
                    <input type="radio" name="type" id="inlineRadio1" <?php if($type == 'all' || $type == ''){echo 'checked="checked"';}?> value="all"> 全部
                </label>
                <label class="radio-inline">
                    <input type="radio" name="type" id="inlineRadio2" value="0" <?php if($type == '0'){echo 'checked="checked"';}?>>申请
                </label>
                <label class="radio-inline">
                    <input type="radio" name="type" id="inlineRadio2" value="1" <?php if($type == '1'){echo 'checked="checked"';}?>>通过
                </label>
                <label class="radio-inline">
                    <input type="radio" name="type" id="inlineRadio3" value="2" <?php if($type == '2'){echo 'checked="checked"';}?>>冻结
                </label>
                &nbsp;&nbsp;
                <div class="input-group">
                    <div class="input-group-addon">查找发布人</div>
                    <input type="text" class="form-control" id="exampleInputAmount" placeholder="填入发布者姓名" name="search">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">SEARCH</button>
        </form>
    <br>

    <div class="panel panel-default">
    <!-- Table -->
    <table class="table table-hover">
        <tr>
            <td>发布人ID</td>
            <td>发布人姓名</td>
            <td>发布人状态</td>
            <td>操作</td>
        </tr>
        <?php if(empty($data)){?>
            <tr>
                <td colspan="4">没有数据</td>
            </tr>
        <?php }else{?>
            <?php foreach ($data as $v):?>
                <tr>
                    <td><?php echo $v['uid'];?></td>
                    <td><?php echo WebUtils::u($v['username']);?></td>
                    <td><?php echo $staarr[$v['uvalue']];?></td>
                    <td>
                        <?php if($v['uvalue'] == 0){echo '<a href="javascript:void(0);" onclick="editd(0,'.$v['uid'].',this)">通过申请</a>';}if($v['uvalue'] == 1){echo '<a href="javascript:void(0);" onclick="editd(1,'.$v['uid'].',this)">冻结用户</a>';}if($v['uvalue'] ==2){echo '<a href="javascript:void(0);" onclick="editd(2,'.$v['uid'].',this)">解冻用户</a>';}?>
                        &nbsp;|&nbsp;
                        <a target="_blank" href="<?php echo $baseurl; ?>/home.php?mod=space&uid=<?php echo $v['uid'];?>&do=profile">用户详情</a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php }?>
    </table>

    <nav>
        <ul class="pager">
            <li>
                <a <?php if($nowpage>1){echo 'href="'.Yii::app()->createUrl('admin/topic/puttpcmag').'&page='.($nowpage-1).'&type='.$type.'"';}?>>上一页</a>
            </li>
            <li><a <?php if($nowpage<$totpage){echo 'href="'.Yii::app()->createUrl('admin/topic/puttpcmag').'&page='.($nowpage+1).'&type='.$type.'"';}?>>下一页</a></li>
        </ul>
    </nav>

    </div>

    </div>
    <div class="panel-footer">
        一共<strong><?php echo $count; ?></strong>条数据 | 
        当前是第<strong><?php echo $nowpage; ?></strong>页 | 
        共<strong><?php echo $totpage; ?></strong>页

    </div>
</div>
</body>
<script type="text/javascript">
	function editd(status,uid,self){
		$.get('<?php echo Yii::app()->createUrl('admin/topic/puttpcact');?>'+'&uid='+uid+'&status='+status,function(data){
			if(data == 'suc'){
				switch(status){
					case 0:
						$(self).attr('onclick','editd(1,'+uid+',this)');
						$(self).html('冻结用户');
						$(self).parent().prevAll().eq(0).html('已通过');
						break;
					case 1:
						$(self).attr('onclick','editd(2,'+uid+',this)');
						$(self).html('解冻用户');
						$(self).parent().prevAll().eq(0).html('已冻结');
						break;
					case 2:
						$(self).attr('onclick','editd(1,'+uid+',this)');
						$(self).parent().prevAll().eq(0).html('已解冻');
						$(self).html('冻结用户');
						break;
				}
				
			}
		})
	}
</script>