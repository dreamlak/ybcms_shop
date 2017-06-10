<?php if (!defined('THINK_PATH')) exit(); /*a:12:{s:59:"F:\myweb\ybcms_shop/application/index\view\index\index.html";i:1496627919;s:59:"F:\myweb\ybcms_shop/application/index\view\public\base.html";i:1496542608;s:62:"F:\myweb\ybcms_shop/application/index\view\public\thinkjs.html";i:1492444948;s:64:"F:\myweb\ybcms_shop/application/index\view\public\top_fixed.html";i:1496634180;s:61:"F:\myweb\ybcms_shop/application/index\view\public\top_ad.html";i:1496631361;s:65:"F:\myweb\ybcms_shop/application/index\view\public\top_header.html";i:1496632362;s:61:"F:\myweb\ybcms_shop/application/index\view\public\header.html";i:1496634100;s:59:"F:\myweb\ybcms_shop/application/index\view\public\menu.html";i:1496541006;s:61:"F:\myweb\ybcms_shop/application/index\view\public\banner.html";i:1496629706;s:66:"F:\myweb\ybcms_shop/application/index\view\public\right_fixed.html";i:1496541210;s:66:"F:\myweb\ybcms_shop/application/index\view\public\friend_link.html";i:1496541273;s:61:"F:\myweb\ybcms_shop/application/index\view\public\footer.html";i:1496541456;}*/ ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>YBMALL网上商城-<?php echo $config['site_name']; ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1" />
<meta http-equiv="keywords" content="<?php echo $config['site_key']; ?>" />
<meta name="description" content="<?php echo $config['site_desc']; ?>" />
<!-- css_start -->
<link rel="stylesheet" href="__S_CSS__/iconfont/iconfont.css">
<link rel="stylesheet" href="__S_CSS__/common.css">
<link rel="stylesheet" href="__S_CSS__/color-style.css">
<link rel="stylesheet" href="__S_CSS__/index.css">
<!-- css_end -->

<!-- js_start -->
<script src="__P_JS__/jquery/jquery-1.11.1.min.js" type="text/javascript"></script>
<!--[if lt IE 9]>
<script type="text/javascript" src="__P_JS__/ie/html5shiv.min.js"></script>
<script type="text/javascript" src="__P_JS__/ie/respond.min.js"></script>
<![endif]-->
<script src="__P_JS__/layer/layer.js" type="text/javascript"></script>
<script src="__P_JS__/jquery/jquery.lazyload.js" type="text/javascript"></script>
<script type="text/javascript">var ThinkPHP=window.Think={"APP":"","DEEP":"/","MODEL":["3","","html"],"VAR":["m","c","a"]}</script>
<script type="text/javascript" src="__P_JS__/think.js" ></script>
<script src="__S_JS__/common.js" type="text/javascript"></script>

<script src="__S_JS__/szy.cart.js" type="text/javascript"></script>
<script src="__S_JS__/index.js" type="text/javascript"></script>

<!-- js_end -->


</head>
<body>
<!-- 头部滚动通栏悬浮框 _start -->
<div class="as-shelter"></div>
<div class="follow-box">
	<div class="follow-box-con">
		<a href="/" class="logo" title="<?php echo $config['site_name']; ?>"><img src="<?php echo $config['site_logo']; ?>"></a>
		<div class="search SZY-SEARCH-BOX-TOP">
			<form class="search-form SZY-SEARCH-BOX-FORM" method="get" name="" action="<?php echo url("Goods/search"); ?>">
				<div class="search-info">
					<div class="search-type-box">
						<ul class="search-type">
							<li class="search-li-top curr" num="0">宝贝</li>
							<!--li class="search-li-top" num="1">店铺</li-->
						</ul>
						<i></i>
					</div>
					<div class="search-box">
						<div class="search-box-con">
						<input type="text" class="search-box-input SZY-SEARCH-BOX-KEYWORD" name="keyword" tabindex="9" autocomplete="off" data-searchwords="<?php echo input('keyword'); ?>" placeholder="输入关键字搜索..." value="<?php echo input('keyword'); ?>" />
						</div>
					</div>
					<input type='hidden' id="searchtypeTop" name='type' value="0" class="searchtype" />
					<input type="button" id="btn_search_box_submit_top" value="搜索" class="button bg-color SZY-SEARCH-BOX-SUBMIT-TOP" />
				</div>
			</form>
		</div>
		<div class="login-info">
			<font id="login-info" class="login-info SZY-USER-NOT-LOGIN">
				<a class="login color" href="<?php echo url('user/login'); ?>" target="_top">登录</a>
				<a class="register bg-color" href="<?php echo url('user/reg'); ?>" target="_top">免费注册</a>
			</font>
			<font id="login-info" class="login-info SZY-USER-ALREADY-LOGIN" style="display: none;">
				<em>
					<a href="<?php echo url('User/index'); ?>" class="color SZY-USER-NAME"></a>
				</em>
				<a href="<?php echo url('user/logout'); ?>" data-method="post" class="logout bg-color">退出</a>
			</font>
		</div>
	</div>
</div>
<script type="text/javascript">
	//搜索
	$(document).ready(function() {
		$(".SZY-SEARCH-BOX-TOP .SZY-SEARCH-BOX-SUBMIT-TOP").click(function() {
			if ($(".search-li-top.curr").attr('num') == 0) {
				var keyword_obj = $(this).parents(".SZY-SEARCH-BOX-TOP").find(".SZY-SEARCH-BOX-KEYWORD");
				var keywords = $(keyword_obj).val();
				if ($.trim(keywords).length == 0 || $.trim(keywords) == "请输入关键词") {
					keywords = $(keyword_obj).data("searchwords");
				}
				$(keyword_obj).val(keywords);
			}
			$(this).parents(".SZY-SEARCH-BOX-TOP").find(".SZY-SEARCH-BOX-FORM").submit();
		});
	});
</script>
<!-- 头部滚动通栏悬浮框 _end -->

