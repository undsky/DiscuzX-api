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
    <title>注册</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue",Helvetica,"Hiragino Sans GB","Microsoft YaHei",Arial,sans-serif;
            color: #3e3e3e;
            font-size: 17px;
            background: #f9f9f9;
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
            height: 40px;
            display: block;
            width: 100%;
            border-style: solid;
            border-radius: 4px;
            outline: 0;
        }
        .login-btn{
            color: #fff;
            border-radius: 3px;
            text-decoration: none;
            cursor: pointer;
            text-align: center;
            border: 0;
            padding: 6px 12px;
            background-color: #2db7f5;
            width: 100%;
            height: 40px;
            font-size: 17px;
        }
    </style>
    <script type="text/javascript">
        var siteurl = "<?php echo $_G['siteurl']?>"
        var userKey = siteurl.replace('http://','').replace(/\//g,'-') + 'wxshare'
        function inputSubmit() {
            var username = document.getElementById('username').value.trim()
            var email = document.getElementById('email').value.trim()
            var password = document.getElementById('password').value.trim()
            var formId = document.getElementById('formId')
            if (username === '') return alert('username is not')
            if (email === '') return alert('email is not')
            if (password === '') return alert('password is not')
            return registerUser(username, email, password, function(data){
                if (data.errcode) {
                    return alert(data.errcode)
                } else {    
                    var value = {}
                    if (data.uid) value.uid = data.uid;
                    if (data.userName) value.userName = data.userName;
                    if (data.avatar) value.avatar = data.avatar;
                    if (data.token || data.accessToken) value.accessToken = data.token || data.accessToken;
                    if (data.secret || data.accessSecret) value.accessSecret = data.secret || data.accessSecret;
                    localStorage.setItem(userKey,JSON.stringify(value))
                    return window.location.href = siteurl + 'mobcent/app/web/index.php?r=webapp/sharelogin&act=reply&'

                    }
            })

        }
        //注册用户
        function registerUser (username, email, password, callback) {
            console.log(siteurl + 'mobcent/app/web/index.php?r=user/register&username=' + username + '&email=' + email + '&password='+ password)
            return fetch(siteurl + 'mobcent/app/web/index.php?r=user/register&username=' + username + '&email=' + email + '&password='+ password).then(function(res){
                if (res.ok) {
                    res.json().then(function(data) {
                        return callback(data)
                    })
                }
            }, function(error){
                console.log(error)
                return callback(error)
            })
        }
        
    </script>
    
</head>
<body>
    <div class="container">
        <div>
            <p>请注册账户完成绑定</p>
        </div>
        <form onsubmit="return false;" id="formId" >
            <div class="form-item">
                <input class="login-input" type="text" name="username" id="username" placeholder="请输入用户名"></input>
            </div>
            <div class="form-item">
                <input class="login-input" type="email" name="email" id="email" placeholder="请输入您的邮箱"></input>
            </div>
            <div class="form-item">
                <input class="login-input" type="password" name="password" id="password" placeholder="请输入密码"></input>
            </div>
            <div class="form-item">
                <input class="login-btn" type="submit" onclick="inputSubmit()" value="注册" />
            </div>
        </form>

    </div>


</body>
</html>