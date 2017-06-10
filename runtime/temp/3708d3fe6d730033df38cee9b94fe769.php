<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:59:"F:\myweb\ybcms_shop/application/admin\view\index\index.html";i:1497089756;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>YBCMS 后台管理</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<!--CSS部分 -->
<link href="__JS__bootstrap\css/bootstrap.min.css" rel="stylesheet">
<link href="__CSS__font-awesome/font-awesome.min.css" rel="stylesheet">
<link href="__CSS__font-ybcms/style.css" rel="stylesheet">
<link href="__CSS__admin/simplify.min.css" rel="stylesheet">
<!--JS部分 -->
<script type="text/javascript" src="__JS__jquery/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="__JS__bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="__JS__jquery/jquery.cookie.js"></script>
<!--[if lt IE 9]>
<script type="text/javascript" src="__JS__ie/html5shiv.min.js"></script>
<script type="text/javascript" src="__JS__ie/respond.min.js"></script>
<![endif]-->
<script type="text/javascript" src="__JS__admin/jquery.slimscroll.min.js"></script>
<script type="text/javascript">
var ThinkPHP=window.Think={"APP":"","DEEP":"/","MODEL":["3","","<?php echo config('url_html_suffix'); ?>"],"VAR":["m","c","a"]}
</script>
<script type="text/javascript" src="__JS__think.js"></script>
</head>
<body>
<div class="wrapper">
	<!--头部分 -->	
	<header class="top-nav">
		<div class="top-nav-inner">
			<!--缩小时显示部分-->
			<div class="nav-header">
				<!--缩小时菜单开关按钮-->
				<button type="button" class="navbar-toggle pull-left sidebar-toggle" id="sidebarToggleSM">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<!--缩小时用户信息-->
				<ul class="nav-notification pull-right">
					<li>
						<a href="<?php echo url('Admin/Index/index'); ?>" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog fa-lg"></i></a>
						<span class="badge badge-danger bounceIn">100</span>
						<ul class="dropdown-menu dropdown-sm pull-right user-dropdown">
							<li class="user-avatar">
								<img src="" alt="" class="img-circle">
								<div class="user-content">
									<h5 class="no-m-bottom"><?php echo get_adminName(); ?></h5>
									<div class="m-top-xs">
										<a href="<?php echo url('Admin/Admin/myinfo'); ?>" class="m-right-sm">个人资料</a>
										<a href="<?php echo url('Admin/Admin/editmypwd'); ?>">修改密码</a>
										<a href="<?php echo url('Admin/Index/outlogin'); ?>">退出</a>
									</div>
								</div>
							</li>	  
						</ul>
					</li>
				</ul>
				<a href="<?php echo url('Admin/Index/index'); ?>" class="brand">
					<span class="brand-name"><img src="__IMG__admin/logo.png" alt=""/></span>
				</a>
			</div>
			<!--非缩小时显示部分-->
			<div class="nav-container">
				<!--菜单开关按钮-->
				<button type="button" class="navbar-toggle pull-left sidebar-toggle" id="sidebarToggleLG">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<div class="nav-notification">
					<span class="li"> <?php echo $version; ?>
					<!--a href="<?php echo url('admin/index/test'); ?>">调试测试</a--></span>
				</div>
				<div class="pull-right m-right-sm">
					<ul class="nav-notification" style="margin-bottom:0">
						<li><a href="<?php echo url('admin/index/clearcache'); ?>" target="subpage" title="清除缓存"><i class="fa fa-trash"></i> 缓存</a></li>
						<li><a href="/" title="访问前台" target="_blank"><i class="fa fa-desktop"></i> 前台</a></li>
						<!--li><a href="http://help.ybcms.com" title="前台" target="_blank"><i class="fa fa-question-circle"></i> 帮助</a></li-->
					</ul>
					<div class="user-block hidden-xs">
						<a href="#" id="userToggle" data-toggle="dropdown">
							<div class="user-detail inline-block"><i class="fa fa-user"></i> <?php echo get_adminName(); ?>（<?php echo get_rolename(); ?>）<i class="fa fa-angle-down"></i></div>
						</a>
						<div class="panel border dropdown-menu user-panel">
							<div class="panel-body paddingTB-sm">
								<ul>
									<li><a href="<?php echo url('Admin/Admin/myinfo'); ?>" target="subpage"><i class="fa fa-edit fa-lg"></i><span class="m-left-xs">我的个人资料</span></a></li>
									<li><a href="<?php echo url('Admin/Admin/editmypwd'); ?>" target="subpage"><i class="fa fa-inbox fa-lg"></i><span class="m-left-xs">修改密码</span></a></li>
									<li><a href="<?php echo url('Admin/Index/outlogin'); ?>"><i class="fa fa-power-off fa-lg"></i><span class="m-left-xs">退出</span></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</header>
	<!--头部结束-->
	
	<!--左边菜单部分 -->	
	<aside class="sidebar-menu fixed">
		<div class="main-menu scrollable-sidebar">
			<ul>
				<li id="mn_0">
					<a  href="javascript:void(0);" id="0" url="<?php echo url('admin/index/main'); ?>">
						<span class="menu-content block">
							<span class="menu-icon"><i class="block fa fa-home fa-lg"></i></span>
							<span class="menu-text">系统首页</span>
						</span>
					</a>
				</li>
				<?php if(is_array($mainNode) || $mainNode instanceof \think\Collection || $mainNode instanceof \think\Paginator): $i = 0; $__LIST__ = $mainNode;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
				<li id="mn_<?php echo $v['nodeid']; ?>">
					<a href="javascript:void(0);" id="<?php echo $v['nodeid']; ?>" url="<?php echo $v['url']; ?>">
						<span class="menu-content block">
							<span class="menu-icon"><i class="block fa <?php echo $v['icon']; ?> fa-lg"></i></span>
							<span class="menu-text"><?php echo $v['title']; ?></span>
						</span>
					</a>
				</li>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>	
		<div class="sidebar-fix-bottom clearfix">
			<a href="#" class="user-dropdown dropup pull-left dropdwon-toggle font-16">@ybcms</a>
			<a href="lockscreen.html" class="pull-right font-18"><i class="ion-log-out"></i></a>
		</div>
	</aside>
	<!--菜单结束-->
	
	<!--中间部分 -->	
	<div class="main-container">
		<div class="sub-menu">
			<div class="scrollable-sidebar">
				<ul id="sub-menu-tree"></ul>
				<ul id="sub-menu-def">
					<li id="sn_0">
						<a href="javascript:void(0);" id="0" url="<?php echo url('admin/index/main'); ?>" class="main-a">
							<span class="menu-content block">
								<span class="menu-icon"><i class="block fa fa-home"></i></span>
								<span class="text">系统首页</span>
							</span>
						</a>
					</li>
					<li id="sn_a">
						<a href="javascript:void(0);" id="a" url="<?php echo url('Admin/Admin/myinfo'); ?>" class="main-a">
							<span class="menu-content block">
								<span class="menu-icon"><i class="block fa fa-edit"></i></span>
								<span class="text">我的个人资料</span>
							</span>
						</a>
					</li>
					<li id="sn_b">
						<a href="javascript:void(0);" id="b" url="<?php echo url('Admin/Admin/editmypwd'); ?>" class="main-a">
							<span class="menu-content block">
								<span class="menu-icon"><i class="block fa fa-inbox"></i></span>
								<span class="text">修改密码</span>
							</span>
						</a>
					</li>
					<li id="sn_c">
						<a href="<?php echo url('Admin/Index/outlogin'); ?>" class="main-a">
							<span class="menu-content block">
								<span class="menu-icon"><i class="block fa fa-power-off"></i></span>
								<span class="text">退出</span>
							</span>
						</a>
					</li>
				</ul>
			</div>	
		</div>
		<div class="main-content">
			<iframe src="<?php echo url('admin/index/main'); ?>" id="subpage" name="subpage" style="overflow:visible;" frameborder="0" width="100%" height="100%" scrolling="yes" onload="window.parent"></iframe>
		</div>
	</div>
	<!--中间结束-->
