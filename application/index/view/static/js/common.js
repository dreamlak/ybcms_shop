$().ready(function() {
	try {
		// 头部导航下拉菜单
		$('.menu-item .menu').hover(function(){
			 $(this).find('.menu-bd').show();
		 },function(){
			 $(this).find('.menu-bd').hide();
		 }); 
	} catch (e) {
	}

	try {
		// 头部搜索 店铺、宝贝选择切换
		$('.search-type').hover(function() {
			$(this).css({
				"height": "auto",
				"overflow": "visible"
			});
		}, function() {
			$(this).css({
				"height": 36,
				"overflow": "hidden"
			});
		});
		var cur_value = $(".SZY-SEARCH-BOX-KEYWORD").attr('placeholder');
		$('.search-type li:not(".curr")').click(function() {
			var this_text = $(this).text();
			var this_num = $(this).attr('num');
			var curr_text = $(this).siblings('.curr').text();
			var curr_num = $(this).siblings('.curr').attr('num');
			if( this_num==1 ){
				$(".SZY-SEARCH-BOX-KEYWORD").attr('placeholder','');
				
			}else{
				$(".SZY-SEARCH-BOX-KEYWORD").attr('placeholder',cur_value);
			}
			
			$(this).text(curr_text).attr('num', curr_num).siblings('.curr').text(this_text).attr('num', this_num);
			$('.searchtype').val(this_num);
			$('.search-type').css({
				"height": 36,
				"overflow": "hidden"
			});
		})
	} catch (e) {
	}

	try {
		// 全部分类鼠标经过展开收缩效果
		$('.category-box-border .home-category').hover((function() {
			$('.expand-menu').css('display', 'inline-block');
		}), (function() {
			$('.expand-menu').css("display", "none");
		}));
	} catch (e) {
	}

	try {
		// 左侧分类弹框
		$('.list').each(function(){
			var all_width = [];
			var num = $(this).find('.subitems dl').length;
			for(var i=0 ; i< num ; i++){
				all_width.push(parseInt($(this).find('.subitems dl').eq(i).find('dt').find('em').text().length));
				$(this).find('.subitems dl').eq(i).find('dt').find('a').outerWidth()
			}
			$(this).find('.subitems dl dt').width(Math.max.apply(null,all_width)+'em');
		})

		$('.list').hover(function() {
			$(this).find('.categorys').show();
		}, function() {
			$(this).find('.categorys').hide();
		});
	} catch (e) {
	}

	try {
		// 右侧边栏
		$(window).scroll(function() {
			if ($(this).scrollTop() > 300) {
				$('.returnTop').show();
			} else {
				$('.returnTop').hide();
			}
		})
		$(".returnTop").click(function() {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
		
		// 点击用户图标弹出登录框
		$('.quick-login .quick-links-a,.quick-login .quick-login-a,.customer-service-online a').click(function() {
			$('.pop-login,.pop-mask').show();
		})
		$('.quick-area').mouseover(function() {
			$(this).find('.quick-sidebar').show();
		});
		$('.quick-area').mouseout(function() {
			$(this).find('.quick-sidebar').hide();
		})
		// 移动图标出现文字
		$(".right-sidebar-panel li").mouseenter(function() {
			$(this).children(".popup").stop().animate({
				left: -92,
				queue: true
			});
			$(this).children(".popup").css("visibility", "visible");
			$(this).children(".ibar_login_box").css("display", "block");
		});
		$(".right-sidebar-panel li").mouseleave(function() {
			$(this).children(".popup").css("visibility", "hidden");
			$(this).children(".popup").stop().animate({
				left: -121,
				queue: true
			});
			$(this).children(".ibar_login_box").css("display", "none");
		});
		// 点击购物车、用户信息以及浏览历史事件
		$('.sidebar-tabs').click(function() {
			if ($('.right-sidebar-main').hasClass('right-sidebar-main-open') && $(this).hasClass('current')) {
				$('.right-sidebar-main').removeClass('right-sidebar-main-open');
				$(this).removeClass('current');
				$('.right-sidebar-panels').eq($(this).index() - 1).removeClass('animate-in').addClass('animate-out').css('z-index', 1);
			} else {
				$(this).addClass('current').siblings('.sidebar-tabs').removeClass('current');
				$('.right-sidebar-main').addClass('right-sidebar-main-open');
				$('.right-sidebar-panels').eq($(this).index() - 1).addClass('animate-in').removeClass('animate-out').css('z-index', 2).siblings('.right-sidebar-panels').removeClass('animate-in').addClass('animate-out').css('z-index', 1);
			}
		});
		$(".right-sidebar-panels").on('click', '.close-panel', function() {
			$('.sidebar-tabs').removeClass('current');
			$('.right-sidebar-main').removeClass('right-sidebar-main-open');
			$('.right-sidebar-panels').removeClass('animate-out');
		});
		$(document).click(function(e) {
			var target = $(e.target);
			if (target.closest('.right-sidebar-con').length == 0) {
				$('.right-sidebar-main').removeClass('right-sidebar-main-open');
				$('.sidebar-tabs').removeClass('current');
				$('.right-sidebar-panels').removeClass('animate-in').addClass('animate-out').css('z-index', 1);
			}
		})
	} catch (e) {
	}

	// Ajax快速登录
	$(".ajax-login").click(function() {
		var html=$('#openlogin_con').html();
		var openlogin=layer.open({
			id: "open_login_pages",
			type: 1,
			title: '会员登录',
			shadeClose: false,
			shade: 0.3,
			content: html,
			success: function(layero, index){
				//console.log(layero, index);
				//ckverify();
				$('#open_login_pages').find('.text').val('');
			}
		});
	});

	// 底部二维码切换(app/微信)
	$(".QR-code li").hover(function() {
		var index = $(this).index();
		$(this).addClass("current").siblings().removeClass("current");
		$(".QR-code .code").eq(index).removeClass("hide").siblings().addClass("hide");
	})

	// 在线客服
	$(".service-online").click(function() {
		var goods_id = $(this).data("goods_id");
		var shop_id = $(this).data("shop_id");
		var order_id = $(this).data("order_id");
		
		$.openim({goods_id:goods_id,shop_id:shop_id,order_id:order_id});
	})
	


});
//读取 cookie
function getCookie(c_name){
	if (document.cookie.length>0){
	  	c_start = document.cookie.indexOf(c_name + "=")
	  	if (c_start!=-1){
		    c_start=c_start + c_name.length+1 
		    c_end=document.cookie.indexOf(";",c_start)
		    if (c_end==-1) c_end=document.cookie.length{
		    	return unescape(document.cookie.substring(c_start,c_end))
		   	}
	  	} 
	}
	return "";
}
//设置 cookie
function setCookies(name, value, time){
	var cookieString = name + "=" + escape(value) + ";";
	if (time != 0) {
		var Times = new Date();
		Times.setTime(Times.getTime() + time);
		cookieString += "expires="+Times.toGMTString()+";"
	}
	document.cookie = cookieString;
}
//验证码刷新
function ckverify(){
	$('#verify_code').val('');
	$('#verify_code_img').attr('src','/captcha.html?t='+Math.random());
}
/*
function serviceOnLine(shop_id){
	$.openim({shop_id:shop_id});
}
// 动态、普通登录切换
function setTab(name, cursel, n) {
	for (i = 1; i <= n; i++) {
		var menu = $("#" + name + i);
		var con = $("#con_" + name + "_" + i);

		if (i == cursel) {
			$(con).show();
			$(menu).addClass("active");
		} else {
			$(con).hide();
			$(menu).removeClass("active");
		}
	}
}
*/
//滚动切换（下一个，上一个，容器ID，容器上一层ID，显示数量）
function Move(btn1,btn2,box,btnparent,shu){
	var llishu=$(box).first().children().length;
	var liwidth=$(box).children().width();
	var boxwidth=llishu*liwidth;
	var shuliang=llishu-shu;
	$(box).css('width',''+boxwidth+'px');
	var num=0;
	$(btn1).click(function(){
		num++;
		if (num>shuliang) {
			num=shuliang;
		}
		var move=-liwidth*num;
		$(this).closest(btnparent).find(box).stop().animate({'left':''+move+'px'},500);
	});
	$(btn2).click(function(){
		num--;
		if (num<0) {
			num=0;
		}
		var move=liwidth*num;
		$(this).closest(btnparent).find(box).stop().animate({'left':''+-move+'px'},500);
	})
}

(function($) {
	/**
	 * 跳转页面
	 * @param url 跳转的链接，为空则刷新当前页面
	 */
	$.go = function(url, target, show_loading) {
		if (url == undefined) {
			url = window.location.href;
		}
		if (show_loading !== false) {
			// 开启缓载效果
			$.loading.start();
		}
		var id = $.uuid();
		var element = $("<a id='" + id + "' style='display: none;'></a>");
		$(element).attr("href", url);
		if (target) {
			$(element).attr("target", target);
			// 停止缓载效果
			$.loading.stop();
		}
		$("body").append(element);
		document.getElementById(id).click();
	};
	
	var lastUuid = 0;
	$.uuid = function() {
		return (new Date()).getTime() * 1000 + (lastUuid++) % 1000;
	}
	
	/**
	 * 加载
	 */
	$.loading = {
		// 开始加载
		start: function() {
			// 获取网站图标
			var icon = 'images/loading.gif';//$("link[rel='icon']").attr("href");
			// 缓载主题
			var loading_class = "layer-msg-loading SZY-LAYER-LOADING";
			// 缓载颜色
			var color = "#fff";
			// 读取Cookie
			var arr, reg = new RegExp("(^| )loading_style=([^;]*)(;|$)");
			if (arr = document.cookie.match(reg)) {
				if (unescape(arr[2]) == 1) {
					loading_class = "layer-msg-loading-simple SZY-LAYER-LOADING";
					// 读取Cookie
					var arr, reg = new RegExp("(^| )loading_color=([^;]*)(;|$)");
					if (arr = document.cookie.match(reg)) {
						color = unescape(arr[2]);
					}
				}
			}
			var index = layer.msg('<div class="loader-inner ball-clip-rotate"><div style="border-color:' + color + '; border-bottom-color: transparent; width: 30px; height: 30px; animation: rotate 0.45s 0s linear infinite; "></div><img style="width: 16px; height: 16px;" src="' + icon + '" /></div>', {
				time: 0,
				skin: "layui-layer-hui " + loading_class,
				fixed: true,
				anim: -1,
				shade: [0.2, '#F3F3F3'],
				area: ["60px", "60px"],
				success: function(object, index) {
					$(object).removeClass("layui-layer-msg");
				}
			});
			$.loading.index = index;
		},
		// 停止加载
		stop: function() {
			$(".SZY-LAYER-LOADING").each(function() {
				var index = $(this).attr("times");
				layer.close(index);
			});
		}
	};
	/**
	 * 求集合的笛卡尔之积
	 * @param list 必须为数组，否则返回空数组
	 * @return 结果集
	 */
	$.toDkezj = function(list) {
		if ($.isArray(list) == false || list.length == 0) {
			return [];
		}
		if (list.length == 1) {
			var temp_list = [];
			for (var i = 0; i < list[0].length; i++) {
				temp_list.push([list[0][i]]);
			}
			return temp_list;
		}
		var result = new Array();// 结果保存到这个数组
		function dkezj(index, temp_result) {
			if (index >= list.length) {
				result.push(temp_result);
				return;
			}
			var temp_array = list[index];
			if (!temp_result) {
				temp_result = new Array();
			}
			for (var i = 0; i < temp_array.length; i++) {
				var cur_result = temp_result.slice(0, temp_result.length);
				cur_result.push(temp_array[i]);
				dkezj(index + 1, cur_result);
			}
		}
		dkezj(0);
		return result;
	};

	/**
	 * 求数组内的全排序
	 */
	$.toPermute = function(input) {
		var permArr = [], usedChars = [];
		function main(input) {
			var i, ch;
			for (i = 0; i < input.length; i++) {
				ch = input.splice(i, 1)[0];
				usedChars.push(ch);
				if (input.length == 0) {
					permArr.push(usedChars.slice());
				}
				main(input);
				input.splice(i, 0, ch);
				usedChars.pop();
			}
			return permArr
		}
		return main(input);
	};
	/**
	 * 数量步进器
	 * @author niqingyang <niqy@qq.com>
	 */
	$.fn.amount = function(options) {

		var objects = [];

		$(this).each(function() {

			var defaults = {
				target: null,
				value: 1,
				min: 1,
				step: 1,
				max: null,
				// 支持：integer-整数（默认）,number-数字
				// type: 'integer',
				// value改变事件
				// @param element 元素
				change: null,
				// 解析
				parseValue: function(value) {
					return parseInt(value);
				}
			};

			var target = $(this);

			var settings = $.extend(true, {}, defaults, options);

			settings.target = target;

			if (!isNaN($(target).data("amount-min"))) {
				settings.min = $(target).data("amount-min");
			}
			if (!isNaN($(target).data("amount-max"))) {
				settings.max = $(target).data("amount-max");
			}

			if (!isNaN(settings.max) && settings.value > settings.max) {
				settings.value = settings.max;
				target.val(settings.value).change();
			} else if (!isNaN(settings.min) && settings.value < settings.min) {
				settings.value = settings.min;
				target.val(settings.value).change();
			}

			// 加
			$(target).parents(".amount").find(".amount-plus").click(function() {
				if (!isNaN($(target).data("amount-min"))) {
					settings.min = $(target).data("amount-min");
				}
				if (!isNaN($(target).data("amount-max"))) {
					settings.max = $(target).data("amount-max");
				}
				var value = parseInt(target.val()) + settings.step;
				if (isNaN(value)) {
					return;
				}
				if (isNaN(settings.parseValue(settings.max)) || value <= settings.max) {
					var result = true;
					if ($.isFunction(settings.change)) {
						result = settings.change.call(settings, target, value);
					}
					if (result == true || result == undefined) {
						target.val(value);
						settings.value = value;
						target.change();
					}
				}
			});

			// 减
			$(target).parents(".amount").find(".amount-minus").click(function() {
				if (!isNaN($(target).data("amount-min"))) {
					settings.min = $(target).data("amount-min");
				}
				if (!isNaN($(target).data("amount-max"))) {
					settings.max = $(target).data("amount-max");
				}
				var value = settings.parseValue(target.val()) - settings.step;
				if (isNaN(value)) {
					return;
				}
				if (isNaN(settings.parseValue(settings.min)) || value >= settings.min) {
					var result = true;
					if ($.isFunction(settings.change)) {
						result = settings.change.call(settings, target, value);
					}
					if (result == true || result == undefined) {
						target.val(value);
						settings.value = value;
						target.change();
					}
				}
			});

			// 键盘事件
			$(target).keyup(function(event) {
				if (!isNaN($(target).data("amount-min"))) {
					settings.min = $(target).data("amount-min");
				}
				if (!isNaN($(target).data("amount-max"))) {
					settings.max = $(target).data("amount-max");
				}
				if ($.trim(target.val()) == '') {
					target.val('');
					return;
				}
				var value = settings.value;
				// 190 - 小数点
				// 46 - delete
				// 8 - backspace
				// 37、39 - 左右光标
				var usable = true;
				if (event.keyCode != 46 && event.keyCode != 8 && event.keyCode != 37 && event.keyCode != 39) {
					if (!((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105))) {
						usable = false;
					}
				}
				if (usable) {
					value = settings.parseValue(target.val());
				}
				if (isNaN(value)) {
					value = settings.value;
				} else {
					if (!isNaN(settings.parseValue(settings.max)) && value >= settings.max) {
						value = settings.max;
					}

					if (!isNaN(settings.parseValue(settings.min)) && value <= settings.min) {
						value = settings.min;
					}
				}
				var result = true;
				if ($.isFunction(settings.change)) {
					result = settings.change.call(settings, $(this), value);
				}
				if (result == true || result == undefined) {
					target.val(value);
					settings.value = value;
					target.change();
				}
			}).focus(function() {
				// 禁用输入法
				this.style.imeMode = 'disabled';
			}).blur(function() {
				if (!isNaN($(target).data("amount-min"))) {
					settings.min = $(target).data("amount-min");
				}
				if (!isNaN($(target).data("amount-max"))) {
					settings.max = $(target).data("amount-max");
				}
				if ($.trim(target.val()) == '') {
					target.val(settings.min);
					settings.value = settings.min;
				}
			});
			objects.push(settings);
		});
		if ($(this).size() == 1) {
			return options;
		}
		return objects;
	}
})(jQuery);