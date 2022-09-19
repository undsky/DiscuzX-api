<!DOCTYPE html>
<html>
<head>
    <title>话题管理</title>
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
    <div class="panel-heading"><b>话题管理</b></div>
    <div class="panel-body">
        <form class="form-inline">
            <div class="input-group" id="fidonediv">
                <div class="input-group-addon">绑定板块</div>
                <select class="form-control" id="fidone">
                    <?php foreach ($finfo as $v){?>
                        <option value="<?php echo $v['fid'];?>"><?php echo $v['name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="input-group" id="fidtwodiv">
                <select class="form-control" id="fidtwo">
                    <?php foreach ($fstainfo as $v){?>
                        <option value="<?php echo $v['fid'];?>"><?php echo $v['name'];?></option>
                    <?php }?>
                </select>
            </div>
            <div class="input-group" id="fidthreediv">

            </div>
            <button type="button" class="btn btn-primary" id="bindfid">绑定</button>
            <label classs="radio-inline">当前绑定板块：<?php echo $nowfname['name'];?></label>
            <div class="input-group">
                <select class="form-control" id="bindverify">
                    <option value="0">--未定义--</option>
                    <?php foreach ($verify as $k => $v){?>
                        <?php if($v['available'] == 1){?>
                            <option value="<?php echo $k;?>" <?php if($nowverify['cvalue'] == $k){echo 'selected';}?>><?php echo $v['title'];?></option>
                        <?php }?>
                    <?php }?>
                </select>
            </div>
            <button type="button" class="btn btn-primary" id="bindverifybutton">绑定认证</button>
            <label classs="radio-inline" style="color: red;">只有绑定了认证，并且用户通过该认证才能发布话题</label>
            <div class="input-group">
                <div class="input-group-addon">发表帖子是否显示标题</div>
                <select class="form-control" id="setTitle">
                    <option value="0" <?php if($nowsettitle == 0){echo 'selected';}?>>显示</option>
                    <option value="1" <?php if($nowsettitle == 1){echo 'selected';}?>>隐藏</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" id="setTitlebutton">修改</button>
        </form>
        <hr/>
        <form class="form-inline" action="<?php echo Yii::app()->createUrl('admin/topic/tpcmag')?>" method="post">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">搜索话题</div>
                    <input type="text" class="form-control" id="exampleInputAmount" placeholder="填入话题名/发布者姓名" name="search" <?php if($search){echo 'value="'.$search.'"';}?>>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">SEARCH</button>
            <div class="form-group pull-right">
                <div class="input-group">
                    <button type="button" class="btn btn-primary" id="addtpc">新增话题</button>
                </div>
            </div>
        </form>
    <br>

    <div class="panel panel-default">
    <!-- Table -->
    <table class="table table-hover">
        <tr>
            <td>话题ID</td>
            <td>话题标题</td>
            <td>话题开始时间</td>
            <td>话题结束时间</td>
            <td>话题帖子数</td>
            <td>话题创建者</td>
            <td>操作</td>
        </tr>
        <?php if(empty($data)){?>
            <tr>
                <td colspan="7">没有数据</td>
            </tr>
        <?php }else{?>
            <?php foreach ($data as $v):?>
                <tr>
                    <td><?php echo $v['ti_id'];?></td>
                    <td><?php echo WebUtils::u($v['ti_title']);?></td>
                    <td><?php echo date('Y-m-d H:i:s',$v['ti_starttime']);?></td>
                    <td><?php echo date('Y-m-d H:i:s',$v['ti_endtime']);?></td>
                    <td><?php echo $v['ti_topiccount'];?></td>
                    <td><?php echo WebUtils::u($v['ti_authorname']);?></td>
                    <td><a href="<?php echo Yii::app()->createUrl('admin/topic/edittpc').'&tiid='.$v['ti_id'];?>">编辑</a>&nbsp;/&nbsp;<a onclick="del(<?php echo $v['ti_id'];?>,this)" href="javascript:void(0);">删除</a></td>
                </tr>
            <?php endforeach;?>
        <?php }?>
    </table>

    <nav>
        <ul class="pager">
            <li>
                <a <?php if($nowpage>1){echo 'href="'.Yii::app()->createUrl('admin/topic/tpcmag').'&page='.($nowpage-1).'&search='.$search.'"';}?>>上一页</a>
            </li>
            <li><a <?php if($nowpage<$totpage){echo 'href="'.Yii::app()->createUrl('admin/topic/tpcmag').'&page='.($nowpage+1).'&search='.$search.'"';}?>>下一页</a></li>
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
	function del(tiid,self){
		$.get('<?php echo Yii::app()->createUrl('admin/topic/deltpc');?>'+'&tiid='+tiid,function(data){
			if(data == 'suc'){
				$(self).parent().parent().remove();
				alert('删除成功！');
			}
		})
	}
	$(function(){
		$('#fidone').change(function(){
			fid = $('#fidone').find("option:selected").val();
			$.get('<?php echo Yii::app()->createUrl('admin/topic/getfinfo');?>'+'&fid='+fid+'&case=0',function(data){
				if(data != 'fail'){
					$('#fidtwodiv').html(data);
				}
			})
		})
		$('#fidtwodiv').on('change','#fidtwo',function(){
			fid = $('#fidtwo').find("option:selected").val();
			$.get('<?php echo Yii::app()->createUrl('admin/topic/getfinfo');?>'+'&fid='+fid+'&case=1',function(data){
				if(data != 'fail'){
					$('#fidthreediv').html(data);
				}
			})
		})
		$('#bindfid').click(function(){
			fid = $('#fidthree').find('option:selected').val();
			if(!fid || fid == '0'){
				fid = $('#fidtwo').find('option:selected').val();
			}
			$.get('<?php echo Yii::app()->createUrl('admin/topic/bindfid');?>'+'&fid='+fid,function(data){
				if(data == 'suc'){
					alert('更新成功');
				}else{
                    alert('更新失败');
                }
			})
		});
        $('#addtpc').click(function(){
            window.location.href = '<?php echo Yii::app()->createUrl('admin/topic/edittpc');?>';
        });
        $('#bindverifybutton').click(function(){
            var verifydata = $('#bindverify').find('option:selected').val();
            $.get('<?php echo Yii::app()->createUrl('admin/topic/bindverify');?>'+'&verify='+verifydata,function(data){
                if(data == 'suc'){
                    alert('更新成功');
                }else{
                    alert('更新失败');
                }
            })
        });
        $('#setTitlebutton').click(function(){
            var setTitle = $('#setTitle').find('option:selected').val();
            $.get('<?php echo Yii::app()->createUrl('admin/topic/settitle');?>'+'&setTitle='+setTitle,function(data){
                if(data == 'suc'){
                    alert('更新成功');
                }else{
                    alert('更新失败');
                }
            })
        });
	})
</script>