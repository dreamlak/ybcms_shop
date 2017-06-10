//展开更多筛选
var begin_hidden = "2";
function Show_More_Attrgroup() {
	if("2" == 0){
		$(".attr-group-more").hide();
	}
	
	if (begin_hidden == 0) {
		$("[name='attr-group-dl']").each(function(i){
			$(this).show();
		});
		$('#attr-group-more-text').html("收起");
		begin_hidden = "2";
	}else{
		var more_text = "";
		var attr_names = [];
		$("[name='attr-group-dl']").each(function(i){
			if(i >= begin_hidden){
				$(this).hide();
				attr_names.push($(this).data("attr-name"));
			}else{
				$(this).show();
			}
		});
		
		if(attr_names.length > 4){
			attr_names = attr_names.slice(0, 4);
			more_text = "更多选项（"+attr_names.join("、")+" 等）";
		}else{
			attr_names = attr_names.slice(0, attr_names.length);
			more_text = "更多选项（"+attr_names.join("、")+"）";
		}
		
		$('#attr-group-more-text').html(more_text);
		begin_hidden = 0;
	}
	
	var kuan1 = $("#attr-list-ul").width();
	var kuan2 = $("#attr-group-more").width();
	var kuan = (kuan1 - kuan2) / 2;
	$('#attr-group-more').css("margin-left", kuan + "px");
}

$(function(){
	Show_More_Attrgroup();
	
	//栏目页当前位置，面包屑   分类下拉弹框
	$('.breadcrumb .crumbs-nav').hover(function() {
		$(this).toggleClass('curr');
	})
	
	//多选时，打开确定铵钮为提交状态
	$('.other-vattr-li,.brand').bind('click',function(){
		var seled_input_num = $(this).parents('ul').find('input[type="checkbox"]:checked').length;
		if(seled_input_num>0){
			$(this).parents('dd').find('.select-button').eq(0).attr('class','select-button select-button-sumbit');	
		}else if(seled_input_num == 0){
			$(this).parents('dd').find('.select-button').eq(0).attr('class','select-button disabled');
		}
	});
	
	//没有多选时点击直接打开
	$("a[data-go]").click(function(){
		var cn=$(this).parents('dl').attr('class');
		if(cn!='duoxuan'){
			location.href=$(this).data("go");
		}
	});
	
	// a标签、input框、按钮点击出现虚线边框问题解决
	$('a,.btn,button,input[type="radio"],input[type="checkbox"]').focus(function() {
		this.blur()
	});
	
	// 筛选条件色块
	$('.color-value li span').click(function() {
		var seled_num = $(this).parents('ul').find('.selected').length;
		if (seled_num > 0) {
			$(this).parents('dd').find('.select-button').eq(0).attr('class', 'select-button select-button-sumbit');
		} else if (seled_num == 0) {
			$(this).parents('dd').find('.select-button').eq(0).attr('class', 'select-button disabled');
		}
	});
	$('.other-value li input[type="checkbox"]').bind('click', function() {
		var seled_input_num = $(this).parents('ul').find('input[type="checkbox"]:checked').length;
		if (seled_input_num > 0) {
			$(this).parents('dd').find('.select-button').eq(0).attr('class', 'select-button select-button-sumbit');
		} else if (seled_input_num == 0) {
			$(this).parents('dd').find('.select-button').eq(0).attr('class', 'select-button disabled');
		}
	});
	
	//排序
	var scroll_height = $('#filter').offset().top;
	$(window).scroll(function() {
		var this_scrollTop = $(this).scrollTop();
		if (this_scrollTop > scroll_height) {
			$('#filter').addClass('filter-fixed').css({
				'left': ($(window).width() - $('.filter-fixed').outerWidth()) / 2
			});
		} else {
			$('#filter').removeClass('filter-fixed').css('left', '');
		}
	});
	
	//列表页对比展示位置
	if ($('.main').hasClass('main1210')) {
		$('#compareBox').css({
			'left': Math.ceil(($(window).width() - 990) / 2)
		})
	} else {
		$('#compareBox').css({
			'left': 225 + Math.ceil(($(window).width() - 1210) / 2)
		})
	}

	// 商品列表 收缩展开侧边
	$('.category-wrap .slide-aside').click(function() {
		if($('.category-wrap .aside').width() == 0){
			$(this).removeClass('left');
			$('.category-wrap .aside').animate({
				'width':210
			}, 500);
			$('.category-wrap .aside-inner').show().animate({
				'width':210
			}, 500);
			$('.category-wrap .main').removeClass('main1210').animate({
				'padding-left': 225
			}, 500);
			$('.category-wrap .main').find('.item:nth-child(5n)').removeClass('last');//列表5的倍数个加 last
			$('.category-wrap .main').find('.item:nth-child(4n)').addClass('last');//列表4的倍数个加 last
		}else{
			$(this).addClass('left');
			$('.category-wrap .aside').animate({
				'width': 0
			}, 500);
			$('.category-wrap .aside-inner').animate({
				'width': 0
			}, 500, function() {
				$(this).hide();
			});
			$('.category-wrap .main').addClass('main1210').animate({
				'padding-left': 0
			}, 500);
			$('.category-wrap .main').find('.item:nth-child(4n)').removeClass('last');
			$('.category-wrap .main').find('.item:nth-child(5n)').addClass('last');
		}
		if ($('.main').hasClass('main1210')) {
			$('#compareBox').css({
				'left': Math.ceil(($(window).width() - 990) / 2)
			})
		} else {
			$('#compareBox').css({
				'left': 225 + Math.ceil(($(window).width() - 1210) / 2)
			})
		}
	});
	
	//分页跳传
	var page_url = "?page={0}";
	page_url = page_url.replace(/&amp;/g, '&');
	var tablelist = $("#table_list").tablelist({
		page_mode: 1,
		go: function(page){
			page_url = page_url.replace("{0}", page);
			location.href = page_url;
		}
	});
	$(".prev-page").click(function(){
		tablelist.prePage();
	});
	$(".next-page").click(function(){
		tablelist.nextPage();
	});
});

