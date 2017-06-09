<?php
/*
 * @name  前台公用类
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use \think\Db;
class CommonBase extends Controller{
	public $config;
   	public function _initialize(){
   		$this->config=config('config');
		$this->assign('config',$this->config);

   		define('MODULE',strtolower($this->request->module()));
    	define('CONTRO',strtolower($this->request->controller()));
    	define('ACTION',strtolower($this->request->action()));
    	
		$this->assign('MODULE',MODULE);
		$this->assign('CONTRO',CONTRO);
		$this->assign('ACTION',ACTION);
		
		$this->assign('user',session('user'));
		$this->assign('catid',input('catid'));
		$this->assign('artid',input('artid'));
		
		$this->setWebTitle();
		$this->setWapKeywords();
		$this->setWapDescription();
		$this->setModName();
		
		if($this->config['site_open']==0){
			echo $this->fetch('Index/close_site');
			exit;
		}
    }
	//空操作
    public function _empty(){
    	$this->error("链接错误，访问不到该内容！");
    }
	//模块名
    public function setModName($title='首页'){
        $this->assign('mod_name', $title);
    }
	//网站标题
	public function setWebTitle($title=''){
		if($title==''){
			$title=$this->config['site_name'];
		}
        $this->assign('site_name', $title);
    }
	//网站关键字
    public function setWapKeywords($keywords=''){
    	if($keywords==''){
			$keywords=$this->config['site_key'];
		}
        $this->assign('site_key', $keywords);
    }
	//网站描述
    public function setWapDescription($description=''){
    	if($description==''){
			$description=$this->config['site_desc'];
		}
        $this->assign('site_desc', $description);
    }
}
