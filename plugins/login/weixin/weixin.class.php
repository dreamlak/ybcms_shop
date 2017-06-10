<?php
use think\Model; 
use think\Request;
class weixin extends Model{
	//回调地址
	public $return_url;
	public $app_id='wxeb4efb7f83ce3f52';
	public $app_secret='14434ba78d5bd94a7385cc431bc6572b';
	public $access_token;
	public function __construct($config){
		$this->return_url = "http://".$_SERVER['HTTP_HOST']."/index.php/Home/LoginApi/callback/oauth/weixin";	
		$this->app_id = $config['app_id'];
		$this->app_secret = $config['app_secret'];
		
		if (empty($_COOKIE["_access_token"])){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->app_id."&secret=".$this->app_secret; 
            $res = $this->http_request($url); 
            $result = json_decode($res, true); 
            $this->access_token = $result["access_token"]; 
            setcookie("_access_token", $this->access_token, time()+7200);
        }else{
        	$this->access_token = $_COOKIE["_access_token"];
        }
	}
	//构造要请求的参数数组，无需改动
	public function login(){
		if (!isset($_GET["code"])){ 
	        $redirect_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
	        $jumpurl = $this->qrconnect($redirect_url, "snsapi_login", "123"); 
	        Header("Location: $jumpurl"); 
	    }else{ 
	        $oauth2_info = $this->oauth2_access_token($_GET["code"]); 
	        $userinfo = $this->oauth2_get_user_info($oauth2_info['access_token'], $oauth2_info['openid']); 
	        var_dump($userinfo); 
	    } 
	}
	//生成扫码登录的URL 
    public function qrconnect($redirect_url, $scope, $state = NULL) { 
        $url = "https://open.weixin.qq.com/connect/qrconnect?appid=".$this->app_id."&redirect_uri=".urlencode($redirect_url)."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect"; 
        return $url; 
    } 
 
    //生成OAuth2的Access Token 
    public function oauth2_access_token($code) { 
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->app_id."&secret=".$this->app_secret."&code=".$code."&grant_type=authorization_code"; 
        $res = $this->http_request($url); 
        return json_decode($res, true); 
    } 
 
    //获取用户基本信息（OAuth2 授权的 Access Token 获取 未关注用户，Access Token为临时获取） 
    public function oauth2_get_user_info($access_token, $openid) { 
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN"; 
        $res = $this->http_request($url); 
        return json_decode($res, true); 
    }
	
	public function http_request($url){
		return httpRequest($url,'post');
	}
}
?>