//是否显示“更多”__初始化
function init_more(boxid, moreid, height) {
	var obj_brand = document.getElementById(boxid);
	var more_brand = document.getElementById(moreid);
	if (obj_brand.clientHeight > height) {
		obj_brand.style.height = height + "px";
		obj_brand.style.overflow = "hidden";
		more_brand.innerHTML = '<a href="javascript:void(0);"  onclick="slideDiv(this, \'' + boxid + '\', \'' + height + '\');" class="more" >更多</a>';
	}
}
//收起更多筛选
function slideDiv(thisobj, divID, Height) {
	var obj = document.getElementById(divID).style;
	if (obj.height == "") {
		obj.height = Height + "px";
		obj.overflow = "hidden";
		thisobj.innerHTML = "更多";
		thisobj.className = "more";
		// 如果是品牌，额外处理
		if (divID == 'brand-abox') {
			// obj.width="456px";
			getBrand_By_Zimu(document.getElementById('brand-zimu-all'), '');
			document.getElementById('brand-sobox').style.display = "none";
			document.getElementById('brand-zimu').style.display = "none";
			document.getElementById('brand-abox-father').className = "";
		}
	} else {
		obj.height = "";
		obj.overflow = "";
		thisobj.innerHTML = "收起";
		thisobj.className = "more opened";
		// 如果是品牌，额外处理
		if (divID == 'brand-abox') {
			// obj.width="456px";
			document.getElementById('brand-sobox').style.display = "block";
			document.getElementById('brand-zimu').style.display = "block";
			// getBrand_By_Zimu(document.getElementById('brand-zimu-all'),'');
			document.getElementById('brand-abox-father').className = "brand-more";
		}
	}
}
//品牌搜索
function getBrand_By_Name(val) {
	val = val.toLocaleLowerCase();
	var brand_list = document.getElementById('brand-abox').getElementsByTagName('li');
	for (var i = 0; i < brand_list.length; i++) {
		var name_attr_value = brand_list[i].getAttribute("name").toLocaleLowerCase();
		if (brand_list[i].title.indexOf(val) == 0 || name_attr_value.indexOf(val) == 0 || val == '') {
			brand_list[i].style.display = 'block';
		} else {
			brand_list[i].style.display = 'none';
		}
	}
}
//点击字母切换品牌
function getBrand_By_Zimu(obj, zimu) {
	document.getElementById('brand-sobox-input').value = "可搜索拼音、汉字查找品牌";
	obj.focus();
	var brand_zimu = document.getElementById('brand-zimu');
	var zimu_span_list = brand_zimu.getElementsByTagName('span');
	for (var i = 0; i < zimu_span_list.length; i++) {
		zimu_span_list[i].className = '';
	}
	var thisspan = obj.parentNode;
	thisspan.className = 'span';
	var brand_list = document.getElementById('brand-abox').getElementsByTagName('li');
	for (var i = 0; i < brand_list.length; i++) {
		// document.getElementById('brand-abox').style.width="auto";
		if (brand_list[i].getAttribute('rel') == zimu || zimu == '') {
			brand_list[i].style.display = 'block';
		} else {
			brand_list[i].style.display = 'none';
		}
	}
}
var duoxuan_a_valid = new Array();
// 点击多选， 显示多选区
function showDuoXuan(dx_divid, a_valid_id) {
	var dx_dl_div = document.getElementById('attr-list-ul').getElementsByTagName('dl');
	for (var i = 0; i < dx_dl_div.length; i++) {
		dx_dl_div[i].className = 'filter-attr';
		//dx_dl_div[0].className = 'selected-attr-dl';
	}
	var dxDiv = document.getElementById(dx_divid);
	dxDiv.className = "duoxuan";
	duoxuan_a_valid[a_valid_id] = 1;

}
//取消多选， 隐藏多选区
function hiddenDuoXuan(dx_divid, a_valid_id) {
	var dxDiv = document.getElementById(dx_divid);
	dxDiv.className = "filter-attr";
	duoxuan_a_valid[a_valid_id] = 0;
	if (a_valid_id == 'brand') {
		var ul_obj_div = document.getElementById('brand-abox');
		var li_list_div = ul_obj_div.getElementsByTagName('li');
		if (li_list_div) {
			for (var j = 0; j < li_list_div.length; j++) {
				li_list_div[j].className = "";
			}
		}
	} else {
		var ul_obj_div = document.getElementById('attr-abox-' + a_valid_id);
	}
	var input_list = ul_obj_div.getElementsByTagName('input');
	var span_list = ul_obj_div.getElementsByTagName('span');
	for (var j = 0; j < input_list.length; j++) {
		input_list[j].checked = false;
	}
	if (span_list.length) {
		for (var j = 0; j < span_list.length; j++) {
			span_list[j].className = "";
		}
	}
}
//钩选 要多选属性项
function duoxuan_Onclick(a_valid_id, idid, thisobj) {
	if (duoxuan_a_valid[a_valid_id]) {
		if (thisobj) {
			var fatherObj = thisobj.parentNode;
			if (a_valid_id == "brand") {
				fatherObj.className = fatherObj.className == "brand-seled" ? "" : "brand-seled";
			} else {
				fatherObj.className = fatherObj.className == "" ? "selected" : "";
			}
		}
		document.getElementById('chk-' + a_valid_id + '-' + idid).checked = !document.getElementById('chk-' + a_valid_id + '-' + idid).checked;
		return false;
	}
}
//提交多选
function duoxuan_Submit(dxid, indexid, url) {
	var theForm = document.forms['theForm'];
	var chklist = theForm.elements['checkbox_' + dxid + '[]'];
	var value = "";
	var mm = 0;
	for (var k = 0; k < chklist.length; k++) {
		if (chklist[k].checked) {
			value += mm > 0 ? "_" : "";
			value += chklist[k].value;
			mm++;
		}
	}
	
	if (mm == 0) {
		return false;
	}
	if (dxid == 'brand') {
		url = url.replace("{0}", value);
	} else {
		url = url.replace("{0}", value);
	}
	location.href = url;
}

