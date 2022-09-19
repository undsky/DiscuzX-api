<?php 
require(dirname(__FILE__) .'/../common/header.php');
?>
<div class="zhiding">
    <form method="post">
        <div style="padding: 1rem">
            <?php echo WebUtils::lp('topic_topicAdmin_delete_ask') ?><!-- 您确认要 <strong>删除</strong> 选择的帖子么? -->
        </div>
        <p style="text-align:right" class="pnc">
            <button type="submit" name="bansubmit" value="yes" id="bansubmit" class="pn pnc"
                    value="<?php echo WebUtils::lp("user_userAdmin_ok"); ?>">
                <span><?php echo WebUtils::lp("user_userAdmin_ok"); ?></span>
            </button>
        </p>
    </form>
</div>

<?php 
require(dirname(__FILE__) .'/../common/footer.php');
?>