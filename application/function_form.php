<?php

/*插入编辑器
 * @param string 	$id			表单ID
 * @param string 	$value		表单值
 * @param int 		$height		编辑器高度
 * @param string 	$types		编辑器类型（full=完整型、simple=简结型）
 * @param int 		$isup		是否允许上传
 * @param int 		$ismark		是否允许水印
 * @param int 		$auid		管理员ID
 * @param int 		$uuid		会员ID（在前台用户时）
 */
function tpl_ueditor($id,$name,$value='',$height=200,$types='full',$isup=1,$ismark=1,$auid=0,$uuid=0) {
	$auid=is_login();
	$s = "<script type=\"text/javascript\">
		require(['ZeroClipboard'],function (ZeroClipboard){
			window['ZeroClipboard'] = ZeroClipboard;
		});
	</script>";
	$s .= '<script type="text/javascript" src="/static/js/ueditor/ueditor.config.js"></script><script type="text/javascript" src="/static/js/ueditor/ueditor.all.min.js"></script><script type="text/javascript" src="/static/js/ueditor/lang/zh-cn/zh-cn.js"></script>';
	$s .= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$name}\" type=\"text/plain\" style=\"height:{$height}px;\">{$value}</textarea>\r" : '';
	$s .= "
	<script type=\"text/javascript\">
			var ueditoroption = {
				'autoClearinitialContent' : false,
				".($types=='full' ? "
				'toolbars' : [[
					'fullscreen', 'source', '|', 'undo', 'redo', '|',
		            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 
		            'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
		            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
		            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
		            'directionalityltr', 'directionalityrtl', 'indent', '|',
		            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
		            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
		            'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
		            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
		            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
		            'print', 'preview', 'searchreplace', 'drafts', 'help'
				]],
				":"
				'toolbars' : [[
					'fullscreen', 'source', 'preview', '|', 'bold', 'italic', 'underline', 'strikethrough', 'forecolor', 'backcolor', '|',
					'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'blockquote', 'emotion',
					'link', 'removeformat', '|', 'rowspacingtop', 'rowspacingbottom', 'lineheight','indent', 'paragraph', 'fontsize', '|',
					'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol',
					'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'anchor', 'map', 'print', 'drafts'
				]],
				")."
				'elementPathEnabled' : false,
				'initialFrameHeight': {$height},
				'autoHeightEnabled':false,
				'focus' : false,
				'maximumWords' : 9999999999999
			};
			".($isup ? "
			UE.registerUI('myinsertimage',function(editor,uiName){
				editor.registerCommand(uiName, {
					execCommand:function(){
						require(['bootstrap','think'], function(){
							var parame={};
								parame.filetype='images';
								parame.filenum=10;
								parame.ext='jpg,jpeg,gif,png,bmp';
								parame.upfilesize=1024*1024*2;
								parame.ismark={$ismark},
								parame.adminid={$auid};
								parame.userid={$uuid};
								
							util.upfile(parame,function(imgs){
								if (imgs.length == 0) {
									return;
								} else if (imgs.length == 1) {
									var img='<a class=\"img_group\" href=\"'+imgs[0]+'\"><img src=\"'+imgs[0]+'\" style=\"max-width:700px\"></a>';
									editor.execCommand('inserthtml', img);
									/*
									editor.execCommand('insertimage', {
										'src' : imgs[0],
										'width' : '700'
									});
									*/
								}else{
									//var imglist = [];
									var img='';
									for (i in imgs) {
										img+='<a class=\"img_group\" href=\"'+imgs[i]+'\"><img src=\"'+imgs[i]+'\" style=\"max-width:700px\"></a>';
										/*
										imglist.push({
											'src' : imgs[i],
											'width' : '700'
										});
										*/
									}
									//editor.execCommand('insertimage', imglist);
									editor.execCommand('inserthtml', img);
								}
							});
						});
					}
				});
				var btn = new UE.ui.Button({
					name: '插入图片',
					title: '插入图片',
					cssRules :'background-position: -726px -77px',
					onclick:function () {
						editor.execCommand(uiName);
					}
				});
				editor.addListener('selectionchange', function () {
					var state = editor.queryCommandState(uiName);
					if (state == -1) {
						btn.setDisabled(true);
						btn.setChecked(false);
					} else {
						btn.setDisabled(false);
						btn.setChecked(state);
					}
				});
				return btn;
			}, 57);
			":'')."
			".(!empty($id) ? "
				$(function(){
					var ue = UE.getEditor('{$id}', ueditoroption);
					$('#{$id}').data('editor', ue);
					$('#{$id}').parents('form').submit(function() {
						if (ue.queryCommandState('source')) {
							ue.execCommand('source');
						}
					});
				});" : '')."
	</script>\r";
	return $s;
}
/*插入编辑器（微信）
 * @param string 	$id			表单ID
 * @param string 	$value		表单值
 * @param int 		$height		编辑器高度
 * @param int 		$isup		是否允许上传
 * @param int 		$ismark		是否允许水印
 * @param int 		$auid		管理员ID
 * @param int 		$uuid		会员ID（在前台用户时）'undo','redo','|',
 */
