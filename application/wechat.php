<?php
/**
 * 获取微信操作对象（单例模式）
 * @staticvar array $wechat 静态对象缓存对象
 * @param type $type 接口名称 ( Card|Custom|Device|Extend|Media|Menu|Oauth|Pay|Receive|Script|User )
 *┌─────────────────────────┐
 *│ Card	=卡卷接口			│
 *│ Custom	=多客服接口			│
 *│ Device	=周边设备接口		│
 *│ Extend	=其它工具接口		│
 *│ Media	=媒体素材接口		│
 *│ Menu	=菜单操作接口		│
 *│ Oauth	=网页授权接口		│
 *│ Pay		=支付相关接口		│
 *│ Receive	=被动消息处理SDK	│
 *│ Script	=网页脚本工具		│
 *│ User	=粉丝接口			│
 *└─────────────────────────┘
 * @return \Wehcat\WechatReceive 返回接口对接
 */
function & load_wechat($type = ''){
    static $wechat = array();
    $index = md5(strtolower($type));
    if(!isset($wechat[$index])){
    	$getConfig=config('config');
		// 定义微信公众号配置参数（这里是从配置文件读取）
        $options=[
		    'token'          => $getConfig['wx_token'], 			//填写你设定的token
		    'appid'          => $getConfig['wx_appid'], 			//填写高级调用功能的app id, 请在微信开发模式后台查询
	    	'appsecret'      => $getConfig['wx_appsecret'], 		//填写高级调用功能的密钥
		    'encodingaeskey' => $getConfig['wx_encodingaeskey'],	//填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
		    'mch_id'         => $getConfig['wx_mch_id'],  			//微信支付，商户ID（可选）
		    'partnerkey'     => $getConfig['wx_partnerkey'],  		//微信支付，密钥（可选）
		    'ssl_cer'        => $getConfig['wx_ssl_cer'], 			//微信支付，双向证书（可选，操作退款或打款时必需）
		    'ssl_key'        => $getConfig['wx_ssl_key'],  			//微信支付，双向证书（可选，操作退款或打款时必需）
		    'cachepath'      => $getConfig['wx_cache_path'], 		//设置SDK缓存目录（可选，默认位置在./Wechat/Cache下，请保证写权限）
		];
		//确定缓存目录
        $options['cachepath'] = RUNTIME_PATH . 'Data/';
        $wechat[$index] = & \Wechat\Loader::get($type, $options);
    }
    return $wechat[$index];
}
/**
 * 获取网页授权access_token
 * $code 用户同意授权，获取的code
 * $scope        应用授权作用域（snsapi_base:静默授权的，snsapi_userinfo:弹出授权页面）
 * ===========================================================================================================
 * 返回内容（数组）：
 * access_token  网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
 * expires_in    access_token接口调用凭证超时时间，单位7200（秒）
 * refresh_token 用户刷新access_token
 * openid        用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
 * scope         用户授权的作用域，使用逗号（,）分隔
 * ===========================================================================================================
 */
function get_oauth_AccessToken($scope='snsapi_userinfo',$state='0'){
	$oauthAccessToken = cookie('oauth_access_token');
	if(empty($oauthAccessToken)||$oauthAccessToken==''){
		//SDK实例对象
		$oauth = & load_wechat('Oauth');
		if(!isset($_GET['code'])){
			//创建微信网页授权URL
			$url = $oauth->getOauthRedirect(request()->url(true), $state, $scope);
			header("Location:$url");
		}else{
			//通过code换取网页授权access_token
			$result = $oauth->getOauthAccessToken();
			cookie('oauth_access_token',$result,7200);
			return $result;
		}
	}else{
		return $oauthAccessToken;
	}
}

//获取授权后的用户资料,作用域必须为snsapi_userinfo
/*返回内容：
	openid		用户唯一标识
	nickname	用户昵称
	sex			用户性名 1男，2女
	language	语言 "zh_CN"
	city		城市
	province	省
	country		地区
	headimgurl	头像
	privilege
 */
function getOauthUser($access_token, $openid){
	if($access_token=='' || $openid=='')return false;
	//SDK实例对象
	$oauth = & load_wechat('Oauth');
	//获取用户资料
	$result = $oauth->getOauthUserinfo($access_token, $openid);
	if($result===FALSE){
	    return false;
	}else{
	    return $result;
	}
}

