<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
    <title>安米APP管理平台</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="Author" content="" />
    <meta name="CopyRight" content="" />
</head>
<frameset rows="51,*"  frameborder="no" border="0" framespacing="0">
    <!--头部-->
    <frame src="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/index/top" name="top" noresize="noresize" frameborder="0"  scrolling="no" marginwidth="0" marginheight="0" />
    <!--主体部分-->
    <frameset cols="190,*">
        <!--主体左部分-->
        <frame style="border-right:1px #cccccc  solid; background: #f2f2f2" src="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/index/left" name="left" noresize="noresize" frameborder="0" scrolling="auto" marginwidth="0" marginheight="0" />
        <!--主体右部分-->
        <frame style="padding:10px 0px 0px 10px" src="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/index.php?r=admin/index/main" name="main" frameborder="0" scrolling="auto" marginwidth="0" marginheight="0" />
</frameset>
</html>