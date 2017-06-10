<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:61:"F:\myweb\ybcms_shop/application/admin\view\system\config.html";i:1495300898;s:59:"F:\myweb\ybcms_shop/application/admin\view\public\base.html";i:1495297878;}*/ ?>
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
.footbtn{position:fixed;bottom:0;left:280px;right:0px;padding:10px 0;border-top:1px #ddd solid;text-align:center;background:#fff;z-index:88;}
.footbtn #submitbtn{width:160px;}
.mark_sels{position:relative;width:302px;border-top:1px #ddd solid;border-left:1px #ddd solid;display:block;}
.mark_sels li{float:left;border-right:1px #ddd solid;border-bottom:1px #ddd solid;padding:5px;width:100px;text-align:center;}
.mark_sels li label{font-weight:normal;cursor:pointer}
</style>

</head>
<body>
<!--中间部分 -->	

<div class="main-content">
	<div class="main-content-bar">
		<div class="item-title">
			<h3>站点设置</h3>
			<h5>网站系统全局参数配置</h5>
		</div>
		<ul class="tab-base">
			<li class="active"><a href="#style2Tab1" data-toggle="tab"><span class="text-wrapper">站点设置</span></a></li>
			<li class=""><a href="#style2Tab2" data-toggle="tab"><span class="text-wrapper">商城设置</span></a></li>
		  	<li class=""><a href="#style2Tab3" data-toggle="tab"><span class="text-wrapper">版权设置</span></a></li>
		  	<li class=""><a href="#style2Tab4" data-toggle="tab"><span class="text-wrapper">上传设置</span></a></li>
		  	<li class=""><a href="#style2Tab5" data-toggle="tab"><span class="text-wrapper">邮件设置</span></a></li>
		  	<li class=""><a href="#style2Tab6" data-toggle="tab"><span class="text-wrapper">短信设置</span></a></li>
		  	<li class=""><a href="#style2Tab7" data-toggle="tab"><span class="text-wrapper">分销设置</span></a></li>
		</ul>
	</div>
	<!--操作提示-->
	<div id="explanation" class="explanation">
		<div id="checkZoom" class="title">
			<i class="fa fa-lightbulb-o"></i>
			<h4>操作提示</h4>
			<span title="收起提示" id="explanationZoom" style="display: block;"></span>
		</div>
		<ol>
			<li>站点设置,跟据自己站点性质，配置属于自己的内容，包括底部版权信息。</li>
		</ol>
	</div>

	<form action="" method="post" class="form-horizontal">
		<div class="tab-content">
			<div class="tab-pane fade active in" id="style2Tab1">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">网站开关<em>site_open</em></label>
				    <div class="col-xs-8">
			            <?php echo tpl_onoff('site_open','config[site_open]',['1','0','开启','关闭'],$config['site_open'],$msg=''); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">关站提示信息<em>site_close_tip</em></label>
				    <div class="col-xs-8">
                 		<textarea name="config[site_close_tip]" id="site_close_tip" placeholder="关站提示信息" class="form-control"><?php echo $config['site_close_tip']; ?></textarea>
	               	</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点名称<em>site_name</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_name]" id="site_name" placeholder="该网站前台显示的名称" class="form-control" value="<?php echo $config['site_name']; ?>">
                 		<span class="help-block"></span>
	               	</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点域名<em>site_domain</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_domain]" id="site_domain" placeholder="填写站点域名（如:http://www.ybcms.com）" class="form-control" value="<?php echo $config['site_domain']; ?>">
                 		<span class="help-block"></span>
	               	</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label" style="padding-top:0px;">上传LOGO<em>site_logo</em></label>
					<div class="col-xs-8">
						<?php echo tpl_upimg('site_logo','config[site_logo]',$config['site_logo'],0,is_login(),0,'Logo URL,可直接填写文件远程地址','','上传LOGO'); ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label" style="padding-top:0px;">官方微博二维码<em>microblog_qrcode</em></label>
					<div class="col-xs-8">
						<?php echo tpl_upimg('microblog_qrcode','config[microblog_qrcode]',$config['microblog_qrcode'],0,is_login(),0,'二维码URL,可直接填写文件远程地址','','上传二维码'); ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label" style="padding-top:0px;">手机WAP二维码<em>wap_qrcode</em></label>
					<div class="col-xs-8" id="uploader_wapqr">
						<?php echo tpl_upimg('wap_qrcode','config[wap_qrcode]',$config['wap_qrcode'],0,is_login(),0,'二维码URL,可直接填写文件远程地址','','上传二维码'); ?>
					</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点标题<em>site_title</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_title]" id="site_title" placeholder="一般在浏览器头部显示" class="form-control" value="<?php echo $config['site_title']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点描述<em>site_desc</em></label>
				    <div class="col-xs-8">
                 		<textarea name="config[site_desc]" id="site_desc" placeholder="描述内内便于网站收录使用" class="form-control"><?php echo $config['site_desc']; ?></textarea>
	               	</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点关键字<em>site_key</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_key]" id="site_key" placeholder="关键字内更于网站收录使用" class="form-control" value="<?php echo $config['site_key']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">JS路径<em>js_path</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[js_path]" id="js_path" placeholder="JS路径" class="form-control" value="<?php echo $config['js_path']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">CSS路径<em>css_path</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[css_path]" id="css_path" placeholder="CSS路径" class="form-control" value="<?php echo $config['css_path']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">CSS路径<em>img_path</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[img_path]" id="img_path" placeholder="图片路径" class="form-control" value="<?php echo $config['img_path']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">网站后台每页条数<em>admin_list_num</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" name="config[admin_list_num]" id="admin_list_num" placeholder="网站后台每页条数" class="form-control" value="<?php echo $config['admin_list_num']; ?>">
    			    		<span class="input-group-addon">条</span>
    			    	</div>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">网站前台每页条数<em>index_list_num</em></label>
				    <div class="col-xs-8">
				    	<div class="input-group ">
                 			<input type="number" name="config[index_list_num]" id="index_list_num" placeholder="网站前台每页条数" class="form-control" value="<?php echo $config['index_list_num']; ?>">
    			    		<span class="input-group-addon">条</span>
    			    	</div>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">网站统计代码<em>site_count_code</em></label>
				    <div class="col-xs-8">
                 		<textarea name="config[site_count_code]" id="site_count_code" placeholder="网站统计代码" class="form-control"><?php echo $config['site_count_code']; ?></textarea>
	               	</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label" style="padding-top:0px;">宣传视频<em>site_vod</em></label>
					<div class="col-xs-8" id="uploader_wapqr">
						<?php echo tpl_upfile('site_vod','config[site_vod]',$config['site_vod'],is_login(),0,'mp4','视频URL,可直接填写文件远程地址','','上传视频'); ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label" style="padding-top:0px;">宣传音乐<em>site_mp3</em></label>
					<div class="col-xs-8" id="uploader_wapqr">
						<?php echo tpl_upfile('site_mp3','config[site_mp3]',$config['site_mp3'],is_login(),0,'mp3','音乐URL,可直接填写文件远程地址','','上传音乐'); ?>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="style2Tab2">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">所属城市<em>province</em></label>
				    <div class="col-xs-8 form-inline">
                 		<?php echo tpl_area(3,$config['province'],$config['city'],$config['district'],$config['twon']); ?>
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">商城热门搜索词<em>hot_keywords</em></label>
				    <div class="col-xs-8">
                 		<input type="text" value="<?php echo $config['hot_keywords']; ?>" name="config[hot_keywords]" id="hot_keywords" placeholder="商城热门搜索词" class="form-control">
    			    	<span class="help-block">多个词用英文豆号（,）隔开</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">会员注册赠送积分<em>reg_integral</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['reg_integral']; ?>" name="config[reg_integral]" id="reg_integral" placeholder="会员注册赠送积分" class="form-control">
    			    		<span class="input-group-addon">分</span>
    			    	</div>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">默认库存<em>default_storage</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['default_storage']; ?>" name="config[default_storage]" id="default_storage" placeholder="默认库存" class="form-control">
    			    		<span class="input-group-addon">件</span>
    			    	</div>
    			    	<span class="help-block">添加商品的默认库存量</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">库存预警数<em>warning_storage</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['warning_storage']; ?>" name="config[warning_storage]" id="warning_storage" placeholder="库存预警数" class="form-control">
    			    		<span class="input-group-addon">件</span>
    			    	</div>
    			    	<span class="help-block">库存预警,当商品库存少于库存预警数，将在商品列表页库存显示红色</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">满多少才能提现<em>distribut_need</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['distribut_need']; ?>" name="config[distribut_need]" id="distribut_need" placeholder="满多少才能提现" class="form-control">
    			    		<span class="input-group-addon">元</span>
    			    	</div>
    			    	<span class="help-block">需超过多少才能提现金额,分销商有用</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">最少提现额度<em>distribut_min</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['distribut_min']; ?>" name="config[distribut_min]" id="distribut_min" placeholder="最少提现额度" class="form-control">
    			    		<span class="input-group-addon">元</span>
    			    	</div>
    			    	<span class="help-block">最少提现多少，才能体现,分销商有用</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">发票税率%<em>invoice_tax</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['invoice_tax']; ?>" name="config[invoice_tax]" id="invoice_tax" placeholder="发票税率%" class="form-control">
    			    		<span class="input-group-addon">%</span>
    			    	</div>
    			    	<span class="help-block">当买家需要发票的时候就要增加[商品金额]*[税率]的费用</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">全场满多少免运费<em>freight_free</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['freight_free']; ?>" name="config[freight_free]" id="freight_free" placeholder="全场满多少免运费" class="form-control">
    			    		<span class="input-group-addon">元</span>
    			    	</div>
    			    	<span class="help-block">0表示不免运费，1全场免运费，其他表示到多少才免</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">积分换算比例<em>point_rate</em></label>
				    <div class="col-xs-8">
                 		<label class="radio-inline"><input type="radio" name="config[point_rate]" value="1" <?php if($config['point_rate']==1): ?>checked<?php endif; ?>>1元=1积分</label>
                 		<label class="radio-inline"><input type="radio" name="config[point_rate]" value="10" <?php if($config['point_rate']==10): ?>checked<?php endif; ?>>1元=10积分</label>
                 		<label class="radio-inline"><input type="radio" name="config[point_rate]" value="100" <?php if($config['point_rate']==100): ?>checked<?php endif; ?>>1元=100积分</label>
                 		<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">积分最低使用限制<em>point_min_limit</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['point_min_limit']; ?>" name="config[point_min_limit]" id="point_min_limit" placeholder="积分最低使用限制" class="form-control">
    			    		<span class="input-group-addon">分</span>
    			    	</div>
    			    	<span class="help-block">0表示不限制, 大于0时, 用户积分小于该值将不能使用积分</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">积分使用比例<em>point_use_percent</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['point_use_percent']; ?>" name="config[point_use_percent]" id="point_use_percent" placeholder="积分使用比例" class="form-control">
    			    		<span class="input-group-addon">%</span>
    			    	</div>
    			    	<span class="help-block">100时不限制, 为0时不能使用积分, 1时积分抵扣金额不能超过该笔订单应付金额的1%</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">发货后多少天自动收货<em>auto_confirm_date</em></label>
				    <div class="col-xs-8">
				    	<select name="config[auto_confirm_date]" class="form-control">
				    		<?php $__FOR_START_16383__=1;$__FOR_END_16383__=31;for($i=$__FOR_START_16383__;$i < $__FOR_END_16383__;$i+=1){ ?>
				    		<option value="<?php echo $i; ?>" <?php if($config['auto_confirm_date']==$i): ?>selected<?php endif; ?>><?php echo $i; ?>天</option>
				    		<?php } ?>
				    	</select>
                 		<span class="help-block">发货后多少天自动确认收货</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">减库存的机制<em>stock_reduce</em></label>
				    <div class="col-xs-8">
                 		<label class="radio-inline"><input type="radio" name="config[stock_reduce]" value="1" <?php if($config['stock_reduce']==1): ?>checked<?php endif; ?>>订单支付时</label>
                 		<label class="radio-inline"><input type="radio" name="config[stock_reduce]" value="2" <?php if($config['stock_reduce']==2): ?>checked<?php endif; ?>>发货时</label>
                 		<span class="help-block"></span>
				    </div>
				</div>
			</div>
			<div class="tab-pane fade" id="style2Tab3">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">主办单位<em>site_ulit</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_ulit]" id="site_ulit" placeholder="网站使用单位" class="form-control" value="<?php echo $config['site_ulit']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">版权信息<em>copyright</em></label>
				    <div class="col-xs-8">
                 		<textarea name="config[copyright]" id="copyright" placeholder="版权信息" class="form-control"><?php echo $config['copyright']; ?></textarea>
	               	</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点备案号<em>site_icp</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_icp]" id="site_icp" placeholder="站点备案号（如：黔ICP备123456号）" class="form-control" value="<?php echo $config['site_icp']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">站点联系人<em>site_contact</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_contact]" id="site_contact" placeholder="站点联系人" class="form-control" value="<?php echo $config['site_contact']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">联系电话<em>site_tel</em></label>
				    <div class="col-xs-8">
                 		<input type="tel" name="config[site_tel]" id="site_tel" placeholder="多个“,”隔开" class="form-control" value="<?php echo $config['site_tel']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">传真<em>site_fax</em></label>
				    <div class="col-xs-8">
                 		<input type="tel" name="config[site_fax]" id="site_fax" placeholder="传真" class="form-control" value="<?php echo $config['site_fax']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">400电话<em>site_400</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_400]" id="site_400" placeholder="传真" class="form-control" value="<?php echo $config['site_400']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">联系手机<em>site_mobile</em></label>
				    <div class="col-xs-8">
                 		<input type="number" name="config[site_mobile]" id="site_mobile" placeholder="多个“,”隔开" class="form-control" value="<?php echo $config['site_mobile']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">联系地址<em>site_address</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_address]" id="site_address" placeholder="联系地址" class="form-control" value="<?php echo $config['site_address']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">邮编<em>site_zip</em></label>
				    <div class="col-xs-8">
                 		<input type="number" name="config[site_zip]" id="site_zip" placeholder="邮编" class="form-control" value="<?php echo $config['site_zip']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">客服QQ<em>site_qq</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[site_qq]" id="site_qq" placeholder="客服QQ" class="form-control" value="<?php echo $config['site_qq']; ?>">
    			    	<span class="help-block">多个请用英文豆号（,）隔开</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">电子邮件<em>site_email</em></label>
				    <div class="col-xs-8">
                 		<input type="email" name="config[site_email]" id="site_email" placeholder="电子邮件" class="form-control" value="<?php echo $config['site_email']; ?>">
    			    </div>
				</div>
			</div>
			<div class="tab-pane fade" id="style2Tab4">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">上传路径<em>upload_path</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[upload_path]" id="upload_path" placeholder="上传路径" class="form-control" value="<?php echo $config['upload_path']; ?>">
    			    	<span class="help-block">默认上传路径为 /uploads</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">上传大小<em>upload_size</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" name="config[upload_size]" id="upload_size" placeholder="上传大小" class="form-control" value="<?php echo $config['upload_size']; ?>">
    			    		<span class="input-group-addon">B</span>
    			    	</div>
    			    	<span class="help-block">默认上传大小为2M（2048000B），1M=1024000B</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">上传类型格式<em>upload_type</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[upload_type]" id="upload_type" placeholder="上传类型格式" class="form-control" value="<?php echo $config['upload_type']; ?>">
    			    	<span class="help-block">文件格式只允许图片、文档、视频，压缩包文件（多个请用英豆“,”号隔开）</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">是否水印<em>is_mark</em></label>
				    <div class="col-xs-8">
			            <?php echo tpl_onoff('is_mark','config[is_mark]',$value=['1','0','开启','关闭'],$config['is_mark'],''); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">水印类型<em>mark_type</em></label>
				    <div class="col-xs-8">
			            <?php echo tpl_onoff('mark_type','config[mark_type]',$value=['1','0','图片','文本'],$config['mark_type'],''); ?>
				    </div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label" style="padding-top:0px;">水印图片<em>mark_img</em></label>
					<div class="col-xs-8" id="uploader_mark">
						<?php echo tpl_upimg('mark_img','config[mark_img]',$config['mark_img'],0,is_login(),0,'上传水印图片','默认网站LOGO,最佳尺寸为160*60像素','上传水印图'); ?>
					</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">水印图片宽度<em>mark_width</em></label>
				    <div class="col-xs-8">
				    	<div class="input-group ">
                 			<input type="number" name="config[mark_width]" id="mark_width" placeholder="水印图片宽度" class="form-control" value="<?php echo $config['mark_width']; ?>">
    			    		<span class="input-group-addon">PX</span>
    			    	</div>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">水印图片高度<em>mark_height</em></label>
				    <div class="col-xs-8">
				    	<div class="input-group ">
                 			<input type="number" name="config[mark_height]" id="mark_height" placeholder="水印图片高度" class="form-control" value="<?php echo $config['mark_height']; ?>">
    			    		<span class="input-group-addon">PX</span>
    			    	</div>
			    	</div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">水印文字内容<em>mark_txt</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[mark_txt]" id="mark_txt" placeholder="水印文字" class="form-control" value="<?php echo $config['mark_txt']; ?>">
    			    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">水印透明度<em>mark_degree</em></label>
				    <div class="col-xs-8">
                 		<?php echo tpl_range('mark_degree','config[mark_degree]',$config['mark_degree'],0,100,'0代表完全透明，100代表不透明'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">水印质量<em>mark_quality</em></label>
				    <div class="col-xs-8">
                 		<?php echo tpl_range('mark_quality','config[mark_quality]',$config['mark_quality'],0,100,'水印质量请设置为0-100之间的数字,决定 jpg 格式图片的质量'); ?>
				    </div>
				</div>
			 	<div class="form-group">
                    <label class="col-xs-2 control-label" style="padding-top:0px;">水印位置<em>mark_sel</em></label>
                    <div class="col-xs-8">
                        <ul class="mark_sels clearfix">
                        	<li><label><input type="radio" value="1" name="config[mark_sel]" <?php if($config['mark_sel']==1): ?>checked="checked"<?php endif; ?>>顶部居左</label></li>
                        	<li><label><input type="radio" value="2" name="config[mark_sel]" <?php if($config['mark_sel']==2): ?>checked="checked"<?php endif; ?>>顶部居中</label></li>
                        	<li><label><input type="radio" value="3" name="config[mark_sel]" <?php if($config['mark_sel']==3): ?>checked="checked"<?php endif; ?>>顶部居右</label></li>
                        	<li><label><input type="radio" value="4" name="config[mark_sel]" <?php if($config['mark_sel']==4): ?>checked="checked"<?php endif; ?>>中部居左</label></li>
                        	<li><label><input type="radio" value="5" name="config[mark_sel]" <?php if($config['mark_sel']==5): ?>checked="checked"<?php endif; ?>>中部居中</label></li>
                        	<li><label><input type="radio" value="6" name="config[mark_sel]" <?php if($config['mark_sel']==6): ?>checked="checked"<?php endif; ?>>中部居右</label></li>
                        	<li><label><input type="radio" value="7" name="config[mark_sel]" <?php if($config['mark_sel']==7): ?>checked="checked"<?php endif; ?>>底部居左</label></li>
                        	<li><label><input type="radio" value="8" name="config[mark_sel]" <?php if($config['mark_sel']==8): ?>checked="checked"<?php endif; ?>>底部居中</label></li>
                        	<li><label><input type="radio" value="9" name="config[mark_sel]" <?php if($config['mark_sel']==9): ?>checked="checked"<?php endif; ?>>底部居右</label></li>
                        </ul>
                    </div>
                </div>
			</div>
			<div class="tab-pane fade" id="style2Tab5">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">是否开启邮箱验证<em>smtp_open</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('smtp_open','config[smtp_open]',$value=['1','0','开启','关闭'],$config['smtp_open'],'用户注册、修改密码时使用邮箱验证'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">邮件发送服务器(SMTP)<em>smtp_server</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[smtp_server]" id="smtp_server" placeholder="邮件发送服务器(SMTP)" class="form-control" value="<?php echo $config['smtp_server']; ?>">
    			    	<span class="help-block">发送邮箱的smtp地址。如:smtp.qq.com</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">服务器(SMTP)端口<em>smtp_port</em></label>
				    <div class="col-xs-8">
                 		<input type="number" name="config[smtp_port]" id="smtp_port" placeholder="服务器(SMTP)端口" class="form-control" value="<?php echo $config['smtp_port']; ?>">
    			    	<span class="help-block">smtp的端口。默认为25。具体请参看各STMP服务商的设置说明</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">邮箱账号<em>smtp_user</em></label>
				    <div class="col-xs-8">
                 		<input type="email" name="config[smtp_user]" id="smtp_user" placeholder="邮箱账号" class="form-control" value="<?php echo $config['smtp_user']; ?>">
    			    	<span class="help-block">使用发送邮件服务的邮箱账号</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">邮箱密码<em>smtp_pwd</em></label>
				    <div class="col-xs-8">
                 		<input type="password" name="config[smtp_pwd]" id="smtp_pwd" placeholder="邮箱密码" class="form-control" value="<?php echo $config['smtp_pwd']; ?>">
    			    	<span class="help-block">使用发送邮件的邮箱密码,或者授权码。具体请参看各STMP服务商的设置说明</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label">测试收件邮箱</label>
				    <div class="col-xs-8">
				    	<div class="input-group">
                 			<input type="email" name="test_eamil" id="test_eamil" placeholder="测试收件邮箱" class="form-control" value="">
    			    		<span class="input-group-btn">
    			    			<button type="button" class="btn btn-default" id="testEmail">测试</button>
    			    		</span>
				    	</div>
				    	<span class="help-block">首次请先保存配置再测试</span>
			    	</div>
				</div>
			</div>
			<div class="tab-pane fade" id="style2Tab6">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">阿里大鱼(appkey)<em>sms_appkey</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[sms_appkey]" id="sms_appkey" placeholder="阿里大鱼(appkey)" class="form-control" value="<?php echo $config['sms_appkey']; ?>">
    			    	<span class="help-block">阿里大鱼配置appkey，申请地址(<a href="http://www.alidayu.com/" target="_blank">www.alidayu.com</a>)</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">阿里大鱼(secretKey)<em>sms_secretKey</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[sms_secretKey]" id="sms_secretKey" placeholder="阿里大鱼(secretKey)" class="form-control" value="<?php echo $config['sms_secretKey']; ?>">
    			    	<span class="help-block">阿里大鱼配置secretKey</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">签名名称<em>sms_product</em></label>
				    <div class="col-xs-8">
                 		<input type="text" name="config[sms_product]" id="sms_product" placeholder="签名名称" class="form-control" value="<?php echo $config['sms_product']; ?>">
    			    	<span class="help-block">可以输入公司名、品牌名、产品名等，必需与阿里大于一致</span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">用户注册时<em>sms_is_reg</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_is_reg','config[sms_is_reg]',$value=['1','0','开启','关闭'],$config['sms_is_reg'],'用户注册时开启短信验证'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">用户找回密码时<em>sms_forget_pwd</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_forget_pwd','config[sms_forget_pwd]',$value=['1','0','开启','关闭'],$config['sms_forget_pwd'],'用户找回密码时使用短信验证'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">绑定手机时<em>sms_bind_mobile</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_bind_mobile','config[sms_bind_mobile]',$value=['1','0','开启','关闭'],$config['sms_bind_mobile'],'用户身份验证时使用短信验证'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">下单时发短信给商家<em>sms_order_add</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_order_add','config[sms_order_add]',$value=['1','0','开启','关闭'],$config['sms_order_add'],'用户下单时是否发送短信给商家'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">支付时发短信给商家<em>sms_order_pay</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_order_pay','config[sms_order_pay]',$value=['1','0','开启','关闭'],$config['sms_order_pay'],'用户支付订单时是否发送短信给商家'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">发货时发短信给客户<em>sms_delivery</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_delivery','config[sms_delivery]',$value=['1','0','开启','关闭'],$config['sms_delivery'],'商家发货时是否发送短信给客户'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">通知用户已支付<em>sms_notice</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('sms_notice','config[sms_notice]',$value=['1','0','开启','关闭'],$config['sms_notice'],'用户支付成功时通知用户'); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">短信码超时时间<em>sms_time_out</em></label>
				    <div class="col-xs-8">
		            	<select name="config[sms_time_out]" class="form-control">
		            		<option value="30" <?php if($config['sms_time_out']==30): ?>selected<?php endif; ?>>30秒</option>
		            		<option value="60" <?php if($config['sms_time_out']==60): ?>selected<?php endif; ?>>1分钟</option>
		            		<option value="120" <?php if($config['sms_time_out']==120): ?>selected<?php endif; ?>>2分钟</option>
		            		<option value="180" <?php if($config['sms_time_out']==180): ?>selected<?php endif; ?>>3分钟</option>
		            		<option value="300" <?php if($config['sms_time_out']==300): ?>selected<?php endif; ?>>5分钟</option>
		            		<option value="600" <?php if($config['sms_time_out']==600): ?>selected<?php endif; ?>>10分钟</option>
		            	</select>
		            	<span class="help-block">发送短信验证码时间隔时间</span>
				    </div>
				</div>
			</div>
			<div class="tab-pane fade" id="style2Tab7">
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">分销开关<em>distribut_switch</em></label>
				    <div class="col-xs-8">
		            	<?php echo tpl_onoff('distribut_switch','config[distribut_switch]',$value=['1','0','开启','关闭'],$config['distribut_switch']); ?>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">成为分销商条件<em>distribut_condition</em></label>
				    <div class="col-xs-8">
		            	<select name="config[distribut_condition]" class="form-control">
		            		<option value="0" <?php if($config['distribut_condition']==0): ?>selected<?php endif; ?>>直接成为分销商</option>
		            		<option value="1" <?php if($config['distribut_condition']==1): ?>selected<?php endif; ?>>成功购买商品后成为分销商</option>
		            		<option value="2" <?php if($config['distribut_condition']==2): ?>selected<?php endif; ?>>需提交申请审核</option>
		            	</select>
		            	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">分销名称<em>distribut_name</em></label>
				    <div class="col-xs-8">
                 		<input type="text" value="<?php echo $config['distribut_name']; ?>" name="config[distribut_name]" id="distribut_name" placeholder="分销名称" class="form-control">
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">分销模式<em>distribut_pattern</em></label>
				    <div class="col-xs-8">
				    	<select name="config[distribut_pattern]" class="form-control">
		            		<option value="0" <?php if($config['distribut_pattern']==0): ?>selected<?php endif; ?>>按商品设置的分成金额</option>
		            		<option value="1" <?php if($config['distribut_pattern']==1): ?>selected<?php endif; ?>>按订单设置的分成比例</option>
		            	</select>
                 		<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">一级分销商名称<em>distribut_first_name</em></label>
				    <div class="col-xs-8">
                 		<input type="text" value="<?php echo $config['distribut_first_name']; ?>" name="config[distribut_first_name]" id="distribut_first_name" placeholder="一级分销商名称" class="form-control">
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">一级分销商比例%<em>distribut_first_rate</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['distribut_first_rate']; ?>" name="config[distribut_first_rate]" id="distribut_first_rate" placeholder="一级分销商比例%" class="form-control">
    			    		<span class="input-group-addon">%</span>
    			    	</div>
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">二级分销商名称<em>distribut_second_name</em></label>
				    <div class="col-xs-8">
                 		<input type="text" value="<?php echo $config['distribut_second_name']; ?>" name="config[distribut_second_name]" id="distribut_second_name" placeholder="二级分销商名称" class="form-control">
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">二级分销商比例%<em>distribut_second_rate</em></label>
				    <div class="col-xs-8">
    			    	<div class="input-group ">
                 			<input type="number" value="<?php echo $config['distribut_second_rate']; ?>" name="config[distribut_second_rate]" id="distribut_second_rate" placeholder="二级分销商比例%" class="form-control">
    			    		<span class="input-group-addon">%</span>
    			    	</div>
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">三级分销商名称<em>distribut_third_name</em></label>
				    <div class="col-xs-8">
                 		<input type="text" value="<?php echo $config['distribut_third_name']; ?>" name="config[distribut_third_name]" id="distribut_third_name" placeholder="三级分销商名称" class="form-control">
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">三级分销商比例%<em>distribut_third_rate</em></label>
				    <div class="col-xs-8">
                 		<div class="input-group ">
                 			<input type="number" value="<?php echo $config['distribut_third_rate']; ?>" name="config[distribut_third_rate]" id="distribut_third_rate" placeholder="三级分销商比例%" class="form-control">
    			    		<span class="input-group-addon">%</span>
    			    	</div>
    			    	<span class="help-block"></span>
				    </div>
				</div>
				<div class="form-group">
				    <label class="col-xs-2 control-label" style="padding-top:0px;">分成时间<em>distribut_date</em></label>
				    <div class="col-xs-8">
				    	<select name="config[distribut_date]" class="form-control">
				    		<?php $__FOR_START_31518__=1;$__FOR_END_31518__=30;for($i=$__FOR_START_31518__;$i < $__FOR_END_31518__;$i+=1){ ?>
		            		<option value="<?php echo $i; ?>" <?php if($config['distribut_date']==$i): ?>selected<?php endif; ?>><?php echo $i; ?>天</option>
		            		<?php } ?>
		            	</select>
                 		<span class="help-block">订单收货确认后多少天可以分成</span>
				    </div>
				</div>
			</div>
		</div>
		<br/><br/><br/>
		<div class="footbtn">
		   <input type="button" value="确认提交" id="submitbtn" class="btn btn-primary">
		</div>
	</form>
</div>
<script type="text/javascript">
$(document).ready(function(){
	require(['think','layer','bootstrap']);
	//回车事件
	document.onkeydown = function(e) {
		var theEvent = window.event || e;
		var code = theEvent.keyCode || theEvent.which;
		if(code == 13){
			$("#submitbtn").click();
		}
	}
	//提交
	$('#submitbtn').click(function(){
		if($('#site_name').val()==''){
			layer.msg('站点名称不能为空', {icon:5});
			return false;
		}else if($('#site_domain').val()==''){
			layer.msg('网站域名不能为空', {icon:5});
			return false;
		}
		var fields = $('form').serializeArray();
		var ll = layer.load('正在处理，请稍后...', 3);
		$.post(Think.U('Admin/System/config'),fields,function(data){
	        if(data.status==1) {
	        	layer.msg(data.msg,{icon:6}, function(){
					location.reload();
				});
	        } else {
	        	layer.msg(data.msg, {icon:5});
	        }
	        layer.close(ll);
	        return false;
	    });
	});
	
	//测试邮件
	$('#testEmail').click(function(){
		var email=$('#test_eamil').val();
		var ll = layer.load('正在处理，请稍后...', 3);
		$.post(Think.U('Admin/System/test_email'),{email:email},function(data){
	        if(data.status==1) {
	        	layer.msg(data.msg,{icon:6}, function(){
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
</script>

<!--中间结束-->

<!--底部分 -->
<a href="javascript:" class="scroll-to-top hidden-print"><i class="fa fa-chevron-up fa-lg"></i></a>

<!--底部结束-->
</body>
</html>