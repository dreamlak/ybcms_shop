<?php
/**
 * 公共类  
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use think\Controller;
use think\Session;
use think\Db;

class MobileBase extends Controller {
    public $session_id;
    public $config;
    public $cateTrre = array();
    
    /*
     * 初始化操作
     */
    public function _initialize() {
        Session::start();
        header("Cache-control: private");  //history.back返回后输入框值丢失问题   
        $this->session_id = session_id(); //当前的 session_id
        define('SESSION_ID',$this->session_id); //将当前的session_id保存为常量，供其它方法调用
        
        define('MODULE',strtolower($this->request->module()));
    	define('CONTRO',strtolower($this->request->controller()));
    	define('ACTION',strtolower($this->request->action()));
    	
		$this->assign('module_name',MODULE);
		$this->assign('controller_name',CONTRO);
		$this->assign('action_name',ACTION);
		
        //判断当前用户是否手机                
        if(isMobile()){
            cookie('is_mobile','1',3600);
		}else{
            cookie('is_mobile','0',3600);
		}
		
		$this->config=config('config');//配置
		$this->assign('config', config('config'));
		
        //微信浏览器
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') && empty($_SESSION['openid'])){
            $wxuser = $this->GetOpenid(); //授权获取openid以及微信用户信息
            session('subscribe', $wxuser['subscribe']);// 当前这个用户是否关注了微信公众号
            
            //微信自动登录                             
            $logic = new UsersLogic();
            $data = $logic->thirdLogin($wxuser);                                
            
            if($data['status'] == 1){
                session('user',$data['result']);
                setcookie('userid',$data['result']['userid'],null,'/');
                setcookie('is_distribut',$data['result']['is_distribut'],null,'/');//is_distribut=是否为分销商 0 否 1 是
                setcookie('uname',$data['result']['nickname'],null,'/');
                //登录后将购物车的商品的 userid 改为当前登录的id
                Db::name('cart')->where("session_id", $this->session_id)->update(array('userid'=>$data['result']['userid']));
                $cartLogic = new \app\home\logic\CartLogic();
                $cartLogic->login_cart_handle($this->session_id,$data['result']['userid']);  //用户登录后 需要对购物车 一些操作
            }
           
            //微信Jssdk 操作类 用分享朋友圈 JS
            //$jssdk = new \app\mobile\logic\Jssdk($this->weixin_config['appid'], $this->weixin_config['appsecret']);
            //$signPackage = $jssdk->GetSignPackage();
            
            $signPackage=getJsApi();
        	$this->assign('signPackage', $signPackage);
        }
		$this->setModName();
        $this->public_assign();
    }
    //模块名
    public function setModName($title=''){
        $this->assign('mod_name', $title);
    }
    /**
     * 保存变量 
     */   
    public function public_assign(){
       $goods_category_tree = get_goods_category_tree();
	   
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);                     
       $brand_list = Db::name('brand')->cache(true,CACHE_TIME)->field('id,cat_id,logo,is_hot')->where("cat_id>0")->select();              
       $this->assign('brand_list', $brand_list);
	   
	   if(session('?user')) {
            $user = session('user');
            $user = Db::name('member')->where("userid", $user['userid'])->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->userid = $user['userid'];
            $this->assign('user', $user); //存储用户信息
        }
    }      

    // 网页授权登录获取 OpendId
    public function GetOpenid(){
        if($_SESSION['openid'])return $_SESSION['openid'];
		
		$oauthData=get_oauth_AccessToken();//静默授权获取access_token （snsapi_userinfo，snsapi_base）
		$wxuser=getOauthUser($oauthData['access_token'], $oauthData['openid']);//拉取用户信息

		$oauthData['nickname'] = empty($wxuser['nickname']) ? '微信用户' : deal_emoji(trim($wxuser['nickname']),0);
        $oauthData['sex'] = $wxuser['sex'];
        $oauthData['avatar'] = $wxuser['headimgurl']; 
        $oauthData['oauth'] = 'weixin';//授权类型
        if(isset($wxuser['unionid'])){
        	$oauthData['unionid'] = $wxuser['unionid'];
        }
		$gu=getUserInfo($oauthData['openid']);//获取用户基本信息（包括UnionID机制）
        $oauthData['subscribe'] = $gu['subscribe'];//是否关注（1关注，0没关注）
        $oauthData['first_leader']=$_GET['state'];
		
	 	$_SESSION['openid'] = $wxuser['openid'];

        return $oauthData;
    }
    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj){
        $buff = '';
        foreach ($urlObj as $k => $v){
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    public function ajaxReturn($data){
        exit(json_encode($data));
    }

}