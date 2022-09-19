<?php 
require(dirname(__FILE__) .'/../common/header.php');
?>
<div>
    <form method="post">

        <h3>禁言用户</h3>

        <div class="cell-group">
        <?PHP if($member[groupid] == 4 || $member[groupid] == 5){?>
            <div class="cell">
                <label for="bannew_0" class="lb">
                    正常状态
                    <div class="checked">
                        <input type="radio" name="bannew" id="bannew_0" value="0" checked="checked" class="pr" />
                        <span class="type"></span>
                    </div>
                </label>
            </div>
        <?php } ?>
        <?PHP if($member[groupid] != 4 && $_G[group][allowbanuser]){?>
            <div class="cell">
                <label for="bannew_4" class="lb">
                    禁言
                    <div class="checked">
                        <input type="radio" name="bannew" id="bannew_4" class="pr" value="4" <?php if($member[groupid] != 4 && $member[groupid] != 5){?>checked="checked"<?php } ?> />
                        <span class="type"></span>
                    </div>
                </label>
            </div>
        <?php } ?>
        <?PHP if($member[groupid] != 5 && $_G[group][allowbanvisituser]){?>
            <div class="cell">
                <label for="bannew_5" class="lb">
                    禁止访问
                    <div class="checked">
                        <input type="radio" name="bannew" id="bannew_5" class="pr" value="5" <?php if($member[groupid] != 4 && $member[groupid] != 5 && !$_G[group][allowbanuser]){?>checked="checked"<?php } ?> />
                        <span class="type"></span>
                    </div>
                </label>
            </div>
        <?php } ?>
        </div>

        <h4>时间</h4>
        <input type="text" id="banexpirynew" name="banexpirynew" autocomplete="off" value="" class="text" tabindex="1" />
        <p class="note">您需要禁言他的时间</p>

        <h4>理由</h4>
        <textarea name="reason" class="pt" rows="4" cols="80"></textarea>

        <p style="text-align:right" class ="pnc">
            <button type="submit" name="bansubmit" value="yes" id="bansubmit" class="pn pnc" value="<?php echo WebUtils::lp("user_userAdmin_ok");?>" ><span><?php echo WebUtils::lp("user_userAdmin_ok");?></span>
            </button>
        </p>
    </form>
</div>

<?php 
require(dirname(__FILE__) .'/../common/footer.php');
?>