<?php	
return array (
  //站点配置
  'site_open' => '1',												//网站开关(0|关闭,1|开启)
  'site_close_tip' => '网站升级中...',					//关站提示信息
  'site_name' => '黔东南建站网',								//站点名称
  'site_domain' => 'http://jplcdcty.qdn88.com',	//站点域名
  'site_logo' => '/static/images/logo.png',	//站点LOGO
  'site_title' => '黔东南建站网',								//站点标题
  'site_desc' => '无',												//站点描述
  'site_key' => '黔东南,凯里',									//站点关键字
  'site_icp' => '黔IPC备000000号',						//站点备案号
  'site_contact' => '龙牟仁',									//站点联系人
  'site_tel' => '(0855)8888888',						//联系电话
  'site_fax' => '(0855)8888888',						//传真
  'site_mobile' => '15286620001',						//手机
  'site_address' => '贵州省凯里市大十',						//联系地址
  'site_qq' => '315865872',									//客服QQ
  'site_ulit' => ' 贵州省锦屏县金土地扶贫开发有限公司',								//主办单位
  'site_zip' => '556000',										//邮编
  'site_email' => 'dreamlak@qq.com',				//电子邮件
  'microblog_qrcode' => '',									//官方微博二维码地址
  'wap_qrcode' => '',												//手机WAP二维码地址
  //上传配置
  'upload_path' => '/uploads',							//上传路径
  'upload_size' => '2048000',								//上传大小
  'upload_type' => 'jpg,jpeg,gif,png,rar,xls,xlsx,doc,docx,ppt,pptx',//上传类型
  //水印配置
  'is_mark' => '0',												//是否水印(0|否,1|是)
  'mark_type' => '0',											//水印类型(0|文字,1|图片)
  'mark_img' => '/static/images/logo.png',//水印图片
  'mark_txt' => 'ybcms',									//水印文字
  'mark_width' => '500',									//水印图片宽度
  'mark_height' => '500',									//水印图片高度
  'mark_degree' => '55',									//水印透明度
  'mark_quality' => '55',									//水印质量
  'mark_sel' => '9',											//水印位置(1|顶部居左,2|顶部居中,3|顶部居右,4|中部居左,5|中部居中,5|中部居右,6底部居左,7|底部居中,9|底部居右)
  //公共路径
  'js_path' => '/static/js/',							//JS路径
  'css_path' => '/static/css/',						//CSS路径
  'img_path' => '/static/images/',				//图片路径
  //邮件配置
  'smtp_server' => 'smtp.qq.com',					//邮件发送服务器(SMTP)
  'smtp_port' => '25',										//服务器(SMTP)端口
  'smtp_user' => '315865872@qq.com',			//邮箱账号
  'smtp_pwd' => 'Dreamlak1320906',				//邮箱密码
  'test_eamil' => 'dreamlak@qq.com',			//测试收件邮箱
  'smtp_open' => '0',											//是否开启邮箱验证(0|否,1|是)
  'smtp_type' => 'smtp',									//发送类型
  'mail_tpl' => '',												//邮件发送模板
  //网站配置
  'copyright' => 'Copyright © 2017  贵州省锦屏县金土地扶贫开发有限公司. All Rights Reserved。',//版权信息
  'admin_list_num' => '15',										//网站后台每页条数
  'index_list_num' => '10',										//网站前台每页条数
  'site_count_code' => '<script></script>',		//网站统计代码
  //微信配置
  'wx_token' => 'f9caab9ed4656dbd45146a2a23138fc2',			//设定的token
  'wx_appid' => 'wxeb4efb7f83ce3f52',										//高级调用功能的app id, 请在微信开发模式后台查询
  'wx_appsecret' => '14434ba78d5bd94a7385cc431bc6572b',	//高级调用功能的密钥
  'wx_encodingaeskey' => 'stlnVrnktqejSZyfryQHzhcwlMfUykWQEhezzrKSIPb',	//（可选，接口传输选择加密时必需）
  'wx_cache_path' => './runtime/data',		//设置SDK缓存目录（可选，默认位置在./Wechat/Cache下，请保证写权限）
  'wx_wechat_name' => '华思宇网络新生活',						//公众号名称
  'wx_wechat_id' => 'gh_dde245ae244f',				//公众号原始id
  'wx_username' => 'hsywl8',						//微信号
  'wx_avatar' => 'https://mp.weixin.qq.com/misc/getheadimg?token=550143490&fakeid=3071196901&r=849611',			//微信头像地址
  'wx_qrcode' => '',			//微信二维码地址
  'wx_type' => '3',				//微信类型(0订阅号,1认证订阅号,2服务号,3认证服务号)
  //微信支付
  'wx_mch_id' => '',			//微信支付，商户ID（可选）
  'wx_partnerkey' => '',	//微信支付，密钥（可选）
  'wx_ssl_cer' => '',			//微信支付，双向证书（可选，操作退款或打款时必需
  'wx_ssl_key' => '',			//微信支付，双向证书（可选，操作退款或打款时必需）
  //短信配置
  'sms_appkey' => '23706079',				//阿里大鱼配置appkey
  'sms_secretKey' => 'fa723f80c3e1e8be3258b3926a9e79ba',		//阿里大鱼配置secretKey
  'sms_product' => 'Ybcms商城',				//签名标题,可以输入公司名、品牌名、产品名等
  'sms_is_reg' => '0',							//用户注册时使用短信验证（0关，1开）
  'sms_forget_pwd' => '0',					//用户找回密码时使用短信验证（0关，1开）
  'sms_bind_mobile' => '1',					//用户身份验证时使用短信验证（0关，1开）
  'sms_order_add' => '0',						//用户下单时是否发送短信给商家（0关，1开）
  'sms_order_pay' => '0',						//用户支付订单时是否发送短信给商家（0关，1开）
  'sms_delivery' => '0',						//商家发货时是否发送短信给客户（0关，1开）
  'sms_notice' => '1',							//通知用户已支付（0关，1开）
  'sms_time_out' => '120',					//发送短信验证码时间隔时间
  //商城配置
	'province' => '2495',		//省ID
	'city' => '2563',				//市ID
	'district' => '2564',		//县ID
	'twon' => '',						//街道、乡镇ID
	'hot_keywords' => '苹果,小米,三星,华为,内存卡,数据线',					//商城热门搜索词
	'reg_integral' => '0',						//会员注册赠送积分
	'default_storage' => '100',				//默认库存(添加商品的默认库存)
	'warning_storage' => '20',				//库存预警数(库存预警,当商品库存少于库存预警数，将在商品列表页库存显示红色)
	'distribut_need' => '1000',				//满多少才能提现(需超过多少才能提现金额,分销商有用)
	'distribut_min' => '100',				//最少提现额度(最少提现多少，才能体现,分销商有用)
	'invoice_tax' => '8',							//发票税率%(当买家需要发票的时候就要增加[商品金额]*[税率]的费用)
	'freight_free' => '1',						//全场满多少免运费(0表示免运费)
	'point_rate' => '100',							//积分换算比例(1元=1积分,1元=10积分,1元=100积分)
	'point_min_limit' => '1',					//积分最低使用限制(0表示不限制, 大于0时, 用户积分小于该值将不能使用积分)
	'point_use_percent' => '0',				//积分使用比例(100时不限制, 为0时不能使用积分, 1时积分抵扣金额不能超过该笔订单应付金额的1%)
	'auto_confirm_date' => '3',				//发货后多少天自动收货(发货后多少天自动确认收货)
	'stock_reduce' => '1',						//减库存的机制(1=订单支付时，2=发货时)
	//分销配置
	'distribut_switch' => '0',				//分销开关(1开，0关)
	'distribut_condition' => '1',			//成为分销商条件(0直接成为分销商,1成功购买商品后成为分销商)
	'distribut_name' => '我的分销商',			//分销名称
	'distribut_pattern' => '0',				//分销模式(0按商品设置的分成金额,1按订单设置的分成比例)
	'distribut_first_name' => '我的一级分销',			//一级分销商名称
	'distribut_first_rate' => '70',						//一级分销商比例%
	'distribut_second_name' => '我的二级分销',		//二级分销商名称
	'distribut_second_rate' => '20',					//二级分销商比例%
	'distribut_third_name' => '我的三级分销',			//三级分销商名称
	'distribut_third_rate' => '10',						//三级分销商比例%
	'distribut_date' => '2',									//分成时间(订单收货确认后多少天可以分成)
);	
?>