</div>
<script type="text/javascript">
$(function(){
	//侧边菜单滚动条
	$('.scrollable-sidebar').slimScroll({height:'100%',size:'0px'});
	
	//缩小时打开侧边菜单
	$('#sidebarToggleSM').click(function()	{
		$('aside').toggleClass('active');
		$('.wrapper').toggleClass('display-left');
	});
	//非缩小时打开侧边菜单
	$('#sidebarToggleLG').click(function()	{
		if($('.wrapper').hasClass('display-right'))	{
			$('.wrapper').removeClass('display-right');
			$('.sidebar-right').removeClass('active');
		}else{
			$('.top-nav').toggleClass('sidebar-mini');
			$('aside').toggleClass('sidebar-mini');
			$('footer').toggleClass('sidebar-mini');
			$('.main-container').toggleClass('sidebar-mini');
			
			$('.main-menu').find('.openable').removeClass('open');
			$('.main-menu').find('.submenu').removeAttr('style');
		}		
	});
	
	//菜单加载
	indexNavActive($.cookie('index_nodeid'));
	subNavActive($.cookie('sub_nodeid'));
	getsubmenu($.cookie('index_nodeid'),$.cookie('iframesrc'));
	openPage($.cookie('iframesrc'));
	
	//主菜单点击
	$('.main-menu').find('a').click(function(){
		var url=$(this).attr('url');
		var nodeid=$(this).attr('id');
		indexNavActive(nodeid);
        getsubmenu(nodeid,url);
        openPage(url);
    });
    
    submenu();
});

