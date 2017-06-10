<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:66:"F:\myweb\ybcms_shop/application/admin\view\goods\categorylist.html";i:1497089755;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1497089756;}*/ ?>
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



<style type="text/css">
table tbody td .yes{color:#666;cursor:pointer;}
table tbody td .no{color:#ddd;cursor:pointer;}
.trSelected td{color:#333;background:#FFFFDF;border-color:transparent;border-bottom:1px solid #FFEFBF;}
</style>

</head>
<body>
<!--中间部分 -->	

<div class="main-content">
	<div class="main-content-bar">
		<div class="item-title">
			<h3>商品分类管理</h3>
			<h5>商品的分类添加与管理</h5>
		</div>
	</div>
	<!--操作提示-->
	<div id="explanation" class="explanation">
		<div id="checkZoom" class="title">
			<i class="fa fa-lightbulb-o"></i>
			<h4 title="操作要点提示">操作提示</h4>
			<span title="收起提示" id="explanationZoom"></span>
		</div>
		<ol id="listZoom">
			<li>温馨提示：顶级分类（一级大类）设为推荐时才会在首页楼层中显示</li>
      		<li>最多只能分类到三级</li>
		</ol>
	</div>
	
	<!--列表-->
	<div class="panel panel-list" id="ajax_return">
		
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	require(['bootstrap']);
	ajax_get_table()
});
function loadSelect(){
	$(document).ready(function(){	
	    // 表格行点击选中切换
	    $('.panel-body > table>tbody >tr').click(function(){
		    $(this).toggleClass('trSelected');
		});				
 	});
}
//ajax 抓取页面 form 为表单id  page 为当前第几页
function ajax_get_table() {
	require(['think'],function(){
	    $.ajax({
	        type: "POST",
	        url: Think.U('Admin/goods/categoryList'),
	        data:'',
	        success: function (data) {
	            $("#ajax_return").html('');
	            $("#ajax_return").append(data);
	            myTableTree(1);
	            loadSelect();
	        }
	    });
    });
}
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>