<!-- 头部广告 _start 注意：此广告只在网站首页展示 -->
<?php
	if(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME == 'index/Index/index' && $_COOKIE["top-banner"] == null){
?>
<div class="top-active" style="background-color:#e2283d;">
	<?php $pid =1;$dev="pc";$name="首页顶部";$ad_position = db("poster_space")->cache(true,CACHE_TIME)->column("id,name,width,height","id");$result = get_ad($pid,$num);if(!in_array($pid,array_keys($ad_position)) && $pid){
				  	db("poster_space")->insert(array(
				        "id"=>$pid,
				        "name"=>request()->controller()." $name 广告位 $pid ",
				        "status"=>1,
				        "type"=>"banner",
				        "device"=>$dev,
				        "content"=>request()->controller()."页面",
				  	));
				  	delFile(RUNTIME_PATH); //删除缓存  
				}
				foreach($result as $key=>$v):
    			?>
	<div class="top-active-wrap">
		<a href="<?php echo $v['url']; ?>" <?php if($v['target']==1): ?>target="_blank"<?php endif; ?>>
			<img src="<?php echo $v['images']; ?>"  title="<?php echo $v['name']; ?>" />
		</a>
		<a href="javascript:void(0);" title="关闭" class="top-active-close"></a>
	</div>
	<?php endforeach; ?>
</div>
<script type="text/javascript">
//头部广告
$(".top-active-close").click(function(){	
	$(".top-active").slideUp();
	setcookie("top-banner", "1", time()+ 3600); //弹过窗的 不在弹窗
});
</script>
<?php 
}
?>
<!-- 头部广告 _end 注意：此广告只在网站首页展示 -->

<!--站点头部-->
<div class="header-top">
	<div class="header-box">
		<!-- 登录信息 -->
		<font id="login-info" class="login-info SZY-USER-NOT-LOGIN">
			<em>欢迎来到<?php echo $config['site_name']; ?>! </em>
			<a class="login color" href="<?php echo url('user/login'); ?>">请登录</a>
			<a class="register" href="<?php echo url('user/reg'); ?>">免费注册</a>
		</font>
		<font id="login-info" class="login-info SZY-USER-ALREADY-LOGIN" style="display: none;">
			<em>
				<a href="<?php echo url('User/index'); ?>" class="color SZY-USER-NAME"></a>
				<!--欢迎您回来!-->
			</em>
			<a href="<?php echo url('user/logout'); ?>" data-method="post">退出</a>
		</font>
		<ul>
			<li>
				<a class="menu-hd home" href="/" target="_top"><i></i>商城首页</a>
			</li>
			<li class="menu-item">
				<div class="menu">
					<a class="menu-hd myinfo" href="user.html">
						<i></i>我的商城<b></b>
					</a>
					<div id="menu-2" class="menu-bd">
						<span class="menu-bd-mask"></span>
						<div class="menu-bd-panel">
							<a href="<?php echo url('user/order_list'); ?>">已买到的宝贝</a>
							<a href="myaddress.html">我的地址管理</a>
							<a href="goods_collect.html">我收藏的宝贝</a>
							<a href="shop_collect.html">我收藏的店铺</a>
						</div>
					</div>
				</div>
			</li>
			<li class="menu-item cartbox">
				<div class="menu">
					<a class="menu-hd cart" href="<?php echo url('Cart/cart'); ?>" target="_top">
						<i></i>购物车
						<span class="SZY-CART-COUNT">0</span>
						<b></b>
					</a>
					<div id="menu-4" class="menu-bd cart-box-main">
						<span class="menu-bd-mask"></span>
						<div class="dropdown-layer">
							<div class="spacer"></div>
							<div class="dropdown-layer-con cartbox-goods-list">
								<!-- 正在加载 -->
								<div class="cart-type">
									<i class="cart-type-icon"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
			</li>
			<!--li><a class="menu-hd" href="seller.html">卖家中心</a></li-->
			<li class="menu-item">
				<div class="menu">
					<a class="menu-hd we-chat" href="javascript:" target="_top">
						<i></i>关注商城<b></b>
					</a>
					<div id="menu-5" class="menu-bd we-chat-qrcode">
						<span class="menu-bd-mask"></span>
						<a target="_top">
							<img src="__S_IMG__/qrd.jpg" alt="官方微信">
						</a>
						<p class="font-14">关注官方微信</p>
					</div>
				</div>
			</li>
			<li class="menu-item">
				<div class="menu">
					<a class="menu-hd mobile" href="javascript:" target="_top">
						<i></i>手机版<b></b>
					</a>
					<div id="menu-6" class="menu-bd qrcode">
						<span class="menu-bd-mask"></span>
						<a target="_top">
							<img src="__S_IMG__/qrd.jpg" alt="手机客户端">
						</a>
						<p>手机客户端</p>
					</div>
				</div>
			</li>
			<li class="menu-item">
				<div class="menu">
					<a href="javascript:" class="menu-hd site-nav">
						商家支持<b></b>
					</a>
					<div id="menu-7" class="menu-bd site-nav-main">
						<span class="menu-bd-mask"></span>
						<div class="menu-bd-panel">
							<div class="site-nav-con">
								<a href="help.html" title="新手上路">新手上路</a>
								<a href="##a" title="配送服务">配送服务</a>
								<a href="##a" title="商家合作">商家合作</a>
								<a href="##a" title="入驻说明">入驻说明</a>
							</div>
						</div>
					</div>
				</div>
			</li>
		</ul>
	</div>
</div>

<!--站点头部 结束-->

<!--头部LOGO、搜索... 开始-->
<div class="header">
	<div class="w1210">
		<div class="logo-info">
			<a href="/" class="logo" title="<?php echo $config['site_name']; ?>"><img src="<?php echo $config['site_logo']; ?>"></a>
		</div>
		<div class="search SZY-SEARCH-BOX">
			<form class="search-form SZY-SEARCH-BOX-FORM" method="get" action="<?php echo url("Goods/search"); ?>">
				<div class="search-info">
					<div class="search-type-box">
						<ul class="search-type">
							<li class="search-li curr" num="0">宝贝</li>
							<!--li class="search-li" num="1">店铺</li-->
						</ul>
						<i></i>
					</div>
					<div class="search-box">
						<div class="search-box-con">
						<input type="text" class="keyword search-box-input SZY-SEARCH-BOX-KEYWORD" name="keyword" tabindex="9" autocomplete="off" data-searchwords="<?php echo input('keyword'); ?>" placeholder="输入关键字搜索..." value="<?php echo input('keyword'); ?>">
						</div>
					</div>
					<input type="hidden" id="searchtype" name="type" value="0" class="searchtype">
					<input type="button" id="btn_search_box_submit" value="搜索" class="button bg-color btn_search_box_submit SZY-SEARCH-BOX-SUBMIT">
				</div>
			</form>
			<ul class="hot-query">
				<!-- 默认搜索词 -->
				<?php $hot_keywords=explode(',',$config['hot_keywords']);if(is_array($hot_keywords) || $hot_keywords instanceof \think\Collection || $hot_keywords instanceof \think\Paginator): if( count($hot_keywords)==0 ) : echo "" ;else: foreach($hot_keywords as $k=>$wd): ?>
               	<li <?php if($k == 0): ?>class="first"<?php endif; ?>><a href="<?php echo url('Goods/search',['keyword'=>$wd]); ?>"><?php echo $wd; ?></a></li>
        		<?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>
		<!-- 搜索框右侧小广告 _start -->
		<div class="header-right">
			<a href="#a" title="">
				<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/system/config/mall_top_ad/mall_search_right_da_image_0.png">
			</a>
		</div>
		<!-- 搜索框右侧小广告 _end -->
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$(".SZY-SEARCH-BOX .SZY-SEARCH-BOX-SUBMIT").click(function() {
		if ($(".search-li.curr").attr('num') == 0) {
			var keyword_obj = $(this).parents(".SZY-SEARCH-BOX").find(".SZY-SEARCH-BOX-KEYWORD");
			var keywords = $(keyword_obj).val();
			if ($.trim(keywords).length == 0 || $.trim(keywords) == "请输入关键词") {
				keywords = $(keyword_obj).data("searchwords");
			}
			$(keyword_obj).val(keywords);
		}
		$(this).parents(".SZY-SEARCH-BOX").find(".SZY-SEARCH-BOX-FORM").submit();
	});
});
</script>