function submenu(){
   	//折叠侧边栏菜单
	$('.sub-menu .openable > a').click(function(){
		if($(this).parent().children('.submenu').is(':hidden')) {
			//$(this).parent().siblings().removeClass('open').children('.submenu').slideUp(200);
			$(this).parent().addClass('open').children('.submenu').show();
		}else{
			$(this).parent().removeClass('open').children('.submenu').hide();
		}
		return false;
	});
	//子菜单点击
	$('#sub-menu-tree,#sub-menu-def').find('a').click(function(){
		var pclass=$(this).parent().hasClass('openable');
		
		//没有包含class值openable时
		if(!pclass){
			var url=$(this).attr('url');
			var nodeid=$(this).attr('id');
			subNavActive(nodeid);
	        openPage(url);
       	}
   	});
}
//列出二级菜单
function getsubmenu(nodeid,url){
	if(nodeid==0||nodeid==''||url==''||url==null){
		$('#sub-menu-tree').html('');
		$('#sub-menu-def').show();
		return false;
	}else{
		$('#sub-menu-def').hide();
	}
	$.post(Think.U('Admin/Node/ajaxGetSubmenu'),{main_nodeid:nodeid,url:url},function(data){
		if(data!=''){
			$('#sub-menu-tree').html(data);
		}
        submenu();
    });
}

//点击菜单iframe页面跳转
function openPage(url){
	//alert(url);
	if(url==''||url==null){
		url="<?php echo url('admin/index/main'); ?>";
	}
    $('#subpage').attr('src',url);
    $.cookie('iframesrc', url);
}

//选中
function indexNavActive(index_nodeid){
	$('.main-menu').find('li').removeClass('active');//移出主菜单选中
	$('#mn_'+index_nodeid).addClass('active');
	$.cookie('index_nodeid', index_nodeid);
}
function subNavActive(sub_nodeid){
	$('.sub-menu').find('li').removeClass('active');//移出子菜单选中
	$('#sn_'+sub_nodeid).addClass('active');
	$.cookie('sub_nodeid', sub_nodeid);
}
</script>
</body>
</html>
