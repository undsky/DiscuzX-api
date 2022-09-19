<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
        <meta http-equiv="Cache-control" content="no-cache"></meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"></meta>
        <meta name="format-detection" content="telephone=no"></meta>
        <?php
        global $_G;
        header("Content-type: text/html; charset=utf-8");
        ?>
        <title><?php echo WebUtils::lp('forum_topicRate_rate_title'); ?> </title>
        <script type="text/javascript" src="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/js/appbyme/appbyme.js"></script>
        <script type="text/javascript" src="<?php echo $this->rootUrl.'/js/'; ?>jquery-2.0.3.min.js"></script>
        <script type="text/javascript">
            $(function(){var a={};$(".list").delegate(".slider-handler","touchstart",function(g){var d=$(this);var h=$(this).prev();var i=$(this).parent();var f=$(this).next();a[i.data("name")]["startLeft"]=Math.round(parseFloat(d.css("left")))||0;a[i.data("name")]["startX"]=g.originalEvent.targetTouches[0].clientX;h.css({webkitTransition:"background-color 0.3s ease",transition:"background-color 0.3s ease"});d.css({webkitTransition:"border-color 0.3s ease",transition:"border-color 0.3s ease"});f.css({display:"block"})});$(".list").delegate(".slider-handler","touchmove",function(n){var q=$(this);var k=$(this).prev();var d=$(this).parent().parent().next().children("input");var g=$(this).parent();var h=$(this).next();var r=g.width();var f=a[g.data("name")]["startLeft"];var l=a[g.data("name")]["startX"];var o=f+n.originalEvent.targetTouches[0].clientX-l;o=o<0?0:o>r?r:o;var m=Math.round(o/r*100);k.css("width",m+"%");q.css("left",m+"%");h.css("left",m+"%");var j=a[g.data("name")]["min"]===1?0:a[g.data("name")]["min"];var p=a[g.data("name")]["max"];var i=Math.round((p-j)*m/100+j);if(m===0){i=""}d.val(i);n.preventDefault()});$(".list").delegate(".slider-handler","touchend",function(f){var d=$(this).next();d.css({display:"none"});f.preventDefault()});$(".list").delegate(".slider-value input","input",function(k){var d=$(this);var f=$(this).parent().prev().children();var l=f.children(".slider-handler");var i=f.children(".slider-track");var g=Math.round($(this).val());var h=a[f.data("name")]["min"]===1?0:a[f.data("name")]["min"];var m=a[f.data("name")]["max"];var j=Math.round((g-h)/(m-h)*100);if(g<a[f.data("name")]["min"]){j=0}if(g>=a[f.data("name")]["max"]){j=100}i.css({"width":j+"%",webkitTransition:"background-color 0.3s ease, width 0.3s ease",transition:"background-color 0.3s ease, width 0.3s ease"});l.css({"left":j+"%",webkitTransition:"border-color 0.3s ease, left 0.3s ease",transition:"border-color 0.3s ease, left 0.3s ease"})});$(".list").delegate(".slider-value input","blur",function(){var d=$(this);var f=$(this).parent().prev().children();var e=Math.round($(this).val());if(e<a[f.data("name")]["min"]){e=""}if(e>=a[f.data("name")]["max"]){e=a[f.data("name")]["max"]}d.val(e)});var c=[<?php echo "'",WebUtils::lp('forum_topicRate_reason_good'),"','",WebUtils::lp('forum_topicRate_reason_good1'),"','",WebUtils::lp('forum_topicRate_reason_good2'),"','",WebUtils::lp('forum_topicRate_reason_good3'),"','",WebUtils::lp('forum_topicRate_reason_good4'),"','",WebUtils::lp('forum_topicRate_reason_good5'),"','",WebUtils::lp('forum_topicRate_reason_good6'),"','",WebUtils::lp('forum_topicRate_reason_good7'),"','",WebUtils::lp('forum_topicRate_reason_good8'),"'";?>];$(".ram").on("click",function(){$("#reason").text(c[Math.floor((Math.random()*c.length))])});function b(){$("#reason").text(c[Math.floor((Math.random()*c.length))]);$(".list .slider-inner").each(function(){a[$(this).data("name")]={};a[$(this).data("name")]["min"]=$(this).data("min");a[$(this).data("name")]["max"]=$(this).data("max")})}b()});
        </script>
    </head>

    <body class="bg">
    <style>

        *{word-wrap: break-word;} ul,ol,li,span,p,form,h1,h2,h3,4,h5,h6,dl,dt,dd{margin: 0; padding: 0; border: 0; z-index:inherit;} img,a img{border:0; margin:0; padding:0;} ul,ol,li{list-style:none;} *{margin:0; padding:0;} html,body{height:100%; font:12px/1.6 Microsoft YaHei, Helvetica, sans-serif; color:#4C4C4C;} input,select,textarea,button{font:14px/1.5 Microsoft YaHei, Helvetica, sans-serif; -webkit-appearance: none;} body, ul, ol, li, dl, dd, p, h1, h2, h3, h4, h5, h6, form, fieldset, .pr, .pc{margin: 0; padding: 0;} table{empty-cells: show; border-collapse: collapse;} caption, th{text-align: left; font-weight: 400;} ul li, .xl li{list-style: none;} h1, h2, h3, h4, h5, h6{font-size: 1em;} em, cite, i{font-style: normal;} a img{border: none;} label{cursor: pointer;} .bg{background:#eeeeee;} .rq{color: red;} a:link,a:visited,a:hover{color:#4C4C4C; text-decoration:none;} .blue{color: #0086CE;} a.blue:link, a.blue:visited, a.blue:hover{color:#0086CE; text-decoration:none;} .grey{color:#9C9C9C;} a.grey:link, a.grey:visited, a.grey:hover{color:#9C9C9C; text-decoration:none;} .orange{color:#F60;} a.orange:link,a.orange:visited,a.orange:hover{color:#F60;text-decoration:none} .z{float: left;} .y{float: right;} .cl:after{content: "."; display: block; height: 0; clear: both; visibility: hidden;} .cl{zoom: 1;} .overflow{overflow:hidden;} .none{display:none;} .vm{vertical-align: middle;} .vm *{vertical-align: middle;} .hm{text-align: center;} .mbm{width:100%; background:#fff;} .mbm .trtop{background:#f0f4f7;padding:3px;} .mbm td,.mbm th{text-align:center; padding:5px;} .mbm td{border-bottom:#d9d9d9 1px solid;} .px{border:#d9d9d9 1px solid; border-radius:3px;} .tpclg{background:#fff; margin-top:20px;} .tpclg textarea{width:100%; border:0;} .tpclg span{padding:0 10px; line-height:30px;} .tongzhi{margin-top:20px; background:#fff; padding:10px;} .button{margin:20px;} .button2{background:#ee5e39; border-radius:3px; width:100%; border:0; color:#fff; padding:10px;} /* #sendreasonpm{margin-top:5px;}*/ .weui_switch{-webkit-appearance: none; -moz-appearance: none; appearance: none; position: relative; width: 52px; height: 27px; border: 1px solid #DFDFDF; outline: 0; border-radius: 16px; box-sizing: border-box; background: #DFDFDF;} .weui_switch:before{content: " "; position: absolute; top: 0; left: 0; width: 50px; height: 25px; border-radius: 15px; background-color: #FDFDFD; -webkit-transition: -webkit-transform .3s; transition: transform .3s;} .weui_switch:after{content: " "; position: absolute; top: 0; left: 0; width: 25px; height: 25px; border-radius: 15px; background-color: #FFFFFF; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4); -webkit-transition: -webkit-transform .3s; transition: transform .3s;} .weui_switch:checked{border-color: #d75847; background-color: #d75847;} .weui_switch:checked:before{-webkit-transform: scale(0); -ms-transform: scale(0); transform: scale(0);} .weui_switch:checked:after{-webkit-transform: translateX(25px); -ms-transform: translateX(25px); transform: translateX(25px);} /* 按钮*/ .weui_btn_primary{background-color: #04BE02;} .weui_btn_primary:not(.weui_btn_disabled):visited{color: #FFFFFF;} .weui_btn_primary:not(.weui_btn_disabled):active{color: rgba(255, 255, 255, 0.4); background-color: #bb4c3d;} .weui_btn{position: relative; display: block; margin-left: auto; margin-right: auto; padding-left: 14px; padding-right: 14px; box-sizing: border-box; font-size: 18px; text-align: center; text-decoration: none; color: #FFFFFF; line-height: 2.33333333; border-radius: 5px; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); overflow: hidden;} .weui_btn:after{content: " "; width: 200%; height: 200%; position: absolute; top: 0; left: 0; border: 1px solid rgba(0, 0, 0, 0.2); -webkit-transform: scale(0.5); -ms-transform: scale(0.5); transform: scale(0.5); -webkit-transform-origin: 0 0; -ms-transform-origin: 0 0; transform-origin: 0 0; box-sizing: border-box; border-radius: 10px;} button.weui_btn, input.weui_btn{width: 100%; border-width: 0; outline: 0; -webkit-appearance: none;} /* yves 美化页面 */ *{outline: 0; -webkit-user-select: none; user-select: none; -moz-appearance: textfield; -webkit-tap-highlight-color: transparent;} .bg{background-color: #fff;} .rate{overflow: hidden; width: 100%;} .header{background-color: #fff; height: 180px; position: relative;} .header:after{content: ''; display: block; position: absolute; top: -60px; left: -5%; right: -5%; height: 170px; background-color: #d75847; border-bottom-left-radius: 50%; border-bottom-right-radius: 50%;} .header img{position: absolute; width: 64px; height: 64px; border-radius: 50%; top: 78px; left: 50%; margin-left: -32px; background-color: #fff; z-index: 1;} .header span{position: absolute; top: 152px; left: 0; right: 0; text-align: center; color: #323236; font-size: 22px;} .body{background-color: #fff;} .body .desc{text-align: center; font-size: 20px; padding-top: 10px; color: #d75847; position: relative; background-color: #fff; height: 30px; margin-bottom: 10px;} .body .desc:after{content: ''; display: block; position: absolute; top: 60%; height: 1px; background-color: #d75847; width: 80%; left: 50%; margin-left: -40%;} .body .desc span{padding: 2px 10px; background-color: #fff; position: relative; z-index: 1;} .slider-box{position: relative; padding-bottom: 10px;} .slider-box .title{display: block; height: 32px; color: #d75847; line-height: 32px; font-size: 18px; font-weight: bold; float: left; width: 20%; text-align: right; padding: 0 10px 0 0;} .slider-box .slider{float: left; width: 50%;} .slider-box .slider-inner{position: relative; margin: 10px 6px; height: 4px; border-radius: 5px; background-color: #e9e9e9; cursor: pointer; border-top: 4px solid #fff; border-bottom: 4px solid #fff; -webkit-transition: background-color 0.3s ease; transition: background-color 0.3s ease;} .slider-box .slider-track{position: absolute; height: 4px; border-radius: 5px; background-color: #ef7868; left: 0; width: 0%; -webkit-transition: background-color 0.3s ease; transition: background-color 0.3s ease;} .slider-box .slider-handler{position: absolute; height: 20px; width: 20px; cursor: pointer; border-radius: 50%; background-color: #fff; border: 2px solid #ef7868; top: 50%; left: 0%; margin-top: -12px; margin-left: -13px; -webkit-transition: border-color 0.3s ease; transition: border-color 0.3s ease;} .slider-box .slider:hover .slider-inner{background-color: #e1e1e1;} .slider-box .slider:hover .slider-track{background-color: #d75847;} .slider-box .slider:hover .slider-handler{border-color: #d75847;} .slider-box .slider-taps{background-color: rgba(0,0,0,0.5); color: #fff; padding: 3px 5px; width: 60px; text-align: center; font-size: 14px; border-radius: 5px; position: absolute; z-index: 5; top: -60px; margin-left: -35px; display: none;} .slider-box .slider-taps span{display: block; font-size: 12px;} .slider-box .slider-taps:after{content: ''; display: block; width: 0; height: 0; border: 5px solid transparent; border-top: 5px solid rgba(0,0,0,0.5); position: absolute; bottom: -10px; left: 50%; margin-left: -6px;} .slider-box .slider-value{float: left; width: 17%;} .slider-box .slider-value input{-webkit-user-select: initial; user-select: initial; margin-left: 15%; width: 100%; text-align: center; height: 32px; line-height: 32px; color: rgba(0, 0, 0, 0.65); padding: 0 7px; border: 1px solid #e2e2e2; border-radius: 4px; font-size: 12px; outline: 0; -moz-appearance: textfield; -webkit-transition: border-color 0.3s linear; transition: border-color 0.3s linear;} .slider-box .slider-value:hover input{border-color: #d75847;} .content{position: relative; padding: 30px 10% 20px 10%;} .content:after{content: ''; display: block; position: absolute; top: 10px; height: 1px; background-color: #d75847; width: 80%; left: 50%; margin-left: -40%;} .content .title{font-size: 16px; margin: 0 0 5px 0;} .content .ram{float: right; color: #d75847;} .content textarea{-webkit-user-select: initial; user-select: initial; width: 100%; height: 80px; border: 1px solid #d75847; border-radius: 5px; padding: 5px 10px; color: #ef7868; font-size: 16px;} .rate .tongzhi{margin-top: 0; padding: 0px 10% 20px 10%} .weui_btn{background-color: #d75847;}
        .header:after, .body .desc:after, .content:after, .weui_btn, .weui_switch:checked, .slider-box .slider-track, .weui_btn_primary:not(.weui_btn_disabled):active{
            background-color:<?php echo $color; ?>;
        }
        .body .desc, .slider-box .title, .content .ram, .content textarea {color: <?php echo $color; ?>;}
        .weui_switch:checked, .content textarea, .slider-box .slider-handler {
            border-color: <?php echo $color; ?>;
        }
    </style>
</head>
        <form id="rateform" method="post" autocomplete="off"  action="<?php echo $formUrl; ?>">
            <div class="rate">
                <div class="header">
                    <img src="<?php echo $userInfo['avatar']; ?>"/>
                    <span><?php echo WebUtils::u($userInfo['username']); ?></span>
                </div>
                <div class="body">
                    <div class="desc">
                        <span><?php echo WebUtils::lp('forum_topicRate_rate_title_new');?></span>
                    </div>
                    <ul class="list">
                        <?php foreach ($ratelist as $id => $options): ?>
                            <li>
                                <div class="slider-box cl">
                                    <div class="title"><?php echo WebUtils::u($_G['setting']['extcredits'][$id]['title']); ?></div>
                                    <div class="slider">
                                        <div class="slider-inner" data-name="score<?php echo $id ?>" data-min="<?php echo $_G['group']['raterange'][$id]['min']; ?>" data-max="<?php echo $_G['group']['raterange'][$id]['max']; ?>" data-value="0">
                                            <div class="slider-track" style="width:0%;"></div>
                                            <div class="slider-handler" style="left:0%;"></div>
                                            <div class="slider-taps" style="left:0%;">
                                                <span><?php echo WebUtils::lp('forum_topicRate_now_surplus'); ?></span>
                                                <span><?php echo $maxratetoday[$id]; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="slider-value" >
                                        <input type="number" name="score<?php echo $id ?>" value="" placeholder="<?php echo $_G['group']['raterange'][$id]['min']; ?> ~ <?php echo $_G['group']['raterange'][$id]['max']; ?>">
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="content">
                        <div class="title">
                            <span><?php echo WebUtils::lp('forum_topicRate_reason');?></span>
                            <span class="ram"><?php echo WebUtils::lp('forum_topicRate_rate_ram');?></span>
                        </div>
                        <textarea name="reason" id="reason" ><?php echo WebUtils::lp('forum_topicRate_reason_good');?></textarea>
                    </div>
                </div>
                <div class="tongzhi" style="font-size: 16px;" >
                    <?php echo WebUtils::lp('forum_topicRate_notification_author');?>
                    <label for="sendreasonpm">
                        <input class="weui_switch" type="checkbox" name="sendreasonpm" id="sendreasonpm" style=" float:right ;margin-top: -5px;" />
                    </label>
                </div>
            </div>

            <dd class="button">
                <input name="modsubmit" type="submit" value="<?php echo WebUtils::lp('forum_topicRate_ok'); ?>" class="weui_btn weui_btn_primary"style="
                           border-radius: 3px;
                           width: 100%;
                           border: 0;
                           color: #fff;
                           /* padding: 10px; */
                           "></dd>
        </form>
        <br><br><br>
        </body>
        </html>