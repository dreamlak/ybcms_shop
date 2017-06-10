$(function(){
	//栏目页当前位置，面包屑   分类下拉弹框
	$('.breadcrumb .crumbs-nav').hover(function() {
		$(this).toggleClass('curr');
	});
	
	//会员等级显示下拉
	$('.rank-prices').hover(function(){
		$(".vip1").hide();
		$(".vip2").show();
	},function(){
		$(".vip2").hide(); 
		$(".vip1").show();
	});
	
	//本店活动下拉效果
	$('.shop-prom-title').click(function(){
		$(this).hide();
		$(".shop-prom-Box").show();
	});
	//本店活动收起下拉效果
	$('.shop-prom-Box .hd').click(function(){
		$('.shop-prom-Box').hide();
		$(".shop-prom-title").show();
	});
	
	//左侧分类树点击展开收缩效果
	$('.tree li:has(ul)').addClass('parent_li');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
	
	//将商品详情左侧内容的高度给右侧定位元素模块
	$(".right-side-con").height($("#main_widget_1").height());
	
	//去手机购买二维码弹框
	$('.btn-phone').hover(function(){
		$("#phone-tan").toggle();
	});
	
	// 步进器
	var goods_number_amount = $(".amount-input").amount({
		value: 1,
		min: 1,
		max: "10",
		change: function(value) {
			if (value == this.max) {

			}
		}
	});
		
	//属性分类勾选
	$(".goods-spec-item").click(function() {
		if ($(this).hasClass("invalid")) {
			return;
		}
		$(this).siblings(".selected").removeClass("selected").find("i").remove();
		$(this).addClass("selected").append("<i></i>");
		// 是否为默认规格
		var is_default = $(this).data("is-default");
		var sku_id = getSkuId();
		if (sku_id) {
			getSkuInfo(sku_id, is_default, function(sku) {
				setSkuInfo(sku);
				// 变更地址栏信息
				//History.replaceState(null, '', sku_id + '.html');
				//$("title").html(sku.sku_name);
			});
		} else {
			// 变更地址栏信息
			//History.replaceState(null, '', 'goods-38924.html');
			//$("title").html("韩版全棉床上四件套1.8m纯棉简约秋冬季提花床单被套被罩床上用品");
			setSkuInfo(false);
		}
	}).hover(function() {
		$(this).addClass("selected");
	}, function() {
		if ($(this).find("i").size() == 0) {
			$(this).removeClass("selected");
		}
	});
	// 立即购买
	$(".buy-goods").click(function() {
		
		if ($(this).hasClass("disabled")) {
			return;
		}
		var sku_id = getSkuId();
		var number = $(".amount-input").val();
		$.cart.quickBuy(sku_id, number);
	})
	//在线客服
	/* 	$(".service-online").click(function() {
			var goods_id = 38924;
			$.openim({
				goods_id:goods_id
			});
		}) */

	// 添加购物车
	$(".add-cart").click(function(event) {
		if ($(this).hasClass("disabled")) {
			return;
		}
		var image_url = $(".goodsgallery").find(".gg-handler li:first img").attr("src");
		var sku_id = getSkuId();
		$.cart.add(sku_id, $(".amount-input").val(), {
			is_sku: true,
			image_url: image_url,
			event: event
		})
	});

	// 添加对比
	$(".add-compare").click(function(event) {
		var target = $(this);
		var goods_id = $(this).data("goods-id");
		var sku_id = $(this).add("sku-id");
		var image_url = $(this).data("image-url");
		$.compare.toggle(goods_id, image_url, event, function(result) {
			if (result.data == 1) {
				$(target).addClass("curr");
			} else {
				$(target).removeClass("curr");
			}
		});
	});

	// 添加收藏
	$(".collect-goods").click(function(event) {
		var target = $(this);
		var goods_id = $(this).data("goods-id");
		var sku_id = getSkuId();
		$.collect.toggleGoods(goods_id, sku_id, function(result) {
			if (result.code != 0) {
				return;
			}
			if (result.data == 1) {
				$(target).addClass("curr");
				$(target).find("span").html("取消收藏(" + result.collect_count + "人气)");
			} else {
				$(target).removeClass("curr");
				$(target).find("span").html("收藏商品(" + result.collect_count + "人气)");
			}
		}, true);
	});
	// 添加收藏
	$(".collect-shop").click(function(event) {
		var target = $(this);
		var shop_id = "7";
		$.collect.toggleShop(shop_id, function(result) {
			if (result.data == 1) {
				$(target).find("span").html("取消收藏");
			} else {
				$(target).find("span").html("收藏本店");
			}
		});
	});

	// 领取红包
	$("body").on("click", ".bonus-receive", function() {
		var bonus_id = $(this).data("bonus-id");
		var target = $(this);
		$.bonus.receive(bonus_id, function(result) {
			if (result.code == 0) {
				if (result.data == 0) {
					$(target).html("已领取").removeClass("color").removeClass("bonus-receive").addClass("bonus-received");
				}
				$.msg(result.message);
				return;
			} else if (result.code == 130) {
				$(target).html("已领取").removeClass("color").removeClass("bonus-receive").addClass("bonus-received");
			} else if (result.code == 131) {
				$(target).html("已抢光").removeClass("color").removeClass("bonus-receive").addClass("bonus-received");
			} else {

			}
			$.msg(result.message, {
				time: 5000
			});
		});
	});
	
	
	// 加载商品详情
	var desc_container = $(".goods-detail-content");
	var evaluate_container = $("#goods_evaluate");
	function load() {
		//详情
		if (!$("body").data("loading-goods-desc")) {
			// 计算高度
			if ($(document).scrollTop() >= $(desc_container).offset().top - $(window).height()) {
				$("body").data("loading-goods-desc", true);
				$.get("http://qhd.68dsw.com/goods/desc.html", {
					sku_id: "42397"
				}, function(result) {
					$(desc_container).html(result.pc_desc);
				}, "json");
			}
		}
		//评论
		if (!$("body").data("loading-goods-comment")) {
			// 计算高度
			if ($(document).scrollTop() >= $(evaluate_container).offset().top - $(window).height()) {
				$("body").data("loading-goods-comment", true);
				$.get('http://qhd.68dsw.com/goods/comment.html', {
					sku_id: "42397",
					output: 1
				}, function(result) {
					if (result.code == 0) {
						$(evaluate_container).html(result.data);
					}
				}, "json");
			}
		}
	}
	load();
	// 加载商品详情和评论
	$(window).scroll(function() {
		load();
	});
});