<!--头部LOGO、搜索... 结束-->

<!--导航-->
<div class="category-box">
	<div class="w1210">
		<div class="home-category bg-color fl">
			<a href="category.html" class="menu-event" title="查看全部商品分类">
				<i></i>
				全部商品分类
			</a>
			<div class="expand-menu category-layer category-layer1">
				<span class="category-layer-bg bg-color"></span>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="##a" title="家用电器">家用电器</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
							<div class="item-channels">
								<div class="channels">
									<a href="##a" title="家电城"> 家电城 </a>
								</div>
							</div>
							<div class="item-channels">
								<div class="channels">
									<a href="##a" title="品牌日"> 品牌日 </a>
								</div>
							</div>
							<div class="item-channels">
								<div class="channels">
									<a href="##a" title="智能生活馆"> 智能生活馆 </a>
								</div>
							</div>
							<div class="item-channels">
								<div class="channels">
									<a href="##a" title="家电众筹馆"> 家电众筹馆 </a>
								</div>
							</div>
							<div class="subitems">
								<dl class="fore1">
									<dt style="width: 4em;">
										<a href="#a" title="大家电">
											<em>大家电</em>
											<i>&gt;</i>
										</a>
									</dt>
									<dd>
										<a href="##a" title="平板电视">平板电视</a>
										<a href="##a" title="空调">空调</a>
										<a href="##a" title="冰箱">冰箱</a>
										<a href="##a" title="洗衣机">洗衣机</a>
										<a href="#E" title="家庭影院">家庭影院</a>
										<a href="##a" title="DVD">DVD</a>
										<a href="##a" title="迷你音箱">迷你音箱</a>
									</dd>
								</dl>
								<dl class="fore1">
									<dt style="width: 4em;">
										<a href="#a" title="厨卫大电">
											<em>厨卫大电</em>
											<i>&gt;</i>
										</a>
									</dt>
									<dd>
										<a href="##a" title="油烟机">油烟机</a>
										<a href="##a" title="煤气灶">煤气灶</a>
										<a href="##a" title="消毒柜">消毒柜</a>
										<a href="##a" title="洗碗机">洗碗机</a>
										<a href="##a" title="电热水器">电热水器</a>
										<a href="##a" title="燃气热水器">燃气热水器</a>
										<a href="##a" title="烟灶套装">烟灶套装</a>
									</dd>
								</dl>
								<dl class="fore1">
									<dt style="width: 4em;">
										<a href="#a" title="厨房小电">
											<em>厨房小电</em>
											<i>&gt;</i>
										</a>
									</dt>
									<dd>
										<a href="#E" title="电饭煲">电饭煲</a>
										<a href="##a" title="微波炉">微波炉</a>
										<a href="##a" title="电烤箱">电烤箱</a>
										<a href="#E" title="电磁炉">电磁炉</a>
										<a href="#E" title="电压力锅">电压力锅</a>
										<a href="##a" title="豆浆机">豆浆机</a>
										<a href="##a" title="咖啡机">咖啡机</a>
									</dd>
								</dl>
							</div>
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<div class="item-brands">
								<div class="brands-inner">
									<a href="#" title="飞利浦">
										<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/brandlogos/2016/06/07/14652968057260.png" width="87.5" height="35">
									</a>
									<a href="#" class="img-link" title="乐视">
										<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/brandlogos/2016/06/07/14652966601116.png" width="87.5" height="35">
									</a>
									<a href="#" title="创维">
										<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/brandlogos/2016/06/07/14652967491457.png" width="87.5" height="35">
									</a>
									<a href="#" class="img-link" title="海信">
										<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/brandlogos/2016/06/07/14652968848233.png" width="87.5" height="35">
									</a>
									<a href="#" title="奥克斯">
										<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/brandlogos/2016/06/07/14652970500713.png" width="87.5" height="35">
									</a>
									<a href="#E" class="img-link" title="飞科">
										<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/brandlogos/2016/06/07/14652971958181.png" width="87.5" height="35">
									</a>
								</div>
							</div>
							<!-- 分类广告图片 -->
							<div class="item-promotions">
								<a href="#a" class="img-link">
									<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/adimages/2016/08/16/14713487694880.jpg" width="180">
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="手机">手机</a>、<a href="#a" title="数码">数码</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="电脑、办公">电脑、办公</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="服饰">服饰</a>、<a href="#a" title="鞋帽">鞋帽</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="个护化妆、清洁用品">个护化妆、清洁用品</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="鞋靴箱包">鞋靴箱包</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="汽车用品">汽车用品</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo --><!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="母婴">母婴</a>、<a href="#a" title="玩具乐器">玩具乐器</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="图书、音像、电子书">图书、音像、电子书</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="生鲜">生鲜</a>、<a href="#a" title="水果">水果</a>、<a href="#a" title="生鲜水果">生鲜水果</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="生活百货">生活百货</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="运动">运动</a>、<a href="#a" title="户外">户外</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
						</div>
					</div>
				</div>
				<div class="list">
					<dl class="cat">
						<dt class="cat-name">
							<i class="iconfont"></i>
							<a href="#a" title="家纺">家纺</a>、<a href="#a" title="家居">家居</a>
						</dt>
						<i class="right-arrow">&gt;</i>
					</dl>
					<div class="categorys">
						<div class="item-left fl">
							<!-- 推荐分类 -->
						</div>
						<div class="item-right fr">
							<!-- 品牌logo -->
							<!-- 分类广告图片 -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="all-category fl" id="nav">
			<ul>
				<li class="fl">
					<a class="nav " href="list.html" title="大家电">大家电</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fl">
					<a class="nav " href="shop.html" title="店铺街">店铺街</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fl">
					<a class="nav " href="group-buy.html" title="团购">团购</a>
					<!-- 导航小标签 _start -->
					<span class="nav-icon">
						<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/site/1/images/2017/03/02/14884401181467.gif">
					</span>
					<!-- 导航小标签 _end -->
				</li>
				<li class="fl">
					<a class="nav " href="brand.html" title="品牌专区">品牌专区</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fl">
					<a class="nav " href="list.html" title="圣诞狂欢">圣诞狂欢</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fl">
					<a class="nav " href="list.html" title="国际大牌">国际大牌</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fl">
					<a class="nav " href="news.html" title="文章资讯">文章资讯</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fr">
					<a class="nav " href="help.html" title="帮助中心">帮助中心</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
				<li class="fr">
					<a class="nav " href="market.html" title="批发市场">批发市场</a>
					<!-- 导航小标签 _start -->
					<!-- 导航小标签 _end -->
				</li>
			</ul>
			<div class="wrap-line" style="left: 0px; width: 88px;">
				<span style="left: 15px;"></span>
			</div>
		</div>

	</div>
