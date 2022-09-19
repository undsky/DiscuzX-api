<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Cache-control" content="no-cache">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<meta name="format-detection" content="telephone=no">
<?php
global $_G;
header("Content-type: text/html; charset=utf-8");
?>
<title><?php echo WebUtils::lp('forum_topicRate_rate_all'); ?></title>

</head>
<script type="text/javascript" src="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/js/appbyme/sq-3.0.0.js"></script>
<script type="text/javascript">
    var inApp = 0;
    connectSQJavascriptBridge(function(){
        var json = {};
        sq.customButton(JSON.stringify(json));
        inApp = 1;
    });
    function userCenter(uid){
        //用户中心 userCenter(userId)
        if(inApp == 1) {
            sq.userCenter(uid);
        } else {
            sq3.toast({
                text: 'is not appbyme',
                isLong: 1
            });
        }
    }

</script>
<body class="bg">
<style>*{word-wrap:break-word}ul,ol,li,span,p,form,h1,h2,h3,4,h5,h6,dl,dt,dd{margin:0;padding:0;border:0;z-index:inherit}img,a img{border:0;margin:0;padding:0}ul,ol,li{list-style:none}*{margin:0;padding:0}html,body{height:100%;font:12px/1.6 Microsoft YaHei,Helvetica,sans-serif;color:#4c4c4c}input,select,textarea,button{font:14px/1.5 Microsoft YaHei,Helvetica,sans-serif}body,ul,ol,li,dl,dd,p,h1,h2,h3,h4,h5,h6,form,fieldset,.pr,.pc{margin:0;padding:0}table{empty-cells:show;border-collapse:collapse}caption,th{text-align:left;font-weight:400}ul li,.xl li{list-style:none}h1,h2,h3,h4,h5,h6{font-size:1em}em,cite,i{font-style:normal}a img{border:0}label{cursor:pointer}.bg{background:#eee}.rq{color:red}a:link,a:visited,a:hover{color:#4c4c4c;text-decoration:none}.blue{color:#0086ce}a.blue:link,a.blue:visited,a.blue:hover{color:#0086ce;text-decoration:none}.grey{color:#9c9c9c}a.grey:link,a.grey:visited,a.grey:hover{color:#9c9c9c;text-decoration:none}.orange{color:#F60}a.orange:link,a.orange:visited,a.orange:hover{color:#F60;text-decoration:none}.z{float:left}.y{float:right}.cl:after{content:".";display:block;height:0;clear:both;visibility:hidden}.cl{zoom:1}.overflow{overflow:hidden}.none{display:none}.vm{vertical-align:middle}.vm *{vertical-align:middle}.hm{text-align:center}.mbm{width:100%;background:#fff}.mbm .trtop{background:#f0f4f7;padding:3px}.mbm td,.mbm th{padding:10px}.mbm td{border-bottom:#d9d9d9 1px solid}.px{border:#d9d9d9 1px solid;border-radius:3px}.rate-list .title{background-color:#f5f5f5;height:24px;line-height:24px;font-size:16px;padding:10px;color:#3b3b3b}.rate-list .list{border-bottom:1px solid #eee}.rate-list .list li{position:relative;overflow:hidden;background-color:#fff;border-top:1px solid #eee;min-height:70px}.rate-list .list li img{width:48px;height:48px;border-radius:50%;position:absolute;top:50%;margin-top:-24px;left:18px}.rate-list .list li .rank{position:absolute;top:0;left:0;width:30px;height:30px;color:#fff;font-weight:bold;z-index:1;font-size:16px;font-style:italic;padding-left:1px}.rate-list .list li .rank-bg{width:0;height:0;color:#c2cece;border-top:30px solid currentColor;border-right:30px solid transparent;position:absolute;top:0;left:0}.rate-list .list li:nth-child(1) .rank-bg{color:#ed514c}.rate-list .list li:nth-child(2) .rank-bg{color:#ffa845}.rate-list .list li:nth-child(3) .rank-bg{color:#fad731}.rate-list .list li .info{min-height:50px;margin-left:70px;padding:10px}.rate-list .list li .name{font-size:16px;color:#2f2f2f;font-weight:bold}.rate-list .list li .desc{color:#a3a3a3;font-size:14px}.rate-list .list li .desc span{margin:0 2px;color:#d75847}.rate-list .list li .desc font{margin:0 3px;font-size:18px;font-style:italic;color:#d75847}.rate-list .explain{font-size:12px;color:#ccc}
    .rate-list .list li .desc span, .rate-list .list li .desc font{
        color: <?php echo $color; ?>;
    }
</style>


<form id="rateform" method="post" autocomplete="off" action="">
    <div class="rate-list">
        <div class="title">
            <?php echo WebUtils::lp('forum_topicRate_rate_ranke'); ?>
            <span class="explain">
                (<?php echo WebUtils::lp('forum_topicRate_rate_rule'),":",WebUtils::lp('forum_topicRate_rate_rule_des'); ?>)
            </span>
        </div>
        <ul class="list">
            <?php $i=1; foreach ($loglist as $s => $k) { ?>
                <li>
                    <div class="rank"><?php echo $i++; ?></div>
                    <div class="rank-bg"></div>
                    <img src="<?php echo UserUtils::getUserAvatar($k['uid']); ?>" onClick="userCenter(<?php echo $k['uid']; ?>)"/>
                    <div class="info">
                        <div class="name"><?php echo WebUtils::u($k['username']); ?></div>
                        <div class="desc"><?php echo WebUtils::lp('forum_topicRate_rate_to'); ?>
                            <?php foreach ($logcount as $ss => $kk) { ?>
                                <span><?php echo "<font>", WebUtils::u($k['score'][$ss]), "</font>", WebUtils::u($k['score'][$ss]) ? WebUtils::u($_G['setting']['extcredits'][$ss]['title']) : ''; ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </li>
                <?php } ?>
        </ul>
    </div>
</form>
<br><br><br>
</body>
</html>