function tpl_ueditor_wx($id,$name,$value='',$height=200,$isup=1,$ismark=1,$auid=0,$uuid=0) {
	$auid=is_login();
	$config=config('config');
	$domain=$config['site_domain'].'/';
	$s = "<script type=\"text/javascript\">
		require(['ZeroClipboard'],function (ZeroClipboard){
			window['ZeroClipboard'] = ZeroClipboard;
		});
	</script>";
	$s .= '<script type="text/javascript" src="/static/js/ueditor/ueditor.config.js"></script><script type="text/javascript" src="/static/js/ueditor/ueditor.all.min.js"></script><script type="text/javascript" src="/static/js/ueditor/lang/zh-cn/zh-cn.js"></script>';
	$s .= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$name}\" type=\"text/plain\" style=\"height:{$height}px;\">{$value}</textarea>\r" : '';
	$s .= "
	<script type=\"text/javascript\">
			var ueditoroption = {
				'autoClearinitialContent' : false,
				'toolbars' : [[
					'fullscreen','source','|','undo','redo','|','bold','italic','underline','strikethrough','forecolor','backcolor','|',
					'justifyleft','justifycenter','justifyright','|','insertorderedlist','insertunorderedlist','blockquote','emotion',
					'link','removeformat','|','rowspacingtop','rowspacingbottom','lineheight','indent','paragraph','fontsize','|',
					'inserttable'
				]],
				'elementPathEnabled' : false,
				'initialFrameHeight': {$height},
				'autoHeightEnabled':false,
				'focus' : false,
				'maximumWords' : 9999999999
			};
			".($isup ? "
			UE.registerUI('myinsertimage',function(editor,uiName){
				editor.registerCommand(uiName, {
					execCommand:function(){
						require(['bootstrap','layer','think'], function(){
							var parame={};
								parame.filetype='images';
								parame.filenum=10;
								parame.ext='jpg,jpeg,gif,png,bmp';
								parame.upfilesize=1024*1024*1;
								parame.ismark={$ismark},
								parame.adminid={$auid};
								parame.userid={$uuid};
							util.upfile(parame,function(imgs){
								if (imgs.length == 0) {
									return;
								}else if(imgs.length == 1){
									var img='<img src=\"'+getWxmediaURL(imgs[0])+'\" data-latex=\"'+imgs[0]+'\" width=\"700\">';
									editor.execCommand('inserthtml', img);
									/*editor.execCommand('insertimage', {
										'src' : imgs[0],
										'data-latex' : imgs[0],
										'width' : '700'
									});*/
								}else{
									//var imglist = [];
									var img='';
									for (i in imgs) {
										img+='<img src=\"'+getWxmediaURL(imgs[i])+'\" data-latex=\"'+imgs[i]+'\" width=\"700\">';
										/*imglist.push({
											'src' : getWxmediaURL(imgs[i]),
											'data-latex' : imgs[i],
											'width' : '700'
										});*/
									}
									//editor.execCommand('insertimage', imglist);
									editor.execCommand('inserthtml', img);
								}
							});
						});
					}
				});
				var btn = new UE.ui.Button({
					name: '插入图片',
					title: '插入图片',
					cssRules :'background-position: -726px -77px',
					onclick:function () {
						editor.execCommand(uiName);
					}
				});
				editor.addListener('selectionchange', function () {
					var state = editor.queryCommandState(uiName);
					if (state == -1) {
						btn.setDisabled(true);
						btn.setChecked(false);
					} else {
						btn.setDisabled(false);
						btn.setChecked(state);
					}
				});
				return btn;
			}, 57);
			":'')."
			".(!empty($id) ? "
				$(function(){
					var ue = UE.getEditor('{$id}', ueditoroption);
					$('#{$id}').data('editor', ue);
					$('#{$id}').parents('form').submit(function() {
						if (ue.queryCommandState('source')) {
							ue.execCommand('source');
						}
					});
				});" : '')."
	</script>\r";
	return $s;
}
/* 滑块表单
 * @param string 	$formid			表单ID
 * @param int 		$value			值
 * @param int 		$min			起始值
 * @param int 		$max			最大值
 * @param string 	$msg			说明
 */