//自定义价格提交
function setPrice(url) {
	var min = $('#price_min').val();
	var max = $('#price_max').val();
	
	if(min == "" && max == ""){
		return;
	}
	
	if(!isNaN(min) && min != "" && min >= 0){
		url = url.replace("{0}", min);
	}else{
		url = url.replace("{0}", 0);
	}
	
	if(!isNaN(max) && max != "" && max >= 0){
		url = url.replace("{1}", max);
	}else{
		url = url.replace("{1}", 0);
	}
	location.href = url;
}

$().ready(function() {
	//添加购物车
	$(".add-cart").click(function(event) {
		var goods_id = $(this).data("goods-id");
		var image_url = $(this).data("image-url");
		var buy_enable = $(this).data("buy-enable");
		if(buy_enable){
			$.msg(buy_enable);
			return;
		}
		$.cart.add(goods_id, 1, {
			is_sku: false, 
			event: event,
			image_url: image_url,
			callback: function(){
				var attr_list = $('.attr-list').height(); 
				$('.attr-list').css({ 
					"overflow":"hidden" 
				});
				if(attr_list>=200){ 
					$('.attr-list').addClass("attr-list-border");
					$('.attr-list').css({ 
						"overflow-y":"auto" 
					}); 
				}       
			}
		});
		//列表页面属性弹框
	});
	//对比
	$(".compare-btn").click(function(event) {
		var goods_id = $(this).data("compare-goods-id");
		var image_url = $(this).data("image-url");
		var that = $(this);
		//alert(image_url);return false;
		if ($(this).hasClass("curr")) {
			$.compare.remove(goods_id, function(result) {
				if (result.code == 0) {
					that.removeClass('curr');
				}
			});
		} else {
			$.compare.add(goods_id, image_url, event, function(result) {
				if (result.code == 0) {
					that.addClass('curr');
				}
			});
		}
	});

	//移除对对比商品
	$.compare.removeCallback = function(goods_id, result) {
		$("[data-compare-goods-id='" + goods_id + "']").removeClass('curr');
	}
	//清空对比商品
	$.compare.clearCallback = function(goods_id, result) {
		$("[data-compare-goods-id]").removeClass('curr');
	}
	
	//规格相册
	sildeImg(0);
	
	//收藏
	$('.collet-btn').click(function() {
		$('.pop-login,.pop-mask').show();
	});
	
	//地区组件
	/*var region_chooser = $(".region-chooser-container").regionchooser({
		value: "13,03",
		change: function(value, names, is_last) {
			if (value == '') {
				var values = this.values();
				if (values.length > 0) {
					value = values[values.length - 1].region_code;
				}
			}
			
			var region_code = "13,03";
			if (is_last && value != region_code ) {
				value = value + "";
				value = value.replace(/,/g, "_");
				var url = "list.html?cat_id={0}";
				url = url.replace(/&amp;/g, '&');
				url = url.replace("{0}", value);
				$.go(url);
			} 
		}
	});*/
	
	var goods_ids = '39695-39693-39039-39047-39674-39597-39671-38779-39000-38924-38598-38597-38858-38857-38829-38748-38745-38519-51-46';
	$.collect.getGoodsList(goods_ids, null, function(result){
		return false;//正试使用时删除
		var goods_list = result.data;
		$(".goods-collect").each(function(){
			var goods_id = $(this).data("goods-id");
			if(result.code == 0){
				if(goods_list[goods_id]){
					$(this).addClass("curr");
					$(this).find("span").html("已收藏");
				}else{
					$(this).removeClass("curr");
					$(this).find("span").html("收藏");
				}
			}
		});
	});
	
	$.compare.getGoodsList(goods_ids, function(result){
		return false;//正试使用时删除
		var goods_list = result.data;
		$(".goods-comapre").each(function(){
			var goods_id = $(this).data("compare-goods-id");
			if(result.code == 0){
				if(goods_list[goods_id]){
					$(this).addClass("curr");
				}else{
					$(this).removeClass("curr");
				}
			}
		});
	});
});

