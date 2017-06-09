<?php
/*
 * @name  首页
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Index extends CommonBase{
    public function index(){
    	//如果是手机跳转到 手机模块
        if(true == isMobile()){
            header("Location: ".url('Mobile/Article/index'));
        }
    	//dump(config());die;
    	return view();
    }
}