function tpl_range($formid,$names,$value='',$min=0,$max=100,$msg=''){
	if(empty($formid))return false;
	$str='';
	$str.="
		<input value=\"{$value}\" class=\"form-range\" name=\"{$names}\" id=\"{$formid}\" type=\"range\" min=\"{$min}\" step=\"1\" max=\"{$max}\">
		<span class=\"form-range-num\" id=\"{$formid}2\">{$value}</span>
	\r";
	if($msg!='')$str.="<span class=\"help-block\">{$msg}</span>\r";
	$str.="<script type=\"text/javascript\">
	    $(function(){
	        $('input[type=range]#{$formid}').bind('input propertychange',function(){
			    $('#{$formid}2').html($(this).val());
			});
	    });
	</script>
	\r";
	return $str;
}

/* 开关表单
 * @param string 	$formid			表单ID
 * @param array 	$value			值（数组['1','0','开启','关闭']）
 * @param int 		$selected		默认选中项（1=开，0=关）
 * @param string 	$msg			说明
 */
function tpl_onoff($formid,$names,$value=['1','0','开启','关闭'],$selected=1,$msg=''){
	if(empty($formid))return false;
	$open_value=$value[0];
	$open_text=$value[2];
	$close_value=$value[1];
	$close_text=$value[3];
	if($selected==1){
		$isopen='selected';
		$isopen_radio='checked="checked"';
		$isclose='';
		$isclose_radio='';
	}else{
		$isopen='';
		$isopen_radio='';
		$isclose='selected';
		$isclose_radio='checked="checked"';
	}
	$str="
	<div class=\"onoff\">
        <label for=\"{$formid}1\" class=\"cb-enable {$isopen}\">{$open_text}</label>
        <label for=\"{$formid}0\" class=\"cb-disable {$isclose}\">{$close_text}</label>
        <input id=\"{$formid}1\" name=\"{$names}\" {$isopen_radio} value=\"{$open_value}\" type=\"radio\">
        <input id=\"{$formid}0\" name=\"{$names}\" {$isclose_radio} value=\"{$close_value}\" type=\"radio\">
    </div>\r";
	if($msg!='')$str.="<span class=\"help-block\">{$msg}</span>\n";
	return $str;
}

/*单个上传文件
 * @param string 	$id			表单ID
 * @param string 	$name		表单名称
 * @param string 	$value		表单值
 * @param int 		$auid		管理员ID
 * @param int 		$uuid		会员ID（在前台用户时）
 * @param string 	$placeholder表单说明
 * @param string 	$msg		说明
 */
