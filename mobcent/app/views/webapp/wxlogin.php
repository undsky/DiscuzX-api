<?php
/**
 * @author  三方绑定 
 * @copyright 2003-2016 GoYoo Inc.
 */
global $_G;
?>
<base href="<?php echo $_G['siteurl']?>">
<!DOCTYPE html>
<html>
<head>
    <title>绑定公账</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <style type="text/css">
    body{
        padding:0;
        margin: 0;
        color: #3e3e3e;
        font-size: 17px;
        font-family: "Helvetica Neue",Helvetica,"Hiragino Sans GB","Microsoft YaHei",Arial,sans-serif;
    }
    .container{
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
    .login-title{
        color: #666;
        line-height: 30px;
        margin-bottom: 15px;
    }
    .form-item {    
        margin-bottom: 15px;
    }
    .login-input {
        -webkit-appearance: none;
        background-color: #FFF;
        border-color: #E3E3E3;
        border-width: 1px;
        box-sizing: border-box;
        color: #565656;
        font: inherit;
        padding: 7px 12px;
        height: 50px;
        font-size: 20px;
        display: block;
        width: 100%;
        border-style: solid;
        border-radius: 4px;
        outline: 0;
    }
    .login-btn{
        -webkit-appearance: none;
        color: #fff;
        text-align: center;
        border: 0;
        padding: 6px 12px;
        background-color: #2db7f5;
        width: 100%;
        height: 50px;
        border-radius: 6px;
        font-size: 20px;
    }
    .regist{
        display: block;
        text-align: center;
        text-decoration: none;
        color: #2db7f5;
    }
    #login-div {
        display: none; 
    }
    #reg-div {
        display: none;
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
    <script type="text/javascript">
        $(document).ready(function() {
            var openId = ''
            var oauthToken = ''
            var tid = ''
            var siteurl = "<?php echo $_G['siteurl']?>"
            var userKey = siteurl.replace('http://','').replace(/\//g,'-') + 'wxshare'
            var loginDiv = $('#login-div')
            var regDiv = $('#reg-div')
            var registBtn = $('#registBtn')

            hasBinding()

            $('#registBtn').on('click', function () {
                loginDiv.hide()
                regDiv.show()
            } )

            $('#regAndBindingBtn').on('click', function(){
                binding('register')
            })

            $('#bindingBtn').on('click', function(){
                binding('bind')
            })


            //判断用户是否已经绑定
            function hasBinding() {
                var getRequest = GetRequest()
                if (!!getRequest.openId && !!getRequest.oauthToken) {
                    openId = getRequest.openId
                    oauthToken = getRequest.oauthToken
                    tid = getRequest.state.split('_')[1]
                    $('#login-div').show()
                    return 
                } else {
                    tid = getRequest.state.split('_')[1]
                    return getUserInfoByToken(getRequest.token, function(err, response) {
                        if (err) {
                            $('#errcode').text('获取用户信息错误');
                            $('#message').show().delay(2000).hide(0);
                            return 
                        }
                        return window.location.href = siteurl+'mobcent/app/web/index.php?r=webapp/share&tid=' + tid
                    })
                }
            }

            //通过临时token获取用户信息
            function getUserInfoByToken(token, callback) {
                var _url = siteurl + 'mobcent/app/web/index.php?r=htmlapi/getuserinfo&token=' + token
                $.get(_url, function(data){
                    if (data.rs == 0 && data.errcode) {
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return window.location.href = siteurl + 'mobcent/app/web/index.php?r=webapp/share&act=reply&tid=' + tid
                    }
                    var value = {}
                    if (data.uid) value.uid = data.uid;
                    if (data.userName) value.userName = data.userName;
                    if (data.avatar) value.avatar = data.avatar;
                    if (data.token || data.accessToken) value.accessToken = data.token || data.accessToken;
                    if (data.secret || data.accessSecret) value.accessSecret = data.secret || data.accessSecret;
                    localStorage.setItem(userKey, JSON.stringify(value))
                    return callback(null)
                }, 'json')
            }

            //获取url中的"?"符后的字串
            function GetRequest() {
                var url = location.search
                var theRequest = {}
                if (url.indexOf('?') != -1) {
                    var str = url.substr(1)
                    strs = str.split('&')
                    for(var i = 0; i < strs.length; i++) {
                        theRequest[strs[i].split('=')[0]]=(strs[i].split("=")[1])
                    }
                }
                return theRequest
            }

            //微信账号和用户帐号绑定
            function binding(act){
                var username = document.getElementById('username').value.trim()
                var password = document.getElementById('password').value.trim()
                var regUsername = document.getElementById('regUsername').value.trim()
                var regEmail = document.getElementById('regEmail').value.trim()
                var regPassword = document.getElementById('regPassword').value.trim() 
                if (act === 'bind' && username === '') {
                    $('#errcode').text('用户名不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return
                }  
                if (act === 'bind' && password === '') {
                    $('#errcode').text('密码不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
                if (act === 'register' && regUsername === '') {
                    $('#errcode').text('用户名不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
                if (act === 'register' && regEmail === '') {
                    $('#errcode').text('邮箱不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
                if (act === 'register' && regPassword === '') {
                    $('#errcode').text('密码不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return 
                }
                var _url = "<?php echo $_G['siteurl']?>mobcent/app/web/index.php?r=user/saveplatforminfo&platformId=60&oauthToken=" + oauthToken + '&openId=' + openId
                if (username) _url += '&username=' + username
                if (password) _url += '&password=' + password
                if (act) _url += '&act=' + act 
                if (regUsername) _url += '&username=' + regUsername
                if (regEmail) _url += '&email=' + regEmail
                if (regPassword) _url += '&password=' + regPassword
                $.get(_url, function(data){
                    if (data.rs == 0 && data.errcode) {
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return 
                    }
                    var value = {}
                    if (data.body.uid) value.uid = data.body.uid;
                    if (data.body.userName) value.userName = data.body.userName;
                    if (data.body.avatar) value.avatar = data.body.avatar;
                    if (data.body.token || data.body.accessToken) value.accessToken = data.body.token || data.body.accessToken;
                    if (data.body.secret || data.body.accessSecret) value.accessSecret = data.body.secret || data.body.accessSecret;
                    localStorage.setItem(userKey, JSON.stringify(value))
                    return window.location.href = siteurl + 'mobcent/app/web/index.php?r=webapp/sharelogin&act=reply&tid=' + tid
                }, 'json')
            }
        })
    </script>
</head>
<body>
    <div class="container">
        <div id="login-div" >
            <div class="login-title">请登录账户完成绑定</div>
            <form action="" onsubmit="return false;" >
                <div class="form-item">
                    <input class="login-input" type="text" name="username" id="username" placeholder="请输入用户名/手机号"/>
                </div>
                <div class="form-item">
                    <input class="login-input" type="password" name="password" id="password"placeholder="请输入密码" />
                </div>
                <div class="form-item">
                    <input class="login-btn" type="submit" id="bindingBtn" value="绑定" />
                </div>
                <div class="form-item">
                    <a class="regist" id="registBtn">新注册用户进行绑定</a>
                </div>
            </form>
        </div>
        <div id="reg-div">
            <div>
                <p>请注册账户完成绑定</p>
            </div>
            <form onsubmit="return false;" >
                <div class="form-item">
                    <input class="login-input" type="text" name="regUsername" id="regUsername" placeholder="请输入用户名"></input>
                </div>
                <div class="form-item">
                    <input class="login-input" type="email" name="regEmail" id="regEmail" placeholder="请输入您的邮箱"></input>
                </div>
                <div class="form-item">
                    <input class="login-input" type="password" name="regPassword" id="regPassword" placeholder="请输入密码"></input>
                </div>
                <div class="form-item">
                    <input class="login-btn" type="submit" id="regAndBindingBtn" value="注册并绑定" />
                </div>
            </form>
        </div>
        <div class="message" id="message">
            <p id="errcode"></p>
        </div>
    </div>
    
    
</body>
</html>