var sku_ids = [];
var local_region_code = "13,03";
var sku_freights = [];

function getSkuId() {
	var spec_ids = [];
	$(".choose").find(".attr").each(function() {
		var spec_id = $(this).find(".selected").data("spec-id");
		spec_ids.push(spec_id);
	});
	var sku_id = $.cart.getSkuId(spec_ids, sku_ids);
	if (sku_id == null) {
		return false;
	}
	return sku_id;
}
function getSkuInfo(sku_id, is_default, callback) {
	if ($(document).data("SZY-SKU-" + sku_id)) {
		var sku = $(document).data("SZY-SKU-" + sku_id);
		sku.is_default = is_default;
		// 回调
		if ($.isFunction(callback)) {
			callback.call({}, sku);
		}
	} else {
		$.get('http://qhd.68dsw.com/goods/sku', {
			sku_id: sku_id
		}, function(result) {
			if (result.code == 0) {
				var sku = result.data;
				sku.is_default = is_default;
				$(document).data("SZY-SKU-" + sku_id, sku);
				// 回调
				if ($.isFunction(callback)) {
					callback.call({}, sku);
				}
			} else {
				$.msg(result.message, {
					time: 5000
				});
			}
		}, "json");
	}
}
// 设置SKU信息
function setSkuInfo(sku) {
	if (sku == undefined || sku == null || sku == false) {
		$(".add-cart").addClass("disabled");
		$(".buy-goods").addClass("disabled");
		$(".SZY-GOODS-NUMBER").html("库存不足");
		return;
	}
	var goods_number = sku.goods_number;
	if (goods_number > 0) {
		if (sku_freights[local_region_code]) {
			if (sku_freights[local_region_code].limit_sale == 1) {
				goods_number = sku_freights[local_region_code].goods_number;
			}
		} else {
			changeLocation(local_region_code).always(function(result) {
				if (result.code == 0 && result.data.limit_sale == 1) {
					setSkuInfo(sku);
				}
			});
			return;
		}
	}
	// 点击默认规格才会切换相册
	if (sku.is_default == 1) {
		// 相册
		$(".goodsgallery").goodsgallery({
			images: sku.sku_images
		});
	}
	// 商品名称
	$(".SZY-GOODS-NAME").html(sku.sku_name);
	// 售价
	$(".SZY-GOODS-PRICE").html(sku.goods_price_format);
	// 市场价
	$(".SZY-MARKET-PRICE").html(sku.market_price_format);
	if (parseFloat(sku.market_price) == 0) {
		$(".SZY-MARKET-PRICE").parents(".show-price").hide();
	} else {
		$(".SZY-MARKET-PRICE").parents(".show-price").show();
	}
	// 会员价格
	if ($(".SZY-RANK-PRICES").size() > 0 && sku.rank_prices != undefined && sku.rank_prices != null) {
		$(".SZY-RANK-PRICES").nextAll().remove();
		var html = "";
		for (var i = 0; i < sku.rank_prices.length; i++) {
			var item = sku.rank_prices[i];
			html += "</br><span>" + item.rank_name + ":" + item.rank_price_format + "</span>";
		}
		$(".SZY-RANK-PRICES").after(html);
	}
	// 库存
	if (goods_number > 0) {
		$(".SZY-GOODS-NUMBER").html("库存" + goods_number + "件");
	} else {
		$(".SZY-GOODS-NUMBER").html("库存不足");
	}
	if (goods_number == 0) {
		$(".add-cart").addClass("disabled");
		$(".buy-goods").addClass("disabled");
	} else {
		$(".buy-goods").removeClass("disabled");
		$(".add-cart").removeClass("disabled");
	}
	$(".amount-input").data("amount-min", 1);
	$(".amount-input").data("amount-max", goods_number);
	if (goods_number > 0 && $(".amount-input").val() == 0) {
		$(".amount-input").val(1);
	} else if (goods_number == 0 && $(".amount-input").val() != 0) {
		$(".amount-input").val(0);
	}
	var goods_number_input = parseInt($(".amount-input").val());
	if (goods_number_input > goods_number) {
		$(".amount-input").val(goods_number);
	}
	// 处理赠品
	if (sku.gift_list && sku.gift_list.length > 0) {
		$(".SZY-GIFT-LIST").show();
		$(".SZY-GIFT-LABEL").nextAll().remove();
		for (var i = 0; i < sku.gift_list.length; i++) {
			var gift = sku.gift_list[i];
			var template = $("#SZY_GIFT_TEMPLATE").html();
			var element = $($.parseHTML(template));
			$(element).find("img").attr("src", gift.goods_image_thumb);
			$(element).find("a").attr("href", "" + gift.gift_sku_id + ".html");
			$(element).find("a").attr("title", "" + gift.sku_name);
			$(element).find(".gift-number").html("×" + gift.gift_number);
			$(".SZY-GIFT-LABEL").after(element);
		}
	} else {
		$(".SZY-GIFT-LIST").hide();
		$(".SZY-GIFT-LABEL").nextAll().remove();
	}
}