</div>

<!--导航 end-->

<!--Banner-->
<div class="template-one">
	<!-- banner模块 _start -->
	<div class="banner">
		<!-- banner轮播 _start -->
		<ul id="fullScreenSlides" class="full-screen-slides">
			<?php $pid =15;$dev="pc";$name="首页Banner幻灯";$ad_position = db("poster_space")->cache(true,CACHE_TIME)->column("id,name,width,height","id");$result = get_ad($pid,$num);if(!in_array($pid,array_keys($ad_position)) && $pid){
				  	db("poster_space")->insert(array(
				        "id"=>$pid,
				        "name"=>request()->controller()." $name 广告位 $pid ",
				        "status"=>1,
				        "type"=>"banner",
				        "device"=>$dev,
				        "content"=>request()->controller()."页面",
				  	));
				  	delFile(RUNTIME_PATH); //删除缓存  
				}
				foreach($result as $k=>$v):
    			?>
			<li style="background: url('<?php echo $v['images']; ?>') center center;display:<?php if($k==0): ?>list-item<?php else: ?>none<?php endif; ?>;">
				<a href="<?php echo $v['url']; ?>" title="" <?php if($v['target'] == 1): ?>target="_blank"<?php endif; ?>>&nbsp;</a>
			</li>
			<?php endforeach; ?>
		</ul>
		<ul class="full-screen-slides-pagination">
			<?php $pid =15;$dev="pc";$name="页面自动增加";$ad_position = db("poster_space")->cache(true,CACHE_TIME)->column("id,name,width,height","id");$result = get_ad($pid,$num);if(!in_array($pid,array_keys($ad_position)) && $pid){
				  	db("poster_space")->insert(array(
				        "id"=>$pid,
				        "name"=>request()->controller()." $name 广告位 $pid ",
				        "status"=>1,
				        "type"=>"banner",
				        "device"=>$dev,
				        "content"=>request()->controller()."页面",
				  	));
				  	delFile(RUNTIME_PATH); //删除缓存  
				}
				foreach($result as $k=>$v):
    			?>
        	<li<?php if($k==0): ?> class="current"<?php endif; ?>><a href="javascript:void(0);"><?php echo $k; ?></a></li>
        	<?php endforeach; ?>
		</ul>
		<!-- banner轮播 _end -->
		<div class="right-sidebar  SZY-TEMPLATE-NAV-CONTAINER">
			<!-- 商城公告版式1 -->
			<!-- banner右侧公告 _start -->
			<div class="proclamation1">
				<h3>
					<span><i></i> 公告</span>
					<a href="<?php echo url('article/lists',['catid'=>1]); ?>">更多<i>&gt;</i></a>
				</h3>
				<ul class="mall-news">
					<?php
                            $md5_key = md5("select * from `__PREFIX__article`  where catid = 4 order by artid desc limit 8");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from `__PREFIX__article`  where catid = 4 order by artid desc limit 8"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
            		<li><a href="<?php echo url('article/show',['artid'=>$v['artid']]); ?>" title="<?php echo $v['title']; ?>"><?php echo $v['title']; ?></a></li>
                    <?php endforeach; ?>
				</ul>
			</div>
			<!-- banner右侧公告 _end -->
			<!-- 促销活动版式1 -->
			<!-- banner右侧限时抢购 _start -->
			<div class="sale-discount">
				<ul class="saleDiscount">
					<?php $tadys=time();
                            $md5_key = md5("select * from __PREFIX__goods as g inner join __PREFIX__flash_sale as f on g.goods_id = f.goods_id   where  `start_time`<$tadys and `end_time`>$tadys");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from __PREFIX__goods as g inner join __PREFIX__flash_sale as f on g.goods_id = f.goods_id   where  `start_time`<$tadys and `end_time`>$tadys"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
					<li>
						<div class="sale-con">
							<div class="goods-info">
								<div class="goods-detail">
									<p class="time-remain" data-time="<?php echo $v['end_time']; ?>">
										<!--span>
											<em class="bg-color">73</em> 天 
											<em class="bg-color">12</em> 小时 
											<em class="bg-color">09</em> 分 
											<em class="bg-color">15</em> 秒
										</span-->
									</p>
									<a href="<?php echo url('Goods/goodsInfo',array('id'=>$v['goods_id'])); ?>" title="<?php echo $v[goods_name]; ?>" class="goods-thumb">
										<img src="<?php echo goods_thum_images($v['goods_id'],220,220); ?>"  alt="21">
									</a>
									<p class="goods-name">
										<a href="<?php echo url('Goods/goodsInfo',array('id'=>$v['goods_id'])); ?>" title="<?php echo $v[goods_name]; ?>"><?php echo $v[goods_name]; ?></a>
									</p>
									<p class="goods-price">
										<span class="color"> ￥<?php echo $v[shop_price]; ?> </span>
										<span class="goods-discount color"><?php echo round($v['price'] / $v[shop_price] * 10 , 1); ?>折</span>
									</p>
								</div>
							</div>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>
				<div class="arrow pre" style="opacity: 0;"></div>
				<div class="arrow next" style="opacity: 0;"></div>
				<script type="text/javascript">
					Move(".next", ".pre", ".saleDiscount", ".sale-discount", "1");
				</script>
			</div>
			<!-- banner右侧限时抢购 _end -->
		</div>
	</div>
	<!-- banner模块 _end -->