function tpl_upfile($id,$name,$value='',$auid=0,$uuid=0,$ext='rar,zip,doc,docx,xls,xlsx,ppt,pptx',$placeholder='文件URL,可直接填写文件远程地址',$msg='',$btname='上传文件'){
	$str="
		<div class=\"input-group\">
			<input type=\"text\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" placeholder=\"{$placeholder}\" class=\"form-control\">
			<span class=\"input-group-btn\">
				<button class=\"btn btn-default\" type=\"button\" id=\"upload_{$id}\">{$btname}</button>
			</span>
		</div>
	";
	if($msg!='')$str.="<span class=\"help-block\">{$msg}</span>\n";
	$str.="
		<script type=\"text/javascript\">
        $('#upload_{$id}').click(function(){
	        require(['bootstrap','think'], function(){
				var parame={};
					parame.filetype='file';
					parame.filenum=1;
					parame.ext='{$ext}';
					parame.upfilesize=1024*1024*500;
					parame.ismark=0,
					parame.adminid={$auid};
					parame.userid={$uuid};
				util.upfile(parame,function(img){
					$('#{$id}').val(img[0]);
				});
			});
		});
        </script>
	";
	return $str;
}

/*单张上图片
 * @param string 	$id			表单ID
 * @param string 	$name		表单名称
 * @param string 	$value		表单值
 * @param int 		$ismark		是否允许水印
 * @param int 		$auid		管理员ID
 * @param int 		$uuid		会员ID（在前台用户时）
 * @param string 	$placeholder表单说明
 * @param string 	$msg		说明
 */
function tpl_upimg($id,$name,$value='',$ismark=1,$auid=0,$uuid=0,$placeholder='文件URL,可直接填写文件远程地址',$msg='',$btname='上传图片'){
	$str="
		<div class=\"input-group\">
			<input type=\"text\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" placeholder=\"{$placeholder}\" class=\"form-control\">
			<span class=\"input-group-btn\">
				<button class=\"btn btn-default\" type=\"button\" id=\"show_{$id}\">预览</button>
				<button class=\"btn btn-default\" type=\"button\" id=\"upload_{$id}\">{$btname}</button>
			</span>
		</div>
	";
	if($msg!='')$str.="<span class=\"help-block\">{$msg}</span>\n";
	$str.="
		<script type=\"text/javascript\">
        $('#upload_{$id}').click(function(){
	        require(['bootstrap','think'], function(){
				var parame={};
					parame.filetype='images';
					parame.filenum=1;
					parame.ext='jpg,jpeg,gif,png,bmp';
					parame.upfilesize=1024*1024*2;
					parame.ismark={$ismark},
					parame.adminid={$auid};
					parame.userid={$uuid};
				util.upfile(parame,function(img){
					$('#{$id}').val(img[0]);
				});
			});
		});
	\n";
	$str.="
		$('#show_{$id}').click(function(){
			require(['layer'],function(){
				var src = $('#{$id}').val();
				if(src==''){
					layer.msg('您还没有上传图片呢！', {icon:5});
					return false;
				}
				var modalobj=util.dialog('','<img src=\"'+src+'\">');
				modalobj.modal('show');
			});
		});
	</script>
	\n";
	return $str;
}
/*单个视频上传
 * @param string 	$id			表单ID
 * @param string 	$name		表单名称
 * @param string 	$value		表单值
 * @param int 		$auid		管理员ID
 * @param int 		$uuid		会员ID（在前台用户时）
 * @param string 	$placeholder表单说明
 * @param string 	$msg		说明
 */
function tpl_upvod($id,$name,$value='',$auid=0,$uuid=0,$placeholder='文件URL,可直接填写文件远程地址',$msg=''){
	$str="
		<div class=\"input-group\">
			<input type=\"text\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" placeholder=\"{$placeholder}\" class=\"form-control\">
			<span class=\"input-group-btn\">
				<button class=\"btn btn-default\" type=\"button\" id=\"show_{$id}\">预览</button>
				<button class=\"btn btn-default\" type=\"button\" id=\"upload_{$id}\">上传视频</button>
			</span>
		</div>
	";
	if($msg!='')$str.="<span class=\"help-block\">{$msg}</span>\n";
	$str.="
		<script type=\"text/javascript\">
        $('#upload_{$id}').click(function(){
	        require(['bootstrap','think'], function(){
				var parame={};
					parame.filetype='video';
					parame.filenum=1;
					parame.ext='mp4';
					parame.upfilesize=1024*1024*500;
					parame.ismark=0,
					parame.adminid={$auid};
					parame.userid={$uuid};
				util.upfile(parame,function(img){
					$('#{$id}').val(img[0]);
				});
			});
		});
	\n";
	$str.="
		$('#show_{$id}').click(function(){
			require(['layer'],function(){
				var src = $('#{$id}').val();
				if(src==''){
					layer.msg('您还没有上传视频呢！', {icon:5});
					return false;
				}
				var html='<embed src=\"/static/js/ckplayer/ckplayer.swf\" flashvars=\"f='+src+'&p=1\" quality=\"high\" width=\"100%\" height=\"370\" align=\"middle\" allowScriptAccess=\"always\" allowFullscreen=\"true\" type=\"application/x-shockwave-flash\"></embed>';
				var modalobj=util.dialog('',html);
				modalobj.modal('show');
			});
		});
	</script>
	\n";
	return $str;
}

