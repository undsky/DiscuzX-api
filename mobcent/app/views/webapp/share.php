<?php
/**
 * @author  NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 GoYoo Inc.
 */
global $_G;
?>
<base href="<?php echo $_G['siteurl']?>">
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo WebUtils::u($_G['forum_thread']['subject']) ?></title>
    <style type="text/css">
        body {
            padding:0;
            margin: 0;
            color: #3e3e3e;
            font-size: 14px;
            font-family: "Helvetica Neue",Helvetica,"Hiragino Sans GB","Microsoft YaHei",Arial,sans-serif;
            background-color: #f9f9f9;
        }
        .sharePage {
            max-width: 420px;
            margin: 0px auto;
        }
        .container {
            position: relative;
            padding: 20px 15px 15px;
            background-color: #fff;
            border-bottom: 1px solid #f1f1f1;
            overflow: hidden;
        }
        .container img {
            max-width: 100%;
            margin: 5px 0;
        }
        .topic-title {
            margin: 0 0 15px;
            font-size: 24px;
            line-height: 1.4;
            font-weight: 400;
        }
        .biaozhi {
            display: inline-block;
            color: #8c8c8c;
            border-radius: 3px;
            border: 1px solid #8c8c8c;
            font-size: 14px;
            padding: 0px 6px;
            margin-right: 4px;
        }
        .dateLine {
            display: inline-block;
            font-size: 16px;
            color: #8c8c8c;
            line-height: 22px;
            margin-right: 10px;
        }
        .avatar {
            display: inline-block;
            font-size: 16px;
            line-height: 22px;
            color: #606060;
        }
        .content {
            font-size: 16px;
        }
        .content ul{
            padding-left: 25px
        }
        .topic_buttom {
            margin-top: 20px;

        }
        .topic_buttom a {
            text-decoration: none;
            float:right;
            display: inline-block;
            line-height: 20px;
            color:#4DC4F0;
        }
        .topic_buttom div {
            display: inline-block;
            font-size: 15px;
            line-height: 18px;
            color: #999;
        }
        .topic_buttom div span {
            font-size: 18px;
            padding: 0 8px;
            font-weight: 100;
            color: #606060;
        }
        .adPostion {
            display: none;

        }
        .adPostion h1 {
            text-align: center;
            font-size: 24px;
            color: #8c8c8c;
            font-weight: 100;
            margin-bottom: 20px;
        }
        .comments {
            position: relative;
            padding: 20px 15px 15px;
            background-color: #f9f9f9;
            overflow: hidden;
        }
        .comments-title {
            text-align: center;
            font-size: 16px;
            color: #8c8c8c;
            font-weight: 200;
            margin: 0 0 5px;
        }
        .create-comment-button {
            display: none;
            text-align: right;
            color: #607fa6;
            font-size: 16px;
            padding-right: 10px;
            text-decoration: none;
        }
        .hasOutIcon {
            margin-top: 20px;
            height: 35px;
            line-height: 35px;
            background: #ededed;
            text-align: center;
            font-size: 16px;
            border-radius: 3px;
        }
        .hasOutIcon a {
            display: none;
            height: 35px;
            line-height: 35px;
            color: #00b4ff;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .comment {
            padding-top: 10px;
            overflow: hidden;
            display: flex;
            display: -webkit-box; /* 老版本语法: Safari, iOS, Android browser, older WebKit browsers. */
            display: -moz-box; /* 老版本语法: Firefox (buggy) */
            display: -ms-flexbox; /* 混合版本语法: IE 10 */
            display: -webkit-flex; /* 新版本语法: Chrome 21+ */ 
            flex-direction: row;

        }
        .comment .comment_avatar {
            width: 58px;
            position: relative;
            margin-right: 10px;
        }
        .comment .comment_avatar img {
            width: 48px;
            height: 48px;
            border-radius: 5px;
        }
        .comment .comment_content {
            width: 100%;
        }
        .comment .comment_content p {
            font-size: 14px;
            color: #8c8c8c;
        }
        .comment .comment_content div {
            vertical-align: top;
        }
        .comment-username, .comment-time {
            color: #8c8c8c;
        }
        .comment-username {
            font-size: 15px;
            margin-bottom: 8px;
        }
        .comment-time {
            font-size: 12px;
        }
        .comment-text {
            margin-bottom: 5px;
        }
        .comment-text img {
            max-width: 100%;
            margin: 5px 0;
        }
        .comment-text blockquote {
            margin: 5px;
        }
        .download{
            width: 100%;
            max-width: 420px;
            height: 60px;
            background: rgba(0,0,0,.62);
            bottom: 0px;
            position: fixed;
            display: block;
            overflow: hidden;
        }
        .close {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #CCC;
            text-align: center;
            color: #fff;
            line-height: 20px;
            position: absolute;
            top: 20px;
            left: 10px;
        }
        .app-icon {
            margin-left: 36px;
            display: inline-block;
            padding: 10px;
            float: left;
        }
        .app-icon img {
            width: 40px;
            height: 40px;

        }
        .app-info {
            display: inline-block;
            color: #fff;
            height: 60px;
            float: left;
            padding-top: 5px;

        }
        .app-name {
            font-size: 17px;
            line-height: 30px
        }
        .app-describe{
            line-height: 15px;
            font-size: 12px;
        }
        .app-btn {
            width:80px;
            height: 40px;
            display: inline-block;
            background: #EF2020;
            font-size: 14px;
            color: #fff;
            text-decoration: none;
            line-height: 40px;
            margin-bottom: 10px;
            margin-top: 10px;
            float: right;
            margin-right: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .bottom-margin {
            height: 60px;
            text-align: center;
            font-size: 16px;
            color: #ccc;
            font-weight: 200;
            line-height: 60px;
        }
    </style>
    <script src="<?php echo $this->rootUrl; ?>/js/jquery-2.0.3.min.js"></script> 
</head>
<body>
    <div class="sharePage"> 
        <div class="container">
            <h1 class="topic-title">
                <?php echo WebUtils::u($_G['forum_thread']['subject']) ?>
            </h1>

            <?php $topic =  array_shift($postlist) ?>
            <div style="margin-bottom: 18px;line-height: 20px;justify-content: center;">
                <div class="biaozhi">原创</div>
                <div class="dateLine"><?php echo diconv($topic['dateline'],CHARSET,'utf-8')?></div>
                <div class="avatar"><?php echo  WebUtils::u($topic['author'])?></div>
            </div>
            <div class="content">
                <div id="topicInfo">
                    <?php echo WebUtils::u($topic['message'])?>
                </div>
                
                <div class="topic_buttom">
                    <div style="margin-right:20px;">阅读<span><?php echo $_G['forum_thread']['views']?></span></div>
                    <div>评论<span><?php echo $_G['forum_thread']['replies']?></span></div>

                    <a href="<?php echo $downInfo['download'] ? $downInfo['download'] : "http://www.baidu.com"; ?>" class="" >查看原文</a>
                </div>
            </div>
        </div>
        <div class="adPostion">
        </div>
        <div class="comments">
            <h2 class="comments-title">精选留言</h2>
            <a id="create-comment-button" class="create-comment-button">写评论</a>
            <?php if($_G['forum_thread']['replies'] > 0){ ?>
                <?php $i = 0; foreach ($postlist as $k => $post) { ?>
                    <div class="comment">
                        <div class="comment_avatar"><?php echo $post['avatar']?></div>
                        <div class="comment_content">
                            <div class="comment-username"><?php echo WebUtils::u($post['author'])?></div>
                            <div class="comment-text"><?php echo WebUtils::u($post['message'])?></div>
                            <div class="comment-time"><?php echo diconv($post['dateline'],CHARSET,'utf-8')?></div>
                        </div> 
                    </div>
                <?php } ?>
            <?php } ?>
            <div class="hasOutIcon">
                <a href="<?php echo $downInfo['download'] ? $downInfo['download'] : "http://www.baidu.com"; ?>" id="more-link">打开<?php echo $downInfo['appname'] ? WebUtils::u($downInfo['appname']) : "安米社区"; ?>客户端，看更多热评</a>
            </div>
        </div>
        <input id="siteurl" value="<?php echo $_G['siteurl']?>" style="display: none" />
        <div class="bottom-margin">
            以上由<?php echo $downInfo['appname'] ? WebUtils::u($downInfo['appname']) : "安米社区"; ?>分享
        </div>
    <div>

    <div class="download">
        <div class="close" onClick="closeBtn()">
            X
        </div>
        <div class="app-icon">
            <img src="<?php echo $downInfo['icon'] ? $downInfo['icon'] : 'http://img.appbyme.com/d/img/aca/icon/2015/10/23/57x57/89deb3bf-d9f4-4573-a7cf-1e724f37f5a6.png' ?>" />
        </div>
        <div class="app-info">
            <div class="app-name" ><?php echo $downInfo['appname'] ? WebUtils::u($downInfo['appname']) : "安米社区"; ?></div>
            <div class="app-describe" id="test" ><?php echo $downInfo['describe'] ? WebUtils::u($downInfo['describe']) : "121万人正在使用";  ?></div>
        </div>
        <a href="<?php echo $downInfo['download'] ? $downInfo['download'] : "http://www.baidu.com"; ?>" class="app-btn">
            立即体验
        </a>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
           var siteurl = "<?php echo $_G['siteurl']?>"
            var redirectUrl = encodeURIComponent(siteurl + 'mobcent/app/web/index.php?r=webapp/wxcall').toLowerCase();
            var STATE = "wxshare_<?php echo $_G['forum_thread']['tid']?>"
            // var APPID = 'wxb4fb29266130bb85'
            var APPID = '';
            checkToken(function(tokenErr, res){
                checkHasWX(function(err, data) {
                    APPID = !err && data && data.body.data.wxAppid;
                    ['more-link', 'create-comment-button'].forEach(function(x) {
                        var element = document.getElementById(x);
                        element.href = generateUrl(!tokenErr, APPID);
                        element.style.display = 'block';
                    });
                });
            });
            var arr = document.querySelectorAll('.content img');
            var length = arr.length;
            for(var i = 0 ; i < length ;i++ ) {
                arr[i].removeAttribute('height')
                arr[i].removeAttribute('width')
                if (arr[i].parentElement.tagName === 'A') {
                    arr[i].parentElement.setAttribute("href","javascript:;");
                }
            }
            var describe = document.getElementsByClassName('app-describe');
            describe[0].innerHTML = describe[0].innerText.substring(0,9)+'...';

            handleMedia();

            function handleMedia() {
                //处理视频［media］形式的视频
                var str = document.getElementById('topicInfo').innerHTML;
                var media = str.match(/\[media\].+[^]/ig);
                if(media){
                    media.forEach(function(item){
                        var url = item.match(/http.+\[/ig)[0].replace('[','');
                        str = urlToIframe(url, item, str);
                    });
                    document.getElementById('topicInfo').innerHTML = str;
                }
                
                //处理a 连接到视频
                var arrayHtml = document.getElementById('topicInfo').innerHTML;
                var array_a = arrayHtml.match(/<a .+<\/a>/ig);
                if(array_a){
                    array_a.forEach(function(item){
                        var url = item.match(/href\=.+\"/ig)[0].replace('href="','').replace('"','');
                        arrayHtml = urlToIframe(url, item, arrayHtml);
                    });
                    document.getElementById('topicInfo').innerHTML = arrayHtml;
                }

                var arrayImg = document.getElementsByClassName('zoom')
                var arrayImgLength = arrayImg.length
                for(var i = 0; i < arrayImgLength; i++){
                    var obj = arrayImg[i]
                    var url = obj.getAttribute('file')
                    var id = obj.getAttribute('id')
                    if (url && url !== '') {
                        document.getElementById(id).setAttribute('src', url)
                    }
                }
            }



            //判断视频来源 转化成对应的iframe
            function urlToIframe (url, item, obj){
                var regYouKu = /http\:\/\/player\.youku\.com/ig,
                    regTuDou = /http\:\/\/www\.tudou\.com/ig,
                    regTencent = /video\.qq\.com/ig;
                    // reg56 = /http\:\/\/www\.56\.com\/./ig,
                    // regKu6 = /http\:\/\/v\.ku6\.com\/./ig;
                var bodyw = document.getElementsByClassName('container')[0].offsetWidth;
                var height = bodyw*9/16+'px';
                if(regYouKu.test(url)){
                    try{
                        var sid = url.match(/sid\/.+\//ig)[0].replace('sid/','').replace('/','');
                        obj = obj.replace(item,'<iframe  width="100%" height="'+height+'" src="http://player.youku.com/embed/'+sid+'" frameborder=0 allowfullscreen></iframe>');

                    } catch (e) {} 
                }
                if(regTuDou.test(url)){
                    try{
                        var resourceId = url.match(/resourceId=.+\//ig)[0].replace('resourceId=','').replace('/','').replace('0_04','0_06');
                        obj = obj.replace(item,'<iframe src="http://www.tudou.com/programs/view/html5embed.action?type=2&code=GwaWxe1I-WI&lcode=tLW1AhweBCc&resourceId='+resourceId+'" allowtransparency="true" allowfullscreen="true" allowfullscreenInteractive="true" scrolling="no" border="0" frameborder="0" style="width:100%;height:'+height+'"></iframe>');
                    } catch (e){}
                    
                }
                if(regTencent.test(url)){
                    try{
                        var vid = url.match(/vid=.+\&/ig)[0].replace('vid=','').replace('&','');
                        obj = obj.replace(item,'<iframe frameborder="0" width="100%" height="'+height+'" src="http://v.qq.com/iframe/player.html?vid='+vid+'&tiny=0&auto=0" allowfullscreen></iframe>')
                    
                    } catch (e){}
                }
                return obj;
            }
            //检测是否绑定微信公众号
            function checkHasWX (callback) {
                $.get("<?php echo $_G['siteurl']?>mobcent/app/web/index.php?r=htmlapi/getappinfo", function(data){
                        return callback(null, data)
                }, 'json')
            }
            //判断微信浏览器
            function isWeiXin(){
                var ua = window.navigator.userAgent.toLowerCase()
                return ua.match(/MicroMessenger/i) == 'micromessenger'
            }

            function generateUrl(isLogin, APPId) {
                if (APPId && isWeiXin()) {
                    return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' + APPId + '&redirect_uri=' + redirectUrl + '&response_type=code&scope=snsapi_userinfo&state=' + STATE + '#wechat_redirect'
                }
                return siteurl + 'mobcent/app/web/index.php?r=webapp/sharelogin&act=' + (isLogin ? 'reply' : 'login') + "&tid=<?php echo $_G['forum_thread']['tid']?>"
            }

            //判断用户token是否有效
            function checkToken (callback) {
                var siteurl = "<?php echo $_G['siteurl']?>"
                var userKey = siteurl.replace('http://','').replace(/\//g,'-') + 'wxshare'
                var userInfo = JSON.parse(localStorage.getItem(userKey))
                if (!userInfo) return callback({err: '用户没有登录'})
                var uid = userInfo['uid']
                var accessToken = userInfo['accessToken'] ? userInfo['accessToken'] :''
                var accessSecret = userInfo['accessSecret'] ? userInfo['accessSecret'] :''
                var url = siteurl + 'mobcent/app/web/index.php?r=user/userinfo&userId=' + uid + '&accessToken=' + accessToken + '&accessSecret=' + accessSecret

                $.get(url, function(data){
                    if (data.errcode) {
                        localStorage.removeItem(userKey)
                        return callback({err: data.head.errInfo})
                    }
                    return callback(null, data)
                }, 'json')
            }
        })
        function closeBtn(){
            document.getElementsByClassName("download")[0].style.display = "none";
        }
    </script>


    
</body>
</html>


