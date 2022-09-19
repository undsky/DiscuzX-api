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
    <title>欢迎登录</title>
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
            background-color: #FFF;
            border-color: #E3E3E3;
            border-width: 1px;
            box-sizing: border-box;
            color: #565656;
            font: inherit;
            padding: 7px 12px;
            height: 50px;
            display: block;
            width: 100%;
            border-style: solid;
            border-radius: 6px;
            outline: 0;
            font-size: 20px;
        }
        .login-btn{
            -webkit-appearance: none;
            color: #fff;
            border-radius: 3px;
            text-decoration: none;
            cursor: pointer;
            text-align: center;
            border: 0;
            padding: 6px 12px;
            background-color: #2db7f5;
            width: 100%;
            height: 50px;
            font-size: 20px;
        }
        .regist{
            display: block;
            text-align: center;
            text-decoration: none;
            color: #2db7f5;
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
            var siteurl = "<?php echo $_G['siteurl']?>"
            var userKey = siteurl.replace('http://','').replace(/\//g,'-') + 'wxshare'
            var tid = location.search.split('&tid=')[1]

            $('.regist').on('click', function() {
                $('#login-div').hide();
                $('#regist-div').show();
            })

            $('#loginBtn').on('click', function() {
                login()
            })

            $('#registBtn').on('click', function() {
                inputSubmit()
            })


            //用户登录
            function login() {
                var username = document.getElementById('username')
                var password = document.getElementById('password')
                var usernameValue = username.value.trim()
                var passwordValue = password.value.trim()
                console.log(usernameValue, passwordValue)
                if (usernameValue === '') {
                    $('#errcode').text('用户名不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return
                }
                if (passwordValue === '') {
                    $('#errcode').text('密码不能为空');
                    $('#message').show().delay(2000).hide(0);
                    return
                }
                fetchLogin(usernameValue, passwordValue, function(data) {
                    if (data.errcode) {
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return
                    } else {
                        var value = {}
                        if (data.uid) value.uid = data.uid;
                        if (data.userName) value.userName = data.userName;
                        if (data.avatar) value.avatar = data.avatar;
                        if (data.token || data.accessToken) value.accessToken = data.token || data.accessToken;
                        if (data.secret || data.accessSecret) value.accessSecret = data.secret || data.accessSecret;
                    }
                    localStorage.setItem(userKey,JSON.stringify(value))
                    return window.location.href = siteurl + 'mobcent/app/web/index.php?r=webapp/sharelogin&act=reply&tid=' + tid
                })
            }
            //获取用户信息
            function fetchLogin(name, password, callback) {
                var _url = siteurl + 'mobcent/app/web/index.php?r=user/login&username=' + name + '&password=' + password + '&type=login'
                $.get(_url, function(data){
                    if (data.errcode) {
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return callback({errcode: data.errcode})
                    }
                    return callback(data)
                }, 'json')
            }
            //校验注册用户
            function inputSubmit() {
                var username = document.getElementById('regUsername').value.trim()
                var email = document.getElementById('regEmail').value.trim()
                var password = document.getElementById('regPassword').value.trim()
                var formId = document.getElementById('formId')
                if (username === '') return alert('username is not')
                if (email === '') return alert('email is not')
                if (password === '') return alert('password is not')
                return registerUser(username, email, password, function(data){
                    if (data.errcode) { 
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return
                    } else {    
                        var value = {}
                        if (data.uid) value.uid = data.uid;
                        if (data.userName) value.userName = data.userName;
                        if (data.avatar) value.avatar = data.avatar;
                        if (data.token || data.accessToken) value.accessToken = data.token || data.accessToken;
                        if (data.secret || data.accessSecret) value.accessSecret = data.secret || data.accessSecret;
                        localStorage.setItem(userKey,JSON.stringify(value))
                        return window.location.href = siteurl + 'mobcent/app/web/index.php?r=webapp/sharelogin&act=reply&tid=' + tid

                        }
                })

            }
            //注册用户
            function registerUser (username, email, password, callback) {
                $.get(siteurl + 'mobcent/app/web/index.php?r=user/register&username=' + username + '&email=' + email + '&password='+ password, function(data) {
                    if (data.errcode) { 
                        $('#errcode').text(data.errcode);
                        $('#message').show().delay(2000).hide(0);
                        return
                    }
                    return callback(data)
                }, 'json')
            }
        })
    </script>
</head>
<body>
    <div class="container">
        <div id="login-div">
            <div class="login-title">请登录账户</div>
            <form onsubmit="return false;">
                <div class="form-item">
                    <input class="login-input" type="text" id="username" name="username" placeholder="请输入用户名/手机号"/>
                </div>
                <div class="form-item">
                    <input class="login-input" type="password" id="password" name="password" placeholder="请输入密码" />
                </div>
                <div class="form-item">
                    <input class="login-btn" type="submit" id="loginBtn" value="登录" />
                </div>
            </form>
            
            <div class="form-item">
                <a class="regist" >新注册用户</a>
            </div>
        </div>
        <div id="regist-div" style="display: none">
            <div>
                <p>请注册账户用户</p>
            </div>
            <form onsubmit="return false;" >
                <div class="form-item">
                    <input class="login-input" type="text" name="username" id="regUsername" placeholder="请输入用户名"></input>
                </div>
                <div class="form-item">
                    <input class="login-input" type="email" name="email" id="regEmail" placeholder="请输入您的邮箱"></input>
                </div>
                <div class="form-item">
                    <input class="login-input" type="password" name="password" id="regPassword" placeholder="请输入密码"></input>
                </div>
                <div class="form-item">
                    <input class="login-btn" type="submit" id="registBtn" value="注册" />
                </div>
            </form>
        </div>
        <div class="message" id="message">
            <p id="errcode"></p>
        </div>
    </div>
</body>
</html>