//判段是否微信打开
function iswxopen(){
	if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')!==false){
		return true;
	}else{
		return false;
	}
}

//获取JsApi使用签名包
function getJsApi(){
	$script = &  load_wechat('Script');
	//获取JsApi使用签名，通常这里只需要传 $url参数
	$options = $script->getJsSign(request()->url(true));
	//处理执行结果
	if($options===FALSE){
	    return false;
	}else{
	    return $options;
	}
}
/**
 * 获取access_token(基础)
 * ===========================================================================================================
 * 返回内容：
 * access_token  获取到的凭证
 * expires_in    凭证有效时间(7200秒)，单位：秒
 * ===========================================================================================================
 */
function get_Access_Token(){
	$AccessToken = cookie('web_access_token');
	if(empty($AccessToken)||$AccessToken==''){
		$appid=config('config.wx_appid');
    	$secret=config('config.wx_appsecret');

		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";	
		$jsonArr=json_decode(httpRequest($url,'GET'),true);
		cookie('web_access_token',$jsonArr,7200);
		$access_token=$jsonArr['access_token'];
	}else{
		$access_token=$AccessToken['access_token'];
	}
	return $access_token;
}
//开发者认证用户此获取(UnionID机制)
function getUserInfo($openid=''){
	$access_token=get_Access_Token();
	$url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
	$json=json_decode(httpRequest($url,'GET'),true);
	return $json;
}



/**
 * 错误提示
 * 取error_code 对应的说明文字
 * @param type $error_code      //错误编码
 * @return string
 */