/* 浏览历史与猜你喜欢 */
function clear_history() {
	Ajax.call('user.php', 'act=clear_history', clear_history_Response, 'GET', 'TEXT', 1, 1);
}
function clear_history_Response(res) {
	document.getElementById('history_list').innerHTML = '您已清空最近浏览过的商品';
}

// 鼠标经过浏览历史与猜你喜欢切换js
$('.browse-history .browse-history-tab .tab-span').mouseover(function() {
	$(this).addClass('color').siblings('.tab-span').removeClass('color');
	$('.browse-history-line').stop().animate({
		'left': $(this).position().left,
		'width': $(this).outerWidth()
	}, 500);
	$('.browse-history-other').find('a').eq($(this).index()).removeClass('none').siblings('a').addClass('none');
	$('.browse-history-inner ul').eq($(this).index()).removeClass('none').siblings('ul').addClass('none');
})
var history_num = 0;
var history_li = $('.browse-history .recommend-panel li');
var history_slide_w = history_li.outerWidth() * 6;
var history_slide_num = Math.ceil(history_li.length / 6);
$('.browse-history .history-recommend-change').click(function() {
	history_num++;
	if (history_num > (history_slide_num - 1)) {
		history_num = 0;
	}
	$('.browse-history .recommend-panel').css({
		'left': -history_num * history_slide_w
	});
})

