<?php
/**
 * @author  NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 GoYoo Inc.
 */
global $_G;
?>
<base href="<?php echo $_G['siteurl']?>">
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title>评论</title>
    <style type="text/css">
        body {
            padding:0;
            margin: 0;
            color: #3e3e3e;
            font-size: 17px;
            font-family: "Helvetica Neue",Helvetica,"Hiragino Sans GB","Microsoft YaHei",Arial,sans-serif;
            background-color: #f9f9f9;
        }
        .container {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            max-width: 420px;
            margin: 0px auto;
            background: #F0EFF5;
            padding: 15px;
        }
        .reply-title {
            color: #666;
            line-height: 30px;
        }
        .reply-textarea {
            -webkit-appearance: none;
            width: 100%;
            height: 120px;
            border-color: #E3E3E3;
            border-width: 1px;
            font-size: 17px;
        }
        .form-item {
            margin-top: 15px;
        }
        .reply-submit {
            -webkit-appearance: none;
            display: block;
            color: #fff;
            width: 100%;
            height: 50px;
            border: 1px solid #efefef;
            border-radius: 5px;
            background: #2db7f5;
            margin-bottom: 10px;
            font-weight: 100;
            font-size: 20px;
        }
        .reply-cancel {
            -webkit-appearance: none;
            display: block;
            color: #fff;
            width: 100%;
            height: 50px;
            border: 1px solid #efefef;
            margin-right: 10px;
            border-radius: 5px;
            background: #FF8025;
            font-weight: 100;
            font-size: 20px;
        }
        .message{
            display: none;
            border-radius: 5px;
            position: fixed;
            background: rgba(0,0,0,.85);
            bottom: 20%;
            left: 20%;
            width: 60%;

        }
        .message p {
            color: #fff;
            text-align: center;
            font-size: 15px;
            padding: 5px 10px;
            margin: 0;
        }
    </style>
    <script src="<?php echo $this->rootUrl; ?>/js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="./mobcent/app/views/webapp/md5.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var siteurl = "<?php echo $_G['siteurl']?>"
            var _sdkVersion = '2.4.1'
            var tid = location.search.split('&tid=')[1]
            checkToken(function(err, data){
                if (err) return window.location.href = siteurl + 'mobcent/app/web/index.php?r=webapp/sharelogin&act=login&tid=' + tid
                return
            })

            //提交
            $('#SubmintBtn').on('click', function(){
                var content = document.getElementById('reply').value
                if (tid === '') {
                    $('#errcode').text('数据出错');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
                if (content === '') {
                    $('#errcode').text('评论不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
                var url = createUrl('forum/topicadmin', createComment(content,tid))
                $.get(url, function(data) {
                    if (data.rs == 0 && data.errcode) {
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return
                    }
                    return window.location.href = siteurl+'mobcent/app/web/index.php?r=webapp/share&tid=' + tid
                }, 'json')

            })

            //生成链接
            function createUrl(router, options) {
                options.sdkVersion = options.sdkVersion || _sdkVersion;
                var _url = siteurl + 'mobcent/app/web/index.php?r=' + router;
                if (options !== undefined) {
                    for (var key in options) {
                        _url += '&' + key + '=' + options[key];
                    }
                }
                return _url;
            }

            //创建评论
            function createComment(content, tid) {
                var userKey = siteurl.replace('http://','').replace(/\//g,'-') + 'wxshare'
                var userInfo = JSON.parse(localStorage.getItem(userKey))
                var apphash = (md5((new Date().getTime() + '').substr(0, 5) + 'appbyme_key')).substr(8, 8)
                var _content = encodeURIComponent('[{"type" : "0","infor" : "' + content + '"}]') 
                var _body = {
                    "body" : {
                        "json" : {
                            "isShowPostion" : "0",//是否显示地理位置？？？todo
                            "content" : _content,
                            "aid" : "",//todo 附件
                            "fid" : '',
                            "isQuote" : "0",//是否引用回复
                            "tid" : tid
                        }
                    }
                };
                //发送内容 两次转码
                var _json = encodeURIComponent(encodeURIComponent(JSON.stringify(_body)))
                var data = {
                    platType : 1,
                    act: 'reply',
                    json: _json,
                    accessToken: userInfo.accessToken,
                    accessSecret: userInfo.accessSecret,
                    apphash: apphash
                }
                return data
            }

            //判断用户token是否有效
            function checkToken (callback) {
                var siteurl = "<?php echo $_G['siteurl']?>"
                var userKey = siteurl.replace('http://','').replace(/\//g,'-') + 'wxshare'
                var userInfo = JSON.parse(localStorage.getItem(userKey))
                if (!userInfo) {
                    $('#errcode').text('用户没有登录');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
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
    </script>
    
</head>
<body>
    <div class="container">
        <div class="reply-title">进行评论</div>
        <form id="formId" onsubmit="return false;">
            <div class="form-item">
                <textarea class="reply-textarea" rows="3" name="reply" id="reply"></textarea> 
            </div>
            <div class="form-item">
                <input class="reply-submit" type="submit" id="SubmintBtn" value="提交">
                <input class="reply-cancel" type="reset" id="cancelBtn" value="取消">
            </div>
        </form>
    </div>
    <div class="message" id="message">
        <p id="errcode"></p>
    </div> 
</body>
</html>