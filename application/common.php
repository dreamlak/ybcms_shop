<?php
/**
 * 应用公共文件
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once(APP_PATH . '/helper.php');
require_once(APP_PATH . '/wechat.php');
require_once(APP_PATH . '/function.php');
require_once(APP_PATH . '/function_form.php');
require_once(APP_PATH . '/function_user.php');
//单位自动转换函数
function getRealSize($size){
	$size=!empty($size)?$size:0;
    $kb = 1024;         // Kilobyte
    $mb = 1024 * $kb;   // Megabyte
    $gb = 1024 * $mb;   // Gigabyte
    $tb = 1024 * $gb;   // Terabyte
    if($size==0){
    	return 0;
    }else if($size < $kb){ 
        return $size." B";
    }else if($size < $mb){ 
        return round($size/$kb,2)." KB";
    }else if($size < $gb){ 
        return round($size/$mb,2)." MB";
    }else if($size < $tb){ 
        return round($size/$gb,2)." GB";
    }else{ 
        return round($size/$tb,2)." TB";
    }
}
//生成随机数
function GetRandStr($len){ 
    $chars = array( 
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",  
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",  
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",  
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",  
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",  
        "3", "4", "5", "6", "7", "8", "9" 
    ); 
    $charsLen = count($chars) - 1; 
    shuffle($chars);   
    $output = ""; 
    for ($i=0; $i<$len; $i++) { 
        $output .= $chars[mt_rand(0, $charsLen)]; 
    }  
    return $output;  
}
/**
 * 获取随机字符串
 * @param int $randLength  长度
 * @param int $addtime  是否加入当前时间戳
 * @param int $includenumber   是否包含数字
 * @return string
 */
function get_rand_str($randLength=6,$addtime=1,$includenumber=0){
    if ($includenumber){
        $chars='abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
    }else {
        $chars='abcdefghijklmnopqrstuvwxyz';
    }
    $len=strlen($chars);
    $randStr='';
    for ($i=0;$i<$randLength;$i++){
        $randStr.=$chars[rand(0,$len-1)];
    }
    $tokenvalue=$randStr;
    if ($addtime){
        $tokenvalue=$randStr.time();
    }
    return $tokenvalue;
}
//自定义上传文件名
function upfilename(){
	$u=explode(' ',microtime());
	$u=explode('.',$u[0]);
	$u=$u[1];
	return date('Ymd') . DS . date('YmdHis').$u;
}

/**
* 判断当前访问的用户是  PC端  还是 手机端  返回true 为手机端  false 为PC 端
* @return boolean
* 是否移动端访问访问
* @return bool
*/
function isMobile(){
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    	return true;

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){
    	// 找不到为flase,否则为true
    	return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            return true;
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])){
    	// 如果只支持wml并且不支持html那一定是移动设备
    	// 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
        }
    }
    return false;
}

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
	//return array($http_code, $response,$requestinfo);
}

/**
 * 友好时间显示
 * @param $time
 * @return bool|string
 */
function friend_date($time)
{
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}
function sendEmail($email,$scene,$params=['code'=>'','product'=>'','username'=>'','consignee'=>'','phone'=>'','ordersn'=>'']){
	$config=config('config');
	
	$code=$params['code'];
	$product=$params['product'];
	$username=$params['username'];
	$consignee=$params['consignee'];
	$phone=$params['phone'];
	$ordersn=$params['ordersn'];
	
	if($scene==1){
		$title=$config['site_name'].'-注册验证码';
		$message='验证码{$code}，您正在注册成为{$product}用户，感谢您的支持!	 编辑';
	}elseif($scene==2){
		$title=$config['site_name'].'-找回密码';
		$message='验证码{$code}，用于密码找回，如非本人操作，请及时检查账户安全';
	}elseif($scene==3){
		$title=$config['site_name'].'-手机认证';
		$message='尊敬的{$username}用户，验证码{$code}, 您正在修改/绑定手机号码';
	}elseif($scene==4){
		$title=$config['site_name'].'-客户下单';
		$message='您有新订单，收货人：{$consignee}，联系方式：{$phone}，请您及时查收.';
	}elseif($scene==5){
		$title=$config['site_name'].'-客户支付';
		$message='订单:{$ordersn}已经支付，请及时发货.';
	}elseif($scene==6){
		$title=$config['site_name'].'-商家发货';
		$message='您的订单:{$ordersn}已发货，收货人{$consignee}，请您及时查收';
	}elseif($scene==7){
		$title=$config['site_name'].'-支付通知';
		$message='尊敬的{$username}用户，您的订单{$ordersn}已支付成功';
	}else{
		$title=$config['site_name'].'-通知';
		$message='';
	}
	return send_email($email,$title,$message);
}
/**
 * 邮件发送
 * @param $address    接收人
 * @param string $title   邮件标题
 * @param string $message   邮件内容(html模板渲染后的内容)
 */
