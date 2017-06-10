<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:46:"F:\myweb\ybcms_shop\data/tpl\dispatch_jump.tpl";i:1483382287;}*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>跳转提示</title>
<style type="text/css">
	*{margin:0;padding:0;}
    body{background:#fff;font-family:"Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif;color:#333;font-size:16px;}
    .system-message{padding:20px 10px;display:table;margin:10% auto 0;border:1px #ddd solid;border-radius:10px;}
	.system-message.success,.system-message.error{padding-left:130px;}
	.system-message.success{background:url('/static/images/public/success.png') no-repeat 10px center;}
	.system-message.error{background:url('/static/images/public/cancel.png') no-repeat 10px center;}
    .system-message .msginfo{line-height:1.8em;font-size:26px;}
    .system-message .jump{padding-top:10px;color:#666;}
    .system-message .jump a{color:#666;}
    .system-message .detail{font-size:12px;line-height:20px;margin-top:12px;display:none;}
</style>
</head>
<body>
<?php
	switch ($code) {
		case 1:
			$classname='success';
			//$msg=strip_tags($msg);
			break;
		case 0:
			$classname='error';
			//$msg=strip_tags($msg);
			break;
	}
?>
<div class="system-message <?php echo $classname;?>">
    <p class="msginfo"><?php echo($msg);?></p>
    <p class="detail"></p>
    <p class="jump">
        页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
    </p>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
       	var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>
