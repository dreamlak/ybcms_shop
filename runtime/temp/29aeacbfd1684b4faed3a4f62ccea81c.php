<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:60:"F:\myweb\ybcms_shop/application/admin\view\member\index.html";i:1495300544;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1495297878;}*/ ?>
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



</head>
<body>
<!--中间部分 -->	

<div class="main-content">
	<div class="main-content-bar">
		<div class="item-title">
			<h3>会员管理</h3>
			<h5>系统会员管理</h5>
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
			<li>会员列表管理, 会员审核编辑，及密码等修改。</li>
		</ol>
	</div>
	<!--列表-->
	<div class="panel panel-list">
		<div class="panel-heading">
			<span class="tit-name"><i class="fa fa-folder-open-o"></i> 会员管理</span> 
			<span class="tit-info">(共<?php echo $total; ?>条记录)</span>
			<div class="head-right">
				<form action="" method="get" class="form-inline">
		          	<input type="text" name="keys" value="" placeholder="账号/手机/邮箱" class="form-control"/>
		          	<select name="status" id="status" class="form-control">
        				<option value="" selected="">全部状态</option>
                       	<option value="1" <?php if(input('status')=='1'): ?>selected<?php endif; ?>>正常状态</option>
                       	<option value="0" <?php if(input('status')=='0'): ?>selected<?php endif; ?>>禁用状态</option>
                    </select>
		          	<button type="submit" class="btn btn-default">搜索</button>
				</form>
			</div>
		</div>
		<div class="panel-btn">
			<a href="<?php echo url('add'); ?>" class="btn btn-default"><i class="fa fa-plus"></i> 添加会员</a>
			<a href="javascript:" url="<?php echo url('del'); ?>" class="btn btn-default" data="ajax"><i class="fa fa-trash-o"></i> 删除会员</a>
			<div class="btn-group">
			  	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">状态设置 <span class="caret"></span></button>
			  	<ul class="dropdown-menu" role="menu">
				    <li><a href="javascript:" url="<?php echo url('setStatus',['status'=>1]); ?>" data="ajax">启用会员</a></li>
					<li><a href="javascript:" url="<?php echo url('setStatus',['status'=>0]); ?>" data="ajax">禁用会员</a></li>
			  	</ul>
			</div>
			<a url="<?php echo url('admin/message/sendmessage'); ?>" id="sendmessage" class="btn btn-default"><i class="fa fa-plus"></i> 发送消息</a>
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
						<th>ID</th>
						<th>登录账号</th>
						<th>会员昵称</th>
						<th>会员等级</th>
						<th>邮箱</th>
						<th>手机</th>
						<th>余额</th>
						<th>积分</th>
						<th>注册时间</th>
						<th>状态</th>
						<th>操作</th>
					</tr>
				</thead>
				<tbody>
					<?php if(is_array($lists) || $lists instanceof \think\Collection || $lists instanceof \think\Paginator): $i = 0; $__LIST__ = $lists;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<tr for="chk<?php echo $v['userid']; ?>">
						<td>
							<div class="custom-checkbox">
								<input type="checkbox" name="ids[]" id="chk<?php echo $v['userid']; ?>" class="inbox-check" value="<?php echo $v['userid']; ?>">
								<label for="chk<?php echo $v['userid']; ?>"></label>
							</div>
						</td>
						<td><?php echo $v['userid']; ?></td>
						<td><?php echo $v['username']; ?></td>
						<td><?php echo deal_emoji($v['nickname']); ?></td>
						<td><?php echo $level[$v['levelid']]; ?></td>
						<td><?php echo $v['email']; ?></td>
						<td><?php echo $v['tel']; ?></td>
						<td><?php echo $v['mymoney']; ?></td>
						<td><?php echo $v['mypoints']; ?></td>
						<td><?php echo date('Y-m-d H:i:s',$v['regtime']); ?></td>
						<td><?php if($v['status']==1): ?>正常<?php else: ?>禁用<?php endif; ?></td>
						<td>
							<a href="<?php echo url('edit',['userid'=>$v['userid']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i> 编辑</a>
							<a href="javascript:" id="ajaxResetPwd" data="<?php echo $v['userid']; ?>" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i> 重置密码</a>
							<a href="<?php echo url('account',['userid'=>$v['userid']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-file-text-o"></i> 资金</a>
							<a href="<?php echo url('address',['userid'=>$v['userid']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-file-text-o"></i> 地址</a>
							<a href="<?php echo url('member_log',['userid'=>$v['userid']]); ?>" class="btn btn-default btn-xs"><i class="fa fa-file-text-o"></i> 日志</a>
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
	require(['bootstrap']);
	
	$('a#ajaxResetPwd').click(function(){
		var userid = $(this).attr('data');
		require(['think','layer'],function(){
			var ll = layer.load('正在处理，请稍后...', 3);
			$.post(Think.U('Admin/Member/resetpwd'),{userid:userid},function(data){
		        if(data.status==1) {
		        	layer.msg(data.msg,{icon:6,time:1000}, function(){
						return false;
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
	
	$('a#sendmessage').click(function(){
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
			submitForm(urls,fields);
			layer.close(ll);
	        return false;
     	});
	});
	
	//js模拟form POST提交
	function submitForm(action, params) {  
	    var form = $("<form></form>");  
	    form.attr('action', action);  
	    form.attr('method', 'post');  
	    form.attr('target', '_self');  
	    for(var i=0 ; i < params.length;i ++){  
	        var input1 = $("<input type='hidden' name='"+params[i].name+"' />");  
	        input1.attr('value', params[i].value);  
	        form.append(input1);  
	    }  
	    form.appendTo("body");  
	    form.css('display', 'none');  
	    form.submit();
	}
})
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>