<?php
/**
 * 公共
 * ============================================================================
 * 版权所有 Ybcms开发团队，并保留所有权利
 * 网站地址: http://www.ybcms.com
 * ============================================================================
 * Author: ybcms(dreamlak@qq.com)      
 * UpDate: 2017-03-01
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\response\Json;
use think\Session;

class Base extends Controller {
    public $session_id;
    public $cateTrre = array();
    /*
     * 初始化操作
     */
    public function _initialize() {
        Session::start();
        header("Cache-control: private");
    	$this->session_id = session_id();//当前的 session_id
        define('SESSION_ID',$this->session_id);//将当前的session_id保存为常量，供其它方法调用
        
        //判断当前用户是否手机                
        if(isMobile()){
            cookie('is_mobile','1',3600); 
        }else{
            cookie('is_mobile','0',3600);
		}
		
		define('MODULE_NAME',$this->request->module());  // 当前模块名称是
        define('CONTROLLER_NAME',$this->request->controller()); // 当前控制器名称
        define('ACTION_NAME',$this->request->action()); // 当前操作名称是
        
        $this->public_assign(); 
    }
    /**
     * 保存公告变量到 smarty中 比如 导航 
     */
    public function public_assign(){
       $goods_category_tree = get_goods_category_tree();    
       $this->cateTrre = $goods_category_tree;
       $this->assign('goods_category_tree', $goods_category_tree);
	   
       $brand_list = Db::name('brand')->cache(true,CACHE_TIME)->field('id,parent_cat_id,logo,is_hot')->where("parent_cat_id>0")->select();              
       $this->assign('brand_list', $brand_list);
	   
       $this->assign('config', config('config'));
	   $user=!empty(session('user'))?session('user'):['username'=>'匿名'];
	   $this->assign('user', $user);
    }
	
    public function ajaxReturn($data){                        
        exit(json_encode($data)); 
    }
}