</div>
<script type="text/javascript" src="__S_JS__/jquery.countdown.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$(".time-remain").each(function(i) {
			var time = $(this).data("time");
			$(this).countdown({
				time: time,
				htmlTemplate: '<span><em class="bg-color">%{d}</em> 天 <em class="bg-color">%{h}</em> 小时 <em class="bg-color">%{m}</em> 分 <em class="bg-color">%{s}</em> 秒</span>',
				leadingZero: true,
				onComplete: function(event) {
					$(this).html("活动已经结束啦!");
				}
			});
		});

		//加入购物车
		$('body').on('click', '.add-cart', function(event) {
			var goods_id = $(this).data('goods_id');
			var image_url = $(this).data('image_url');
			$.cart.add(goods_id, 1, {
				is_sku: false,
				image_url: image_url,
				event: event,
				callback: function() {
					var attr_list = $('.attr-list').height();
					$('.attr-list').css({
						"overflow": "hidden"
					});
					if (attr_list >= 200) {
						$('.attr-list').addClass("attr-list-border");
						$('.attr-list').css({
							"overflow-y": "auto"
						});
					}
				}
			});
		});
	});
</script>

<!--Banner end-->

<!--main_start-->

<div class="template-one">
	<!-- 默认缓载图片 -->
	<script type="text/javascript">$.imgloading.loading();</script>
	<!-- 推荐的商品 _start -->
	<div class="w1210 index-sale2">
		<h2 class="index-sale-title">发现好货</h2>
		<ul class="tabs-nav">
			<li class="tabs-selected"><i class="arrow"></i><h3>精品推荐</h3></li>
			<li ><i class="arrow"></i><h3>新货上架</h3></li>
			<li ><i class="arrow"></i><h3>热卖单品</h3></li>
		</ul>
		<div class="tabs-panel">
			<ul>
				<?php
                            $md5_key = md5("select * from `__PREFIX__goods` where is_new = 1 and is_on_sale = 1 order by sort desc limit 5");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from `__PREFIX__goods` where is_new = 1 and is_on_sale = 1 order by sort desc limit 5"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
				<li>
					<dl>
						<dt class="goods-thumb">
							<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>" >
								<img class="" src="<?php echo goods_thum_images($v['goods_id'],220,220); ?>"  alt="<?php echo $v['goods_name']; ?>" style="display:inline;">
							</a>
						</dt>
						<dd class="goods-info">
							<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>" class="goods-name"><?php echo $v['goods_name']; ?></a>
							<em class="goods-price second-color">￥<?php echo $v['shop_price']; ?></em>
						</dd>
					</dl>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="tabs-panel tabs-hide">
			<ul>
				<?php
                            $md5_key = md5("select * from `__PREFIX__goods` where is_recommend = 1 and is_on_sale = 1 order by on_time desc limit 5");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from `__PREFIX__goods` where is_recommend = 1 and is_on_sale = 1 order by on_time desc limit 5"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
				<li>
					<dl>
						<dt class="goods-thumb">
							<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>" >
								<img class="" src="<?php echo goods_thum_images($v['goods_id'],220,220); ?>"  alt="<?php echo $v['goods_name']; ?>" style="display:inline;">
							</a>
						</dt>
						<dd class="goods-info">
							<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>" class="goods-name"><?php echo $v['goods_name']; ?></a>
							<em class="goods-price second-color">￥<?php echo $v['shop_price']; ?></em>
						</dd>
					</dl>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="tabs-panel tabs-hide">
			<ul>
				<?php
                            $md5_key = md5("select * from `__PREFIX__goods` where is_hot = 1 and is_on_sale = 1 order by sales_sum desc limit 5");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from `__PREFIX__goods` where is_hot = 1 and is_on_sale = 1 order by sales_sum desc limit 5"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
				<li>
					<dl>
						<dt class="goods-thumb">
							<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>" >
								<img class="" src="<?php echo goods_thum_images($v['goods_id'],220,220); ?>"  alt="<?php echo $v['goods_name']; ?>" style="display:inline;">
							</a>
						</dt>
						<dd class="goods-info">
							<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>" class="goods-name"><?php echo $v['goods_name']; ?></a>
							<em class="goods-price second-color">￥<?php echo $v['shop_price']; ?></em>
						</dd>
					</dl>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<!-- 推荐的商品 _end -->
	
	<!-- 品牌 _start -->
	<div class="w1210 store-wall2">
    	<h2><a class="store-wall-title" href="#" title="推荐品牌" style="color: #333333">推荐品牌</a></h2>
		<div class="store-con2">
			<div class="store-wall2-ad">
			 	<?php $pid =16;$dev="pc";$name="首页品牌";$ad_position = db("poster_space")->cache(true,CACHE_TIME)->column("id,name,width,height","id");$result = get_ad($pid,$num);if(!in_array($pid,array_keys($ad_position)) && $pid){
				  	db("poster_space")->insert(array(
				        "id"=>$pid,
				        "name"=>request()->controller()." $name 广告位 $pid ",
				        "status"=>1,
				        "type"=>"banner",
				        "device"=>$dev,
				        "content"=>request()->controller()."页面",
				  	));
				  	delFile(RUNTIME_PATH); //删除缓存  
				}
				foreach($result as $key=>$v):
    			?>
	            <a href="<?php echo $v['url']; ?>" <?php if($v['target'] == 1): ?>target="_blank"<?php endif; ?>>
	        		<img src="<?php echo $v['images']; ?>" title="<?php echo $v['name']; ?>" style="<?php echo $v['style']; ?>;display:block;"/>
	            </a>
				<?php endforeach; ?>
			</div>
            <ul class="store-wall2-list">
            	<?php
                            $md5_key = md5("select * from `__PREFIX__brand` where is_hot = 1 order by sort asc limit 18");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from `__PREFIX__brand` where is_hot = 1 order by sort asc limit 18"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
                <li title="<?php echo $v['name']; ?>" <?php if(($k+1)%6==0): ?>class="item"<?php endif; ?>> 
                    <img alt="<?php echo $v['name']; ?>" src="<?php echo $v['logo']; ?>">
                    <div class="black-cover" style="display: none;"></div>
                    <div class="cover-content" style="display: none;">
                      	<a href="<?php echo url('brand/info',['id'=>$v['id']]); ?>" class="enter" >点击进入</a>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
		</div>
	</div>
	<!--品牌 _end-->
	
	<!--推荐楼层 _star -->
	<?php
                            $md5_key = md5("SELECT * FROM `__PREFIX__goods_category` WHERE parent_id=0 and is_show=1 and is_hot=1 and `level` = 1 ORDER BY sort_order LIMIT 7");
                            $result_name = $sql_result_val = S("sql_".$md5_key);
                            if(empty($sql_result_val)){                            
                                $result_name = $sql_result_val = query("SELECT * FROM `__PREFIX__goods_category` WHERE parent_id=0 and is_show=1 and is_hot=1 and `level` = 1 ORDER BY sort_order LIMIT 7"); 
                                S("sql_".$md5_key,$sql_result_val,31104000);
                            }
                     	 foreach($sql_result_val as $key=>$val): 
	    $cat_id_arr = getCatGrandson($val['id']); //找到某个大类下面的所有子分类id
	    $cat_id_str = implode(',',$cat_id_arr);
	    //dump($cat_id_str);
	?>
	<!--楼层<?php echo $key+1; ?>-->
	<div class="w1210 floor">
		<div class="floor-brief">
			<h1 class="floor-title">
				<span class="floor-num SZY-FLOOR-NAME" data-name="<?php echo getSubstr($val['name'],0,2); ?>"><?php echo $key+1; ?>F</span><em></em>
				<a href="<?php echo url('Goods/goodsList',['id'=>$val['id']]); ?>" class="floor-name"><?php echo $val['name']; ?></a>
			</h1>
			<div class="floor-ad"><a href="<?php echo url('Goods/goodsList',['id'=>$val['id']]); ?>"><img src="<?php echo $val['image']; ?>"></a></div>
			<div class="floor-cat">
				<?php
                            $md5_key = md5("select * from `__PREFIX__goods_category` where is_show=1 and parent_id=$val[id]");
                            $result_name = $sql_result_val2 = S("sql_".$md5_key);
                            if(empty($sql_result_val2)){                            
                                $result_name = $sql_result_val2 = query("select * from `__PREFIX__goods_category` where is_show=1 and parent_id=$val[id]"); 
                                S("sql_".$md5_key,$sql_result_val2,31104000);
                            }
                     	 foreach($sql_result_val2 as $key2=>$val2): ?>
				<a href="<?php echo url('Goods/goodsList',['id'=>$val2['id']]); ?>" title="<?php echo $val2['name']; ?>"><?php echo $val2['name']; ?></a>
				<?php endforeach; ?> 
			</div>
		</div>
		<div class="floor-con">
			<?php
                            $md5_key = md5("select * from `__PREFIX__goods` where cat_id in($cat_id_str) and is_on_sale = 1 order by sort desc limit 8");
                            $result_name = $sql_result_v = S("sql_".$md5_key);
                            if(empty($sql_result_v)){                            
                                $result_name = $sql_result_v = query("select * from `__PREFIX__goods` where cat_id in($cat_id_str) and is_on_sale = 1 order by sort desc limit 8"); 
                                S("sql_".$md5_key,$sql_result_v,31104000);
                            }
                     	 foreach($sql_result_v as $k=>$v): ?>
			<div class="floor-goods">
				<div class="good-img <?php if($v['store_count']==0): ?>empty<?php endif; ?>">
					<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>"><img src="<?php echo goods_thum_images($v['goods_id'],220,220); ?>" alt="<?php echo $v['goods_name']; ?>"></a>
					<?php if($v['is_new']==1): ?><span class="tag"><em>新品</em><i></i></span><?php endif; if($v['is_hot']==1): ?><span class="tag"><em>热买</em><i></i></span><?php endif; if($v['is_recommend']==1): ?><span class="tag"><em>推荐</em><i></i></span><?php endif; if($v['store_count']==0): ?><span class="shouqing"></span><?php endif; ?>
				</div>
				<div class="good-price">
					<em>￥<?php echo $v['shop_price']; ?></em><s>￥<?php echo $v['market_price']; ?></s>
					<a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" class="buy add-cart" title="立即购买"><img src="__S_IMG__/add-cart.png" width="30" height="30"></a>
				</div>
				<div class="good-name" title=""><a href="<?php echo url('Goods/goodsInfo',['id'=>$v['goods_id']]); ?>" title="<?php echo $v['goods_name']; ?>"><?php echo $v['goods_name']; ?></a></div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endforeach; ?>
	<!-- 左侧楼层定位-->
	<div class="elevator" style="visibility:hidden;">
		<div class="elevator-floor" style="transform: scale(1); opacity: 1;"></div>
	</div>
	<script type="text/javascript">
	$(function(){
		$('.floor .floor-con').each(function(ci,cv){
			$(cv).find('.floor-goods').each(function(gi,gv){
				if(gi==0||gi==4){
					$(gv).hover(function(){
						$(gv).parent().prev().css('z-index',-1).css('border-width',0);
					},function(){
						$(gv).parent().prev().css('z-index',1).css('border-width',1);
					});
				}else{
					$(gv).parent().prev().css('z-index',1).css('border-width',1);
				}
			});
		});
	});
	</script>
	<!-- 楼层 _end -->
