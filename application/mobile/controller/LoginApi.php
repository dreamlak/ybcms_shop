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
namespace app\mobile\controller;
use app\home\logic\UsersLogic;
use app\home\logic\CartLogic;
use think\Request;
class LoginApi extends MobileBase{
    public $config;
    public $oauth;
    public $class_obj;

    public function __construct(){
        parent::__construct();    
        $this->oauth = input('get.oauth');
        //获取配置
        $data = Db::name('Plugin')->where("code=:code and  type = 'login' ")->bind(['code'=>$this->oauth])->find();
        $this->config = unserialize($data['config_value']); // 配置反序列化
        if(!$this->oauth){
            $this->error('非法操作',url('Home/User/login'));
		}
        include_once  "plugins/login/{$this->oauth}/{$this->oauth}.class.php";
        $class = '\\'.$this->oauth; //
        $login = new $class($this->config); //实例化对应的登陆插件
        $this->class_obj = $login;
    }

    public function login(){
        if(!$this->oauth)
            $this->error('非法操作',url('Home/User/login'));
        include_once  "plugins/login/{$this->oauth}/{$this->oauth}.class.php";
        $this->class_obj->login();
    }

    public function callback(){
        $data = $this->class_obj->respon();
        $logic = new UsersLogic();
        $data = $logic->thirdLogin($data);
        if($data['status'] != 1)
            $this->error($data['msg']);
        session('user',$data['result']);
        // 登录后将购物车的商品的 user_id 改为当前登录的id            
        Db::name('cart')->where("session_id", $this->session_id)->update(array('userid'=>$data['result']['userid']));
        $cartLogic = new CartLogic();
        $cartLogic->login_cart_handle($this->session_id,$data['result']['userid']);  //用户登录后 需要对购物车 一些操作
        
        $this->success('登陆成功',url('Mobile/User/index'));
    }
}