<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:58:"F:\myweb\ybcms_shop/application/admin\view\index\main.html";i:1497089756;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1497089756;}*/ ?>
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


<style>
.header_title{border-bottom:1px #ddd solid;padding-bottom:10px;}
.text-wrapper{font-size:16px;}
.tab-content{height:280px;}
</style>

</head>
<body>
<!--中间部分 -->	

<link href="__CSS__admin/simplify.min.css" rel="stylesheet">
<div class="padding-md">
	<div class="row header_title">
		<div class="col-sm-6">
			<div class="page-title">控制台中心</div>
			<div class="page-sub-header">欢迎进入YBCMS控制台，上次登录时间：<?php echo lastlogintime(); ?>，上次登录IP：<?php echo lastloginip(); ?></div>
		</div>
	</div>
	<div class="row m-top-md">
		<div class="col-lg-3 col-sm-6">
			<div class="statistic-box bg-success m-bottom-sm">
				<div class="statistic-value"><?php echo todayOrder(); ?></div>
				<div class="statistic-title"><i class="fa fa-shopping-cart"></i> 今日订单总数</div>
				<div class="statistic-icon-background"><i class="fa fa-shopping-cart"></i></div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="statistic-box bg-danger m-bottom-sm">
				<div class="statistic-value"><?php echo todayUser(); ?></div>
				<div class="statistic-title"><i class="fa fa-user"></i> 今日会员总数</div>
				<div class="statistic-icon-background"><i class="fa fa-user"></i></div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="statistic-box bg-info m-bottom-sm">
				<div class="statistic-value"><?php echo totalgoods(); ?></div>
				<div class="statistic-title"><i class="fa fa-bar-chart"></i> 上架商品总数</div>
				<div class="statistic-icon-background"><i class="fa fa-bar-chart"></i></div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-6">
			<div class="statistic-box bg-purple m-bottom-sm">
				<div class="statistic-value"><?php echo totalUser(); ?></div>
				<div class="statistic-title"><i class="fa fa-group"></i> 会员总数</div>
				<div class="statistic-icon-background"><i class="fa fa-group"></i></div>
			</div>
		</div>
	</div>
	
	<div class="conPanel">
		<div class="conPanel_title">
			<h3><i class="yb-warrant"></i>版权信息</h3>
		</div>
		<div class="conPanel_con">
			<table class="mytable" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<th>程序版本:</th><td><?php echo $system['version']; ?> / ThinkPHP <?php echo THINK_VERSION; ?></td>
						<th>更新时间:</th><td><?php echo $system['updates']; ?></td>
					</tr>
					<tr>
						<th>程序开发:</th><td><?php echo $system['programmer']; ?></td>
						<th>版权所有:</th><td><?php echo $system['copyright']; ?></td>
					</tr>
					<tr>
						<th>官方授权:</th><td><?php echo $system['authorize']; ?></td>
						<th>官方网站:</th><td><?php echo $system['authweb']; ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="conPanel">
		<div class="conPanel_title">
			<h3><i class="yb-warrant"></i>系统信息</h3>
		</div>
		<div class="conPanel_con">
			<table class="mytable" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<th>服务器操作系统:</th><td><?php echo $system['os']; ?></td>
						<th>服务器域名/IP:</th><td><?php echo $system['domain']; ?>&nbsp;/&nbsp;<?php echo $system['ip']; ?></td>
					</tr>
					<tr>
						<th>服务环景:</th><td><?php echo $system['web_server']; ?></td>
						<th>安全模式:</th><td><?php echo $system['safe_mode']; ?></td>
					</tr>
					<tr>
						<th>PHP版本:</th><td><?php echo $system['phpversion']; ?></td>
						<th>数据库信息:</th><td>MySQL(<?php echo $system['mysqlinfo']; ?>) &nbsp;/&nbsp; 数据库大小(<?php echo $system['mysqlsize']; ?>)</td>
					</tr>
					<tr>
						<th>Zlib支持:</th><td><?php echo $system['zlib']; ?></td>
						<th>GD库是否开启:</th><td><?php echo $system['gd']; ?></td>
					</tr>
					<tr>
						<th>Curl支持:</th><td><?php echo $system['curl']; ?></td>
						<th>Socket支持:</th><td><?php echo $system['socket']; ?></td>
					</tr>
					<tr>
						<th>上传文件:</th><td><?php echo $system['fileupload']; ?></td>
						<th>系统文件占用大小:</th><td id="filesize">0</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	<p style="text-align:center;padding:20px 0 0 0;font-size:12px;">
		版权所有 © <a href="http://www.ybcms.com" target="_blank">Ybcms开发团队</a>，并保留所有权利。
	</p>
</div>
<script type="text/javascript">
$(function(){
	require(['think']);
	
	$('#filesize').html('<img src="/static/images/public/loading.gif" width="16" height="16"/>');
	setTimeout(function(){
		ajaxsyssize();
	},1000);
});
function ajaxsyssize(){
	$.post(Think.U('Admin/Index/ajaxsyssize'),{},function(data){
        if(data!='') {
			$('#filesize').text(data);
        }
        return false;
    });
}
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>