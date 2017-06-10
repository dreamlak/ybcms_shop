<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:62:"F:\myweb\ybcms_shop/application/admin\view\wechat\setting.html";i:1495300932;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1495297878;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<!--CSS部分 -->
<link href="__JS__bootstrap\css/bootstrap.min.css" rel="stylesheet">
<link href="__CSS__font-awesome/font-awesome.min.css" rel="stylesheet">
<link href="__CSS__font-ybcms/style.css" rel="stylesheet">
<link href="__CSS__admin/style.css" rel="stylesheet">

<script type="text/javascript" src="__JS__jquery/jquery-1.11.1.min.js"></script>
<!--[if lt IE 9]>
<script type="text/javascript" src="__JS__ie/html5shiv.min.js"></script>
<script type="text/javascript" src="__JS__ie/respond.min.js"></script>
<![endif]-->
<!--JS部分 -->

<script type="text/javascript">
var ThinkPHP=window.Think={"APP":"","DEEP":"/","MODEL":["3","true","<?php echo config('url_html_suffix'); ?>"],"VAR":["m","c","a"]}
var require={urlArgs:'v=<?php echo time(); ?>'};
</script>

<script type="text/javascript" src="__JS__util.js"></script>
<script type="text/javascript" src="__JS__require.js"></script>
<script type="text/javascript" src="__JS__main.js"></script>

<script type="text/javascript" src="__JS__admin/common.js"></script>