function get_wechat_error($error_code = ''){
    if (empty($error_code)){
        $error_code = $this->error_code;
    }
    $error = '';
    switch($error_code){
        case '-1': $error = '系统繁忙，此时请开发者稍候再试';
            break;
        case '0' : $error = '请求成功';
            break;
        case '40001' : $error = '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口';
            break;
        case '40002' : $error = '不合法的凭证类型';
            break;
        case '40003' : $error = '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID';
            break;
        case '40004' : $error = '不合法的媒体文件类型';
            break;
        case '40005' : $error = '不合法的文件类型';
            break;
        case '40006' : $error = '不合法的文件大小';
            break;
        case '40007' : $error = '不合法的媒体文件id';
            break;
        case '40008' : $error = '不合法的消息类型';
            break;
        case '40009' : $error = '不合法的图片文件大小';
            break;
        case '40010' : $error = '不合法的语音文件大小';
            break;
        case '40011' : $error = '不合法的视频文件大小';
            break;
        case '40012' : $error = '不合法的缩略图文件大小';
            break;
        case '40013' : $error = '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写';
            break;
        case '40014' : $error = '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口';
            break;
        case '40015' : $error = '不合法的菜单类型';
            break;
        case '40016' : $error = '不合法的按钮个数';
            break;
        case '40017' : $error = '不合法的按钮个数';
            break;
        case '40018' : $error = '不合法的按钮名字长度';
            break;
        case '40019' : $error = '不合法的按钮KEY长度';
            break;
        case '40020' : $error = '不合法的按钮URL长度';
            break;
        case '40021' : $error = '不合法的菜单版本号';
            break;
        case '40022' : $error = '不合法的子菜单级数';
            break;
        case '40023' : $error = '不合法的子菜单按钮个数';
            break;
        case '40024' : $error = '不合法的子菜单按钮类型';
            break;
        case '40025' : $error = '不合法的子菜单按钮名字长度';
            break;
        case '40026' : $error = '不合法的子菜单按钮KEY长度';
            break;
        case '40027' : $error = '不合法的子菜单按钮URL长度';
            break;
        case '40028' : $error = '不合法的自定义菜单使用用户';
            break;
        case '40029' : $error = '不合法的oauth_code';
            break;
        case '40030' : $error = '不合法的refresh_token';
            break;
        case '40031' : $error = '不合法的openid列表';
            break;
        case '40032' : $error = '不合法的openid列表长度';
            break;
        case '40033' : $error = '不合法的请求字符，不能包含\uxxxx格式的字符';
            break;
        case '40035' : $error = '不合法的参数';
            break;
        case '40038' : $error = '不合法的请求格式';
            break;
        case '40039' : $error = '不合法的URL长度';
            break;
        case '40050' : $error = '不合法的分组id';
            break;
        case '40051' : $error = '分组名字不合法';
            break;
        case '40117' : $error = '分组名字不合法';
            break;
        case '40118' : $error = 'media_id大小不合法';
            break;
        case '40119' : $error = 'button类型错误';
            break;
        case '40120' : $error = 'button类型错误';
            break;
        case '40121' : $error = '不合法的media_id类型';
            break;
        case '40132' : $error = '微信号不合法';
            break;
        case '40137' : $error = '不支持的图片格式';
            break;
        case '40155' : $error = '请勿添加其他公众号的主页链接';
            break;
        case '41001' : $error = '缺少access_token参数';
            break;
        case '41002' : $error = '缺少appid参数';
            break;
        case '41003' : $error = '缺少refresh_token参数';
            break;
        case '41004' : $error = '缺少secret参数';
            break;
        case '41005' : $error = '缺少多媒体文件数据';
            break;
        case '41006' : $error = '缺少media_id参数';
            break;
        case '41007' : $error = '缺少子菜单数据';
            break;
        case '41008' : $error = '缺少oauth code';
            break;
        case '41009' : $error = '缺少openid';
            break;
        case '42001' : $error = 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明';
            break;
        case '42002' : $error = 'refresh_token超时';
            break;
        case '42003' : $error = 'oauth_code超时';
            break;
        case '42007' : $error = '用户修改微信密码，accesstoken和refreshtoken失效，需要重新授权';
            break;
        case '43001' : $error = '需要GET请求';
            break;
        case '43002' : $error = '需要POST请求';
            break;
        case '43003' : $error = '需要HTTPS请求';
            break;
        case '43004' : $error = '需要接收者关注';
            break;
        case '43005' : $error = '需要好友关系';
            break;
        case '43019' : $error = '需要将接收者从黑名单中移除';
            break;
        case '44001' : $error = '多媒体文件为空';
            break;
        case '44002' : $error = 'POST的数据包为空';
            break;
        case '44003' : $error = '图文消息内容为空';
            break;
        case '44004' : $error = '文本消息内容为空';
            break;
        case '45001' : $error = '多媒体文件大小超过限制';
            break;
        case '45002' : $error = '消息内容超过限制';
            break;
        case '45003' : $error = '标题字段超过限制';
            break;
        case '45004' : $error = '描述字段超过限制';
            break;
        case '45005' : $error = '链接字段超过限制';
            break;
        case '45006' : $error = '图片链接字段超过限制';
            break;
        case '45007' : $error = '语音播放时间超过限制';
            break;
        case '45008' : $error = '图文消息超过限制';
            break;
        case '45009' : $error = '接口调用超过限制';
            break;
        case '45010' : $error = '创建菜单个数超过限制';
            break;
        case '45011' : $error = 'API调用太频繁，请稍候再试';
            break;
        case '45015' : $error = '回复时间超过限制';
            break;
        case '45016' : $error = '系统分组，不允许修改';
            break;
        case '45017' : $error = '分组名字过长';
            break;
        case '45018' : $error = '分组数量超过上限';
            break;
        case '45047' : $error = '客服接口下行条数超过上限';
            break;
        case '46001' : $error = '不存在媒体数据';
            break;
        case '46002' : $error = '不存在的菜单版本';
            break;
        case '46003' : $error = '不存在的菜单数据';
            break;
        case '46004' : $error = '不存在的用户';
            break;
        case '47001' : $error = '解析JSON/XML内容错误';
            break;
        case '48001' : $error = 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限';
            break;
        case '48002' : $error = '粉丝拒收消息（粉丝在公众号选项中，关闭了“接收消息”）';
            break;
		case '48003' : $error = '该公众号无权群发或suitetoken无效';
            break;
        case '48004' : $error = 'api接口被封禁，请登录mp.weixin.qq.com查看详情';
            break;
        case '48005' : $error = 'api禁止删除被自动回复和自定义菜单引用的素材';
            break;
        case '48006' : $error = 'api禁止清零调用次数，因为清零次数达到上限';
            break;
        case '50001' : $error = '用户未授权该api';
            break;
        case '50002' : $error = '用户受限，可能是违规后接口被封禁';
            break;
        case '61451' : $error = '参数错误(invalid parameter)';
            break;
        case '61452' : $error = '无效客服账号(invalid kf_account)';
            break;
        case '61453' : $error = '客服帐号已存在(kf_account exsited)';
            break;
        case '61454' : $error = '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid   kf_acount length)';
            break;
        case '61455' : $error = '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in     kf_account)';
            break;
        case '61456' : $error = '客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)';
            break;
        case '61457' : $error = '无效头像文件类型(invalid   file type)';
            break;
        case '61450' : $error = '系统错误(system error)';
            break;
        case '61500' : $error = '日期格式错误';
            break;
        case '65301' : $error = '不存在此menuid对应的个性化菜单';
            break;
        case '65302' : $error = '没有相应的用户';
            break;
        case '65303' : $error = '没有默认菜单，不能创建个性化菜单';
            break;
        case '65304' : $error = 'MatchRule信息为空';
            break;
        case '65305' : $error = '个性化菜单数量受限';
            break;
        case '65306' : $error = '不支持个性化菜单的帐号';
            break;
        case '65307' : $error = '个性化菜单信息为空';
            break;
        case '65308' : $error = '包含没有响应类型的button';
            break;
        case '65309' : $error = '个性化菜单开关处于关闭状态';
            break;
        case '65310' : $error = '填写了省份或城市信息，国家信息不能为空';
            break;
        case '65311' : $error = '填写了城市信息，省份信息不能为空';
            break;
        case '65312' : $error = '不合法的国家信息';
            break;
        case '65313' : $error = '不合法的省份信息';
            break;
        case '65314' : $error = '不合法的城市信息';
            break;
        case '65316' : $error = '该公众号的菜单设置了过多的域名外跳（最多跳转到3个域名的链接）';
            break;
        case '65317' : $error = '不合法的URL';
            break;
        case '9001001' : $error = 'POST数据参数不合法';
            break;
        case '9001002' : $error = '远端服务不可用';
            break;
        case '9001003' : $error = 'Ticket不合法';
            break;
        case '9001004' : $error = '获取摇周边用户信息失败';
            break;
        case '9001005' : $error = '获取商户信息失败';
            break;
        case '9001006' : $error = '获取OpenID失败';
            break;
        case '9001007' : $error = '上传文件缺失';
            break;
        case '9001008' : $error = '上传素材的文件类型不合法';
            break;
        case '9001009' : $error = '上传素材的文件尺寸不合法';
            break;
        case '9001010' : $error = '上传失败';
            break;
        case '9001020' : $error = '帐号不合法';
            break;
        case '9001021' : $error = '已有设备激活率低于50%，不能新增设备';
            break;
        case '9001022' : $error = '设备申请数不合法，必须为大于0的数字';
            break;
        case '9001023' : $error = '已存在审核中的设备ID申请';
            break;
        case '9001024' : $error = '一次查询设备ID数量不能超过50';
            break;
        case '9001025' : $error = '设备ID不合法';
            break;
        case '9001026' : $error = '页面ID不合法';
            break;
        case '9001027' : $error = '页面参数不合法';
            break;
        case '9001028' : $error = '一次删除页面ID数量不能超过10';
            break;
        case '9001029' : $error = '页面已应用在设备中，请先解除应用关系再删除';
            break;
        case '9001030' : $error = '一次查询页面ID数量不能超过50';
            break;
        case '9001031' : $error = '时间区间不合法';
            break;
        case '9001032' : $error = '保存设备与页面的绑定关系参数错误';
            break;
        case '9001033' : $error = '门店ID不合法';
            break;
        case '9001034' : $error = '设备备注信息过长';
            break;
        case '9001035' : $error = '设备申请参数不合法';
            break;
        case '9001036' : $error = '查询起始值begin不合法';
		default:$error = $error_code;
    }
    return $error;
}

/**
 * 正文内容图文处理（并将URL替换）
 * $content 内容字段的值
 **/
function optArtcons($content=''){
	preg_match_all('/<img.*? src=\"?(.*?)\"? data-latex=\"?(.*?\.(jpg|gif|bmp|bnp|png|mp4))\"?.*?>/i',$content,$match);
	$http_url=$match[1];//src的值
	$data_url=$match[2];//data-latex的值
	if(empty($match)||empty($http_url)||empty($data_url)){
		return $content;
	}
	//dump($match);die;
	foreach($http_url as $k=>$v){
		//将URL替换为微信平台返回的URL 
		$content = str_replace($v,$data_url[$k],$content); 
	}
	return $content;
}
?>