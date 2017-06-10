<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:59:"F:\myweb\ybcms_shop/application/admin\view\hotel\index.html";i:1495300448;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1495297878;}*/ ?>
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
</style>

</head>
<body>
<!--中间部分 -->	

<div class="main-content">
	<div class="main-content-bar">
		<div class="item-title">
			<h3>酒店管理</h3>
			<h5>酒店的信息管理</h5>
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
			<li>酒店信息管理，可以对酒店信息及房间管理。</li>
		</ol>
	</div>
	<!--列表-->
	<div class="panel panel-list">
		<div class="panel-heading">
			<span class="tit-name"><i class="fa fa-folder-open-o"></i> 酒店列表</span> 
			<span class="tit-info">(共<?php echo $total; ?>条记录)</span>
			<div class="head-right">
				<form action="" method="get" class="form-inline">
					<input type="text" name="name" value="<?php echo input('name'); ?>" placeholder="酒店名称" class="form-control"/>
                    <select name="status" id="status" class="form-control">
        				<option value="" selected="">全部状态</option>
                       	<option value="0" <?php if(input('status')=='0'): ?>selected<?php endif; ?>>已禁用</option>
                       	<option value="1" <?php if(input('status')=='1'): ?>selected<?php endif; ?>>已启用</option>
                    </select>
		          	<button type="submit" class="btn btn-default">搜索</button>
				</form>
			</div>
		</div>
		<div class="panel-btn">
			<a href="<?php echo url('addedithotel'); ?>" class="btn btn-default"><i class="fa fa-plus"></i> 添加酒店</a>
			<a href="javascript:" url="<?php echo url('delhotel'); ?>" class="btn btn-default" data="ajax"><i class="fa fa-trash-o"></i> 删除酒店</a>
		</div>
		<div class="panel-body table-responsive ng-scope">
			<table class="table table-hover">
				<thead class="navbar-inner">
					<tr>
						<th style="width:30px;">
							<div class="custom-checkbox">
								<input type="checkbox" id="chkAll" class="inbox-check">
								<label for="chkAll"></label>
							</div>
						</th>
						<th>酒店ID</th>
						<th>酒店名称</th>
						<th>星级</th>
						<th>标准价</th>
						<th>联系电话</th>
						<th>房间数</th>
						<th>是否推荐</th>
						<th>是否热门</th>
						<th>状态</th>
						<th>排序</th>
						<th>操作</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($lists) || $lists instanceof \think\Collection || $lists instanceof \think\Paginator): $i = 0; $__LIST__ = $lists;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<tr for="chk<?php echo $v['hotel_id']; ?>">
						<td>
							<div class="custom-checkbox">
								<input type="checkbox" name="ids[]" id="chk<?php echo $v['hotel_id']; ?>" class="inbox-check" value="<?php echo $v['hotel_id']; ?>">
								<label for="chk<?php echo $v['hotel_id']; ?>"></label>
							</div>
						</td>
						<td><?php echo $v['hotel_id']; ?></td>
						<td><?php echo $v['name']; ?></td>
						<td><?php echo $v['level']; ?></td>
						<td><?php echo $v['price']; ?></td>
						<td><?php echo $v['tel']; ?></td>
						<td><?php echo $v['room_count']; ?></td>
						<td>
							<?php if($v['is_recommend']==1): ?>
					      	<span class="yes" onClick="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','is_recommend',this)" ><i class="fa fa-check-circle"></i>是</span>
					      	<?php else: ?>
					      	<span class="no" onClick="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','is_recommend',this)" ><i class="fa fa-ban"></i>否</span>
					        <?php endif; ?>
						</td>
						<td>
							<?php if($v['is_hot']==1): ?>
					      	<span class="yes" onClick="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','is_hot',this)" ><i class="fa fa-check-circle"></i>是</span>
					      	<?php else: ?>
					      	<span class="no" onClick="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','is_hot',this)" ><i class="fa fa-ban"></i>否</span>
					        <?php endif; ?>
						</td>
						<td>
							<?php if($v['status']==1): ?>
					      	<span class="yes" onClick="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','status',this)" ><i class="fa fa-check-circle"></i>启用</span>
					      	<?php else: ?>
					      	<span class="no" onClick="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','status',this)" ><i class="fa fa-ban"></i>禁用</span>
					        <?php endif; ?>
						</td>
						<td><input type="text" onKeyUp="this.value=this.value.replace(/[^\d]/g,'')" onpaste="this.value=this.value.replace(/[^\d]/g,'')" onChange="changeTableVal('hotel','hotel_id','<?php echo $v['hotel_id']; ?>','sort',this)" size="4" value="<?php echo $v['sort']; ?>" class="form-control" style="display:inline-block" /></td>
						<td>
							<a href="<?php echo url('addedithotel',['hotel_id'=>$v['hotel_id']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 编辑</a>
						</td>
					</tr>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</tbody>
			</table>
		</div>
		<?php if($total>$listRows): ?>
		<div class="panel-footer">
			<ul class="pagination">
				<li><a><?php echo $total; ?> 条记录</a></li>
				<li><a><?php echo $currentPage; ?>/<?php echo $lastPage; ?></a></li>
			</ul>
			<?php echo $pages; ?>
		</div>
		<?php endif; ?>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	require(['bootstrap']);
})
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>