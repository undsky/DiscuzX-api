<?php 
require(dirname(__FILE__) .'/../common/header.php');
?>
<script src="<?php echo Yii::app()->baseUrl . '/js/jquery-2.0.3.min.js' ?>"></script>
<div class="zhiding">
<form method="post" action="<?php echo $formUrl; ?>">
<table cellpadding="0" cellspacing="0" class="fwin" width="100%">
    <tr>
        <td class="m_c">
            <div class="tm_c">
                <h3 class="flb">
                    <?php echo WebUtils::lp('topic_topicAdmin_title')?><!-- 选择了 1 篇帖子 -->
                </h3>
                <ul class="cell-group">
                    <?php  if ($action == 'delete') { ?>
                    <div class="tplw">
                        <ul class="llst">
                            <li><p><?php echo WebUtils::lp('topic_topicAdmin_delete_ask')?><!-- 您确认要 <strong>删除</strong> 选择的帖子么? --></p></li>
                        </ul>
                    </div>
                    <?php } else { ?>
                        <?php  if ($action == 'top') {?>
                        <li class="cell cell-select">
                            <label class="labeltxt" for="sticklevel"><?php echo WebUtils::lp('topic_topicAdmin_top_title')?><!-- 置顶 --></label>
                            <select class="ps select" name="sticklevel" id="sticklevel">
                            <?php if($_G['forum']['status'] != 3) { ?>
                            <option value="0"><?php echo WebUtils::lp('topic_topicAdmin_top_none')?><!-- 无 --></option>
                            <option value="1" <?php echo $stickcheck['1'];?>><?php echo WebUtils::u($_G['setting']['threadsticky']['2']);?></option>
                            <?php if($_G['group']['allowstickthread'] >= 2) { ?>
                            <option value="2" <?php echo $stickcheck['2'];?>><?php echo WebUtils::u($_G['setting']['threadsticky']['1']);?></option>
                            <?php if($_G['group']['allowstickthread'] == 3) { ?>
                            <option value="3" <?php echo $stickcheck['3'];?>><?php echo WebUtils::u($_G['setting']['threadsticky']['0']);?></option>
                            <?php } } } else { ?>
                            <option value="0"><?php echo WebUtils::lp('topic_topicAdmin_top_no')?><!-- 否 -->&nbsp;</option>
                            <option value="1" <?php echo $stickcheck['1'];?>><?php echo WebUtils::lp('topic_topicAdmin_top_yes')?><!-- 是 -->&nbsp;</option>
                            <?php } ?>
                            </select>
                            <p class="hasd" style="display:none">
                                <label for="expirationstick" class="labeltxt"><?php echo WebUtils::lp('topic_topicAdmin_top_period')?><!-- 有效期 --></label>
                                <input type="text" id="expirationstick" name="expirationstick" class="px" value="" tabindex="1" />
                            </p>
                        </li>
                        <?php } else if ($action == 'marrow') {?>
                        <li class="cell cell-select">
                            <label class="labeltxt"><?php echo WebUtils::lp('topic_topicAdmin_elite_title')?><!-- 精华 --></label>
                            <select name="digestlevel" class="select">
                            <option value="0"><?php echo WebUtils::lp('topic_topicAdmin_elite_remove')?><!-- 解除 --></option>
                            <option value="1" <?php echo $digestcheck['1'];?>><?php echo WebUtils::lp('topic_topicAdmin_elite_1')?><!-- 精华 1 --></option>
                            <?php if($_G['group']['allowdigestthread'] >= 2) { ?>
                            <option value="2" <?php echo $digestcheck['2'];?>><?php echo WebUtils::lp('topic_topicAdmin_elite_2')?><!-- 精华 2 --></option>
                            <?php if($_G['group']['allowdigestthread'] == 3) { ?>
                            <option value="3" <?php echo $digestcheck['3'];?>><?php echo WebUtils::lp('topic_topicAdmin_elite_3')?><!-- 精华 3 --></option>
                            <?php } } ?>
                            </select>
                            <p class="hasd" style="display:none">
                                <label for="expirationdigest" class="labeltxt"><?php echo WebUtils::lp('topic_topicAdmin_elite_period')?><!-- 有效期 --></label>
                                <input type="text" id="expirationdigest" name="expirationdigest" class="px" value="" tabindex="1" />
                            </p>
                        </li>
                        <?php } else if ($action == 'move') {?>
                        <li class="cell cell-select">
                            <label class="labeltxt"><?php echo WebUtils::lp('topic_topicAdmin_target_board')?><!-- 目标版块 --></label>
                            <select name="moveto" id="moveto" class="ps vm select" onchange="
                                js.post(
                                '<?php echo $this->dzRootUrl; ?>/forum.php?mod=ajax&action=getthreadtypes&fid=' + this.value,
                                function(data) {
                                var xml = js('*',data);
                                js('#vm').html(xml.text());
                                });
                            /*ajaxget('<?php echo $this->dzRootUrl; ?>/forum.php?mod=ajax&action=getthreadtypes&fid=' + this.value, 'threadtypes'); 老版*/
                                if(this.value) {js('#moveext').show();} else {js('moveext').hide();}">
                            <?php echo $forumselect;?>
                            </select>
                        </li>
                        <li class="cell cell-select">
                            <label class="labeltxt">
                                <?php echo WebUtils::lp('topic_topicAdmin_target_category')?><!-- 目标分类 -->
                            </label>
                            <select name="threadtypeid" id="vm" class="ps vm select"><option value="0" /></option></select>
                        </li>
                        <ul class="llst" id="moveext" style="display:none;margin:5px 0;">
                        <li class="wide"><label><input type="radio" name="appbyme_movetype" class="pr" value="normal" checked="checked" /><?php echo WebUtils::lp('topic_topicAdmin_move_theme')?><!-- 移动主题 --></label></li>
                        <li class="wide"><label><input type="radio" name="appbyme_movetype" class="pr" value="redirect" /><?php echo WebUtils::lp('topic_topicAdmin_retain_turn')?><!-- 保留转向 --></label></li>
                        </ul>
                        <?php } else if ($action == 'open' || $action == 'close') {?>
                        <li class="cell">
                            <label for="act-open">
                                <?php echo WebUtils::lp('topic_topicAdmin_open_theme')?><!-- 打开主题 -->
                                <div class="checked">
                                    <input type="radio" name="act" id="act-open" value="open" <?php echo $closecheck['0'];?>  />
                                    <span class="type"></span>
                                </div>
                            </label>
                        </li>
                        <li class="cell">
                            <label for="act-close">
                                <?php echo WebUtils::lp('topic_topicAdmin_close_theme')?><!-- 关闭主题 -->
                                <div class="checked">
                                    <input type="radio" name="act" id="act-close" class="checkbox" value="close" <?php echo $closecheck['1'];?> />
                                    <span class="type"></span>
                                </div>
                            </label>
                        </li>
                        <?php } else if ($action == 'band') {?>
                        <?php echo $banid;?>
                        <li class="cell">
                            <label for="banned-1">
                                <?php echo WebUtils::lp('topic_topicAdmin_shield')?><!-- 屏蔽 -->
                                <div class="checked">
                                    <input type="radio" name="banned" id="banned-1" class="pr" value="1" <?php echo $checkban;?> />
                                    <span class="type"></span>
                                </div>
                            </label>
                        </li>
                        <li class="cell">
                            <label for="banned-0">
                                <?php echo WebUtils::lp('topic_topicAdmin_relieve')?><!-- 解除 -->
                                <div class="checked">
                                    <input type="radio" name="banned" id="banned-0" class="pr" value="0" <?php echo $checkunban;?> />
                                    <span class="type"></span>
                                </div>
                            </label>
                        </li>
                        <?php if(($modpostsnum == 1 || $authorcount == 1) && $crimenum > 0) { ?>
                        <br /><div style="clear: both; text-align: right;"><?php echo WebUtils::lp('topic_topicAdmin_shield_num','crimeauthor',$crimeauthor,'crimenum',$crimenum)?><!-- 用户*帖子已被屏蔽*次 --></div>
                        <?php } ?>
                        <?php } ?>
                    <?php } ?>
                        <li class="cell cell-select">
                            <label><?php echo WebUtils::lp('topic_topicAdmin_reason')?><!-- 操作原因 -->:</label>
                            <select id="reasonSelect" class="select">
                                <option value="-1"><?php echo WebUtils::lp('topic_topicAdmin_select')?><!-- 请选择 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_1')?><!-- 广告/SPAM --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_2')?><!-- 恶意灌水 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_3')?><!-- 违规内容 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_4')?><!-- 文不对题 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_5')?><!-- 重复发帖 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_6')?><!-- -------- --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_7')?><!-- 我很赞同 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_8')?><!-- 精品文章 --></option>
                                <option><?php echo WebUtils::lp('topic_topicAdmin_select_9')?><!-- 原创内容 --></option>
                                <option value="0"><?php echo WebUtils::lp('topic_topicAdmin_select_10')?><!-- 自定义 --></option>
                            </select>
                        </li>
                        <li class="cell-textarea">
                            <textarea id="reason" name="reason" class="pt" rows="4"></textarea>
                        </li>
                        <?php if ($action == 'delete') {?>
                        <li class="cell">
                        <label for="crimerecord" class="checked">
                            <input type="checkbox" name="crimerecord" id="crimerecord" class="pc" fwin="mods"><?php echo WebUtils::lp('topic_topicAdmin_get_out_register')?><!-- 违规登记 -->
                            <span class="type"></span>
                        </label>
                        </li>
                        <?php } ?>
                    <li class="cell">
                        <label for="sendreasonpm">
                            <?php echo WebUtils::lp('topic_topicAdmin_notification_author')?><!-- 通知作者 -->
                            <div class="checked">
                                <input type="checkbox" name="sendreasonpm" id="sendreasonpm" class="pc" style="margin-right:5px;">
                                <span class="type"></span>
                            </div>
                        </label>
                    </li>
                </ul>
                <p style="text-align:center">
                    <button type="submit" name="modsubmit" id="modsubmit" class="pn pnc" value="确定" ><span><?php echo WebUtils::lp('topic_topicAdmin_ok')?><!-- 确定 --></span>
                    </button>
                </p>
            </div>
        </td>
    </tr>
</table>
</form>
</div>
<script type="text/javascript">
var js = jQuery.noConflict();
    /*$('#moveto').change(function () {
        $.post(
            '<?php echo $this->dzRootUrl; ?>/forum.php?mod=ajax&action=getthreadtypes&fid=' + this.value,
            function(data) {
                var xml = $('*',data);
                $('#vm').html(xml.text());
            });
    });*/
$('reasonSelect').onchange = function () {
    var value = this.value;
    if (value != '-1') {
        if (value == '0') {
            $('reason').focus();
        } else {
            $('reason').value = value;
        }
    }
};
var errorMsg = '<?php echo $errorMsg; ?>';
if (errorMsg != '') {
    alert(errorMsg);
}
</script>
<?php 
require(dirname(__FILE__) .'/../common/footer.php');
?>