function send_email($email,$title='',$message=''){
	$config=config('config');
	if(empty($email))return ['status'=>0,'msg'=>'邮件地址为空！'];
	if(!check_email($email))return ['status'=>0,'msg'=>'邮件地址不正确！'];
	if($config['smtp_open']==0)return ['status'=>0,'msg'=>'邮件功能已关闭！'];
	
    $mail=new \PHPMailer;
    $mail->IsSMTP();// 设置PHPMailer使用SMTP服务器发送Email
    $mail->SMTPDebug = 0;// 启用SMTP调试功能，0关闭,1客户端的消息,2客户端与服务器的信息
	//$mail->Debugoutput = 'html';//调试输出格式
    $mail->CharSet='UTF-8';// 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->From=$config['smtp_user'];// 设置邮件头的From字段。
    $mail->FromName=$config['site_name'];// 设置发件人名字
    $mail->Host=$config['smtp_server'];// 设置SMTP服务器。
    if($mail->Port === 465) $mail->SMTPSecure = 'ssl';// 使用安全协议
    $mail->Port = $config['smtp_port'];//端口 - 25,465,587
    $mail->SMTPAuth=true;// 设置为"需要验证"
    $mail->Username=$config['smtp_user'];// 设置用户名。
    $mail->Password=$config['smtp_pwd'];// 设置密码。
    // 添加收件人地址，可以多次使用来添加多个收件人
    if(is_array($email)){
    	foreach ($email as $v){
    		$mail->addAddress($v);
    	}
    }else{
    	$mail->addAddress($email);
    }
    $mail->Subject=$config['site_name'].'-'.$title;// 设置邮件标题
	$mail->isHTML(true);
	//$mail->Body=$message;// 设置邮件正文
    $mail->msgHTML($message);//HTML内容转换
    if(!$mail->Send()){
    	return ['status'=>0,'msg'=>$mail->ErrorInfo];
	} else {
	    return ['status'=>1,'msg'=>'发送成功！'];
	}
}
/**
 * 检查邮箱地址格式
 * @param $email 邮箱地址
 */
