<?php

header("Content-Type: text/html; charset=utf-8");
global $_G;
?>

<!doctype html>
<html>
<head>
<!--	<title></title>-->
    <meta charset="utf-8">
    <meta http-equiv="Cache-Control" content="no-transform"/>
    <link rel="alternate" type="application/vnd.wap.xhtml+xml" media="handheld" href="target"/>
    <meta name="viewport" content="width=device-width,user-scalable=no,minimum-scale=1.0,initial-scale=1.0">
    <link rel="stylesheet" href="../web/css/mobile.css">
    <script type="text/javascript" src ="<?php echo $this->dzRootUrl; ?>/static/js/common.js"></script>
</head>
<body>
<script type="text/javascript" src="<?php echo $this->dzRootUrl; ?>/mobcent/app/web/js/appbyme/appbyme.js"></script>
<script type="text/javascript">
connectAppbymeJavascriptBridge(function (bridge) {
	var json = {};
 	AppbymeJavascriptBridge.customButton(JSON.stringify(json));
 })
</script>