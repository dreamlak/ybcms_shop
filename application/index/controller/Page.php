<?php
/*
 * @name  单页
 * @time on 2016/09/20
 * @Author  dreamlak   dreamlak@qq.com
 */
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
class Page extends CommonBase{
    public function index(){
    	$catid=input('catid');
		//栏目信息
		$catinfo=Db::name('article_cat')->where('catid',$catid)->find();
		
		//单页详情
		$info=Db::name('page')->where('catid',$catid)->find();
		$template=$catinfo['pagetpl'];
		if($template==''){
			$template='page';                                 
		}
		$this->assign('info',$info);
		//标题
		if($info['title']==''){
			$title=$catinfo['catname'];
		}else{
			$title=$info['title'];
		}
		$this->setModName($title);
		$this->assign("catname", $title);
		$this->assign("catid", $catid);
		
		//浏览量
		Db::name('page')->where('catid',$catid)->setInc('hot',1);
		
		//二级栏目
		$catlist=model('Article')->getCatLists();
		$this->assign("catlist", $catlist);
		
    	return $this->fetch('page/'.$template);
    }
}