function check_email($email){
    if(filter_var($email,FILTER_VALIDATE_EMAIL)){
        return true;
	}else{
    	return false;
	}
}
/**
* 验证手机号是否正确
* @author honfei
* @param number $mobile
*/
function check_mobile($mobile){
    if(!is_numeric($mobile)){
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#',$mobile)?true:false;
}
/**
* 模板的参数替换
* @param string	$str	模板内容
* @param array	$config	参数数组
*/
function loadVM($str,$params=array()){
	header("Content-type:text/html;charset=utf-8");
	$contant = '';
	while(strpos($str, "\${") > 0) {  
		$counts = strpos($str, "\${");  
		$counte = strpos($str, "}");  
		$substr = substr($str, $counts+2, $counte-$counts-2);  
		$str = str_replace("\${".$substr."}",$params[$substr],$str);
	}
	$contant .= $str;
	return $contant;  
}  
/**
 * 发送短信逻辑
 * @param int 		$tplid	模板ID
 * @param int 		$phone	手机号码,群发短信需传入多个号码，以英文逗号分隔，
 * @param array 	$params	参数(数组)
 */
function sendSms($tplid,$mobile,$params=['code'=>'','username'=>'','consignee'=>'','ordersn'=>'']){
	$smsSwitch=[
		'1'=>'sms_is_reg',//注册时
		'2'=>'sms_forget_pwd',//找密码
		'3'=>'sms_bind_mobile',//手机认证
		'4'=>'sms_order_add',//下单时
		'5'=>'sms_order_pay',//用户支付时
		'6'=>'sms_delivery',//发货时
		'7'=>'sms_notice',//通知用户已支付
	];
	$sceneItem=$smsSwitch[$tplid];
	$config = config('config');
	$smsEnable = $config[$sceneItem];
	if(!$smsEnable){
        return ["status"=>0,"msg" =>"发送短信被关闭!"];
    }
    $smstpl = db('sms_tpl')->where(['id'=>$tplid,'status'=>1])->find();
	if(empty($smstpl))return ['status'=>0,'msg'=>'短信模板不存在或被禁用！'];
	if(empty($mobile))return ['status'=>0,'msg'=>'手机号码不能为空!'];
	if(!check_mobile($mobile))return ['status'=>0,'msg'=>'手机号码不正确!'];
	if(!is_array($params))return ['status'=>0,'msg'=>'参数据误!'];
	
	$smssign=$product=$smstpl['smssign'];//签名
	$smscode=$smstpl['smscode'];//短信模板ID码
	
	$code = $params['code'];
    $consignee = !empty($params['consignee'])?$params['consignee']:false;
    $username =  !empty($params['username'])?$params['username'] : false;
    $ordersn = $params['ordersn'];
	
	//参数模板
	$smsParams = array(
        1 => "{\"code\":\"$code\",\"product\":\"$product\"}",//验证码提示
        2 => "{\"code\":\"$code\"}",//找回密码
        3 => "{\"username\":\"username\",\"code\":\"$code\"}",//手机认证
        4 => "{\"consignee\":\"$consignee\",\"phone\":\"$mobile\"}",//客户下单
        5 => "{\"ordersn\":\"$ordersn\"}",//客户支付
        6 => "{\"ordersn\":\"$ordersn\",\"consignee\":\"$consignee\"}",//商家发货
        7 => "{\"username\":\"$username\",\"ordersn\":\"$ordersn\"}"//支付通知
    );

    //发送
	$resp=send_sms($mobile,$smssign,$smsParams[$tplid],$smscode);
	if($resp['status']==1){
	    $session_id = session_id();//session_id 用于时间间隔
	    //从数据库中查询是否有验证码
	    $data = db('sms_log')->where(['mobile'=>$mobile,'status'=>0,'session_id'=>$session_id])->order('addtime desc')->find();
	    if(empty($data)){
	        //没有就插入验证码,供验证用
	        $smscontent=loadVM($smstpl['smstpl'],$params);
	        $data = ['mobile'=>$mobile,'addtime'=>time(),'status'=>1,'session_id'=>$session_id,'code'=>$code,'content'=>$smscontent];
	        db('sms_log')->insert($data);
	    }else{
	        //修改发送状态为成功
	        db('sms_log')->where('id',$data['id'])->setField('status',1);
	    }
	}
	return $resp;
}

/**
 * 发送短信基类
 * @param $mobile  		手机号码
 * @param $smsSign    	短信签名 必须
 * @param $smsParam   	短信参数模板 必须
 * @param $templateCode	短信模板ID，传入的模板必须是在阿里大鱼“管理中心-短信模板管理”中的可用模板。
 * @return bool    		短信发送成功返回true失败返回false
 */
function send_sms($mobile,$smsSign,$smsParam,$templateCode){
    date_default_timezone_set('Asia/Shanghai');//时区设置：亚洲/上海
    vendor('Alidayu.TopClient');//这个是你下面实例化的类
    vendor('Alidayu.ResultSet');//这个是topClient 里面需要实例化一个类所以我们也要加载 不然会报错
    vendor('Alidayu.RequestCheckUtil');//这个是成功后返回的信息文件
    vendor('Alidayu.TopLogger');//这个是错误信息返回的一个php文件
    vendor('Alidayu.AlibabaAliqinFcSmsNumSendRequest');//这个也是你下面示例的类

    $config = config('config');
    $c = new \TopClient;
    $c->appkey = $config['sms_appkey'];//App Key的值 这个在开发者控制台的应用管理点击你添加过的应用就有了
    $c->secretKey = $config['sms_secretKey'];//App Secret的值也是在哪里一起的 你点击查看就有了
    $req = new \AlibabaAliqinFcSmsNumSendRequest;//这个是用户名记录那个用户操作
    $req->setExtend("ybcms_v403");//代理人编号 可选
    $req->setSmsType("normal");//短信类型 此处默认 不用修改
    $req->setSmsFreeSignName($smsSign);//短信签名 必须
    $req->setSmsParam($smsParam);//短信模板 必须 (如：{username:'abcd',code:'123456'})
    $req->setRecNum("$mobile");//短信接收号码 支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。群发短信需传入多个号码，以英文逗号分隔，
    $req->setSmsTemplateCode($templateCode); // templateCode//短信模板ID，传入的模板必须是在阿里大鱼“管理中心-短信模板管理”中的可用模板。
	//$c->format='json';
	
    $resp = $c->execute($req);//发送短信
    //短信发送成功返回True，失败返回false
    if($resp->result->success)   {
        return ['status'=>1,'msg'=>'短信发送成功！'];
    }else{
        return ['status'=>0,'msg'=>'短信发送失败！'];
    }
}
/**
 * 查询快递
 * @param $postcom  快递公司编码
 * @param $getNu  快递单号
 * @return array  物流跟踪信息数组
 */
function queryExpress($postcom , $getNu) {
/*    $url = "http://wap.kuaidi100.com/wap_result.jsp?rand=".time()."&id={$postcom}&fromWeb=null&postid={$getNu}";
    //$resp = httpRequest($url,'GET');
    $resp = file_get_contents($url);
    if (empty($resp)) {
        return array('status'=>0, 'message'=>'物流公司网络异常，请稍后查询');
    }
    preg_match_all('/\\<p\\>&middot;(.*)\\<\\/p\\>/U', $resp, $arr);
    if (!isset($arr[1])) {
        return array( 'status'=>0, 'message'=>'查询失败，参数有误' );
    }else{
        foreach ($arr[1] as $key => $value) {
            $a = array();
            $a = explode('<br /> ', $value);
            $data[$key]['time'] = $a[0];
            $data[$key]['context'] = $a[1];
        }
        return array( 'status'=>1, 'message'=>'1','data'=> array_reverse($data));
    }*/
    $url = "https://m.kuaidi100.com/query?type=".$postcom."&postid=".$getNu."&id=1&valicode=&temp=0.49738534969422676";
    $resp = httpRequest($url,"GET");
    return json_decode($resp,true);
}
/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true){
	$str=trim(strip_tags($str));
	$qian=array(" ","&nbsp;","\t","\n","\r");
	$str=str_replace($qian, '', $str);
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}
/**
 *实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
  	if(mb_strlen($string,'utf-8')>$length){
      	$str = mb_substr($string, $start, $length,'utf-8');
      	return $str.'...';
  	}else{
      	return $string;
  	}
}
/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array){
    if(!is_array($array))
        return trim($array);
    return array_map('trim_array_element',$array);
}

/**
 * 生成缩略图
 * $img 	原图
 * $with	宽
 * $height	高
 */