</div>

<!--main_end-->


<!-- 右侧边栏 _start -->
<div class="right-sidebar-con">
	<div class="right-sidebar-main">
		<div class="right-sidebar-panel">
			<div id="quick-links" class="quick-links">
				<ul>
					<li class="quick-area quick-login sidebar-user-trigger">
						<!-- 用户 -->
						<a href="javascript:void(0);" class="quick-links-a"><i class="setting"></i></a>
						<div class="sidebar-user quick-sidebar">
							<i class="arrow-right"></i>
							<div class="sidebar-user-info">
								<!-- 没有登录的情况 _start -->
								<div class="SZY-USER-NOT-LOGIN" style="display:block;">
									<div class="user-pic">
										<div class="user-pic-mask"></div>
										<img src="../static/images/public/no-photo.png" />
									</div>
									<br />
									<p>
										你好！请
										<a href="javascript:void(0);" class="quick-login-a color ajax-login">登录</a>
										|
										<a href="#" class="color">注册</a>
									</p>
								</div>
								<!-- 没有登录的情况 _end -->
								<!-- 有登录的情况 _start -->
								<div class="SZY-USER-ALREADY-LOGIN" style="display: none;">
									<div class="user-have-login">
										<div class="user-pic">
											<div class="user-pic-mask"></div>
											<img src="" class="SZY-USER-PIC" />
										</div>
										<div class="user-info">
											<p>
												用&nbsp;&nbsp;&nbsp;户：
												<span class="SZY-USER-NAME"></span>
											</p>
										</div>
									</div>
									<p class="m-t-10">
										<span class="prev-login">
											上次登录时间：
											<span class="SZY-USER-LAST-LOGIN"></span>
										</span>
										<a href="#a" class="btn account-btn">个人中心</a>
										<a href="#a" class="btn order-btn">订单中心</a>
									</p>
								</div>
								<!-- 有登录的情况 _end -->
							</div>
						</div>
					</li>
					<li class="sidebar-tabs">
						<!-- 购物车 -->
						<div class="cart-list quick-links-a sidebar-cartbox-trigger">
							<i class="cart"></i>
							<div class="span">购物车</div>
							<span class="ECS_CARTINFO">
								<span class="cart_num SZY-CART-COUNT">0</span>
								<div class="sidebar-cart-box">
									<h3 class="sidebar-panel-header">
										<a href="javascript:void(0);" class="title"><i class="cart-icon"></i><em class="title">购物车</em></a>
										<span class="close-panel"></span>
									</h3>
									
								</div>
							</span>
						</div>
					</li>
					<li class="sidebar-tabs">
						<a href="javascript:void(0);" class="mpbtn_history quick-links-a sidebar-historybox-trigger"><i class="history"></i></a>
						<div class="popup"><font id="mpbtn_histroy">我看过的</font><i class="arrow-right"></i></div>
					</li>
					<!-- 如果当前页面有对比功能 则显示对比按钮 _start-->
					<li class="sidebar-tabs">
						<a href="javascript:void(0);" class="mpbtn-contrast quick-links-a sidebar-comparebox-trigger"><i class="contrast"></i></a>
						<div class="popup">对比商品<i class="arrow-right"></i></div>
					</li>
					<!-- 如果当前页面有对比功能 则显示对比按钮 _end-->
					<li>
						<a href="#W" class="mpbtn_stores quick-links-a"><i class="stores"></i></a>
						<div class="popup">我收藏的店铺<i class="arrow-right"></i></div>
					</li>
					<li id="collectGoods">
						<a href="#" class="mpbtn_collect quick-links-a"><i class="collect"></i></a>
						<div class="popup">我的收藏<i class="arrow-right"></i>
						</div>
					</li>
				</ul>
			</div>
			<div class="quick-toggle">
				<ul>
					<li class="quick-area">
						<a class="quick-links-a" href="javascript:void(0);"><i class="customer-service"></i></a>
						<div class="sidebar-service quick-sidebar">
							<i class="arrow-right"></i>
							<div class="customer-service">
								<a href="http://wpa.qq.com/msgrd?v=3&uin=315865872&site=qq&menu=yes"><span class="icon-qq"></span>QQ</a>
							</div>
							<div class="customer-service">
								<a href="http://amos1.taobao.com/msg.ww?v=2&uid=dreamlak&s=2"><span class="icon-ww"></span>旺旺</a>
							</div>
							<div class="customer-service">
								<a href="javascript:void(0);" class="service-online"><span class="icon-online"></span>在线客服</a>
							</div>
						</div>
					</li>
					<li class="quick-area">
						<a class="quick-links-a" href="javascript:void(0);"><i class="qr-code"></i></a>
						<div class="sidebar-code quick-sidebar">
							<i class="arrow-right"></i>
							<img src="images/qrd.jpg" />
						</div>
					</li>
					<li class="returnTop">
						<a href="javascript:void(0);" class="return_top quick-links-a"><i class="top"></i></a>
						<div class="popup">返回顶部<i class="arrow-right"></i></div>
					</li>
				</ul>
			</div>
		</div>
		<div class="rpoen">
			<!--购物车 start-->
			<div class="right-sidebar-panels sidebar-cartbox">
				<div class="sidebar-cart-box">
					<h3 class="sidebar-panel-header">
						<a href="javascript:void(0);" class="title"><i class="cart-icon"></i><em class="title">购物车</em></a>
						<span class="close-panel"></span>
					</h3>
					<div class="sidebar-cartbox-goods-list">
						<div class="cart-panel-main">
							<div class="cart-panel-content" style="height:703px;">
								<!-- 没有商品的展示形式 _start -->
								<div class="tip-box">
									<i class="tip-icon"></i>
									<div class="tip-text">
										您的购物车里什么都没有哦
										<br />
										<a class="color" href="#a" title="再去看看吧">再去看看吧</a>
									</div>
								</div>
								<!-- 没有商品的展示形式 _end-->
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--购物车 end-->
			<!--浏览历史 start-->
			<div class="right-sidebar-panels sidebar-historybox">
				<h3 class="sidebar-panel-header">
					<a href="javascript:;" class="title">
						<i></i>
						<em class="title">我的足迹</em>
					</a>
					<span class="close-panel"></span>
				</h3>
				<div class="sidebar-panel-main">
					<div class="sidebar-panel-content sidebar-historybox-goods-list">
						<!-- 没有浏览历史的展示形式 _start -->
						<div class="tip-box">
							<i class="tip-icon"></i>
							<div class="tip-text">
								您还没有在商城留下任何足迹哦
								<br />
								<a class="color" href="./">赶快去看看吧</a>
							</div>
						</div>
						<!-- 没有浏览历史的展示形式 _end-->
					</div>
				</div>
			</div>
			<!--浏览历史 end-->
			<!--对比列表 start-->
			<div class="right-sidebar-panels sidebar-comparebox">
				<h3 class="sidebar-panel-header">
					<a href="javascript:void(0);" class="title">
						<i class="compare-icon"></i>
						<em class="title">宝贝对比</em>
					</a>
					<span class="close-panel"></span>
				</h3>
				<div>
					<div class="sidebar-panel-main sidebar-comparebox-goods-list">
						<div class="sidebar-panel-content compare-panel-content">
							<!-- 没有对比商品的展示形式 _start -->
							<div class="tip-box">
								<i class="tip-icon"></i>
								<div class="tip-text">
									您还没有选择任何的对比商品哦 
									<br />
									<a class="color" href="./">再去看看吧</a>
								</div>
							</div>
							<!-- 没有对比商品的展示形式 _end-->
						</div>
					</div>
				</div>
			</div>
			<!--对比列表 end-->
		</div>
	</div>