<style type="text/css">
.col-xs-1,.col-sm-1,.col-md-1,.col-lg-1,
.col-xs-2,.col-sm-2,.col-md-2,.col-lg-2,
.col-xs-3,.col-sm-3,.col-md-3,.col-lg-3,
.col-xs-4,.col-sm-4,.col-md-4,.col-lg-4,
.col-xs-5,.col-sm-5,.col-md-5,.col-lg-5,
.col-xs-6,.col-sm-6,.col-md-6,.col-lg-6,
.col-xs-7,.col-sm-7,.col-md-7,.col-lg-7,
.col-xs-8,.col-sm-8,.col-md-8,.col-lg-8,
.col-xs-9,.col-sm-9,.col-md-9,.col-lg-9,
.col-xs-10,.col-sm-10,.col-md-10,.col-lg-10,
.col-xs-11,.col-sm-11,.col-md-11,.col-lg-11,
.col-xs-12,.col-sm-12,.col-md-12,.col-lg-12{padding:0 5px 0}
.form-group{margin-bottom:6px;}
.form-group label.control-label{font-weight:normal;padding:7px 5px 0 0;}
.form-group span.help-block{padding:5px 0;margin:0;color:#999;}
.form-group span.help-block:hover{color:#000;}
</style>

</head>
<body>
<!--中间部分 -->	

<div class="main-content">
	<div class="main-content-bar">
		<div class="item-title">
			<h3>微信接口配置</h3>
			<h5>配置微信公众号与系统绑定</h5>
		</div>
	</div>
	<!--操作提示-->
	<div id="explanation" class="explanation">
		<div id="checkZoom" class="title">
			<i class="fa fa-lightbulb-o"></i>
			<h4 title="操作要点提示">操作提示</h4>
			<span title="收起提示" id="explanationZoom"></span>
		</div>
		<ol id="listZoom">
			<li>登录 微信公众平台（mp.weixin.qq.com），点击高级功能 → 进入开发模式</li>
			<li>点击“开发者模式”进入</li>
			<li>填写接口配置信息（填写上面对应的URL和token值）</li>
			<li>成功后，确认开启开发者模式</li>
			<li>你的接口URL是：<span style="color:red;"><?php echo $config['wx_url']; ?></span></li>
			<li>您的token是：<span style="color:red;"><?php echo $config['wx_token']; ?></span></li>
		</ol>
	</div>
	<form action="" method="post" class="form-horizontal">
		<input type="hidden" value="<?php echo $config['wx_token']; ?>" name="wx_token" />
        <div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">公众号名称：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_wechat_name']; ?>" name="wx_wechat_name" id="wx_wechat_name" placeholder="您的公众号名称" class="form-control">
		    </div>
		</div>
    	<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">公众号原始id：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_wechat_id']; ?>" name="wx_wechat_id" id="wx_wechat_id" placeholder="公众号唯一身份账号" class="form-control">
           	</div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信号：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_username']; ?>" name="wx_username" id="wx_username" placeholder="微信公众平台所设置的账号" class="form-control">
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信头像地址：</label>
		    <div class="col-sm-9 col-xs-12">
		    	<?php echo tpl_upimg('wx_avatar','wx_avatar',$config['wx_avatar'],0,is_login(),0,'头像URL,可直接填写文件远程地址','','上传头像'); ?>
           	</div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信二维码地址：</label>
		    <div class="col-sm-9 col-xs-12">
		    	<?php echo tpl_upimg('wx_qrcode','wx_qrcode',$config['wx_qrcode'],0,is_login(),0,'二维码URL,可直接填写文件远程地址','','上传二维码'); ?>
           	</div>
		</div>
        <div class="form-group">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">微信类型：</label>
		    <div class="col-sm-9 col-xs-12">
	            <label class="radio-inline"><input type="radio" name="wx_type" value="0" <?php if($config['wx_type']==0): ?>checked<?php endif; ?>>订阅号</label>
	            <label class="radio-inline"><input type="radio" name="wx_type" value="1" <?php if($config['wx_type']==1): ?>checked<?php endif; ?>>认证订阅号</label>
	            <label class="radio-inline"><input type="radio" name="wx_type" value="2" <?php if($config['wx_type']==2): ?>checked<?php endif; ?>>服务号</label>
	            <label class="radio-inline"><input type="radio" name="wx_type" value="3" <?php if($config['wx_type']==3): ?>checked<?php endif; ?>>认证服务号</label>
            	<span class="help-block">公众号注册认证的类型</span>
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">AppId：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_appid']; ?>" name="wx_appid" id="wx_appid" placeholder="高级调用功能的app id, 请在微信开发模式后台查询" class="form-control">
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">AppSecret：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_appsecret']; ?>" name="wx_appsecret" id="wx_appsecret" placeholder="高级调用功能的密钥" class="form-control">
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">EncodingAESKey：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_encodingaeskey']; ?>" name="wx_encodingaeskey" id="wx_encodingaeskey" placeholder="公众平台申请到的EncodingAesKey(如果选择了安全模式 必须填写此项)" class="form-control">
		    </div>
		</div>
		
		<!--div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商户ID：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_mch_id']; ?>" name="wx_mch_id" id="wx_mch_id" placeholder="微信支付，商户ID（可选）" class="form-control">
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商户密钥：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_partnerkey']; ?>" name="wx_partnerkey" id="wx_partnerkey" placeholder="微信支付，密钥（可选）" class="form-control">
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">CER证书地址：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_ssl_cer']; ?>" name="wx_ssl_cer" id="wx_ssl_cer" placeholder="微信支付，双向证书（可选，操作退款或打款时必需，默认位置在./Wechat下，请保证写权限）" class="form-control">
		    </div>
		</div>
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">KEY证书地址：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_ssl_key']; ?>" name="wx_ssl_key" id="wx_ssl_key" placeholder="微信支付，双向证书（可选，操作退款或打款时必需，默认位置在./Wechat下，请保证写权限）" class="form-control">
		    </div>
		</div-->
		
		<div class="form-group article">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label">SDK缓存目录：</label>
		    <div class="col-sm-9 col-xs-12">
         		<input type="text" value="<?php echo $config['wx_cache_path']; ?>" name="wx_cache_path" id="wx_cache_path" placeholder="设置SDK缓存目录（可选，默认位置在./Wechat/Cache下，请保证写权限）" class="form-control">
		    </div>
		</div>
		<div class="form-group">
		    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
		    <div class="col-sm-9 col-xs-12">
		    	<input type="button" value="提交" id="submitbtn" class="btn btn-primary col-lg-1">
		    </div>
		</div>
    </form>
</div>

<script type="text/javascript">
$(document).ready(function(){
	require(['bootstrap','think','layer']);
	//回车事件
	document.onkeydown = function(e) {
		var theEvent = window.event || e;
		var code = theEvent.keyCode || theEvent.which;
		if(code == 13){
			$("#submitbtn").click();
		}
	}
	//提交
	$('#submitbtn').click(function(){
		var fields = $('form').serializeArray();
		var ll = layer.load('正在处理，请稍后...', 3);
		$.post(Think.U('Admin/Wechat/setting'),fields,function(data){
	        if(data.status==1) {
	        	layer.msg(data.msg,{icon:6}, function(){
					location.reload();
				});
	        } else {
	        	layer.msg(data.msg, {icon:5});
	        }
	        layer.close(ll);
	        return false;
	    });
	});
});
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>