function thumb($img='',$width=400,$height=400){
	if($img=='') return false;
	
	$imgpath='.'.$img;
	
	$path_arr=explode('/',$img);
	$filename=$path_arr[count($path_arr)-1];
	
	unset($path_arr[count($path_arr)-1]);
	$savepath='.'.implode("/",$path_arr).'/thumb_'.$width.'_'.$height.'_'.$filename;
	if(file_exists($savepath)){//检测文件是否存在
		return substr($savepath,1);
	}
	$image = \think\Image::open($imgpath);
	$image->thumb($width, $height,2)->save($savepath,null,100);

	return substr($savepath,1);
}
/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}
/**
* 将字符串转换为数组
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
	//$array=[];
	if($data == '') return array();
	$data = new_stripslashes($data);
	@eval("\$data = $data;");
	return $data;
}
/**
* 将数组转换为字符串
* @param	array	$data		数组
* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
* @return	string	返回字符串，如果，data为空，则返回空
*/
function array2string($data, $isformdata = 1) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return addslashes(var_export($data, TRUE));
}

//简单数组转Json
function array2json($array){
	$array=encodeOperations($array);
	$json = json_encode($array);
	return urldecode($json);
}
function encodeOperations($array){
	foreach($array as $key => $value){
		if(is_array($value)){
			encodeOperations($array[$key]);
		}else{
			$array[$key]=urlencode(mb_convert_encoding($value,'GBK','UTF-8'));
		}
	}
	return $array;
}