function toggleGoods(goods_id, sku_id, obj) {
	$.collect.toggleGoods(goods_id, sku_id, function(callback) {
		if (callback.code == 0) {
			$(obj).toggleClass("curr");
			if ($(obj).children().next().html() == "已收藏") {
				$(obj).children().next().html("收藏");
			} else {
				$(obj).children().next().html("已收藏");

			}

			if ($(obj).parent().attr("class") == "action") {
				if ($(obj).html() == "已收藏") {
					$(obj).html("收藏");
				} else {
					$(obj).html("已收藏");
				}
			}
		}
	}, false);
}
//每个商品 规格相册 切换
function sildeImg(num) {
	$(".img-scroll li").hover(function() {
		var src = $(this).find('img').data("src");
		$(this).parents(".img-scroll").prev().find("img").attr("src", src);
		$(this).find("a").addClass("curr").parent().siblings().find("a").removeClass("curr");
	});
}
//列表页规格图片鼠标经过效（左右滚动）
$(function() {
	$('.list-grid .item').each(function() {
		var num01 = 0;
		var num = $(this).find('.img-main li').length;
		if (num > 5) {
			$(this).find('.img-scroll').addClass('scrolled');
			$(this).find('.img-main').width(34 * num);
			$(this).find('.img-next').click(function() {
				num01++;
				$(this).siblings('.img-prev').removeClass('disabled');
				if (num01 == (num - 5)) {
					$(this).addClass('disabled');
				}
				if (num01 > (num - 5)) {
					num01 = num - 5;
				}
				if (num < 6) {
					num01 = 0;
					$(this).addClass('disabled');
					$(this).siblings('.img-prev').addClass('disabled');
				}
				$(this).siblings('.img-wrap').find('.img-main').animate({
					left: -num01 * 34
				}, 200);
			})
			$(this).find('.img-prev').click(function() {
				num01--;
				if (num01 == 0) {
					$(this).siblings('.img-next').removeClass('disabled');
					$(this).addClass('disabled');
				}
				if (num01 < 0) {
					num01 = 0;
				}
				$(this).siblings('.img-wrap').find('.img-main').animate({
					left: -num01 * 34
				}, 200);
			})
		}
	})
})
