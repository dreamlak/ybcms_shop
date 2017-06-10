<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:67:"F:\myweb\ybcms_shop/application/admin\view\goods\_categorylist.html";i:1496458537;}*/ ?>
<div class="panel-heading">
	<span class="tit-name"><i class="fa fa-folder-open-o"></i> 商品分类列表</span> 
	<span class="tit-info">(共<?php echo count($cat_list); ?>条记录)</span>
</div>
<div class="panel-btn">
	<a href="<?php echo url('addEditCategory'); ?>" class="btn btn-default"><i class="fa fa-plus"></i> 添加分类</a>
</div>
<div class="panel-body table-responsive ng-scope">
	<table class="table table-hover" id="myTreeTable" style="width:100%">
		<thead class="navbar-inner">
			<tr>
				<th style="width:50px;">Id</th>
				<th>分类名称</th>
				<th>手机显示名称</th>
				<th>是否推荐</th>
				<th>是否显示</th>
				<th>分组</th>
				<th>排序</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody id="getGoodsCatLists">
			<?php if(is_array($cat_list) || $cat_list instanceof \think\Collection || $cat_list instanceof \think\Paginator): $k = 0; $__LIST__ = $cat_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?>
			<tr data-id="<?php echo $vo['id']; ?>" data-pid = "<?php echo $vo['parent_id']; ?>" id="goods_category_<?php echo $vo['id']; ?>" <?php if($vo['level']>1): ?> style="display:none;"<?php endif; ?>>
				<td><?php echo $vo['id']; ?></td>
				<td><input type="text" value="<?php echo $vo['name']; ?>" onChange="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','name',this)" class="form-control" style="display:inline-block;width:180px;"/></td>
				<td><input type="text" value="<?php echo $vo['mobile_name']; ?>" onChange="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','mobile_name',this)" class="form-control" style="display:inline-block;width:180px;"/></td>
				<td>
					<?php if($vo['is_hot']==1): ?>
			      	<span class="yes" onclick="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','is_hot',this)" ><i class="fa fa-check-circle"></i>是</span>
			      	<?php else: ?>
			      	<span class="no" onclick="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','is_hot',this)" ><i class="fa fa-ban"></i>否</span>
			        <?php endif; ?>
				</td>
				<td>
					<?php if($vo['is_show']==1): ?>
					<span class="yes" onclick="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','is_show',this)" ><i class="fa fa-check-circle"></i>是</span>
			  		<?php else: ?>
			  		<span class="no" onclick="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','is_show',this)" ><i class="fa fa-ban"></i>否</span>
					<?php endif; ?>
				</td>
				<td><input type="text" onkeyup="this.value=this.value.replace(/[^\d]/g,'')" onpaste="this.value=this.value.replace(/[^\d]/g,'')" onChange="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','cat_group',this)" size="4" value="<?php echo $vo['cat_group']; ?>"  class="form-control" style="display:inline-block"/></td>
				<td><input type="text" onkeyup="this.value=this.value.replace(/[^\d]/g,'')" onpaste="this.value=this.value.replace(/[^\d]/g,'')" onChange="changeTableVal('goods_category','id','<?php echo $vo['id']; ?>','sort_order',this)" size="4" value="<?php echo $vo['sort_order']; ?>" class="form-control" style="display:inline-block"/></td>
				<td>
					<a href="<?php echo url('addEditCategory',['id'=>$vo['id']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 编辑</a>
					<a onclick="delcategory(<?php echo $vo['id']; ?>)" class="btn btn-default btn-xs"><i class="fa fa-trash-o"></i> 删除</a>
				</td>
			</tr>
			<?php endforeach; endif; else: echo "" ;endif; ?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
function delcategory(id){
	require(['layer','think'],function(){
		var ll = layer.load('系统正在为您处理，请稍后...', 3);
		var url=Think.U('Admin/goods/delGoodsCategory','id='+id);
		$.ajax({
			url:url,			
			success: function(data){
				if(data.status==1){
				   	$('#goods_category_'+id).remove();
				}
				layer.msg(data.msg, {icon: 1});
				layer.close(ll);
				return false;
			}
		});
	});
}
</script>