$(function(){
	//点击滚动到顶部
	$(".scroll-to-top").click(function()	{
		$("html, body").animate({ scrollTop: 0 }, 600);
		return false;
	});

	// 切换滚动到顶部按钮
	$(window).scroll(function(){	
	 	var position = $(window).scrollTop();
	 	if(position >= 10)	{
			$('.scroll-to-top').addClass('active')
	 	}else{
			$('.scroll-to-top').removeClass('active')
	 	}
	});

	//隐藏panel-body
	$('#panel-body-hide').click(function(){
		var panel_body = $(this).parent().next('.panel-body');
		if(panel_body.css("display")=="none"){ 
			panel_body.css("display","block");
			$(this).html('<i class="fa fa-chevron-up"></i>');
		}else{
			panel_body.css("display","none");
			$(this).html('<i class="fa fa-chevron-down"></i>');
		}
	});
	//table选中行时
 	$('.table tbody tr').click(function(){
 		$(this).toggleClass('active');
 		var hasClass = $(this).hasClass('active');
 		if(hasClass){
 			$(this).find('input').prop('checked',true);
 		}else{
 			$(this).find('input').prop('checked',false);
 		}
 		var type=$(this).find('input').attr('type');
 		if(type=='radio'){
 			$(this).addClass("active").siblings().removeClass("active");
 		}
	});
	//选中checkbox时
	$('.custom-checkbox>label').click(function(){
 		$(this).parent().parent().parent().toggleClass('active');
 		var hasClass = $(this).parent().parent().parent().hasClass('active');
 		if(hasClass){
 			$(this).prev().prop('checked',false);
 		}else{
 			$(this).prev().prop('checked',true);
 		}
	});
	//全选
	$('#chkAll').click(function()	{
		if($(this).prop('checked'))	{
			$('.inbox-check').prop('checked',true);
			$('.inbox-check').parent().parent().parent().addClass('active');
		}else{
			$('.inbox-check').prop('checked',false);
			$('.inbox-check').parent().parent().parent().removeClass('active');
		}
	});
	
	//选中的操作处理
	$('a[data="ajax"]').click(function(){
		var urls=$(this).attr('url');
		require(['layer'], function(){
			var fields = $('input[name="ids[]"]').serializeArray();
			if(fields.length==0){
				layer.msg('您还没有钩选任何一项呢', {icon:5});
				return false;
			}
			
			if(typeof(urls)=='nudefined'||urls==undefined){
				layer.msg('链接地址错误', {icon:5});
				return false;
			}

			var ll = layer.load('系统正在为您处理，请稍后...', 3);
			$.post(urls,fields,function(data){
	            if(data.status==1) {
		        	layer.msg(data.msg,{icon:6,time:1000}, function(){
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
	
	//列表排序
	$('a[data="ajax_sort"]').click(function(){
		var urls=$(this).attr('url');
		require(['layer'], function(){
			var fields = $('input[id="sort"]').serializeArray();
			if(fields.length==0){
				layer.msg('没有数据可更新', {icon:5});
				return false;
			}
			var ll = layer.load('系统正在为您处理，请稍后...', 3);
			$.post(urls,fields,function(data){
	            if(data.status==1) {
		        	layer.msg(data.msg,{icon:6,time:1000}, function(){
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

	//自定义radio样式
    $(".cb-enable").click(function(){
        var parent = $(this).parents('.onoff');
        $('.cb-disable',parent).removeClass('selected');
        $(this).addClass('selected');
        var id=$(this).attr('for');
        $('input[type=radio]',parent).removeAttr('checked');
        $('#'+id,parent).attr('checked', true);
    });
    $(".cb-disable").click(function(){
        var parent = $(this).parents('.onoff');
        $('.cb-enable',parent).removeClass('selected');
        $(this).addClass('selected');
        var id=$(this).attr('for');
        $('input[type=radio]',parent).removeAttr('checked');
        $('#'+id,parent).attr('checked', true);
    });
});

function arrayToJson(o) {
    var r = [];
    if (typeof o == "string") return "\"" + o.replace(/([\'\"\\])/g, "\\$1").replace(/(\n)/g, "\\n").replace(/(\r)/g, "\\r").replace(/(\t)/g, "\\t") + "\"";
    if (typeof o == "object") {
        if (!o.sort) {
            for (var i in o)
                r.push(i + ":" + arrayToJson(o[i]));
            if (!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)) {
                r.push("toString:" + o.toString.toString());
            }
            //r = "{" + r.join() + "}";
            r = r.join();
        } else {
            for (var i = 0; i < o.length; i++) {
                r.push(arrayToJson(o[i]));
            }
            r = "[" + r.join() + "]";
        }
        return r;
    }
    return o.toString();
}

/**              
 * 日期 转换为 Unix时间戳
 * @param <string> 2014-01-01 20:20:20  日期格式              
 * @return <int>        unix时间戳(秒)              
 */
function DateToUnix(string){
    var f = string.split(' ', 2);
    var d = (f[0] ? f[0] : '').split('-', 3);
    var t = (f[1] ? f[1] : '').split(':', 3);
    return (new Date(
            parseInt(d[0], 10) || null,
            (parseInt(d[1], 10) || 1) - 1,
            parseInt(d[2], 10) || null,
            parseInt(t[0], 10) || null,
            parseInt(t[1], 10) || null,
            parseInt(t[2], 10) || null
            )).getTime() / 1000;
}
/**              
 * 时间戳转换日期              
 * @param <int> unixTime    待时间戳(秒)              
 * @param <bool> isFull    返回完整时间(Y-m-d 或者 Y-m-d H:i:s)              
 * @param <int>  timeZone   时区              
 */
function add0(m){return m<10?'0'+m:m;}
function UnixToDate(unixTime, isFull, timeZone) {
	if(isFull==''||isFull=='undefined'||isFull==null){isFull=true;}
    if (typeof (timeZone) == 'number'){
        unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
    }
    var time = new Date(unixTime * 1000);
    var ymdhis = "";
    ymdhis += time.getFullYear() + "-";
    ymdhis += add0(time.getMonth()+1) + "-";
    ymdhis += add0(time.getDate());
    if (isFull === true){
        ymdhis += " " + add0(time.getHours()) + ":";
        ymdhis += add0(time.getMinutes());
        //ymdhis +=":"+add0(time.getSeconds());
    }
    return ymdhis;
}
//将传入数据转换为字符串,并清除字符串中非数字与.的字符  
//按数字格式补全字符串  
function getFloatStr(num){  
    num += '';  
    num = num.replace(/[^0-9|\.]/g, ''); //清除字符串中的非数字非.字符  
      
    if(/^0+/) //清除字符串开头的0  
        num = num.replace(/^0+/, '');  
    if(!/\./.test(num)) //为整数字符串在末尾添加.00  
        num += '.00';  
    if(/^\./.test(num)) //字符以.开头时,在开头添加0  
        num = '0' + num;  
    num += '00';        //在字符串末尾补零  
    num = num.match(/\d+\.\d{2}/)[0];
    return num;
}

function myTableTree(column){
	if(column=='')column=0;
	var option={
		branchAttr:"ttBranch",//可选，强制打开节点的展开图标，允许将一个没有儿子节点的节点定义为分支节点，在HTML里面以data-tt-branch 属性形式展现
        clickableNodeNames:false,//默认false，点击展开图标打开子节点。设置为true时，点击节点名称也打开子节点.
        column:column,//表中要展示为树的列数。
        columnElType:"td",//展示为树的单元格的类别(th,td or both).
        expandable:true,//树是否可以展开，可以展开的树包含展开/折叠按钮。
        expanderTemplate:'<a>&nbsp;</a>',//展开按钮的html 片段。
        indent:20,//每个分支缩进的像素数。
        indenterTemplate:'<span class="indenter"></span>',//折叠按钮的HTML片段
        initialState:"collapsed",//初始状态，可选值: "expanded"展开 or "collapsed"折叠.
        nodeIdAttr:'id',//用来识别节点的数据属性的名称。在HTML里面以 data-id  体现。
        parentIdAttr:'pid',//用了设置父节点的数据属性的名称. 在HTML里面以data-pid 体现。
        stringCollapse:"折叠",//折叠按钮的title,国际化使用。
        stringExpand:"展开",//展开按钮的title,国际化使用。
        onInitialized:function(id){
    		//树初始化完毕后的回调函数.
    		//window.console && console.log('树初始化完毕后:' + id);
        },
        onNodeInitialized:function(id){
			//节点初始化完毕后的回调函数
			//window.console && console.log('节点初始化完毕:' + id);
		},
        onNodeCollapse:function(id){
        	//节点折叠时的回调函数.
            //window.console && console.log('节点折叠时:' + id);
		},
		onNodeExpand:function(id){
			//节点展开时的回调函数.
			//window.console && console.log('节点展开时:' + id);
		}
	};
	/*
	对树操作的一些方法，附加方法必须通过treetable()方法调用。Eg：折叠id=42的节点， $("#tree").treetable("collapseNode", "42").
	collapseAll()：折叠所有节点
	collapseNode(id)：折叠某个ID的节点.
	expandAll()：立即扩展所有节点.
	expandNode(id)：展开某个ID的节点.
	loadBranch(node, rows)：向树中插入新行(<tr>s), 传入参数 node 为父节点，rows为待插入的行. 如果父节点node为null ，新行被作为父节点插入
	move(nodeId, destinationId)：移动nodeId到destinationId
	node(id)：从树中选择一个节点。返回一个节点对象treetable。
	removeNode(id)：从树中移除某个节点及其所有子节点
	reveal(id)：展示树中的某个节点
	sortBranch(node)
	sortBranch(node, columnOrFunction)：根据字母顺序对node 节点的所有子节点排序。
	unloadBranch(node)：从树上删除节点/行（HTML<s>s），使用父节点。注意父（节点）不会被移除。
	*/
	require(['jquery-treetable'],function(){
    	$('#myTreeTable').treetable(option);
    });
}

// 修改指定表的指定字段值 包括有按钮点击切换是否 或者 排序 或者输入框文字
function changeTableVal(table,id_name,id_value,field,obj){	
	var src = "";
	if($(obj).hasClass('no')){//图片点击是否操作
		$(obj).removeClass('no').addClass('yes');
		$(obj).html("<i class='fa fa-check-circle'></i>是");
		var value = 1;
 	}else if($(obj).hasClass('yes')){//图片点击是否操作 
		$(obj).removeClass('yes').addClass('no');
		$(obj).html("<i class='fa fa-ban'></i>否");
		var value = 0;
 	}else{//其他输入框操作
	 	var value = $(obj).val();			 
 	}
 	require(['layer','think'], function(){
 		var url=Think.U('Admin/Index/changeTableVal');
		$.post(url,{table:table,id_name:id_name,id_value:id_value,field:field,value:value},function(data){
			if(data.status==1){
			   	if(!$(obj).hasClass('no') && !$(obj).hasClass('yes'));
			}
			layer.msg(data.msg, {icon: 1});
			return false;
		});
	});
}