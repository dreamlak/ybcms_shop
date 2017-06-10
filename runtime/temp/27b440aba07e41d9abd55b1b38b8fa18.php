<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:61:"F:\myweb\ybcms_shop/application/admin\view\plugins\index.html";i:1495300782;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1495297878;}*/ ?>
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

<script type="text/javascript">
$(document).ready(function(){
	var type='<?php echo $type; ?>';
	$('.submenu > li').each(function(){
		var href=$(this).find('a').attr('href');
		if(href.indexOf(type)>0){
			$(this).addClass('active');
		}else{
			$(this).removeClass('active');
		}
	});
});
</script>


</head>
<body>
<!--中间部分 -->	

<div class="main-content">
	<div class="main-content-bar">
		<div class="item-title">
			<h3>插件管理-<?php echo $typename[$type]; ?></h3>
			<h5>系统<?php echo $typename[$type]; ?>插件管理</h5>
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
			<li>系统插件自行安装卸载使用，插件添加在 /plugins 目录</li>
		</ol>
	</div>
	<!--列表-->
	<div class="panel panel-list">
		<div class="panel-heading">
			<span class="tit-name"><i class="fa fa-folder-open-o"></i> 插件列表</span> 
			<span class="tit-info">(共<?php echo $total; ?>条记录)</span>
		</div>
		<?php if($type=='shipping'): ?>
		<div class="panel-btn">
			<a href="<?php echo url('add_shipping',['type'=>$type]); ?>" class="btn btn-default"><i class="fa fa-plus"></i> 添加物流</a>
		</div>
		<?php endif; ?>
		<div class="panel-body table-responsive ng-scope">
			<table class="table table-hover">
				<thead class="navbar-inner">
					<tr>
						<th>插件图标</th>
						<th>插件名称</th>
						<th>插件描述</th>
						<th>插件代码</th>
						<th>插件版本</th>
						<th>插件作者</th>
						<th>使用场景</th>
						<th>操作</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($lists) || $lists instanceof \think\Collection || $lists instanceof \think\Paginator): $i = 0; $__LIST__ = $lists;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<tr>
						<td><img src="/plugins/<?php echo $v['type']; ?>/<?php echo $v['code']; ?>/<?php echo $v['icon']; ?>" alt="..." style="width:auto;height:30px;"></td>
						<td><?php echo $v['name']; ?></td>
						<td>
							<span id="desc_span_<?php echo $v['code']; ?>" ondblclick="show_input('<?php echo $v['code']; ?>')" style="display:block;width:100%;height:100%"><?php echo $v['desc']; ?></span>
							<input onblur="change_desc('<?php echo $v['code']; ?>')" value="<?php echo $v['desc']; ?>" id="desc_<?php echo $v['code']; ?>" style="display:none;" type="text">
						</td>
						<td><?php echo $v['code']; ?></td>
						<td><?php echo $v['version']; ?></td>
						<td><?php echo $v['author']; ?></td>
						<td>
							<?php if($v['scene']==0): ?>
								<i class="fa fa-desktop" title="PC"></i>
								<i class="fa fa-mobile" title="手机" style="font-size:18px;"></i>
							<?php elseif($v['scene']==1): ?>
								<i class="fa fa-mobile" title="手机" style="font-size:18px;"></i>
							<?php else: ?><i class="fa fa-desktop" title="PC"></i><?php endif; ?>
						</td>
						<td>
							<?php if($v['status']==0): ?>
							<a onclick="installPlugin('<?php echo $v['type']; ?>','<?php echo $v['code']; ?>',1)" class="btn btn-default btn-xs"><i class="fa fa-gavel"></i> 安装</a>
								<?php if($type=='shipping'): ?>
								<a href="javascript:" onclick="if(confirm('确定要删除吗?')) del_shipping('<?php echo $v['code']; ?>');" class="btn btn-default btn-xs"><i class="fa fa-trash-o"></i> 删除</a>
								<?php endif; else: if($type=='shipping'): ?>
								<a href="<?php echo url('shipping_list',['type'=>$v['type'],'code'=>$v['code']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 配置</a>
								<a href="<?php echo url('shipping_print',['type'=>$v['type'],'code'=>$v['code']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 模板编辑</a>
								<a href="javascript:" onclick="installPlugin('<?php echo $v['type']; ?>','<?php echo $v['code']; ?>',0)" class="btn btn-default btn-xs"><i class="fa fa-ban"></i> 卸载</a>
								<?php else: ?>
								<a href="<?php echo url('setting',['type'=>$v['type'],'code'=>$v['code']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 配置</a>
								<a href="javascript:" onclick="installPlugin('<?php echo $v['type']; ?>','<?php echo $v['code']; ?>',0)" class="btn btn-default btn-xs"><i class="fa fa-ban"></i> 卸载</a>
								<?php endif; endif; ?>
						</td>
					</tr>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</tbody>
			</table>
		</div>
		<div class="panel-footer">
			<?php if($total>$listRows): ?>
			<ul class="pagination">
				<li><a><?php echo $total; ?> 条记录</a></li>
				<li><a><?php echo $currentPage; ?>/<?php echo $lastPage; ?></a></li>
			</ul>
			<?php endif; ?>
			<?php echo $pages; ?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	require(['bootstrap','think','layer']);
})
// 删除物流
function del_shipping(code){
	var ll = layer.load('正在处理，请稍后...', 3);
    $.ajax({
        type : "POST",
        url:"<?php echo url('Admin/Plugins/del_shipping',['type'=>$type]); ?>",
        dataType: "json",
        data : {code:code},
        success: function(data){
            layer.msg(data.msg,{icon:6},function(){
                location.reload();
            });
            layer.close(ll);
        }
    });
}

//插件安装(卸载)
function installPlugin(type,code,type2){
    var url =Think.U('Admin/Plugins/install','type='+type+'&code='+code+'&install='+type2)
    var ll = layer.load('正在处理，请稍后...', 3);
    $.get(url,function(data){
        if(data.status == 1){
        	layer.msg(data.msg,{icon:6},function(){
            	location.reload();
            });
        }else{
        	layer.msg(data.msg,{icon:5});
        }
        layer.close(ll);
        return false;
    })
}

function change_desc(code){
    var desc = $('#desc_'+code).val();
    var type = '<?php echo $type; ?>';
    $.post("<?php echo url('Admin/Plugins/shipping_desc'); ?>",{code:code,desc:desc,type:type},function(data){
        $('#desc_span_'+code).show();
        $('#desc_'+code).hide();
        if(data.status == 1){
            $('#desc_span_'+code).text(desc);
        }else{
            layer.alert('修改失败', {icon: 2});  // alert('修改失败');
        }
    })
}
function show_input(code){
    $('#desc_span_'+code).hide();
    $('#desc_'+code).show();
}
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>