//商品详情右侧商品信息等定位切换效果
$(document).ready(function() {
	var navH = $("#main-nav-holder").offset().top;
	$(window).scroll(function() {
		var scroH = $(this).scrollTop(); // 获取滚动条的滑动距离
		if (scroH >= navH) {
			$("#main-nav-holder").addClass('fixed');// 滚动条的滑动距离大于等于定位元素距离浏览器顶部的距离，就固定，反之就不固定
		} else if (scroH < navH) {
			$("#main-nav-holder").removeClass('fixed');
		}
	})
	$('.goods-detail .title-list').click(function() {
		$(this).addClass('current').siblings('.title-list').removeClass('current');
		$("html,body").scrollTop($('.goods-detail-tabs').eq($(this).index()).offset().top - 30);
	});
	
	var scroll_h = 0;
	window.onscroll = function() {
		scroll_h = $(this).scrollTop();
		for (var i = 0; i < 5; i++) {
			if ($('.goods-detail-con').eq(i).offset()) {
				if (scroll_h > $('.goods-detail-con').eq(i).offset().top - 150) {
					$('.right-side-ul li').eq(i).addClass('abs-active').siblings().removeClass('abs-active');
				}
			}
		}
	}
	$(".right-side-ul li").hover(function() {
		$(this).addClass("abs-hot").siblings().removeClass("abs-hot");
	}, function() {
		$(".right-side-ul li").removeClass("abs-hot");
	});
	$(".right-side-ul li").click(function() {
		$(this).addClass("abs-active").siblings().removeClass("abs-active");
		$('html,body').animate({
			scrollTop: $('.goods-detail-con').eq($(this).index()).offset().top - 30
		}, 300);
	});
});


