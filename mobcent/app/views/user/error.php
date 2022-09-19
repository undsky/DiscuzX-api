<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Cache-Control" content="no-transform" />
        <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport" />
        <script type="text/javascript" src="<?php echo $this->dzRootUrl; ?>/static/js/common.js"></script>
        <script type="text/javascript" src="<?php echo $this->rootUrl.'/js/'; ?>jquery-2.0.3.min.js"></script>

        <style type="text/css">
            a, a:visited {text-decoration: none; }
            .step3{ display: none }

            /* by pwh str */

            .button1{float:right;border-radius:5px; width:25%;border:3px solid #000; border:0; height:42px; line-height:38px; font-size:16px; color:#289ED7;}
            .button2{border-radius:5px; width:100%;border: 1px solid #289ED7; background:#289ED7; border:0; height:42px; line-height:38px; font-size:16px; color:#FFF;}
            .input1{-webkit-appearance:none; width:100%; float:left;height:40px; line-height:40px;  font-size: 16px; padding:1px 5px; display:block; border-radius:5px; border:1px solid #C9C9C9;margin-left:-5px;}

            .per_margin{margin:20px 10px;}
            .per_margintb{margin-top:20px;margin-bottom:20px;}
            .per_marginb{margin-bottom:20px;}
            /* by pwh end */
            #tips{ padding-top: 100px; font-size: 16px; text-align: center}
            .abtn_box{width: calc(100% - 20px); margin: auto 10px;}
.abtn_box .a_btn{display:inline-block;width: 100%;background-color: #ff003c;line-height: 40px;text-align: center;color: #fff;font-size: 18px;border-radius: 5px;margin-bottom: 10px;}
.abtn_box .toupiao{background-color: #5bcdca;}
            #codewin,#code2win,#smswin,#loginwin,#wxcode,#success,#alert{width:280px; height:100px;padding:4px 10px 10px;background-color:#FFFFFF;border:1px solid #05549d;color:#333333;line-height:24px;text-align:left;-webkit-box-shadow:5px 2px 6px #000;-moz-box-shadow:3px 3px 6px #555;  border-radius: 5px;}
        #loginwin {text-align: center;height:60px;padding:40px;}
        #success{text-align: center;height:150px; }

        #success h3{ font-size: 160%;margin-bottom: 10px; }
        #success p{ line-height: 2em; font-size: 12px; }
        #success  .a_btn{  background-color: #5bcdca;}
        #success  .abtn_box{ margin-top: 10px; }

        #alert {text-align: center;height:auto; }
        #alert p{ line-height: 2em; font-size: 120%; font-weight: bold }
        </style>

    </head>
    <body>
<script>
function connectAppbymeJavascriptBridge(callback) {
    if (window.AppbymeJavascriptBridge) {
        callback(AppbymeJavascriptBridge)
    } else {
        document.addEventListener('connectAppbymeJavascriptBridge', function() {
            callback(AppbymeJavascriptBridge)
        }, false)
    }
}
connectAppbymeJavascriptBridge(function(bridge){
                               
     //前面name，后面value
    
    var json ={
    
    }
    
    //自定义顶部导航按钮 customButton(json)
    AppbymeJavascriptBridge.customButton(JSON.stringify(json));
    $(document).on('click','.savatar img',function(){
          var id = $(this).attr('data-id');
        AppbymeJavascriptBridge.userCenter(id);
    });
});
function closeActivity(){
    AppbymeJavascriptBridge.closeActivity();
}

</script>
  <div id="alert" style="display: none;">
         
    </div>
        
        <h3 id="usernameTPL"></h3>
  
        <div class="per_margin" id="gmform">
	<div class="per_margintb step1 form_item">
            <b>提示：</b>
               
                <div style="clear:both;"></div>
            </div>
            <div class="per_margintb step1 form_item" style="text-align:center;color:#ff003c">
                <?php echo $msg?>
            </div>

            <?php
            if(!$status){
            ?>
            <div class="per_margintb step2 form_item" style="margin-top:50px">
                <button type="button" onclick="history.back()" id="BtnSubmit2" class="button2">返回</button>
               
                <div style="clear:both;"></div>
            </div>
            <?php }?>
			
			<?php
            if($status==100){
            ?>
            <div class="per_margintb step2 form_item" style="margin-top:50px">
                <button type="button" onclick="closeActivity()" id="BtnSubmit2" class="button2">确定</button>
               
                <div style="clear:both;"></div>
            </div>
            <?php }?>
            <div style="clear:both;"></div>
        </div>
      
        
    
    </body>
</html>

