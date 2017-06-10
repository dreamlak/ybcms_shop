<?php
/**
 * 微信交互
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\index\controller;
use app\index\logic\UsersLogic;
use app\index\logic\CartLogic;
use think\Request;
use think\Db;
class LoginApi extends Base {
    public $config;
    public $oauth;
    public $class_obj;

    public function __construct(){
        parent::__construct();
        $this->oauth = input('oauth');
        //获取配置
        $data = Db::name('Plugin')->where("code",$this->oauth)->where("type","login")->find();
        $this->config = unserialize($data['config_value']); // 配置反序列化
        if(!$this->oauth){
            $this->error('非法操作',url('User/login'));
		}
        include_once  "plugins/login/{$this->oauth}/{$this->oauth}.class.php";
        $class = '\\'.$this->oauth; //
        $this->class_obj = new $class($this->config); //实例化对应的登陆插件
    }

    public function login(){
        if(!$this->oauth){
            $this->error('非法操作',url('User/login'));
		}
        include_once  "plugins/login/{$this->oauth}/{$this->oauth}.class.php";
        $this->class_obj->login();
    }
    
    public function callback(){
        $data = $this->class_obj->respon();
        $logic = new UsersLogic();
        if(session('?user')){
        	$res = $logic->oauth_bind($data);//已有账号绑定第三方账号
        	if($res['status'] == 1){
        		$this->success('绑定成功',url('User/index'));
        	}else{
        		$this->error('绑定失败',url('User/index'));
        	}
        }
        $data = $logic->thirdLogin($data);
        if($data['status'] != 1){
            $this->error($data['msg']);
		}
        session('user',$data['result']);
        setcookie('userid',$data['result']['userid'],null,'/');
        setcookie('is_distribut',$data['result']['is_distribut'],null,'/');
        $nickname = empty($data['result']['nickname']) ? '第三方用户' : $data['result']['nickname'];
        setcookie('uname',urlencode($nickname),null,'/');
        setcookie('cn',0,time()-3600,'/');
        // 登录后将购物车的商品的 userid 改为当前登录的id            
        $cartLogic = new CartLogic();
    	$cartLogic->login_cart_handle($this->session_id,$data['result']['userid']);  //用户登录后 需要对购物车 一些操作
        if(isMobile())
            $this->success('登陆成功',url('Mobile/User/index'));
        else
            $this->success('登陆成功',url('User/index'));
    }
}