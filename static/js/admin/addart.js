var userAgent = navigator.userAgent.toLowerCase();
jQuery.browser = {
	version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [0,'0'])[1],
	safari: /webkit/.test( userAgent ),
	opera: /opera/.test( userAgent ),
	msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
	mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
};
//加粗
function input_font_bold() {
	if($('#title').css('font-weight') == '700' || $('#title').css('font-weight')=='bold') {
		$('#title').css('font-weight','normal');
		$('#font_weight').val('');
		$('.fa-bold').css('font-weight','normal');
	} else {
		$('#title').css('font-weight','bold');
		$('#font_weight').val('font-weight:bold;');
		$('.fa-bold').css('font-weight','bold');
	}
}

//颜色
require(["util"],function(util){
	$(function(){
		$(".colorpicker").each(function(){
			var elm = this;
			util.colorpicker(elm, function(color){
				$(elm).parent().prev().css('color', color.toHexString());
				$('.fa-th-large').css('color', color.toHexString());
				$('#font_color').val('color:'+ color.toHexString()+';');
			});
		});
		$(".colorclean").click(function(){
			$(this).parent().prev().css("color",'transparent');
			$('.fa-th-large').css("color", "#333");
			$('#font_color').val('');
		});
	});
});

//关键词
function get_keywords(){
	$.post('/api/keywords/index.php?sid='+Math.random()*5, {data:$('#title').val()}, function(data){
		if(data && $('#keywords').val()=='') $('#keywords').val(data);
	})
}

//重名
function isrename(e){
	$.post('/admin/Article/isrenameAjax.html', {data:$('#title').val()}, function(data){
		if(data.status==1){
			$(e).attr('style','color:red');
			$(e).text('可以发布');
		}
	})
}

//字符长度计算
var charset = 'utf-8';
function strlen_verify(obj, checklen, maxlen) {
	var v = obj.val(), charlen = 0, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = strlen(v);
	for(var i = 0; i < v.length; i++) {
		if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 255) {
			curlen -= charset == 'utf-8' ? 2 : 1;
		}
	}
	if(curlen >= len) {
		$('#'+checklen).html(curlen - len);
	} else {
		obj.val(mb_cutstr(v, maxlen, true));
	}
}
function mb_cutstr(str, maxlen, dot) {
	var len = 0;
	var ret = '';
	var dot = !dot ? '...' : '';
	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}
function strlen(str) {
	//return ($.browser.msie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
	return (str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

//转向链接
function islinkck(){
	if($('#islink').is(':checked')){
		$('#linkurl').removeAttr('disabled');
	}else{
		$('#linkurl').attr('disabled','true');
	}
}
