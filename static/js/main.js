require.config({
	/*用于加载模块的根路径。*/
	//baseUrl:'/static/js',
	/*用于映射不存在根路径下面的模块路径*/
	paths: {
		'jquery': '/static/js/jquery/jquery-1.11.1.min',
		'jquery.zclip': '/static/js/zclip/jquery.zclip.min',
		'jquery.caret': '/static/js/jquery/jquery.caret',
		'jquery.qrcode': '/static/js/jquery/jquery.qrcode.min',
		'jquery.jqprint': '/static/js/jquery/jquery.jqprint-0.3',
		'jquery.migrate': '/static/js/jquery/jquery-migrate-1.2.1.min',
		'jquery-event-drag': '/static/js/jquery/jquery.event.drag',
		'jquery-treetable': '/static/js/jquery/jquery.treetable',
		'jquery-treeview': '/static/js/jquery/jquery.treeview',
		'jquery-colorbox': '/static/js/colorbox/jquery.colorbox-min',
		'layer': '/static/js/layer/layer',
		'layer.mobile': '/static/js/layer/mobile/layer',
		'bootstrap': '/static/js/bootstrap/js/bootstrap.min',
		'filestyle': '/static/js/bootstrap/js/bootstrap-filestyle.min',
		'bootstrap-datetimepicker': '/static/js/bootstrap/js/bootstrap-datetimepicker.min',
		'bootstrap-datetimepicker-zh-CN': '/static/js/bootstrap/js/bootstrap-datetimepicker.zh-CN',
		'datetimepicker': '/static/js/datetimepicker/jquery.datetimepicker',
		'daterangepicker': '/static/js/daterangepicker/daterangepicker',
		'colorpicker': '/static/js/colorpicker/spectrum',
		'clockpicker': '/static/js/clockpicker/clockpicker.min',
		'webuploader': '/static/js/webuploader/js/webuploader.min',
		'uploadfile': '/static/js/webuploader/js/uploadfile',
		'slimscroll': '/static/js/admin/jquery.slimscroll.min',
		'think': '/static/js/think',
		'util': '/static/js/util',
		'moment': '/static/js/moment',
		'css': '/static/js/css.min',
		'TeachDialog':'/static/js/TeachDialog',
		'ZeroClipboard':'/static/js/ueditor/third-party/zeroclipboard/ZeroClipboard',
		
		'map': 'http://api.map.baidu.com/getscript?v=2.0&ak=3fb2a8afec62f91bfe5388d61aafba6c&services=&t=20140530104353',
		'gpi':'http://api.map.baidu.com/api?v=2.0&ak=3fb2a8afec62f91bfe5388d61aafba6c'
	},
	/*
	配置在脚本/模块外面并没有使用RequireJS的函数依赖并且初始化函数。假设underscore并没有使用  RequireJS定义，但是你还是想通过RequireJS来使用它，那么你就需要在配置中把它定义为一个shim。
	deps——加载依赖关系数组
	*/
	shim:{
		'jquery-colorbox': {
			//exports: "$",
			deps: ['css!/static/js/colorbox/colorbox.css']
		},
		'jquery-treeview': {
			//exports: "$",
			deps: ['css!/static/js/jquery/jquery.treeview.css','jquery']
		},
		'jquery-treetable': {
			//exports: "$",
			deps: ['css!/static/js/jquery/jquery.treetable.css']
		},
		'jquery-event-drag': {
			//exports: "$",
			deps: ['jquery']
		},
		'jquery.jqprint': {
			exports: "$",
			deps: ['jquery','jquery.migrate']
		},
		'bootstrap': {
			exports: "$",
			deps: ['jquery']
		},
		'bootstrap-datetimepicker-zh-CN':{
			deps: ['css!/static/js/bootstrap/css/bootstrap-datetimepicker.min.css','bootstrap-datetimepicker']
		},
		'layer': {
			exports: 'layer',
			deps: ['css!/static/js/layer/skin/default/layer.css']
		},
		'layer.mobile': {
			exports: 'layer.mobile',
			deps: ['css!/static/js/layer/mobile/need/layer.css']
		},
		'filestyle': {
			exports: '$',
			deps: ['bootstrap']
		},
		'colorpicker': {
			exports: '$',
			deps: ['css!/static/js/colorpicker/spectrum.css']
		},
		'clockpicker': {
			exports: "clockpicker",
			deps: ['css!/static/js/clockpicker/clockpicker.min.css', 'bootstrap']
		},
		'daterangepicker': {
			exports: '$',
			deps: ['bootstrap', 'moment', 'css!/static/js/daterangepicker/daterangepicker.css']
		},
		'datetimepicker' : {
			exports : '$',
			deps: ['jquery','css!/static/js/datetimepicker/jquery.datetimepicker.css']
		},
		'webuploader': {
			exports : 'WebUploader',
			deps: ['css!/static/js/webuploader/css/webuploader.css', 'css!/static/js/webuploader/images/style.css']
		},
		'jquery.qrcode': {
			exports: "$",
			deps: ['jquery']
		},
		'map':{exports: 'BMap'},
		'gpi':{exports: 'gpi'}
	}
});
