# DiscuzX

> 使用范围：DiscuzX2.5，X3，X3.1，X3.2，X3.3，X3.4、F1.0，L1.0 GBK/简体UTF-8/BIG5/繁体BIG5

### 全新安装

[Discuz!X3.4全新安装教程](https://www.dismall.com/thread-77-1-1.html)

### 插件部署

1. 安装 [小云插件](https://addon.dismall.com/plugins/appbyme_app.html)
2. 安装 [签到插件](https://addon.dismall.com/plugins/dsu_paulsign.html)
1. 将mobcent接口包上传到 Discuz 根目录
2. 访问链接 <http://你的论坛网址/mobcent/requirements/index.php> 确保服务器的环境符合插件要求

### uni-app 客户端

[开源版](https://ext.dcloud.net.cn/plugin?id=5450)

1. 修改接口地址

``` javascript 
// ./common/request.js
const config = {
	baseURL: 'https://你的论坛网址/mobcent/app/web/index.php',
	...
}
```

### 常见问题

+ #### 板块不显示

1. 论坛/板块管理，设置两级
1. 应用/小云APP手机客户端/设置/能在客户端显示的版块

+ #### 板块分类设置

![](/resource/板块分类设置.png)

+ #### 开启 HTTPS

![](/resource/开启HTTPS1.png)
![](/resource/开启HTTPS2.png)

+ #### 开启相册

后台--用户--用户组--编辑--空间相关--上传图片选择:是 上传图片需要审核:否 相册单张图片最大尺寸(单位K 1M=1024K)

![](/resource/开启相册.png)

+ #### 您当前的访问请求当中含有非法字符，已经被系统拒绝

解决方法1：

```php
// ./source/class/discuz/discuz_application.php
// 修改 _xss_check() 方法
private function _xss_check() {


$temp = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));


if(strpos($temp, '<') !== false || strpos($temp, '"') !== false || strpos($temp, 'CONTENT-TRANSFER-ENCODING') !== false) {


system_error('request_tainting');
}
return true;


}
```

解决方法2：

通过修改discuz站点的配置文件config/config_global.php，把安全检查禁用

```php
$_config['security']['urlxssdefend'] = 1;
// 改为
$_config['security']['urlxssdefend'] = 0;
```

+ #### 帖子图片不显示

```sql
update  表前缀_common_usergroup_field set allowgetimage = 1

```

![](/resource/帖子图片不显示.png)

+ #### 修改头像无效

站长/UCenter设置/头像调用方式：使用静态地址调用头像

+ #### Undefined offset: 1 in \www\mobcent\app\components\discuz\discuz_core.php on line 100

解决方法1：

在程序开头加一句：
error_reporting(E_ALL & ~E_NOTICE); 或error_reporting(E_ALL ^ E_NOTICE);

解决方法2：

修改php.ini
error_reporting = E_ALL & ~E_NOTICE

+ #### 安装本地插件

![](/resource/安装本地插件.png)

安装本地插件提示非正版

![](/resource/安装本地插件提示非正版.png)