</div>
<div id="openlogin_con" style="display:none;">
	<div class="login-form">
		<div class="login-con pos-r">
			<div class="login-wrap">
				<div class="login-tit">
					还没有账号？
					<a href="#a" class="regist-link color">立即注册 <i>&gt;</i></a>
				</div>
				<!-- 普通登录 star -->
				<div id="con_login_2" class="form">
					<form id="form2" action="#a" method="POST" novalidate="novalidate">
					<div class="form-group item-name">
						<div class="form-control-box">
							<i class="icon"></i> 
							<input type="text" id="username" name="username" value="" class="text" tabindex="1" placeholder="已验证手机/邮箱/用户名" autocomplete="off" aria-required="true">
						</div>
					</div>
					<div class="form-group item-password">
						<div class="form-control-box">
							<i class="icon"></i>
							<input type="password" id="password" name="password" value="" class="text" tabindex="2" placeholder="密码" autocomplete="off" aria-required="true">
						</div>
					</div>
					<div class="form-group item-vcode">
						<div class="form-control-box">
							<i class="icon"></i>
							<input type="text" id="verify_code" name="verify_code" value="" class="text" tabindex="2" placeholder="验证码" autocomplete="off" aria-required="true">
							<img id="verify_code_img" class="verify-code" src="/captcha.html" onclick="$(this).parent().find('#verify_code').val('');$(this).attr('src','/captcha.html?t='+Math.random());" title="点击刷新">
						</div>
					</div>
					<div class="safety">
						<label for="remember">
							<input type="checkbox" value="1" name="remember" id="remember" class="checkbox">
							<span>自动登录</span>
						</label>
						<a class="forget-password fr" href="#a">忘记密码？</a>
					</div>
					<div class="login-btn">
						<input type="submit" name="submit" class="btn-img btn-entry bg-color" id="loginsubmit" value="立即登录">
					</div>
					<div class="item-coagent">
						<a href="javascript:void(0);" data-id="pc_weixin" class="website-login"><i class="weixin"></i></a>
						<a href="javascript:void(0);" data-id="qq" class="website-login"><i class="qq"></i></a>
						<a href="javascript:void(0);" data-id="weibo" class="last website-login"><i class="sina"></i></a>
					</div>
				</form>
				</div>
				<!-- 普通登录 end -->
			</div>
		</div>
	</div>