/*添加图标
 * @param string 	$id			表单ID
 * @param string 	$name		表单名称
 * @param string 	$value		表单值
 */
function tpl_addicon($id,$value='',$name=''){
	if(empty($id))return false;
	if(empty($name)||$name=='')$name=$id;
	if(empty($value)||$value==''){
		$claa="fa fa-home";
	}else{
		$claa=$value;
	}
	$str="
		<div class=\"input-group\">
			<input type=\"text\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" placeholder=\"Font Awesome 图标样式\" class=\"form-control\">
			<span class=\"input-group-addon\"><i class=\"{$claa}\"></i></span>
     		<span class=\"input-group-btn\">
            	<button class=\"btn btn-default\" type=\"button\" onclick=\"showIconDialog(this);\">选择图标</button>
        	</span>
		</div>
		<span class=\"help-block\"><i class=\"fa fa-home\"></i>样式如：fa fa-home，不明白点“<a href=\"http://fontawesome.io/icons/\" target=\"_blank\">参考网站</a>”</span>
	";
	$str.="
		<script type=\"text/javascript\">
        function showIconDialog(elm) {
			require(['bootstrap'], function(){
				var spview = $(elm).parent().prev();
				var ipt = spview.prev();
				if(!ipt.val()){
					spview.css('display','none');
				}
				util.iconBrowser(function(ico){
					ipt.val(ico);
					spview.show();
					spview.find('i').attr('class','');
					spview.find('i').addClass('fa').addClass(ico);
				});
			});
		}
		</script>
	\n";
	return $str;
}
/*添加地区联动
 * @param string 	$lvel		显示的层级
 * @param int 	$province		省ID
 * @param int 	$city			市ID
 * @param int 	$district		县ID
 * @param int 	$twon			镇ID
 */
function tpl_area($lvel=3,$province='',$city='',$district='',$twon=''){
	$province_option=$city_option=$district_option=$twon_option='';
	$str='<div class="form-inline">';
	$str.='<script type="text/javascript" src="/static/js/city/city.js"></script>';
	if($lvel>0){
		$province_option=getCitySelect(0,$province);//省
		$onchange=($lvel>1)?'onchange="getCity(this)"':'';
		$str.="<select name=\"province\" id=\"province\" {$onchange} class=\"form-control\">
                <option value=\"0\">请选择省份</option>
                {$province_option}
            </select>\n";
	}

	if($lvel>1){
		if($city!='')$city_option=getCitySelect($province,$city);//市
		$onchange=($lvel>2)?'onchange="getArea(this)"':'';
		$str.="<select name=\"city\" id=\"city\" {$onchange} class=\"form-control\">
                <option value=\"0\">请选择城市</option>
                {$city_option}
            </select>\n";
	}
	
	if($lvel>2){
		if($district!='')$district_option=getCitySelect($city,$district);//县
		$onchange=($lvel>3)?'onchange="getTwon(this)"':'';
		$str.="<select name=\"district\" id=\"district\" {$onchange} class=\"form-control\">
                <option value=\"0\">请选择县</option>
                {$district_option}
            </select>\n";
	}

	if($lvel>3){
		if($twon!='')$twon_option=getCitySelect($district,$twon);//县
		$str.="<select name=\"twon\" id=\"twon\" class=\"form-control\">
                <option value=\"0\">请选择</option>
                {$twon_option}
            </select>";
	}
	$str.='</div>';
	return $str;
}
?>