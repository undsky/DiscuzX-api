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

### issues

+ 板块不显示

论坛/板块管理，设置两级
 应用/小云APP手机客户端/设置/能在客户端显示的版块

+ 板块分类设置

![](/resource/A6F0136B-2EC8-41E3-91C4-28678C6B3366.png)

+ 开启 HTTPS

![](/resource/D922C2AB-A5E2-4BCF-AC6F-960A3B53EFFF.png)
![](/resource/20FC4FAE-60D9-4F14-B08A-F438ADBEB603.png)

+ 开启相册

后台--用户--用户组--编辑--空间相关--上传图片选择:是 上传图片需要审核:否 相册单张图片最大尺寸(单位K 1M=1024K)

![](/resource/DFD49037-FF1F-405B-AF00-BF308ACCA60E.png)

+ 您当前的访问请求当中含有非法字符，已经被系统拒绝

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

+ 帖子图片不显示

```sql
update  bbs_common_usergroup_field set allowgetimage = 1

```

![](/resource/19D88D48-93A9-41CC-8EF9-0390F66B9023.png)

+ 修改头像无效

站长/UCenter设置/头像调用方式：使用静态地址调用头像

+ Undefined offset: 1 in \www\mobcent\app\components\discuz\discuz_core.php on line 100

在程序开头加一句：
error_reporting(E_ALL & ~E_NOTICE); 或error_reporting(E_ALL ^ E_NOTICE);

或者
修改php.ini
error_reporting = E_ALL & ~E_NOTICE