</div>

<!-- 右侧边栏 _end -->

<!-- 底部 _start-->

<div class="links-box w1210">
	<div class="links-title"><span>友情链接</span></div>
	<div class="links-content">
		<!-- 友情链接循环开始 -->
		<a href="#a" title="">公司简介</a>
		<a href="#a" title="">企业文化</a>
		<a href="#a" title="">同商城</a>
		<a href="#a" title="">小京东+</a>
		<a href="#a" title="">有物流</a>
		<a href="#a" title="">在ERP</a>
		<a href="#a" title="">的收银</a>
		<a href="#a" title="">小京东经典版</a>
		<a href="#a" title="">单商户</a>
		<a href="#a" title="">ecshop模板</a>
		<!-- 友情链接循环结束 -->
	</div>
</div>
<div class="site-footer">
	<div class="footer-service">
		<div align="center">
			<img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/backend/1/images/2016/11/28/14803038465459.jpg" alt="" height="110" width="1210" /><br />
		</div>
	</div>
	<div class="footer-related">
		<div class="footer-article w1210">
			<dl class="col-article col-article-spe">
				<dt class="phone color">400-000-0004</dt>
				<dd class="email color">555555@qq.com</dd>
				<dd class="customer">
					<span>联系我们</span>
					<a href="javascript:void(0);" class="service-online"><em class="icon-yw service-online"></em></a>
					<a href="http://amos1.taobao.com/msg.ww?v=2&uid=dreamlak&s=2"><em class="icon-ww"></em></a>
					<a href="http://wpa.qq.com/msgrd?v=3&uin=315865872&site=qq&menu=yes"><em class="icon-qq"></em></a>
				</dd>
			</dl>
			<!---->
			<dl class="col-article col-article-first">
				<dt>新手上路</dt>
				<dd><a rel="nofollow" href="#a">购物流程</a></dd>
				<dd><a rel="nofollow" href="#a">订单查询</a></dd>
				<dd><a rel="nofollow" href="#a">常见问题</a></dd>
				<!-- -->
			</dl>
			<!---->
			<dl class="col-article col-article-first">
				<dt>支付方式</dt>
				<dd><a rel="nofollow" href="#a">网上支付</a></dd>
				<dd><a rel="nofollow" href="#a">货到付款</a></dd>
				<dd><a rel="nofollow" href="#a">公司转账</a></dd>
			</dl>
			<dl class="col-article col-article-first">
				<dt>配送服务</dt>
				<dd><a rel="nofollow" href="#a">配送范围及收费标准</a></dd>
				<dd><a rel="nofollow" href="#a">订单进度查询</a></dd>
				<dd><a rel="nofollow" href="#a">验货与签收</a></dd>
			</dl>
			<dl class="col-article col-article-first">
				<dt>售后服务</dt>
				<dd><a rel="nofollow" href="#a">退换货政策</a></dd>
				<dd><a rel="nofollow" href="#a">退换货流程</a></dd>
				<dd><a rel="nofollow" href="#a">退款说明</a></dd>
			</dl>
			<dl class="col-article col-article-first">
				<dt>商家合作</dt>
				<dd><a rel="nofollow" href="#a">商家入驻</a></dd>
				<dd><a rel="nofollow" href="#a">商家规则</a></dd>
				<dd><a rel="nofollow" href="#a">入驻流程</a></dd>
			</dl>
			<div class="QR-code fr">
				<ul class="tabs">
					<li class="current">微信</li>
					<li>APP</li>
				</ul>
				<div class="code-content">
					<div class="code"><img src="images/qrd.jpg"></div>
					<div class="code hide"><img src="images/qrd.jpg"></div>
				</div>
			</div>
		</div>
		<div class="footer-info">
			<div class="info-text">
				<!-- 底部导航 -->
				<p class="nav-bottom">
					<a href="#a">公司简介</a><em>|</em>
					<a href="#a">联系我们</a><em>|</em>
					<a href="#a">官网论坛</a><em>|</em>
					<a href="#a">代理合作</a><em>|</em>
					<a href="#a">帮助中心</a><em>|</em>
					<a href="#a">商家入驻</a><em>|</em>
					<a href="#a">联系我们</a>
				</p>
				<p>
					Copyright  Ybmall(www.ybcms.com) 版权所有
					<a href="#a">黔ICP备07501206号-2</a>
				</p>
				<p class="company-info" style="display: none;">贵州黔东南凯里市风情园</p>
				<p class="qualified">
					<a href="#a"><img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/backend/1/images/2016/11/10/14787661020127.png" alt="" /></a>
					<a href="#a"><img src="http://68dsw.oss-cn-beijing.aliyuncs.com/images/backend/1/images/2016/11/10/14787669819785.png" alt="" /></a>
				</p>
			</div>
			<div class="info-text"></div>
		</div>
	</div>
</div>


<!-- 底部 _end-->
</body>
</html>