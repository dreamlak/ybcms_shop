/**
 * @title 	卡车之家统计
 * @authors ZP (zhanpeng.zhu@360che.com)
 * @date    2016-08-05 14:09:10
 * @version 1.1
 */
;(function(){if(!window.addEventListener)window.prototype.addEventListener=function(a,b,c){a.attachEvent("on"+b,function(e){var$=e||window.event;return c.call(a,$)})}})();

// 新增一个PC端统计筛选的功能，用于过滤无效的统计
var filterAnalyisis = {
    init:function(callback,n){
        this.enabled = null;
        this.domain = location.hostname.indexOf('360che.com') >= 0 ? true : false;
        this.load(callback,n);
    },
    mini:function(){       // 小窗
        if (document.documentElement.clientWidth > 300 && document.documentElement.clientHeight > 300){
            return false;    
        }else{
            return true;
        } 
    },
    input:function(e){       // 键盘输入
        var code = e.keyCode;
        if (code == '37' || code == '38' || code == '39' || code == '40') 
        this.enabled = true;
    },
    inFrame:function(){     // 在iframe中嵌套
        try {
            if (self != top) {
                return true;
            }else{
                return false;
            }
        } catch (e) {
            return false;
        }
    },
    load:function(callback,n){
        if(!this.enabled){
            this.timer && clearTimeout(this.timer);
            this.timer = setTimeout(function(){
            	filterAnalyisis.load(callback,n);
            },10);
            return;       
        }
        if(!this.mini() && !this.inFrame() && this.domain){
            callback(n);
        }
    }
};
document.addEventListener('keydown',function(e){
    filterAnalyisis.input(e); 
});
document.addEventListener('mouseover',function(e){
     filterAnalyisis.enabled = true;    
});
document.addEventListener('mousemove',function(e){
    filterAnalyisis.enabled = true;
});

// 统计主函数
function truckhomeAnalyisis(){
	var ua = navigator.userAgent,n = 3;
	var device = {
		"m":2,
		"pc":3,
		"android":5,
		"iphone":6,
		"wechat":8	
	};

	/* TruckHome */
	var tl = document.createElement('script');
	tl.src = 'http://a1.360che.com/stats/tl.js';
	document.body.appendChild(tl);
	
	if(location.href.indexOf('zhuanti') >= 0 || location.href.indexOf('topic') >= 0){
		var cw = document.createElement('script');
		cw.src = 'http://static.360che.com/main/cw.js';
		document.body.appendChild(cw);
	}
	

	/* Baidu */
	var protocol = document.location.protocol == 'https' ? 'https://' : 'http://';
	var baidu = document.createElement('script');
	baidu.src = protocol + 'hm.baidu.com/h.js?87035a00e917f0eee43e4967b495f7c9';
	document.body.appendChild(baidu);

	/* Google */
	function _truckhomeGa(n){
		var _r = window.document.referrer.replace('http://', '');
		if (_r == '')  _r = 'wulaiyuan';
		(function (i, s, o, g, r, a, m) {
		    i['GoogleAnalyticsObject'] = r; i[r] = i[r] || function () {
		        (i[r].q = i[r].q || []).push(arguments)
		    }, i[r].l = 1 * new Date(); a = s.createElement(o),
		        m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
		})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
		ga('create', 'UA-64002767-'+ n , 'auto');
		ga('set', 'dimension1', _r); 
		ga('send', 'pageview', {
		    'dimension1': _r,
		    'dimension2':ua 
		});		
	};

	if(ua.match(/iphone/gi) || ua.match(/android/gi)){
		if(ua.match(/360che/gi)){
			if(ua.match(/iphone/gi)){
				n = device['iphone']	
			}else if(ua.match(/android/gi)){
				n = device['android']
			}
		}else if(/MicroMessenger/i.test(ua)){
			n = device['wechat'];	
		}else{
			n = device['m'];
		}
		_truckhomeGa(n);
	}else{
		n = device['pc'];
		filterAnalyisis.init(_truckhomeGa,n);
	};
};

window.addEventListener('load',truckhomeAnalyisis);
