<?php
require(dirname(__FILE__) .'/../common/header.php');
?>
<title><?php echo WebUtils::lp("user_userAdmin_title_title");?></title>
<div>
<form method="post" action="<?php echo $formUrl; ?>">
<table cellpadding="0" cellspacing="0" class="fwin" width="100%">
    <tr>
        <td class="m_c">
            <div class="tm_c">
                <div class="c">
                    <ul class="tpcl">
                        <?php if ($action == 'add') {?>
                        <div class="c">
                            <h3 class="flb" id="return_<?php echo $_GET['handlekey'];?>">
                                <?php echo WebUtils::lp("user_userAdmin_title");?>
                            </h3>
                            <div class="user">
                                <div class="avatar"><img src="<?php echo  UserUtils::getUserAvatar($tospace[uid],small);?>"></div>
                                <div class="avatar-text">
                                    <?php echo WebUtils::lp("user_userAdmin_note_title","username",$tospace['username']);?>
                                </div>
                            </div>
                            <input type="text" name="note" value="" size="35" class="text"  onkeydown="ctrlEnter(event, 'addsubmit_btn', 1);" />
                            <p class="note"><?php echo WebUtils::lp("user_userAdmin_note_desc","username",$tospace['username']);?></p>
                            <div class="cell cell-select">
                                <?php echo WebUtils::lp("user_userAdmin_group_title");?>
                                <select name="gid" class="select">
                                    <?php if(is_array($groups))  foreach($groups as $key => $value) { ?>
                                    <option value="<?php echo $key;?>" <?php if(empty($space['privacy']['groupname']) && $key==1) { ?> selected="selected"<?php } ?>>
                                        <?php
                                        echo  WebUtils::detect_encoding($value) == 'GBK' ?
                                                iconv(strtoupper(Yii::app()->charset), 'UTF-8', $value)
                                            : WebUtils::u($value);
                                        ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php } else if ($action == 'add2') {?>
                            <h3 class="flb">
                                <em id="return_<?php echo $_GET['handlekey'];?>"><?php echo WebUtils::lp("user_userAdmin_approve_request");?></em>
                            </h3>
                            <div class="c">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                    <th valign="top" width="60" class="avt"><div class="size40"><img src="<?php echo  UserUtils::getUserAvatar($tospace[uid],small);?>"></div></th>
                                    <td valign="top">
                                        <p><?php echo WebUtils::lp("user_userAdmin_approve_group_title","username",$tospace['username']);?></p>
                                        <table><tr><?php $i=0;?><?php if(is_array($groups)) foreach($groups as $key => $value) { ?><td style="padding:8px 8px 0 0;"><label for="group_<?php echo $key;?>"><input type="radio" name="gid" id="group_<?php echo $key;?>" value="<?php echo $key;?>"<?php echo $groupselect[$key];?> /><?php echo  WebUtils::detect_encoding($value) == 'GBK' ?
                                                            iconv(strtoupper(Yii::app()->charset), 'UTF-8', $value)
                                                            : WebUtils::u($value);
                                                        ?></label></td>
                                        <?php if($i%2==1) { ?></tr><tr><?php } $i++;?><?php } ?>
                                        </tr></table>
                                    </td>
                                </tr>
                                </table>
                            </div>
                        <?php } else if ($action == 'ignore') {?>
                            <h3 class="flb">
                                <em id="return_<?php echo $_GET['handlekey'];?>"><?php echo WebUtils::lp("user_userAdmin_friend_ignore");?></em>
                            </h3>
                            <div class="c"><?php echo WebUtils::lp("user_userAdmin_friend_ignore_ask");?></div>
                        <?php } else if ($action == 'shield') { ?>
                            <h3 class="flb">
                                <em id="return_<?php echo $_GET['handlekey'];?>"><?php echo WebUtils::lp("user_userAdmin_friend_mask");?></em>
                            </h3>
                            <div class="c altw">
                                <p><?php echo WebUtils::lp("user_userAdmin_friend_next_non_display");?></p>
                                <p class="ptn"><label><input type="radio" name="authorid" id="authorid1" value="<?php echo $_GET['uid'];?>" checked="checked" /><?php echo WebUtils::lp("user_userAdmin_friend_ignore_noe");?></label></p>
                                <p class="ptn"><label><input type="radio" name="authorid" id="authorid0" value="0" /><?php echo WebUtils::lp("user_userAdmin_friend_ignore_all");?></label></p>
                            </div>
                        <?php } ?>
                    </ul>
                </div>
                <br />
                <p style="text-align:right" class ="pnc">
                    <button type="submit" name="modsubmit" id="modsubmit" class="pn pnc" value="<?php echo WebUtils::lp("user_userAdmin_ok");?>" ><span><?php echo WebUtils::lp("user_userAdmin_ok");?></span>
                    </button>
                </p>
            </div>
        </td>
    </tr>
</table>
</form>
</div>
<script type="text/javascript">
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