(function($){
	//商品相册大小图
	$.fn.goodsgallery = function(settings) {
		var defaults = {
			container: $(this),
			host: '',
			//当前图片： 0-缩略图 1-大图 2-原图
			current: [],
			//图片列表，每个图片都包含三张图片： 0-缩略图 1-大图 2-原图
			images: [],
			init: function() {
				$(this.container).addClass("goodsgallery");
				//获取当前第一个
				this.current = this.images[0];
				if (!this.current) {
					this.current = [];
				}
				var html = '<div class="gg-current-img">';
				html += '<a href="' + this.current[2] + '" class="MagicZoom" id="gg-zoom" rel="zoom-position:right;">';
				html += '<img src="' + this.current[1] + '" class="gg-image" width="400" height="400" />';
				html += '</div>';
				html += '<div class="gg-imagebar clearfix">';
				//相册向右滑动
				html += '<a href="javascript:;" class="gg-left-btn disabled"></a>';
				html += '<div class="gg-container">';
				html += '<div class="gg-content">';
				html += '<ul class="gg-handler">';
				html += this.render(this.images);
				html += '</ul>';
				html += '</div>';
				html += '</div>';
				//相册向友滑动
				html += '<a href="javascript:;" class="gg-right-btn"></a>';
				$(this.container).html($.parseHTML(html));
				//初始化按钮
				this.initButton();
				this.init = true;
			},
			render: function(images) {
				var html = "";
				for (var i = 0; i < images.length; i++) {
					var image = images[i];
					var current_class = i == 0 ? 'current' : '';
					html += '<li>';
					html += '<a href="' + image[2] + '" data-original="' + image[2] + '" rev="' + image[1] + '" rel="zoom-id: gg-zoom;" title="" class="' + current_class + '">';
					html += '<img src="' + image[1] + '" alt="" class="" />';
					html += '</a>';
					html += '</li>';
				}
				return html;
			},
			//加载
			load: function(images) {
				var settings = this;
				var container = settings.container;
				var html = this.render(this.images);
				$(container).find(".gg-handler").html($.parseHTML(html));
				//初始化按钮
				this.initButton();
				//切换第一个图片显示
				$(container).find(".gg-handler li:first").mouseover();
			},
			//初始化按钮栏
			initButton: function() {
				var settings = this;
				var container = settings.container;
				var index = 0;
				var gg_lis = this.images.length;
				var leftBtn = $(container).find(".gg-left-btn");
				var rightBtn = $(container).find(".gg-right-btn");
				$(container).find(".gg-handler").width(70 * gg_lis);
				//鼠标经过切换大图
				$(container).find(".gg-handler li").mouseover(function() {
					$(this).find('a').addClass('current');
					$(this).siblings().find('a').removeClass('current');
					var original = $(this).find("a").data("original");//图片地址
					//变更预览大图
					$(container).find(".MagicZoom").attr("href", original);
					$(container).find(".gg-image").attr("src", $(this).find("a").attr("rev"));
					$(container).find(".MagicZoomBigImageCont").find("img").attr("src", original);
				})
				if (gg_lis < 6) {
					$(rightBtn).addClass('disabled')
				}
				//点击右边
				$(rightBtn).click(function() {
					index++;
					$(leftBtn).removeClass('disabled');
					if (index == (gg_lis - 5)) {
						$(rightBtn).addClass('disabled');
					}
					if (index > (gg_lis - 5)) {
						index = gg_lis - 5;
					}
					if (gg_lis < 6) {
						index = 0;
						$(rightBtn).addClass('disabled');
						$(leftBtn).addClass('disabled');
					}
					$(container).find(".gg-handler").animate({
						left:-index * 70
					}, 100);
				});
				//点击左边
				$(leftBtn).click(function() {
					index--;
					if (index == 0) {
						$(rightBtn).removeClass('disabled');
						$(leftBtn).addClass('disabled');
					}
					if (index < 0) {
						index = 0;
					}
					$(container).find(".gg-handler").animate({
						left:-index * 70
					}, 100);
				});
			}
		};
		if ($(this).data("szy-goodsgallery")) {
			defaults = $(this).data("szy-goodsgallery");
			settings = $.extend(true, defaults, settings);
			settings.load(settings.images);
		}else{
			settings = $.extend(true, defaults, settings);
			settings.init();
		}
		$(this).data("szy-goodsgallery", settings);
		return settings;
	}
})(jQuery);