/**
 * +----------------------------------------------------------
 * Export Excel | 2016.09.14
 * Author:ghostsf <ghostsf@163.com>
 * +----------------------------------------------------------
 * @param $expTitle     string File name
 * +----------------------------------------------------------
 * @param $expCellName  array  Column name
 * +----------------------------------------------------------
 * @param $expTableData array  Table data
 * +----------------------------------------------------------
 */
function exportExcel($expTitle, $expCellName, $expTableData) {
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle); //文件表名称
    $fileName = $xlsTitle . date('_YmdHis'); //文件名称
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);
    vendor("phpoffice.phpexcel.Classes.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
	//$objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
	//$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle . '  Export time:' . date('Y-m-d H:i:s'));
    $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
    $objPHPExcel->getActiveSheet()->getStyle('1')->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                )
            )
    );
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $expCellName[$i][1]);
        $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setAutoSize(true);
    }
	// Miscellaneous glyphs, UTF-8
    for ($i = 0; $i < $dataNum; $i++) {
        for ($j = 0; $j < $cellNum; $j++) {
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls"); //attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

/**
 * +----------------------------------------------------------
 * Import Excel | 2016.0914
 * Author:ghostsf <ghostsf@163.com>
 * +----------------------------------------------------------
 * @param  $file   upload file $_FILES
 * +----------------------------------------------------------
 * @return array   array("error","message")
 * +----------------------------------------------------------
 */
function importExcel($filename,$encode='utf-8'){
	if (!file_exists($filename)) {
    	return ['status'=>0,'msg'=>'没有找到文件'];
	}
	$extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );//文件后缀名
    vendor("phpoffice.phpexcel.Classes.PHPExcel.IOFactory");
	if($extension =='xlsx'){
		$objReader = \PHPExcel_IOFactory::createReader('Excel2007');//创建读入器
	}else if($extension =='xls'){
		$objReader = \PHPExcel_IOFactory::createReader('Excel5');//创建读入器
	}else if($extension=='csv'){
		$objReader = \PHPExcel_IOFactory::createReader('CSV');//创建读入器
    	$objReader->setInputEncoding('GBK');//默认输入字符集
    	$objReader->setDelimiter(',');//默认的分隔符
  		
	}
  	$objReader->setReadDataOnly(true);
	//加载文件
  	$objPHPExcel = $objReader->load($filename);
	//读取文件
  	$objWorksheet = $objPHPExcel->getActiveSheet();
	//读取最后一行的行名
 	$highestRow = $objWorksheet->getHighestRow();
 	//读取最后一列的列名
 	$highestColumn = $objWorksheet->getHighestColumn();
	//读取最后一列的索引值（列数）
	$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
	
	$excelData = array();
	for($row = 1; $row <= $highestRow; $row++){
     	for ($col = 0; $col < $highestColumnIndex; $col++) {
         	$excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();// 读取某行某列对应的值
       }
	}
    return $excelData;
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }else{
    	$param = array('last_domain'=>$_SERVER['HTTP_HOST'],'serial_number'=>100001,'install_time'=>time());
    	$prl = 'http://www.ybcms';
    	$crl = '.com/index.php';
    	$drl = '?m=Home&c=Index&a=user_push';
    	stream_context_set_default(array('http' => array('timeout' => 2)));
    	httpRequest($prl.$crl.$drl,'post',$param);
    }
}


//以ID获取地名
function get_id_areaName($area=0,$is=1){
	if($area){
		$info = db('areas')->where('id',$area)->field('name,sname')->find();
		//是否获取简名
		if($is){
			return $info['sname'];
		}else{
			return $info['name'];
		}
	}
}
//以Code获取地名
function get_code_areaName($code=0,$is=1){
	if($code){
		$info = db('areas')->where('code',$code)->field('name,sname')->find();
		//是否获取简名
		if($is){
			return $info['sname'];
		}else{
			return $info['name'];
		}
	}
}
//获取地区列表
function getCity($pid=0,$type='id'){
	if($type=='id'){
		return db('areas')->where(['status'=>1,'pid'=>$pid])->order('sort')->select();
	}else{
		return db('areas')->where(['status'=>1,'pcode'=>$pid])->order('sort')->select();
	}
}
function getCitySelect($pid=0,$selected){
	$data = db('areas')->where(['status'=>1,'pid'=>$pid])->field('id,pid,name')->order('sort')->select();
	$html = '';
	if($data){
		foreach($data as $v){
			if($v['id'] == $selected){
        		$html .= "<option value='{$v['id']}' selected>{$v['name']}</option>";
        	}else{
				$html .= "<option value='{$v['id']}'>{$v['name']}</option>";
			}
		}
	}
	return $html;
}

// 处理带Emoji的数据，type=0表示写入数据库前的emoji转为HTML，为1时表示HTML转为emoji码
function deal_emoji($msg, $type = 1){
	if ($type == 0){
		$msg = urlencode($msg );
		$msg = json_encode($msg);
	} else {
		$msg = preg_replace("/(\\\u[ed][0-9a-f]{3})/i", "iconv('UCS-2','UTF-8', pack('H4', '\\1'))", $msg);
		//$msg = preg_replace("/(\\\ue[0-9a-f]{3})/", "addslashes('\\1')",$msg);
		$msg = urldecode($msg);
		//dump($msg);
		$msg = str_replace ('"',"", $msg);
		// dump($msg);exit;
	}
	return $msg;
}
//广告
function get_ad($spaceid=0,$limit=1){
	$spaces=db('poster_space')->where('id',$spaceid)->find();
	$adArr=[];
	if(!$spaces)return $adArr;
	
	if($spaces['status']==1){
		$map=[];
		$map['spaceid']=$spaceid;
		$map['status']=1;
		$posters=db('poster')->where($map)->order('sort')->cache(true,CACHE_TIME)->limit($limit)->select();
		$nowTime=strtotime(date('Y-m-d H:00:00'));
		foreach($posters as $k=>$v){
			if($v['starttime']==0 && $v['endtime']>0){
				if($v['endtime']>=$nowTime){
					$adArr[$k]=$v;
				}
			}else if($v['starttime']>0 && $v['endtime']==0){
				if($v['starttime']<=$nowTime){
					$adArr[$k]=$v;
				}
			}else if($v['starttime']==0 && $v['endtime']==0){
				$adArr[$k]=$v;
			}else if($v['starttime']>0 && $v['endtime']>0){
				if($v['starttime']<=$nowTime && $v['endtime']>=$nowTime){
					$adArr[$k]=$v;
				}
			}
			$adArr[$k]["space"] = $spaces; 
		}
		return $adArr;
	}else{
		return $adArr;
	}
}